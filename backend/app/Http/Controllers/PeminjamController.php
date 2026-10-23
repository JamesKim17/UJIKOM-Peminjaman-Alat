<?php

namespace App\Http\Controllers;

use App\Models\Peminjaman;
use App\Models\DetailPinjam;
use App\Models\Alat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PeminjamController extends Controller
{
    public function katalogAlat()
    {
        $alats = Alat::with('kategori')
            ->where('stok', '>', 0)
            ->get();

        return view('peminjam.katalog', compact('alats'));
    }

    public function ajukanPeminjaman(Request $request)
    {
        $request->validate([
            'tgl_kembali_plan' => 'required|date|after_or_equal:today',
            'alat_id' => 'required|array|min:1',
            'alat_id.*' => 'required|integer|distinct',
            'jumlah' => 'required|array',
        ]);

        // Periksa pilihan alat sebelum memulai transaksi.
        $selectedItems = [];

        foreach ($request->alat_id as $alatId) {
            $jumlah = $request->jumlah[$alatId] ?? 0;

            if (!is_numeric($jumlah) || (int) $jumlah <= 0) {
                continue;
            }

            $selectedItems[$alatId] = (int) $jumlah;
        }

        if (empty($selectedItems)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Pilih minimal 1 alat dengan jumlah lebih dari 0.');
        }

        try {
            DB::transaction(function () use ($selectedItems, $request) {

                // Periksa stok terbaru dan kunci data alat selama transaksi.
                $alats = Alat::whereIn('id', array_keys($selectedItems))
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                // Pastikan semua alat yang dipilih benar-benar ada.
                if ($alats->count() !== count($selectedItems)) {
                    throw new \Exception(
                        'Salah satu alat yang dipilih tidak ditemukan.'
                    );
                }

                foreach ($selectedItems as $alatId => $jumlah) {
                    $alat = $alats->get($alatId);

                    if ($jumlah < 1 || $alat->stok < $jumlah) {
                        throw new \Exception(
                            "Stok alat {$alat->nama_alat} tidak mencukupi."
                        );
                    }
                }

                // Buat pengajuan setelah semua stok diperiksa.
                $peminjaman = Peminjaman::create([
                    'user_id' => auth()->id(),
                    'tgl_pinjam' => now(),
                    'tgl_kembali_plan' => $request->tgl_kembali_plan,
                    'status' => 'diajukan',
                ]);

                // Simpan detail tanpa mengurangi stok.
                foreach ($selectedItems as $alatId => $jumlah) {
                    DetailPinjam::create([
                        'peminjaman_id' => $peminjaman->id,
                        'alat_id' => $alatId,
                        'jumlah' => $jumlah,
                    ]);
                }
            });

            return redirect()
                ->route('peminjam.riwayat')
                ->with('success', 'Pengajuan peminjaman berhasil dikirim.');

        } catch (\Throwable $e) {
            report($e);

            return redirect()->back()
                ->withInput()
                ->with(
                    'error',
                    'Gagal mengajukan peminjaman. Periksa kembali pilihan alat dan stok.'
                );
        }
    }

    public function riwayatPeminjaman()
    {
        $peminjamans = Peminjaman::with('detailPinjam.alat')
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('peminjam.riwayat', compact('peminjamans'));
    }

    public function hapus($id)
    {
        $peminjaman = Peminjaman::where('user_id', auth()->id())
            ->findOrFail($id);

        if (strtolower($peminjaman->status) !== 'diajukan') {
            return redirect()->back()->with(
                'error',
                'Data tidak bisa dihapus karena peminjaman sudah diproses.'
            );
        }

        DB::transaction(function () use ($peminjaman) {
            $peminjaman->delete();
        });

        return redirect()->back()->with(
            'success',
            'Pengajuan peminjaman berhasil dibatalkan.'
        );
    }
}