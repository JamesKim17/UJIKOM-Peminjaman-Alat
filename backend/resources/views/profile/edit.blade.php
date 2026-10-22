@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Edit Profil</h2>

    @if (session('success'))
        <div style="color: green; margin-bottom: 15px;">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div style="color: red; margin-bottom: 15px;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('profile.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div style="margin-bottom: 15px;">
            <label for="name">Nama Lengkap</label>
            <input
                type="text"
                id="name"
                name="name"
                value="{{ old('name', $user->name) }}"
                required
                style="display:block; width:100%; padding:10px;"
            >
        </div>

        <div style="margin-bottom: 15px;">
            <label for="email">Email</label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email', $user->email) }}"
                required
                style="display:block; width:100%; padding:10px;"
            >
        </div>

        <hr>

        <h3>Ubah Password</h3>
        <p>Kosongkan password jika tidak ingin mengubahnya.</p>

        <div style="margin-bottom: 15px;">
            <label for="password">Password Baru</label>
            <input
                type="password"
                id="password"
                name="password"
                autocomplete="new-password"
                style="display:block; width:100%; padding:10px;"
            >
        </div>

        <div style="margin-bottom: 15px;">
            <label for="password_confirmation">
                Konfirmasi Password Baru
            </label>
            <input
                type="password"
                id="password_confirmation"
                name="password_confirmation"
                autocomplete="new-password"
                style="display:block; width:100%; padding:10px;"
            >
        </div>

        <button type="submit"
            style="background:#198754; color:white; padding:10px 20px; border:0; border-radius:5px; cursor:pointer;">
            Simpan Perubahan
        </button>

        <a href="{{ url('/admin/dashboard') }}"
            style="margin-left:10px;">
            Kembali
        </a>
    </form>
</div>
@endsection