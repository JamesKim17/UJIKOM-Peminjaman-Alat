<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\KategoriController;
use App\Http\Controllers\API\AlatController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\PeminjamanController;
use App\Http\Controllers\API\PengembalianController;
use App\Http\Controllers\API\LogAktivitasController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Public Routes: tidak memerlukan token
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected Routes: memerlukan token Sanctum
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Route yang dapat diakses admin
    Route::middleware('role.admin')->group(function () {

        // Kategori
        Route::apiResource('kategori', KategoriController::class);

        // Alat
        Route::apiResource('alat', AlatController::class);

        // User
        Route::apiResource('users', UserController::class);

        // Peminjaman
        Route::get('/peminjaman', [PeminjamanController::class, 'index']);
        Route::get('/peminjaman/{peminjaman}', [PeminjamanController::class, 'show']);
        Route::put('/peminjaman/{peminjaman}', [PeminjamanController::class, 'update']);
        Route::delete('/peminjaman/{peminjaman}', [PeminjamanController::class, 'destroy']);

        // Pengembalian
        Route::get('/pengembalian', [PengembalianController::class, 'index']);
        Route::get('/pengembalian/{pengembalian}', [PengembalianController::class, 'show']);
        Route::put('/pengembalian/{pengembalian}', [PengembalianController::class, 'update']);
        Route::delete('/pengembalian/{pengembalian}', [PengembalianController::class, 'destroy']);

        // Log aktivitas
        Route::get('/log-aktivitas', [LogAktivitasController::class, 'index']);
    });

    // Route yang dapat diakses admin dan petugas
    Route::middleware('role.admin,petugas')->group(function () {
        Route::post(
            '/peminjaman/{peminjaman}/approve',
            [PeminjamanController::class, 'approve']
        );
    });

    // Route petugas
    Route::middleware('role.petugas')->group(function () {
        // Pengembalian diproses oleh PengembalianController
        Route::post('/pengembalian', [PengembalianController::class, 'store']);
    });

    // Route peminjam
    Route::middleware('role.peminjam')->group(function () {

        Route::get('/katalog', [AlatController::class, 'katalog']);

        Route::post('/peminjaman', [PeminjamanController::class, 'store']);

        Route::get('/riwayat-pinjam', [PeminjamanController::class, 'riwayat']);
    });
});
