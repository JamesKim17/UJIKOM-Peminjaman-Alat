@extends('layouts.app')

@section('title', 'Tambah User - Panel Admin')
@section('header-title', 'Tambah Pengguna Baru')

@section('content')

<div class="max-w-xl bg-white rounded-lg shadow-sm border border-gray-200 p-6">

    <form action="{{ route('admin.user.store') }}"
          method="POST"
          enctype="multipart/form-data">

        @csrf

        {{-- NAMA --}}
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-semibold mb-2">
                Nama Lengkap
            </label>

            <input type="text"
                   name="name"
                   value="{{ old('name') }}"
                   required
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">

            @error('name')
                <span class="text-red-500 text-xs">{{ $message }}</span>
            @enderror
        </div>

        {{-- EMAIL --}}
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-semibold mb-2">
                Email
            </label>

            <input type="email"
                   name="email"
                   value="{{ old('email') }}"
                   required
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">

            @error('email')
                <span class="text-red-500 text-xs">{{ $message }}</span>
            @enderror
        </div>

        {{-- PASSWORD --}}
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-semibold mb-2">
                Password
            </label>

            <input type="password"
                   name="password"
                   required
                   autocomplete="new-password"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">

            @error('password')
                <span class="text-red-500 text-xs">{{ $message }}</span>
            @enderror
        </div>

        {{-- ROLE --}}
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-semibold mb-2">
                Role / Hak Akses
            </label>

            <select name="role"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">

                <option value="peminjam" {{ old('role', 'peminjam') == 'peminjam' ? 'selected' : '' }}>
                    Peminjam
                </option>

                <option value="petugas" {{ old('role') == 'petugas' ? 'selected' : '' }}>
                    Petugas
                </option>

                <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>
                    Admin
                </option>

            </select>

            @error('role')
                <span class="text-red-500 text-xs">{{ $message }}</span>
            @enderror
        </div>

        {{-- NO HP --}}
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-semibold mb-2">
                No. HP (Opsional)
            </label>

            <input type="text"
                   name="no_hp"
                   value="{{ old('no_hp') }}"
                   maxlength="15"
                   inputmode="numeric"
                   oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                   placeholder="Masukkan nomor HP"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">

            @error('no_hp')
                <span class="text-red-500 text-xs">{{ $message }}</span>
            @enderror
        </div>

        {{-- FOTO PROFIL --}}
        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-semibold mb-3">
                Foto Profil (Opsional)
            </label>

            <div class="flex items-center gap-4 mb-3">

                <img id="previewFoto"
                     src="https://ui-avatars.com/api/?name=User&background=2563eb&color=fff"
                     alt="Preview Foto"
                     class="w-16 h-16 rounded-full object-cover border border-gray-300">

                <span class="text-xs text-gray-500">
                    Preview foto profil
                </span>

            </div>

            <input type="file"
                   name="foto_profile"
                   id="foto_profile"
                   accept="image/jpeg,image/png,image/webp"
                   class="w-full text-sm text-gray-500 border border-gray-300 rounded-lg p-2">

            @error('foto_profile')
                <span class="text-red-500 text-xs">{{ $message }}</span>
            @enderror

            <p class="text-xs text-gray-400 mt-2">
                Format JPG, JPEG, PNG, WEBP. Maksimal 2 MB.
            </p>
        </div>

        {{-- TOMBOL --}}
        <div class="flex justify-end space-x-2">

            <a href="{{ route('admin.user.index') }}"
               class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg text-sm font-semibold">
                Batal
            </a>

            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold">
                Simpan
            </button>

        </div>

    </form>

</div>

{{-- PREVIEW FOTO --}}
<script>
    document.getElementById('foto_profile').addEventListener('change', function(event) {
        const file = event.target.files[0];

        if (file) {
            document.getElementById('previewFoto').src =
                URL.createObjectURL(file);
        }
    });
</script>

@endsection