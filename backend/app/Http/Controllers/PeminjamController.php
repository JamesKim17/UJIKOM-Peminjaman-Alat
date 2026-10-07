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
        $alats = Alat::with('kategori')->where('stok', '>', 0)->get();
        return view('peminjam.katalog', compact('alats'));
    }

    public function ajukanPeminjaman(Request $request)
    {
        $request->validate([
            'tgl_kembali_plan' => 'required|date|after_or_equal:today',
            'alat_id' => 'required|array',
            'jumlah' => 'required|array',
        ]);

        DB::beginTransaction();
        try {
            // Filter: Ambil hanya alat yang jumlahnya > 0
            $selectedItems = [];
            foreach ($request->alat_id as $alatId) {
                $jumlah = $request->jumlah[$alatId] ?? 0;
                if ($jumlah > 0) {
                    $selectedItems[$alatId] = $jumlah;
                }
            }

            if (empty($selectedItems)) {
                return redirect()->back()->withInput()->with('error', 'Pilih minimal 1 alat dengan jumlah lebih dari 0.');
            }

            // Buat header peminjaman
            $peminjaman = Peminjaman::create([
                'user_id' => auth()->id(),
                'tgl_pinjam' => now(),
                'tgl_kembali_plan' => $request->tgl_kembali_plan,
                'status' => 'diajukan',
            ]);

            // Simpan ke detail_pinjam (Stok TIDAK dikurangi karena masih tahap pengajuan)
            foreach ($selectedItems as $alatId => $jumlah) {
                $alat = Alat::findOrFail($alatId);

                if ($alat->stok < $jumlah) {
                    throw new \Exception("Stok alat {$alat->nama_alat} tidak mencukupi.");
                }

                DetailPinjam::create([
                    'peminjaman_id' => $peminjaman->id,
                    'alat_id' => $alatId,
                    'jumlah' => $jumlah,
                ]);
            }

            DB::commit();
            return redirect()->route('peminjam.riwayat')->with('success', 'Pengajuan peminjaman berhasil dikirim.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal mengajukan peminjaman: ' . $e->getMessage());
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
        $peminjaman = Peminjaman::findOrFail($id);

        if (strtolower($peminjaman->status) === 'diajukan') {
            $peminjaman->delete();
            return redirect()->back()->with('success', 'Pengajuan peminjaman berhasil dibatalkan.');
        }

        return redirect()->back()->with('error', 'Data tidak bisa dihapus karena alat sudah diproses atau dipinjam.');
    }
}