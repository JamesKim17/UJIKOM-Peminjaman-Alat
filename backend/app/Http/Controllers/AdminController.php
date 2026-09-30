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

class AdminController extends Controller
{
    # Menampilkan Dashboard Admin & Log Aktivitas
    public function index()
    {
        $logs = LogAktivitas::with('user')->latest()->take(10)->get();
        return view('admin.dashboard', compact('logs'));
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
            $filename = time() . '_' . $file->getClientOriginalExtension();
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
            $filename = time() . '_' . $file->getClientOriginalExtension();
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
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'no_hp' => $request->no_hp,
        ]);

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

        $user->update($data);

        return redirect()->route('admin.user.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroyUser($id)
    {
        $user = User::findOrFail($id);
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
        $peminjaman = Peminjaman::with('detailPinjam.alat')->findOrFail($id);

        $request->validate([
            'status' => 'required|in:Diajukan,Dipinjam,Dikembalikan,Telat,diajukan,dipinjam,dikembalikan,telat',
        ]);

        DB::beginTransaction();
        try {
            $statusLama = strtolower($peminjaman->status);
            $statusBaru = strtolower($request->status);

            if ($statusLama != 'dipinjam' && $statusBaru === 'dipinjam') {
                foreach ($peminjaman->detailPinjam as $detail) {
                    $alat = $detail->alat;
                    if ($alat->stok < $detail->jumlah) {
                        throw new \Exception("Stok alat {$alat->nama_alat} tidak mencukupi untuk dipinjam.");
                    }
                    $alat->decrement('stok', $detail->jumlah);
                }
            } elseif ($statusLama == 'dipinjam' && $statusBaru === 'dikembalikan') {
                foreach ($peminjaman->detailPinjam as $detail) {
                    $detail->alat->increment('stok', $detail->jumlah);
                }
            }

            $peminjaman->update([
                'status' => $request->status,
            ]);

            DB::commit();
            return redirect()->route('admin.peminjaman.index')->with('success', 'Status peminjaman berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroyPeminjaman($id)
    {
        $peminjaman = Peminjaman::with('detailPinjam.alat')->findOrFail($id);

        if (strtolower($peminjaman->status) == 'dipinjam') {
            foreach ($peminjaman->detailPinjam as $detail) {
                if ($detail->alat) {
                    $detail->alat->increment('stok', $detail->jumlah);
                }
            }
        }

        $peminjaman->delete();

        return redirect()->route('admin.peminjaman.index')->with('success', 'Data peminjaman berhasil dihapus.');
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
        $request->validate([
            'peminjaman_id' => 'required|exists:peminjaman,id',
            'petugas_id' => 'required|exists:users,id',
            'kondisi_kembali' => 'required|in:baik,rusak ringan,rusak sedang,rusak berat',
            'denda' => 'nullable|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            $peminjaman = Peminjaman::with('detailPinjam')->findOrFail($request->peminjaman_id);

            Pengembalian::create([
                'peminjaman_id' => $peminjaman->id,
                'tgl_kembali' => now(), 
                'kondisi_kembali' => $request->kondisi_kembali,
                'denda' => $request->denda ?? 0,
                'petugas_id' => $request->petugas_id, 
            ]);

            $peminjaman->update(['status' => 'dikembalikan']);

            foreach ($peminjaman->detailPinjam as $detail) {
                $alat = Alat::findOrFail($detail->alat_id);
                $alat->increment('stok', $detail->jumlah);
            }

            DB::commit();
            return redirect()->route('admin.pengembalian.index')
                             ->with('success', 'Data pengembalian berhasil dicatat dan stok dipulihkan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroyPengembalian($id)
    {
        $pengembalian = Pengembalian::findOrFail($id);

        if ($pengembalian->peminjaman) {
            $pengembalian->peminjaman->update(['status' => 'dipinjam']);
        }
        
        $pengembalian->delete();

        return redirect()->route('admin.pengembalian.index')->with('success', 'Data pengembalian berhasil dihapus.');
    }
}