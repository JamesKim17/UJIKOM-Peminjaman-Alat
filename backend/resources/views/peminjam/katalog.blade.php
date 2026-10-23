 @extends('layouts.app')

@section('title', 'Katalog Alat - Peminjam')
@section('header-title', 'Katalog Alat')

@section('content')
<div class="pb-28">

    {{-- Notifikasi --}}
    @if(session('success'))
        <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 p-4 rounded-lg text-sm">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 p-4 rounded-lg text-sm">
            <p class="font-semibold mb-1">Periksa kembali data kamu:</p>
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Katalog Alat</h1>
        <p class="text-sm text-gray-600 mt-1">
            Pilih alat yang ingin kamu pinjam, lalu ajukan peminjaman.
        </p>
    </div>

    {{-- Form pengajuan peminjaman: route tetap sama --}}
    <form action="{{ route('peminjam.peminjaman.ajukan') }}" method="POST">
        @csrf

        {{-- Daftar alat --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-6 mb-8">

            @forelse($alats as $alat)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden flex flex-col justify-between hover:shadow-md transition">

                    <div>
                        {{-- Gambar alat --}}
                        <div class="h-48 bg-gray-100 flex items-center justify-center overflow-hidden">
                            @if(!empty($alat->gambar))
                                <img
                                    src="{{ asset($alat->gambar) }}"
                                    alt="{{ $alat->nama_alat }}"
                                    class="w-full h-full object-contain">
                            @else
                                <svg class="w-12 h-12 text-gray-300"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                    </path>
                                </svg>
                            @endif
                        </div>

                        {{-- Informasi alat --}}
                        <div class="p-4">
                            <h3 class="font-bold text-gray-800 text-base mb-2">
                                {{ $alat->nama_alat }}
                            </h3>

                            <p class="text-sm text-gray-500 mb-3">
                                {{ $alat->deskripsi ?? 'Perangkat penunjang operasional.' }}
                            </p>

                            <div class="flex items-center justify-between gap-2 text-xs text-gray-600 mb-2">
                                <span class="bg-gray-100 px-2 py-1 rounded text-gray-700">
                                    {{ $alat->kategori->nama_kategori ?? 'Umum' }}
                                </span>

                                <span class="flex items-center gap-1 font-medium text-emerald-600">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    {{ $alat->status_kondisi ?? 'Baik' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Stok dan jumlah --}}
                    <div class="px-4 py-3 bg-gray-50 border-t border-gray-100 flex items-center justify-between gap-2">
                        <span class="text-xs text-gray-500">
                            Stok {{ $alat->stok }}
                        </span>

                        <input type="hidden" name="alat_id[]" value="{{ $alat->id }}">

                        <div class="flex items-center border border-gray-300 rounded-lg bg-white overflow-hidden">
                            <button
                                type="button"
                                onclick="decrement('jml-{{ $alat->id }}')"
                                class="px-3 py-2 text-gray-600 hover:bg-gray-100 font-bold">
                                −
                            </button>

                            <input
                                type="number"
                                name="jumlah[{{ $alat->id }}]"
                                id="jml-{{ $alat->id }}"
                                value="{{ old('jumlah.' . $alat->id, 0) }}"
                                min="0"
                                max="{{ $alat->stok }}"
                                oninput="validateQuantity(this); updateCounter()"
                                class="w-12 text-center text-sm border-none focus:outline-none">

                            <button
                                type="button"
                                onclick="increment('jml-{{ $alat->id }}', {{ $alat->stok }})"
                                class="px-3 py-2 text-gray-600 hover:bg-gray-100 font-bold">
                                +
                            </button>
                        </div>
                    </div>

                </div>
            @empty
                <div class="col-span-full py-12 text-center text-gray-500 bg-white rounded-xl border border-gray-200">
                    Belum ada alat yang tersedia di katalog.
                </div>
            @endforelse

        </div>

        {{-- Bar pengajuan di bagian bawah --}}
        <div class="fixed bottom-0 left-0 md:left-64 right-0 bg-white border-t border-gray-200 shadow-xl px-4 md:px-6 py-4 flex flex-col sm:flex-row items-center justify-between gap-3 z-40">

            <div class="flex flex-wrap items-center gap-4">
                <span id="selected-counter" class="text-sm font-semibold text-gray-700">
                    0 alat dipilih
                </span>

                <div class="flex items-center gap-2">
                    <label for="tgl_kembali_plan" class="text-sm font-medium text-gray-600 whitespace-nowrap">
                        Rencana Kembali
                    </label>

                    <input
                        type="date"
                        name="tgl_kembali_plan"
                        id="tgl_kembali_plan"
                        value="{{ old('tgl_kembali_plan') }}"
                        min="{{ now()->toDateString() }}"
                        required
                        class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <button
                type="submit"
                class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-xl transition shadow-sm text-sm">
                Ajukan Peminjaman
            </button>

        </div>

    </form>
</div>

{{-- JavaScript jumlah alat --}}
<script>
    function increment(id, maxStock) {
        const input = document.getElementById(id);
        const value = parseInt(input.value || 0);

        if (value < maxStock) {
            input.value = value + 1;
        }

        updateCounter();
    }

    function decrement(id) {
        const input = document.getElementById(id);
        const value = parseInt(input.value || 0);

        if (value > 0) {
            input.value = value - 1;
        }

        updateCounter();
    }

    function validateQuantity(input) {
        let value = parseInt(input.value || 0);
        const maxStock = parseInt(input.max || 0);

        if (isNaN(value) || value < 0) {
            value = 0;
        }

        if (value > maxStock) {
            value = maxStock;
        }

        input.value = value;
    }

    function updateCounter() {
        const inputs = document.querySelectorAll('input[name^="jumlah["]');
        let total = 0;

        inputs.forEach(function(input) {
            total += parseInt(input.value || 0);
        });

        document.getElementById('selected-counter').innerText =
            total + ' alat dipilih';
    }

    document.addEventListener('DOMContentLoaded', updateCounter);
</script>
@endsection