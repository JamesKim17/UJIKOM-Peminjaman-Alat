
@extends('layouts.app')

@section('title', 'Periksa Pengembalian')
@section('header-title', 'Periksa Pengembalian Alat')

@section('content')
<div class="max-w-xl bg-white p-6 rounded-lg shadow">

    <h2 class="text-xl font-bold mb-4">Form Pemeriksaan</h2>

    <p class="mb-2">
        Peminjam:
        <b>{{ $pengembalian->peminjaman->user->name ?? '-' }}</b>
    </p>

    <form action="{{ route('petugas.pengembalian.update', $pengembalian->id) }}"
          method="POST">
        @csrf
        @method('PUT')

        <label class="block mb-2 font-semibold">Kondisi Alat</label>
        <select name="kondisi_kembali"
                class="w-full border p-2 rounded mb-4" required>
            <option value="Baik">Baik</option>
            <option value="Rusak ringan">Rusak ringan</option>
            <option value="Rusak berat">Rusak berat</option>
            <option value="Tidak lengkap">Tidak lengkap</option>
        </select>

        <label class="block mb-2 font-semibold">Denda (Rp)</label>
        <input type="number" name="denda" min="0" value="0"
               class="w-full border p-2 rounded mb-4" required>

        <div class="flex gap-2">
            <button type="submit" name="aksi" value="setuju"
                    class="bg-green-600 text-white px-4 py-2 rounded">
                Setujui
            </button>

            <button type="submit" name="aksi" value="tolak"
                    class="bg-red-600 text-white px-4 py-2 rounded">
                Tolak
            </button>

            <a href="{{ route('petugas.pengembalian.index') }}"
               class="bg-gray-300 px-4 py-2 rounded">
                Kembali
            </a>
        </div>
    </form>
</div>
@endsection