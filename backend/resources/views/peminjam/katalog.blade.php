<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Alat - Peminjam</title>
    <!-- Menggunakan Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 pb-28">

    <!-- Navbar -->
    <nav class="bg-blue-600 text-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <span class="font-bold text-lg">Panel Peminjam</span>
            <div class="flex items-center gap-3">
                <a href="{{ route('peminjam.riwayat') }}" class="bg-blue-500 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg text-sm font-medium transition">
                    Riwayat Pinjam
                </a>
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="bg-white text-blue-600 hover:bg-gray-100 px-3 py-1.5 rounded-lg text-sm font-semibold transition">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Konten Utama -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @if(session('success'))
            <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-lg shadow-sm text-sm">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 bg-red-50 border border-red-200 text-red-800 p-4 rounded-lg shadow-sm text-sm">
                {{ session('error') }}
            </div>
        @endif

        <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Katalog Alat</h1>
                <p class="text-sm text-gray-600 mt-1">Pilih alat yang ingin kamu pinjam, lalu ajukan di bawah.</p>
            </div>
        </div>

        <!-- Form Pengajuan Peminjaman -->
        <form action="{{ route('peminjam.peminjaman.ajukan') }}" method="POST">
            @csrf

            <!-- Grid Card Alat -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 mb-8">
                @forelse($alats as $alat)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden flex flex-col justify-between hover:shadow-md transition">
                        <div>
                            <!-- Gambar Alat -->
                            <div class="h-48 bg-gray-100 flex items-center justify-center overflow-hidden relative">
                                @if(!empty($alat->gambar))
                                    <img src="{{ asset($alat->gambar) }}" alt="{{ $alat->nama_alat }}" class="w-100 h-100 object-cover rounded-lg border">
                                @else
                                    <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                @endif
                            </div>

                            <!-- Detail Info Alat -->
                            <div class="p-4">
                                <h3 class="font-bold text-gray-800 text-base mb-1">{{ $alat->nama_alat }}</h3>
                                <p class="text-xs text-gray-500 mb-3 line-clamp-2">{{ $alat->deskripsi ?? 'Perangkat penunjang operasional.' }}</p>
                                
                                <div class="flex items-center justify-between text-xs text-gray-600 mb-2">
                                    <span class="bg-gray-100 px-2 py-1 rounded text-gray-700">{{ $alat->kategori->nama_kategori ?? 'Umum' }}</span>
                                    <span class="flex items-center gap-1 font-medium text-emerald-600">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Baik
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Bagian Bawah Kartu: Stok & Tombol + / - Jumlah -->
                        <div class="px-4 py-3 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                            <span class="text-xs text-gray-500">Stok {{ $alat->stok }}</span>
                            
                            <!-- Input Hidden untuk mengirim ID alat yang dipilih jika jumlah > 0 -->
                            <input type="hidden" name="alat_id[]" value="{{ $alat->id }}">

                            <div class="flex items-center border border-gray-300 rounded-lg bg-white overflow-hidden">
                                <button type="button" onclick="decrement('jml-{{ $alat->id }}')" class="px-2.5 py-1 text-gray-600 hover:bg-gray-100 text-xs font-bold">-</button>
                                <input type="number" name="jumlah[{{ $alat->id }}]" id="jml-{{ $alat->id }}" value="0" min="0" max="{{ $alat->stok }}" oninput="updateCounter()" class="w-10 text-center text-xs border-none focus:outline-none">
                                <button type="button" onclick="increment('jml-{{ $alat->id }}', {{ $alat->stok }})" class="px-2.5 py-1 text-gray-600 hover:bg-gray-100 text-xs font-bold">+</button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-12 text-center text-gray-500">
                        Belum ada alat yang tersedia di katalog.
                    </div>
                @endforelse
            </div>

            <!-- Sticky Bottom Bar (Bar Mengambang di Bawah) -->
            <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-xl px-6 py-4 flex flex-col sm:flex-row items-center justify-between gap-4 z-50">
                <div class="flex items-center gap-6 w-full sm:w-auto">
                    <span id="selected-counter" class="text-sm font-semibold text-gray-700">0 alat dipilih</span>
                    
                    <div class="flex items-center gap-2">
                        <label for="tgl_kembali_plan" class="text-sm font-medium text-gray-600 whitespace-nowrap">Rencana Kembali</label>
                        <input type="date" name="tgl_kembali_plan" id="tgl_kembali_plan" required
                            class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <button type="submit" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-xl transition shadow-sm text-sm">
                    Ajukan Peminjaman
                </button>
            </div>
        </form>
    </div>

    <!-- JavaScript untuk Tombol + / - dan Penghitung Total Alat -->
    <script>
        function increment(id, maxStock) {
            let input = document.getElementById(id);
            let val = parseInt(input.value || 0);
            if (val < maxStock) {
                input.value = val + 1;
                updateCounter();
            }
        }

        function decrement(id) {
            let input = document.getElementById(id);
            let val = parseInt(input.value || 0);
            if (val > 0) {
                input.value = val - 1;
                updateCounter();
            }
        }

        function updateCounter() {
            let inputs = document.querySelectorAll('input[name^="jumlah["]');
            let total = 0;
            inputs.forEach(input => {
                total += parseInt(input.value || 0);
            });
            document.getElementById('selected-counter').innerText = total + ' alat dipilih';
        }
    </script>
</body>
</html>