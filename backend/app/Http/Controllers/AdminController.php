<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Alat;
use App\Models\DetailPinjam;
use App\Models\Kategori;
use App\Models\LogAktivitas;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminController extends Controller
{
    // Halaman dashboard admin: ringkasan jumlah data di setiap menu
    public function index()
    {
        $stats = [
            'user' => [
                'total' => User::count(),
                'admin' => User::where('role', 'admin')->count(),
                'petugas' => User::where('role', 'petugas')->count(),
                'peminjam' => User::where('role', 'peminjam')->count(),
            ],
            'kategori' => [
                'total' => Kategori::count(),
            ],
            'alat' => [
                'total' => Alat::count(),
                'total_stok' => (int) Alat::sum('stok'),
            ],
            'peminjaman' => [
                'total' => Peminjaman::count(),
                'diajukan' => Peminjaman::where('status', 'diajukan')->count(),
                'dipinjam' => Peminjaman::where('status', 'dipinjam')->count(),
                'dikembalikan' => Peminjaman::where('status', 'dikembalikan')->count(),
                'telat' => Peminjaman::where('status', 'telat')->count(),
            ],
            'pengembalian' => [
                'total' => Pengembalian::count(),
                
            ],
            'log_aktivitas' => [
                'total' => LogAktivitas::count(),
            ],
        ];

        return view('admin.dashboard', compact('stats'));
    }

    public function indexAlat()
    {
        $search = request()->input('search');

        $alats = Alat::with('kategori')
            ->when($search, function ($query) use ($search) {
                $query->where('nama_alat', 'like', "%{$search}%")
                    ->orWhere('status_kondisi', 'like', "%{$search}%")
                    ->orWhereHas('kategori', function ($q) use ($search) {
                        $q->where('nama_kategori', 'like', "%{$search}%");
                    });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.alat.index', compact('alats'));
    }

    public function createAlat()
    {
        $kategoris = Kategori::all();
        return view('admin.alat.create', compact('kategoris'));
    }

    public function storeAlat(Request $request)
    {
        $request->validate([
            'nama_alat' => 'required|string|max:255',
            'kategori_id' => 'required|exists:kategori,id',
            'stok' => 'required|integer|min:0',
            'status_kondisi' => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->all();

        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            if (!is_dir(public_path('storage/alat'))) {
                mkdir(public_path('storage/alat'), 0755, true);
            }

            $file->move(public_path('storage/alat'), $filename);
            $data['gambar'] = 'storage/alat/' . $filename;
        }

        Alat::create($data);
        
        return redirect()->route('admin.alat.index')->with('success', 'Data alat berhasil ditambahkan.');
    }

    public function editAlat($id)
    {
        $alat = Alat::findOrFail($id);
        $kategoris = Kategori::all();
        return view('admin.alat.edit', compact('alat', 'kategoris'));
    }

    public function updateAlat(Request $request, $id)
    {
        $alat = Alat::findOrFail($id);

        $request->validate([
            'nama_alat' => 'required|string|max:255',
            'kategori_id' => 'required|exists:kategori,id',
            'stok' => 'required|integer|min:0',
            'status_kondisi' => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->all();

        if ($request->hasFile('gambar')) {
            if ($alat->gambar && file_exists(public_path($alat->gambar))) {
                unlink(public_path($alat->gambar));
            }

            $file = $request->file('gambar');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            if (!is_dir(public_path('storage/alat'))) {
                mkdir(public_path('storage/alat'), 0755, true);
            }

            $file->move(public_path('storage/alat'), $filename);
            $data['gambar'] = 'storage/alat/' . $filename;
        }

        $alat->update($data);
        
        return redirect()->route('admin.alat.index')->with('success', 'Data alat berhasil diperbarui.');
    }
    
    public function destroyAlat($id)
    {
        $alat = Alat::findOrFail($id);

        if ($alat->gambar && file_exists(public_path($alat->gambar))) {
            unlink(public_path($alat->gambar));
        }

        $alat->delete();
        
        return redirect()->route('admin.alat.index')->with('success', 'Data alat berhasil dihapus.');
    }

    public function indexUser(Request $request)
    {
        $search = $request->input('search');

        $users = User::when($search, function ($query) use ($search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('role', 'like', "%{$search}%");
        })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.user.index', compact('users', 'search'));
    }

    public function createUser()
    {
        return view('admin.user.create');
    }

    # Menyimpan user baru ke database
    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|string|in:admin,petugas,peminjam',
            'no_hp' => 'nullable|string|max:15',
            'foto_profile' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'no_hp' => $request->no_hp,
        ];

        // Simpan foto profil langsung ke public/storage/profiles
        if ($request->hasFile('foto_profile')) {
            $file = $request->file('foto_profile');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            
            // Pastikan folder public/storage/profiles ada
            if (!file_exists(public_path('storage/profiles'))) {
                mkdir(public_path('storage/profiles'), 0777, true);
            }

            $file->move(public_path('storage/profiles'), $filename);
            $data['foto_profile'] = 'storage/profiles/' . $filename;
        }

        User::create($data);

        return redirect()->route('admin.user.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function editUser($id)
    {
        $user = User::findOrFail($id);
        return view('admin.user.edit', compact('user'));
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|string|in:admin,petugas,peminjam',
            'no_hp' => 'nullable|string|max:15',
            'foto_profile' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'no_hp' => $request->no_hp,
        ];
        
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        // Hapus foto lama (jika ada) dan simpan foto baru langsung ke public/storage/profiles
        if ($request->hasFile('foto_profile')) {
            if ($user->foto_profile && file_exists(public_path($user->foto_profile))) {
                unlink(public_path($user->foto_profile));
            }

            $file = $request->file('foto_profile');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            
            if (!file_exists(public_path('storage/profiles'))) {
                mkdir(public_path('storage/profiles'), 0777, true);
            }

            $file->move(public_path('storage/profiles'), $filename);
            $data['foto_profile'] = 'storage/profiles/' . $filename;
        }

        $user->update($data);

        return redirect()->route('admin.user.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroyUser($id)
    {
        $user = User::findOrFail($id);
        
        // Hapus file fisik foto profil jika ada
        if ($user->foto_profile && file_exists(public_path($user->foto_profile))) {
            unlink(public_path($user->foto_profile));
        }

        $user->delete();
        
        return redirect()->route('admin.user.index')->with('success', 'User berhasil dihapus.');
    }

    public function indexKategori(Request $request)
    {
        $search = $request->input('search');

        $kategoris = Kategori::when($search, function ($query, $search) {
            return $query->where('nama_kategori', 'like', "%{$search}%");
        })
            ->latest()
            ->paginate(5)
            ->withQueryString();

        return view('admin.kategori.index', compact('kategoris', 'search'));
    }
    
    # Menampilkan form tambah kategori
    public function createKategori()
    {
        return view('admin.kategori.create');
    }

    # Menyimpan kategori baru
    public function storeKategori(Request $request)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:255|unique:kategori,nama_kategori',
        ]);

        Kategori::create([
            'nama_kategori' => $request->nama_kategori,
        ]);
        
        return redirect()->route('admin.kategori.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    # Menampilkan form edit kategori
    public function editKategori($id)
    {
        $kategori = Kategori::findOrFail($id);
        return view('admin.kategori.edit', compact('kategori'));
    }

    # Memperbarui kategori
    public function updateKategori(Request $request, $id)
    {
        $kategori = Kategori::findOrFail($id);
        
        $request->validate([
            'nama_kategori' => 'required|string|max:255|unique:kategori,nama_kategori,' . $id,
        ]);
        
        $kategori->update([
            'nama_kategori' => $request->nama_kategori,
        ]);

        return redirect()->route('admin.kategori.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    # Menghapus kategori
    public function destroyKategori($id)
    {
        $kategori = Kategori::findOrFail($id);

        if ($kategori->alat->count() > 0) {
            return redirect()->route('admin.kategori.index')->with('error', 'Kategori ini tidak bisa dihapus karena masih digunakan oleh alat.');
        }

        $kategori->delete();

        return redirect()->route('admin.kategori.index')->with('success', 'Kategori berhasil dihapus.');
    }

    public function indexPeminjaman(Request $request)
    {
        $search = $request->input('search');

        $peminjamans = Peminjaman::with(['user', 'detailPinjam.alat'])
        ->when($search, function ($query, $search) {
            return $query->where('status', 'like', "%{$search}%")
                ->orWhereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.peminjaman.index', compact('peminjamans', 'search'));
    }

    public function createPeminjaman()
    {
        $users = User::where('role', 'peminjam')->get();
        $alats = Alat::where('stok', '>', 0)->get();
        return view('admin.peminjaman.create', compact('users', 'alats'));
    }

    public function storePeminjaman(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'tgl_pinjam' => 'required|date',
            'tgl_kembali_plan' => 'required|date|after_or_equal:tgl_pinjam',
            'alat_id' => 'required|array',
            'alat_id.*' => 'exists:alat,id',
            'jumlah' => 'required|array',
            'jumlah.*' => 'integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $peminjaman = Peminjaman::create([
                'user_id' => $request->user_id,
                'tgl_pinjam' => $request->tgl_pinjam,
                'tgl_kembali_plan' => $request->tgl_kembali_plan,
                'status' => 'diajukan',
            ]);

            foreach ($request->alat_id as $index => $alatId) {
                $jumlahPinjam = $request->jumlah[$index];

                $alat = Alat::findOrFail($alatId);

                if ($alat->stok < $jumlahPinjam) {
                    throw new \Exception("Stok alat {$alat->nama_alat} tidak mencukupi.");
                }

                DetailPinjam::create([
                    'peminjaman_id' => $peminjaman->id,
                    'alat_id' => $alatId,
                    'jumlah' => $jumlahPinjam,
                ]);
            }

            DB::commit();
            return redirect()->route('admin.peminjaman.index')->with('success', 'Data peminjaman berhasil diajukan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function updateStatusPeminjaman(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:Diajukan,Dipinjam,Dikembalikan,Telat,diajukan,dipinjam,dikembalikan,telat',
        ]);

        $statusBaru = strtolower($validated['status']);

        try {
            DB::transaction(function () use ($id, $statusBaru) {
                $peminjaman = Peminjaman::with('detailPinjam.alat')
                    ->whereKey($id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $statusLama = strtolower($peminjaman->status);

                if ($statusLama === $statusBaru) {
                    return;
                }

                $transisiDiizinkan = [
                    'diajukan' => ['dipinjam'],
                    'dipinjam' => ['telat', 'dikembalikan'],
                    'telat' => ['dikembalikan'],
                    'dikembalikan' => [],
                ];

                if (!in_array($statusBaru, $transisiDiizinkan[$statusLama] ?? [], true)) {
                    throw new \RuntimeException('Perubahan status peminjaman tidak diizinkan.');
                }

                // Alat baru keluar saat pengajuan disetujui menjadi dipinjam.
                if ($statusLama === 'diajukan' && $statusBaru === 'dipinjam') {
                    foreach ($peminjaman->detailPinjam as $detail) {
                        $alat = Alat::whereKey($detail->alat_id)->lockForUpdate()->firstOrFail();

                        if ($alat->stok < $detail->jumlah) {
                            throw new \RuntimeException("Stok alat {$alat->nama_alat} tidak mencukupi.");
                        }

                        $alat->decrement('stok', $detail->jumlah);
                    }
                }

                // Stok dipulihkan hanya saat status berubah pertama kali menjadi dikembalikan.
                if (in_array($statusLama, ['dipinjam', 'telat'], true) && $statusBaru === 'dikembalikan') {
                    foreach ($peminjaman->detailPinjam as $detail) {
                        $alat = Alat::whereKey($detail->alat_id)->lockForUpdate()->firstOrFail();
                        $alat->increment('stok', $detail->jumlah);
                    }
                }

                if ($statusLama === 'dikembalikan' && $statusBaru !== 'dikembalikan') {
                    throw new \RuntimeException('Status peminjaman yang sudah dikembalikan tidak dapat diubah kembali.');
                }

                $peminjaman->update(['status' => $statusBaru]);
            });

            return redirect()->route('admin.peminjaman.index')
                ->with('success', 'Status peminjaman berhasil diperbarui.');
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function destroyPeminjaman($id)
    {
        $peminjaman = Peminjaman::with('detailPinjam')->findOrFail($id);

        // Jangan hapus transaksi yang alatnya masih berada di tangan peminjam.
        if (in_array(strtolower($peminjaman->status), ['dipinjam', 'telat'], true)) {
            return redirect()->route('admin.peminjaman.index')->with(
                'error',
                'Peminjaman tidak dapat dihapus karena alat belum dikembalikan.'
            );
        }

        if (strtolower($peminjaman->status) === 'dikembalikan' || Pengembalian::where('peminjaman_id', $peminjaman->id)->exists()) {
            return redirect()->route('admin.peminjaman.index')->with(
                'error',
                'Riwayat peminjaman yang sudah dikembalikan tidak dapat dihapus.'
            );
        }

        try {
            DB::transaction(function () use ($peminjaman) {
                $peminjaman->detailPinjam()->delete();
                $peminjaman->delete();
            });

            return redirect()->route('admin.peminjaman.index')->with('success', 'Data peminjaman berhasil dihapus.');
        } catch (\Throwable $e) {
            return redirect()->route('admin.peminjaman.index')->with('error', 'Data peminjaman gagal dihapus.');
        }
    }

    public function indexPengembalian(Request $request)
    {
        $search = $request->input('search');
        
        $pengembalians = Pengembalian::with(['peminjaman.user', 'petugas'])
            ->when($search, function ($query, $search) {
                return $query->whereHas('peminjaman.user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('petugas', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhere('kondisi_kembali', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.pengembalian.index', compact('pengembalians', 'search'));
    }

    public function createPengembalian()
    {
        $peminjamans = Peminjaman::with(['user', 'detailPinjam.alat'])->where('status', 'dipinjam')->get();
        $petugas = User::where('role', 'petugas')->get();
                    
        return view('admin.pengembalian.create', compact('peminjamans', 'petugas'));
    }

    public function storePengembalian(Request $request)
    {
        $validated = $request->validate([
            'peminjaman_id' => 'required|exists:peminjaman,id',
            'petugas_id' => [
                'required',
                \Illuminate\Validation\Rule::exists('users', 'id')->where('role', 'petugas'),
            ],
            'kondisi_kembali' => 'required|in:baik,rusak ringan,rusak sedang,rusak berat',
            'denda' => 'nullable|integer|min:0',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $peminjaman = Peminjaman::whereKey($validated['peminjaman_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                if (strtolower($peminjaman->status) !== 'dipinjam' && strtolower($peminjaman->status) !== 'telat') {
                    throw new \RuntimeException('Hanya peminjaman yang masih dipinjam atau telat yang dapat dikembalikan.');
                }

                if (Pengembalian::where('peminjaman_id', $peminjaman->id)->exists()) {
                    throw new \RuntimeException('Data pengembalian untuk peminjaman ini sudah tercatat.');
                }

                $details = DetailPinjam::where('peminjaman_id', $peminjaman->id)->get();
                if ($details->isEmpty()) {
                    throw new \RuntimeException('Detail alat pada peminjaman ini tidak ditemukan.');
                }

                Pengembalian::create([
                    'peminjaman_id' => $peminjaman->id,
                    'tgl_kembali' => now(),
                    'kondisi_kembali' => $validated['kondisi_kembali'],
                    'denda' => $validated['denda'] ?? 0,
                    'petugas_id' => $validated['petugas_id'],
                ]);

                foreach ($details as $detail) {
                    $alat = Alat::whereKey($detail->alat_id)->lockForUpdate()->firstOrFail();
                    $alat->increment('stok', $detail->jumlah);
                }

                $peminjaman->update(['status' => 'dikembalikan']);
            });

            return redirect()->route('admin.pengembalian.index')
                ->with('success', 'Data pengembalian berhasil dicatat dan stok dipulihkan.');
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function destroyPengembalian($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $pengembalian = Pengembalian::whereKey($id)->lockForUpdate()->firstOrFail();
                $peminjaman = Peminjaman::whereKey($pengembalian->peminjaman_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (strtolower($peminjaman->status) !== 'dikembalikan') {
                    throw new \RuntimeException('Status peminjaman tidak sesuai dengan data pengembalian.');
                }

                // Menghapus catatan pengembalian berarti alat dianggap belum kembali.
                // Karena stok sebelumnya sudah dipulihkan, kurangi kembali stoknya.
                $details = DetailPinjam::where('peminjaman_id', $peminjaman->id)->get();
                foreach ($details as $detail) {
                    $alat = Alat::whereKey($detail->alat_id)->lockForUpdate()->firstOrFail();
                    if ($alat->stok < $detail->jumlah) {
                        throw new \RuntimeException("Stok alat {$alat->nama_alat} tidak cukup untuk membatalkan pengembalian.");
                    }
                    $alat->decrement('stok', $detail->jumlah);
                }

                $pengembalian->delete();
                $peminjaman->update(['status' => 'dipinjam']);
            });

            return redirect()->route('admin.pengembalian.index')
                ->with('success', 'Data pengembalian dihapus. Status peminjaman dikembalikan menjadi dipinjam.');
        } catch (\Throwable $e) {
            return redirect()->route('admin.pengembalian.index')->with('error', $e->getMessage());
        }
    }

    // Menampilkan Laporan admin
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
        return view('admin.laporan.index', compact('peminjamans'));
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
        $pdf = Pdf::loadView('admin.laporan.pdf', compact('peminjamans', 'request'));
        
        // Mengunduh/membuka file PDF
        return $pdf->stream('Laporan-Peminjaman-'.date('Y-m-d').'.pdf');
    }

    // Halaman khusus daftar log aktivitas (dipindahkan dari dashboard)
    public function logAktivitas(Request $request)
    {
        $search = $request->input('search');

        $logs = LogAktivitas::with('user')
            ->when($search, function ($query, $search) {
                return $query->where('aktivitas', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.logAktivitas.index', compact('logs', 'search'));
    }
}