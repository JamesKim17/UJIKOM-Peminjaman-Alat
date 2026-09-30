<?php

namespace App\Http\Controllers;

use App\Models\Peminjaman;
use App\Models\Pengembalian;
use App\Models\Alat;
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
        DB::beginTransaction();
        try {
            $peminjaman = Peminjaman::with('detailPinjam')->findOrFail($id);

            // Validasi stok sebelum disetujui
            foreach ($peminjaman->detailPinjam as $detail) {
                $alat = Alat::findOrFail($detail->alat_id);
                if ($alat->stok < $detail->jumlah) {
                    throw new Exception("Stok alat {$alat->nama_alat} tidak mencukupi.");
                }
            }

            // Ubah status dan kurangi stok
            $peminjaman->update(['status' => 'dipinjam']);

            foreach ($peminjaman->detailPinjam as $detail) {
                Alat::where('id', $detail->alat_id)->decrement('stok', $detail->jumlah);
            }

            DB::commit();
            return redirect()->back()->with('success', 'Peminjaman disetujui dan stok alat dikurangi.');
        } catch (Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // Menolak Peminjaman (Hapus pengajuan)
    public function tolakPeminjaman($id)
    {
        try {
            $peminjaman = Peminjaman::findOrFail($id);

            if (in_array(strtolower($peminjaman->status), ['diajukan'])) {
                $peminjaman->delete();
                return redirect()->back()->with('success', 'Pengajuan peminjaman berhasil ditolak.');
            }

            return redirect()->back()->with('error', 'Status peminjaman sudah berubah.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // Menampilkan halaman pemantauan pengembalian (Read-Only)
    public function indexPengembalian(Request $request)
    {
        $search = $request->input('search');

        $pengembalians = Pengembalian::with(['peminjaman.user', 'petugas', 'peminjaman.detailPinjam.alat'])
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

    // Menampilkan Laporan Petugas
    public function indexLaporan(Request $request)
    {
        $query = Peminjaman::with(['user', 'detailPinjam.alat', 'pengembalian']);

        // Filter Rentang Tanggal
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tgl_pinjam', [$request->start_date, $request->end_date]);
        }

        // Filter Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $peminjamans = $query->latest()->paginate(15)->withQueryString();
        return view('petugas.laporan.index', compact('peminjamans'));
    }

    // Memproses cetak/download PDF
    public function cetakLaporan(Request $request)
    {
        $query = Peminjaman::with(['user', 'detailPinjam.alat', 'pengembalian']);

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tgl_pinjam', [$request->start_date, $request->end_date]);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $peminjamans = $query->latest()->get(); // Tarik semua data tanpa paginasi untuk dicetak
        
        // Load view PDF
        $pdf = Pdf::loadView('petugas.laporan.pdf', compact('peminjamans', 'request'));
        
        // Mengunduh/membuka file PDF
        return $pdf->stream('Laporan-Peminjaman-'.date('Y-m-d').'.pdf');
    }
}