<?php

namespace App\Http\Controllers;

use App\Models\Peminjaman;
use App\Models\Pengembalian;
use App\Models\Alat;
use App\Models\DetailPinjam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;
use Barryvdh\DomPDF\Facade\Pdf;

class PetugasController extends Controller
{
    // Menampilkan daftar pengajuan peminjaman
    public function indexPeminjaman(Request $request)
    {
        $search = $request->input('search');

        $peminjamans = Peminjaman::with(['user', 'detailPinjam.alat'])
            ->whereIn('status', ['diajukan', 'Diajukan'])
            ->when($search, function ($query, $search) {
                return $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        if (view()->exists('petugas.peminjaman.index')) {
            return view('petugas.peminjaman.index', compact('peminjamans', 'search'));
        }

        return view('admin.peminjaman.index', compact('peminjamans', 'search'));
    }

    // Menyetujui Peminjaman (Ubah status & kurangi stok)
    public function setujuPeminjaman($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $peminjaman = Peminjaman::with('detailPinjam')
                    ->lockForUpdate()
                    ->findOrFail($id);

                if (strtolower($peminjaman->status) !== 'diajukan') {
                    throw new Exception(
                        'Peminjaman ini sudah diproses sebelumnya.'
                    );
                }

                if ($peminjaman->detailPinjam->isEmpty()) {
                    throw new Exception(
                        'Peminjaman tidak memiliki detail alat.'
                    );
                }

                foreach ($peminjaman->detailPinjam as $detail) {
                    $alat = Alat::lockForUpdate()
                        ->findOrFail($detail->alat_id);

                    if ($detail->jumlah < 1 || $alat->stok < $detail->jumlah) {
                        throw new Exception(
                            "Stok alat {$alat->nama_alat} tidak mencukupi."
                        );
                    }

                    $alat->decrement('stok', $detail->jumlah);
                }

                $peminjaman->update([
                    'status' => 'dipinjam',
                ]);
            });

            return redirect()->back()->with(
                'success',
                'Peminjaman disetujui dan stok alat berhasil diperbarui.'
            );
        } catch (Exception $e) {
            return redirect()->back()->with(
                'error',
                'Gagal menyetujui peminjaman: ' . $e->getMessage()
            );
        }
    }

    // Menolak Peminjaman (Hapus pengajuan)
    public function tolakPeminjaman($id)
    {
        try {
            $peminjaman = Peminjaman::findOrFail($id);

            if (in_array(strtolower($peminjaman->status), ['diajukan'])) {
                $peminjaman->delete();

                return redirect()->back()->with(
                    'success',
                    'Pengajuan peminjaman berhasil ditolak.'
                );
            }

            return redirect()->back()->with(
                'error',
                'Status peminjaman sudah berubah.'
            );
        } catch (Exception $e) {
            return redirect()->back()->with(
                'error',
                'Terjadi kesalahan: ' . $e->getMessage()
            );
        }
    }

    // Menampilkan daftar pengembalian
    public function indexPengembalian(Request $request)
    {
        $search = $request->input('search');

        $pengembalians = Pengembalian::with([
            'peminjaman.user',
            'petugas',
            'peminjaman.detailPinjam.alat'
        ])
            ->when($search, function ($query, $search) {
                return $query->whereHas('peminjaman.user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })
                ->orWhere('kondisi_kembali', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        if (view()->exists('petugas.pengembalian.index')) {
            return view('petugas.pengembalian.index', compact('pengembalians', 'search'));
        }

        return view('admin.pengembalian.index', compact('pengembalians', 'search'));
    }

    // Menampilkan form tambah pengembalian
    public function createPengembalian()
    {
        $peminjamans = Peminjaman::with([
            'user',
            'detailPinjam.alat'
        ])
            ->whereIn('status', ['dipinjam', 'telat'])
            ->whereDoesntHave('pengembalian')
            ->get();

        return view(
            'petugas.pengembalian.create',
            compact('peminjamans')
        );
    }

    // Menyimpan data pengembalian
    public function storePengembalian(Request $request)
    {
        $validated = $request->validate([
            'peminjaman_id' => 'required|exists:peminjaman,id',
            'kondisi_kembali' => 'required|in:baik,rusak ringan,rusak sedang,rusak berat',
            'denda' => 'required|integer|min:0',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $peminjaman = Peminjaman::whereKey($validated['peminjaman_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                if (!in_array(strtolower($peminjaman->status), ['dipinjam', 'telat'])) {
                    throw new Exception(
                        'Peminjaman tidak dapat dikembalikan.'
                    );
                }

                if (Pengembalian::where('peminjaman_id', $peminjaman->id)->exists()) {
                    throw new Exception(
                        'Pengembalian sudah tercatat.'
                    );
                }

                $details = DetailPinjam::where(
                    'peminjaman_id',
                    $peminjaman->id
                )->get();

                if ($details->isEmpty()) {
                    throw new Exception(
                        'Detail alat tidak ditemukan.'
                    );
                }

                Pengembalian::create([
                    'peminjaman_id' => $peminjaman->id,
                    'tgl_kembali' => now(),
                    'kondisi_kembali' => $validated['kondisi_kembali'],
                    'denda' => $validated['denda'],
                    'petugas_id' => auth()->id(),
                ]);

                // Mengembalikan stok alat
                foreach ($details as $detail) {
                    $alat = Alat::whereKey($detail->alat_id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    $alat->increment('stok', $detail->jumlah);
                }

                // Mengubah status peminjaman
                $peminjaman->update([
                    'status' => 'dikembalikan',
                ]);
            });

            return redirect()
                ->route('petugas.pengembalian.index')
                ->with(
                    'success',
                    'Pengembalian berhasil dicatat dan stok alat dipulihkan.'
                );

        } catch (Exception $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    // Menampilkan form pemeriksaan pengembalian
    public function editPengembalian($id)
    {
        $pengembalian = Pengembalian::with([
            'peminjaman.user',
            'peminjaman.detailPinjam.alat'
        ])->findOrFail($id);

        return view('petugas.pengembalian.edit', compact('pengembalian'));
    }

    // Menyimpan hasil pemeriksaan
    public function updatePengembalian(Request $request, $id)
    {
        $request->validate([
            'kondisi_kembali' => 'required|string',
            'denda' => 'required|numeric|min:0',
            'aksi' => 'required|in:setuju,tolak',
        ]);

        $pengembalian = Pengembalian::findOrFail($id);

        if ($request->aksi == 'setuju') {
            $pengembalian->update([
                'kondisi_kembali' => $request->kondisi_kembali,
                'denda' => $request->denda,
                'petugas_id' => auth()->id(),
            ]);

            return redirect()
                ->route('petugas.pengembalian.index')
                ->with('success', 'Pengembalian berhasil disetujui.');
        }

        return redirect()
            ->route('petugas.pengembalian.index')
            ->with('error', 'Pengembalian ditolak.');
    }

    // Menampilkan Laporan Petugas
    public function indexLaporan(Request $request)
    {
        $query = Peminjaman::with([
            'user',
            'detailPinjam.alat',
            'pengembalian'
        ]);

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tgl_pinjam', [
                $request->start_date,
                $request->end_date
            ]);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $peminjamans = $query->latest()
            ->paginate(15)
            ->withQueryString();

        return view('petugas.laporan.index', compact('peminjamans'));
    }

    // Memproses cetak/download PDF
    public function cetakLaporan(Request $request)
    {
        $query = Peminjaman::with([
            'user',
            'detailPinjam.alat',
            'pengembalian'
        ]);

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tgl_pinjam', [
                $request->start_date,
                $request->end_date
            ]);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $peminjamans = $query->latest()->get();

        $pdf = Pdf::loadView(
            'petugas.laporan.pdf',
            compact('peminjamans', 'request')
        );

        return $pdf->stream(
            'Laporan-Peminjaman-' . date('Y-m-d') . '.pdf'
        );
    }
}