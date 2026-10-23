@extends('layouts.app')

@section('title', 'Riwayat Peminjaman - Peminjam')
@section('header-title', 'Riwayat Peminjaman')

@section('content')

<div class="space-y-6">

    {{-- Judul halaman --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                Riwayat Peminjaman Alat
            </h1>

            <p class="text-sm text-gray-500 mt-1">
                Daftar alat yang pernah atau sedang kamu ajukan peminjamannya.
            </p>
        </div>

        <a href="{{ route('peminjam.katalog') }}"
           class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-lg text-sm font-semibold transition shadow-sm">

            <svg xmlns="http://www.w3.org/2000/svg"
                 class="w-4 h-4"
                 fill="none"
                 viewBox="0 0 24 24"
                 stroke="currentColor">
                <path stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M12 4v16m8-8H4" />
            </svg>

            Katalog Alat
        </a>
    </div>

    {{-- Notifikasi berhasil --}}
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Notifikasi error --}}
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 p-4 rounded-lg text-sm">
            {{ session('error') }}
        </div>
    @endif

    {{-- Notifikasi validasi --}}
    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 p-4 rounded-lg text-sm">
            <p class="font-semibold mb-1">Terjadi kesalahan:</p>

            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Tabel riwayat --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">

        {{-- Header tabel --}}
        <div class="p-4 md:p-6 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800">
                Daftar Peminjaman
            </h2>

            <p class="text-sm text-gray-500 mt-1">
                Periksa status dan detail peminjaman alatmu.
            </p>
        </div>

        <div class="overflow-x-auto">

            <table class="w-full text-left border-collapse">

                <thead class="bg-gray-50">
                    <tr class="border-b border-gray-200 text-gray-600 text-sm">
                        <th class="py-4 px-4 font-semibold whitespace-nowrap">
                            No
                        </th>

                        <th class="py-4 px-4 font-semibold min-w-52">
                            Nama Alat
                        </th>

                        <th class="py-4 px-4 font-semibold whitespace-nowrap">
                            Tgl Pinjam
                        </th>

                        <th class="py-4 px-4 font-semibold whitespace-nowrap">
                            Rencana / Tgl Kembali
                        </th>

                        <th class="py-4 px-4 font-semibold text-center whitespace-nowrap">
                            Total Alat
                        </th>

                        <th class="py-4 px-4 font-semibold text-center">
                            Status
                        </th>

                        <th class="py-4 px-4 font-semibold text-center">
                            Aksi
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 text-sm">

                    @forelse($peminjamans as $index => $pinjam)

                        <tr class="hover:bg-gray-50 transition">

                            {{-- Nomor --}}
                            <td class="py-4 px-4 text-gray-500">
                                {{ $index + 1 }}
                            </td>

                            {{-- Nama alat --}}
                            <td class="py-4 px-4 text-gray-800">
                                @if($pinjam->detailPinjam->count() > 0)
                                    <ul class="space-y-2">
                                        @foreach($pinjam->detailPinjam as $detail)
                                            <li>
                                                <span class="font-medium">
                                                    {{ $detail->alat->nama_alat ?? 'Alat tidak ditemukan' }}
                                                </span>

                                                <span class="block text-xs text-gray-500 mt-1">
                                                    Jumlah: {{ $detail->jumlah ?? 1 }} pcs
                                                </span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <span class="text-gray-400 italic">
                                        Nama alat tidak tersedia
                                    </span>
                                @endif
                            </td>

                            {{-- Tanggal pinjam --}}
                            <td class="py-4 px-4 text-gray-600 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($pinjam->tgl_pinjam ?? $pinjam->created_at)->format('d/m/Y') }}
                            </td>

                            {{-- Tanggal kembali --}}
                            <td class="py-4 px-4 text-gray-600 whitespace-nowrap">
                                {{ $pinjam->tgl_kembali_plan
                                    ? \Carbon\Carbon::parse($pinjam->tgl_kembali_plan)->format('d/m/Y')
                                    : '-' }}
                            </td>

                            {{-- Total alat --}}
                            <td class="py-4 px-4 text-center">
                                <span class="inline-flex items-center justify-center min-w-8 px-2 py-1 bg-gray-100 text-gray-700 rounded-lg font-semibold">
                                    {{ $pinjam->detailPinjam->sum('jumlah') }}
                                </span>
                            </td>

                            {{-- Status --}}
                            <td class="py-4 px-4 text-center whitespace-nowrap">

                                @php
                                    $status = strtolower($pinjam->status ?? 'pending');
                                @endphp

                                @if(in_array($status, ['dipinjam', 'approved', 'disetujui']))

                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-50 text-amber-700 rounded-full text-xs font-semibold">
                                        <span class="w-1.5 h-1.5 bg-amber-500 rounded-full"></span>
                                        Dipinjam
                                    </span>

                                @elseif(in_array($status, ['dikembalikan', 'completed', 'selesai']))

                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 text-emerald-700 rounded-full text-xs font-semibold">
                                        <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>
                                        Dikembalikan
                                    </span>

                                @elseif($status === 'telat')

                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-rose-50 text-rose-700 rounded-full text-xs font-semibold">
                                        <span class="w-1.5 h-1.5 bg-rose-500 rounded-full"></span>
                                        Telat
                                    </span>

                                @elseif(in_array($status, ['diajukan', 'pending']))

                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-blue-50 text-blue-700 rounded-full text-xs font-semibold">
                                        <span class="w-1.5 h-1.5 bg-blue-500 rounded-full"></span>
                                        {{ ucfirst($status) }}
                                    </span>

                                @else

                                    <span class="inline-flex items-center px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-xs font-semibold">
                                        {{ ucfirst($status) }}
                                    </span>

                                @endif

                            </td>

                            {{-- Aksi --}}
                            <td class="py-4 px-4 text-center whitespace-nowrap">

                                @if($status === 'diajukan')

                                    <form action="{{ route('peminjam.hapus', $pinjam->id) }}"
                                          method="POST"
                                          onsubmit="return confirm('Apakah kamu yakin ingin membatalkan pengajuan peminjaman ini?');">

                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="inline-flex items-center gap-1.5 bg-red-50 hover:bg-red-100 text-red-600 px-3 py-2 rounded-lg text-xs font-semibold transition">

                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                 class="w-4 h-4"
                                                 fill="none"
                                                 viewBox="0 0 24 24"
                                                 stroke="currentColor">
                                                <path stroke-linecap="round"
                                                      stroke-linejoin="round"
                                                      stroke-width="2"
                                                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3m-9 0h12" />
                                            </svg>

                                            Hapus
                                        </button>
                                    </form>

                                @else

                                    <span class="text-gray-400">—</span>

                                @endif

                            </td>

                        </tr>

                    @empty

                        {{-- Tampilan jika belum ada riwayat --}}
                        <tr>
                            <td colspan="7" class="py-16 px-4 text-center">

                                <div class="flex flex-col items-center justify-center">

                                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                             class="w-8 h-8 text-gray-400"
                                             fill="none"
                                             viewBox="0 0 24 24"
                                             stroke="currentColor">
                                            <path stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  stroke-width="1.8"
                                                  d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" />
                                        </svg>
                                    </div>

                                    <h3 class="text-gray-700 font-semibold mb-1">
                                        Belum Ada Riwayat
                                    </h3>

                                    <p class="text-gray-500 text-sm mb-4">
                                        Kamu belum memiliki riwayat peminjaman alat.
                                    </p>

                                    <a href="{{ route('peminjam.katalog') }}"
                                       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
                                        Lihat Katalog Alat
                                    </a>

                                </div>

                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

@endsection