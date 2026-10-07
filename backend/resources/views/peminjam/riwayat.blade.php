<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Peminjaman - Panel Peminjam</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50 min-h-screen text-slate-800">

    <!-- Top Navigation Bar -->
    <header class="bg-blue-600 text-white shadow-md">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <h1 class="text-xl font-bold tracking-wide">Panel Peminjam</h1>
            <div class="flex items-center space-x-3">
                <a href="{{ route('peminjam.katalog') }}" class="bg-white/10 hover:bg-white/20 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                    <i class="fa-solid fa-arrow-left mr-1"></i> Katalog Alat
                </a>
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="bg-white text-blue-600 hover:bg-slate-100 px-4 py-2 rounded-lg text-sm font-semibold shadow transition">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Main Content Container -->
    <main class="max-w-7xl mx-auto px-6 py-8">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-slate-800">Riwayat Peminjaman Alat</h2>
            <p class="text-sm text-slate-500 mt-1">Daftar alat dan barang yang pernah atau sedang kamu ajukan peminjamannya dan pengembaliannya.</p>
        </div>

        <!-- Card Container matching the catalog aesthetic -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 md:p-8">
            
            @if(session('success'))
                <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 text-slate-600 text-sm">
                            <th class="py-3 px-4 font-semibold w-16">No</th>
                            <th class="py-3 px-4 font-semibold">Nama Alat</th>
                            <th class="py-3 px-4 font-semibold">Tgl Pinjam</th>
                            <th class="py-3 px-4 font-semibold">Rencana / Tgl Kembali</th>
                            <th class="py-3 px-4 font-semibold text-center">Total Keseluruhan</th>
                            <th class="py-3 px-4 font-semibold text-center">Status</th>
                            <th class="py-3 px-4 w-32 font-semibold text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse ($peminjamans as $index => $pinjam)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="py-4 px-4 text-slate-500">{{ $index + 1 }}</td>
                                <td class="py-4 px-4 font-medium text-slate-800">
                                    @if($pinjam->detailPinjam->count() > 0)
                                        <ul class="list-disc list-inside">
                                            @foreach($pinjam->detailPinjam as $detail)
                                                <li>
                                                    {{ $detail->alat->nama_alat ?? 'Alat tidak ditemukan' }} 
                                                    <span class="text-slate-500 font-normal">({{ $detail->jumlah ?? 1 }} pcs)</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        Nama alat tidak tersedia
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-slate-600">
                                    {{ \Carbon\Carbon::parse($pinjam->tgl_pinjam ?? $pinjam->created_at)->format('d/m/Y') }}
                                </td>
                                <td class="py-4 px-4 text-slate-600">
                                    {{ $pinjam->tgl_kembali_plan ? \Carbon\Carbon::parse($pinjam->tgl_kembali_plan)->format('d/m/Y') : '-' }}
                                </td>
                                <td class="py-4 px-4 text-center font-medium text-slate-700">
                                    {{ $pinjam->detailPinjam->sum('jumlah') }}
                                </td>
                                <td class="py-4 px-4 text-center">
                                    @php
                                        $status = strtolower($pinjam->status ?? 'pending');
                                    @endphp

                                    @if($status == 'dipinjam' || $status == 'approved' || $status == 'disetujui')
                                        <span class="inline-block px-3 py-1 bg-amber-100 text-amber-800 rounded-full text-xs font-semibold">
                                            Dipinjam
                                        </span>
                                    @elseif($status == 'dikembalikan' || $status == 'completed' || $status == 'selesai')
                                        <span class="inline-block px-3 py-1 bg-emerald-100 text-emerald-800 rounded-full text-xs font-semibold">
                                            Dikembalikan
                                        </span>
                                    @elseif($status == 'telat')
                                        <span class="inline-block px-3 py-1 bg-rose-100 text-rose-800 rounded-full text-xs font-semibold">
                                            Telat
                                        </span>
                                    @else
                                        <span class="inline-block px-3 py-1 bg-slate-100 text-slate-700 rounded-full text-xs font-semibold">
                                            {{ ucfirst($pinjam->status ?? 'Pending') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-center align-middle">
                                    @if(strtolower($pinjam->status) === 'diajukan')
                                        <form action="{{ route('peminjam.hapus', $pinjam->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Apakah kamu yakin ingin membatalkan pengajuan ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm bg-red-600 hover:bg-red-700 text-white text-xs font-medium px-3 py-1.5 rounded transition">
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-12 text-slate-400">
                                    <i class="fa-solid fa-folder-open text-3xl mb-2 block"></i>
                                    Belum ada riwayat peminjaman alat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </main>

</body>
</html>