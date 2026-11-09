@extends('layouts.app')

@section('content')

@php
    // Memperbaiki alamat foto profil
    $foto = $user->foto_profile;

    if ($foto) {
        $fotoUrl = asset(
            str_starts_with($foto, 'storage/')
                ? $foto
                : 'storage/' . $foto
        );
    } else {
        $fotoUrl = 'https://ui-avatars.com/api/?name=' . urlencode($user->name);
    }
@endphp

<div class="container mx-auto px-4 py-6">

    <div class="bg-white rounded-xl shadow-sm p-6 max-w-4xl">

        <h2 class="text-2xl font-bold text-gray-800 mb-6">
            Edit Profil
        </h2>

        {{-- PESAN BERHASIL --}}
        @if (session('success'))
            <div class="bg-green-100 text-green-700 p-3 rounded-lg mb-4">
                {{ session('success') }}
            </div>
        @endif

        {{-- PESAN ERROR --}}
        @if ($errors->any())
            <div class="bg-red-100 text-red-700 p-3 rounded-lg mb-4">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('profile.update') }}"
              method="POST"
              enctype="multipart/form-data">

            @csrf
            @method('PUT')

            {{-- FOTO PROFIL --}}
            <div class="mb-6">

                <label class="block font-semibold text-gray-700 mb-3">
                    Foto Profil
                </label>

                <div class="flex items-center gap-5 flex-wrap">

                    <img
                        id="previewFoto"
                        src="{{ $fotoUrl }}"
                        alt="Foto Profil"
                        class="w-28 h-28 rounded-full object-cover border-4 border-gray-200"
                    >

                    <div class="flex-1 min-w-0">

                        <input
                            type="file"
                            name="foto_profile"
                            id="foto_profile"
                            accept="image/jpeg,image/png,image/jpg,image/webp"
                            class="block w-full text-sm text-gray-600
                            border border-gray-300 rounded-lg
                            cursor-pointer p-2"
                        >

                        <p class="text-sm text-gray-500 mt-2">
                            Pilih foto baru jika ingin mengganti foto profil.
                        </p>

                        <p class="text-xs text-gray-400 mt-1">
                            Format JPG, JPEG, PNG, WEBP. Maksimal 2 MB.
                        </p>

                    </div>

                </div>
            </div>

            {{-- NAMA LENGKAP --}}
            <div class="mb-5">

                <label for="name"
                       class="block font-semibold text-gray-700 mb-2">
                    Nama Lengkap
                </label>

                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name', $user->name) }}"
                    required
                    autocomplete="name"
                    class="w-full border border-gray-300 rounded-lg px-4 py-3
                    focus:outline-none focus:ring-2 focus:ring-blue-500"
                >

            </div>

            {{-- EMAIL --}}
            <div class="mb-5">

                <label for="email"
                       class="block font-semibold text-gray-700 mb-2">
                    Email
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email', $user->email) }}"
                    required
                    autocomplete="email"
                    class="w-full border border-gray-300 rounded-lg px-4 py-3
                    focus:outline-none focus:ring-2 focus:ring-blue-500"
                >

            </div>

            {{-- NOMOR HP --}}
            <div class="mb-5">

                <label for="no_hp"
                       class="block font-semibold text-gray-700 mb-2">
                    No. HP
                </label>

                <input
                    type="tel"
                    id="no_hp"
                    name="no_hp"
                    value="{{ old('no_hp', $user->no_hp) }}"
                    maxlength="15"
                    pattern="[0-9]{10,15}"
                    inputmode="numeric"
                    class="w-full border border-gray-300 rounded-lg px-4 py-3
                    focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Masukkan nomor HP"
                >

            </div>

            <hr class="my-6">

            {{-- UBAH PASSWORD --}}
            <h3 class="text-lg font-bold text-gray-800 mb-2">
                Ubah Password
            </h3>

            <p class="text-sm text-gray-500 mb-5">
                Kosongkan password jika tidak ingin mengubahnya.
            </p>

            <div class="mb-5">

                <label for="password"
                       class="block font-semibold text-gray-700 mb-2">
                    Password Baru
                </label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    autocomplete="new-password"
                    class="w-full border border-gray-300 rounded-lg px-4 py-3
                    focus:outline-none focus:ring-2 focus:ring-blue-500"
                >

            </div>

            {{-- KONFIRMASI PASSWORD --}}
            <div class="mb-6">

                <label for="password_confirmation"
                       class="block font-semibold text-gray-700 mb-2">
                    Konfirmasi Password Baru
                </label>

                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    autocomplete="new-password"
                    class="w-full border border-gray-300 rounded-lg px-4 py-3
                    focus:outline-none focus:ring-2 focus:ring-blue-500"
                >

            </div>

            {{-- TOMBOL --}}
            <div class="flex items-center gap-3 flex-wrap">

                <button
                    type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white
                    px-6 py-3 rounded-lg font-semibold transition">
                    Simpan Perubahan
                </button>

                @if (auth()->user()->role === 'peminjam')

                    <a href="{{ route('peminjam.katalog') }}"
                       class="text-gray-700 hover:text-blue-600 px-4 py-3">
                        Kembali
                    </a>

                @elseif (auth()->user()->role === 'admin')

                    <a href="{{ route('admin.dashboard') }}"
                       class="text-gray-700 hover:text-blue-600 px-4 py-3">
                        Kembali
                    </a>

                @else

                    <a href="{{ route('petugas.peminjaman.index') }}"
                       class="text-gray-700 hover:text-blue-600 px-4 py-3">
                        Kembali
                    </a>

                @endif

            </div>

        </form>

    </div>

</div>

{{-- PREVIEW FOTO --}}
<script>
    document.getElementById('foto_profile').addEventListener('change', function(event) {

        const file = event.target.files[0];

        if (file) {

            const preview = document.getElementById('previewFoto');

            if (preview.dataset.objectUrl) {
                URL.revokeObjectURL(preview.dataset.objectUrl);
            }

            const imageUrl = URL.createObjectURL(file);

            preview.src = imageUrl;
            preview.dataset.objectUrl = imageUrl;
        }

    });
</script>

@endsection