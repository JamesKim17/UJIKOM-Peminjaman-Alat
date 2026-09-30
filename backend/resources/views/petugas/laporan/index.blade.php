@extends('layouts.app')
@section('title', 'Laporan Peminjaman - Petugas')
@section('header-title', 'Laporan Transaksi')

@section('content')
<div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200 mb-6">
    <form action="{{ route('petugas.laporan.index') }}" method="GET" class="flex flex-wrap gap-4 items-end">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Mulai Tanggal</label>
            <input type="date" name="start_date" value="{{ request('start_date') }}" class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-emerald-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
            <input type="date" name="end_date" value="{{ request('end_date') }}" class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-emerald-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select name="status" class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-emerald-500">
                <option value="">Semua Status</option>
                <option value="diajukan" {{ request('status') == 'diajukan' ? 'selected' : '' }}>Diajukan</option>
                <option value="dipinjam" {{ request('status') == 'dipinjam' ? 'selected' : '' }}>Dipinjam</option>
                <option value="dikembalikan" {{ request('status') == 'dikembalikan' ? 'selected' : '' }}>Dikembalikan</option>
                <option value="telat" {{ request('status') == 'telat' ? 'selected' : '' }}>Telat</option>
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-gray-900">Filter</button>
            <a href="{{ route('petugas.laporan.cetak', request()->all()) }}" target="_blank" class="bg-emerald-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-emerald-700">Cetak PDF</a>
        </div>
    </form>
</div>

<!-- Render tabel HTML persis seperti tabel di menu pemantauan pengembalian atau peminjaman -->
@endsection