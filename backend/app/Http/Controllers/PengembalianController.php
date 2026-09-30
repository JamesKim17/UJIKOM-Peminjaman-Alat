namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Peminjaman;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PengembalianController extends Controller
{
    public function index(Request $request)
    {
        $query = Peminjaman::with(['user', 'alat']);

        // Filter Pencarian (Nama Peminjam / Nama Alat)
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })->orWhereHas('alat', function ($q) use ($search) {
                $q->where('nama_alat', 'like', "%{$search}%");
            });
        }

        // Mengambil data peminjaman yang berstatus dipinjam / proses dikembalikan / selesai
        $pengembalian = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('admin.pengembalian.index', compact('pengembalian'));
    }

    public function konfirmasi(Request $request, $id)
    {
        $peminjaman = Peminjaman::findOrFail($id);
        
        // Catat tanggal kembali riil
        $tglKembaliRencana = Carbon::parse($peminjaman->tgl_rencana_kembali);
        $tglKembaliRiil = Carbon::now();

        // Contoh Logika Hitung Denda (Rp 10.000 / hari keterlambatan)
        $denda = 0;
        if ($tglKembaliRiil->gt($tglKembaliRencana)) {
            $selisihHari = $tglKembaliRiil->diffInDays($tglKembaliRencana);
            $denda = $selisihHari * 10000;
        }

        $peminjaman->update([
            'status' => 'Dikembalikan',
            'tgl_kembali_riil' => $tglKembaliRiil,
            'denda' => $denda,
        ]);

        return redirect()->back()->with('success', 'Status pengembalian berhasil diperbaruhi.');
    }
}