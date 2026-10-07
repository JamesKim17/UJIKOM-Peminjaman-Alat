<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Peminjaman;
use App\Models\Alat;
use App\Models\Pengembalian;
use App\Observers\AlatObserver;
use App\Observers\PeminjamanObserver;
use App\Observers\PengembalianObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        # Observer jang alat
        Alat::observe(AlatObserver::class);

        # Observer jang peminjaman
        Peminjaman::observe(PeminjamanObserver::class);

        # Observer jang pengembalian
        Pengembalian::observe(PengembalianObserver::class);
    }
}