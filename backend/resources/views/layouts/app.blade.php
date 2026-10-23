<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard App')</title>

    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 font-sans antialiased">

    <div class="flex h-screen overflow-hidden">

        <!-- SIDEBAR -->
        <aside class="w-64 bg-gray-900 text-white flex flex-col hidden md:flex shrink-0">

            <!-- JUDUL PANEL -->
            <div class="p-5 text-xl font-bold tracking-wider border-b border-gray-800 uppercase">
                PANEL {{ auth()->user()->role ?? 'USER' }}
            </div>

            <!-- MENU SIDEBAR -->
            <nav class="flex-1 p-4 space-y-2 overflow-y-auto">

                <!-- MENU ADMIN -->
                @if(auth()->user()->role === 'admin')

                    <a href="{{ route('admin.dashboard') }}"
                       class="block px-4 py-2 rounded-lg transition
                       {{ request()->routeIs('admin.dashboard')
                           ? 'bg-gray-800 text-white font-medium shadow'
                           : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                        Dashboard
                    </a>

                    <a href="{{ route('admin.user.index') }}"
                       class="block px-4 py-2 rounded-lg transition
                       {{ request()->routeIs('admin.user*')
                           ? 'bg-gray-800 text-white font-medium shadow'
                           : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                        Kelola User
                    </a>

                    <a href="{{ route('admin.kategori.index') }}"
                       class="block px-4 py-2 rounded-lg transition
                       {{ request()->routeIs('admin.kategori*')
                           ? 'bg-gray-800 text-white font-medium shadow'
                           : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                        Kelola Kategori
                    </a>

                    <a href="{{ route('admin.alat.index') }}"
                       class="block px-4 py-2 rounded-lg transition
                       {{ request()->routeIs('admin.alat*')
                           ? 'bg-gray-800 text-white font-medium shadow'
                           : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                        Kelola Alat
                    </a>

                    <a href="{{ route('admin.peminjaman.index') }}"
                       class="block px-4 py-2 rounded-lg transition
                       {{ request()->routeIs('admin.peminjaman*')
                           ? 'bg-gray-800 text-white font-medium shadow'
                           : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                        Kelola Peminjaman
                    </a>

                    <a href="{{ route('admin.pengembalian.index') }}"
                       class="block px-4 py-2 rounded-lg transition
                       {{ request()->routeIs('admin.pengembalian*')
                           ? 'bg-gray-800 text-white font-medium shadow'
                           : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                        Kelola Pengembalian
                    </a>

                    <a href="{{ route('admin.laporan.index') }}"
                       class="block px-4 py-2 rounded-lg transition
                       {{ request()->routeIs('admin.laporan*')
                           ? 'bg-gray-800 text-white font-medium shadow'
                           : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                        Cetak Laporan
                    </a>

                    <a href="{{ route('admin.logAktivitas.index') }}"
                       class="block px-4 py-2 rounded-lg transition
                       {{ request()->routeIs('admin.logAktivitas*')
                           ? 'bg-gray-800 text-white font-medium shadow'
                           : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                        Log Aktivitas
                    </a>

                @endif

                <!-- MENU PETUGAS -->
                @if(auth()->user()->role === 'petugas')

                    <a href="{{ route('petugas.peminjaman.index') }}"
                       class="block px-4 py-2 rounded-lg transition
                       {{ request()->routeIs('petugas.peminjaman*')
                           ? 'bg-gray-800 text-white font-medium shadow'
                           : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                        Persetujuan Peminjaman
                    </a>

                    <a href="{{ route('petugas.pengembalian.index') }}"
                       class="block px-4 py-2 rounded-lg transition
                       {{ request()->routeIs('petugas.pengembalian*')
                           ? 'bg-gray-800 text-white font-medium shadow'
                           : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                        Pemantauan Pengembalian
                    </a>

                    <a href="{{ route('petugas.laporan.index') }}"
                       class="block px-4 py-2 rounded-lg transition
                       {{ request()->routeIs('petugas.laporan*')
                           ? 'bg-gray-800 text-white font-medium shadow'
                           : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                        Cetak Laporan
                    </a>

                @endif

                <!-- MENU PEMINJAM -->
                @if(auth()->user()->role === 'peminjam')

                    <a href="{{ route('peminjam.katalog') }}"
                       class="block px-4 py-2 rounded-lg transition
                       {{ request()->routeIs('peminjam.katalog*')
                           ? 'bg-gray-800 text-white font-medium shadow'
                           : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                        Katalog Alat
                    </a>

                    <a href="{{ route('peminjam.riwayat') }}"
                       class="block px-4 py-2 rounded-lg transition
                       {{ request()->routeIs('peminjam.riwayat*')
                           ? 'bg-gray-800 text-white font-medium shadow'
                           : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                        Riwayat Peminjaman
                    </a>

                @endif

            </nav>

            <!-- PROFIL PENGGUNA DI POJOK KIRI BAWAH -->
            <div class="relative border-t border-gray-800 p-3">

                <!-- TOMBOL PROFIL -->
                <button
                    type="button"
                    id="profileMenuButton"
                    aria-expanded="false"
                    aria-controls="profileDropdown"
                    class="w-full flex items-center gap-3 p-3 rounded-xl hover:bg-gray-800 transition text-left focus:outline-none focus:ring-2 focus:ring-blue-500">

                    <!-- FOTO PROFIL -->
                    <div class="w-11 h-11 rounded-full bg-blue-500 flex items-center justify-center overflow-hidden shrink-0 border-2 border-gray-700">

                        @if(auth()->user()->foto_profile)

                            <img
                                src="{{ asset(auth()->user()->foto_profile) }}"
                                alt="Foto profil {{ auth()->user()->name }}"
                                class="w-full h-full object-cover"
                                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">

                            <span
                                class="hidden w-full h-full items-center justify-center text-white font-bold text-sm uppercase">
                                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                            </span>

                        @else

                            <span class="text-white font-bold text-sm uppercase">
                                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                            </span>

                        @endif

                    </div>

                    <!-- NAMA DAN ROLE -->
                    <div class="flex-1 min-w-0">
                        <p class="text-xs text-gray-400">Logged in as</p>

                        <p class="text-sm font-semibold text-white truncate">
                            {{ auth()->user()->name }}
                        </p>

                        <p class="text-xs text-gray-400 capitalize">
                            {{ auth()->user()->role }}
                        </p>
                    </div>

                    <!-- IKON PANAH -->
                    <svg
                        id="profileArrow"
                        xmlns="http://www.w3.org/2000/svg"
                        class="w-4 h-4 text-gray-400 transition-transform shrink-0"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M19 9l-7 7-7-7" />
                    </svg>

                </button>

                <!-- MENU DROPDOWN -->
                <div
                    id="profileDropdown"
                    class="hidden absolute bottom-full left-3 right-3 mb-2 bg-gray-800 border border-gray-700 rounded-xl shadow-xl overflow-hidden z-50">

                    <div class="px-4 py-3 border-b border-gray-700">
                        <p class="text-sm font-semibold text-white">
                            {{ auth()->user()->name }}
                        </p>

                        <p class="text-xs text-gray-400 break-all">
                            {{ auth()->user()->email }}
                        </p>
                    </div>

                    <!-- PROFIL SAYA -->
                    <a
                        href="{{ route('profile.edit') }}"
                        class="flex items-center gap-3 px-4 py-3 text-sm text-gray-200 hover:bg-gray-700 transition">

                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="w-5 h-5" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round" stroke-width="2"
                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>

                        Profil Saya
                    </a>

                    <!-- LOGOUT -->
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf

                        <button
                            type="submit"
                            class="w-full flex items-center gap-3 px-4 py-3 text-sm text-red-400 hover:bg-gray-700 transition text-left">

                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="w-5 h-5" fill="none"
                                 viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round" stroke-width="2"
                                      d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>

                            Logout
                        </button>
                    </form>

                </div>

            </div>

        </aside>

        <!-- KONTEN UTAMA -->
        <div class="flex-1 flex flex-col overflow-y-auto min-w-0">

            <!-- NAVBAR ATAS -->
            <header class="bg-white shadow-sm min-h-16 flex items-center justify-between px-6 py-3 z-10">
                <div class="text-lg font-semibold text-gray-800">
                    @yield('header-title', 'Dashboard')
                </div>
            </header>

            <!-- KONTEN HALAMAN -->
            <main class="flex-1 p-6">
                @yield('content')
            </main>

        </div>

    </div>

    <!-- JAVASCRIPT DROPDOWN PROFIL -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const button = document.getElementById('profileMenuButton');
            const dropdown = document.getElementById('profileDropdown');
            const arrow = document.getElementById('profileArrow');

            if (!button || !dropdown || !arrow) {
                return;
            }

            button.addEventListener('click', function (event) {
                event.stopPropagation();

                const isHidden = dropdown.classList.toggle('hidden');

                button.setAttribute('aria-expanded', String(!isHidden));
                arrow.classList.toggle('rotate-180', !isHidden);
            });

            document.addEventListener('click', function (event) {
                if (!dropdown.contains(event.target) && !button.contains(event.target)) {
                    dropdown.classList.add('hidden');
                    button.setAttribute('aria-expanded', 'false');
                    arrow.classList.remove('rotate-180');
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    dropdown.classList.add('hidden');
                    button.setAttribute('aria-expanded', 'false');
                    arrow.classList.remove('rotate-180');
                }
            });
        });
    </script>

</body>
</html>