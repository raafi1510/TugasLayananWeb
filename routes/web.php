<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\UserApiController;
use App\Http\Controllers\Api\MahasiswaApiController;
use App\Http\Controllers\Api\DosenApiController;
use App\Http\Controllers\Api\MataKuliahApiController;


Route::get('/', function () {
    return view('welcome');
});
Route::prefix('api')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/admin/dashboard', function () {
        return 'Dashboard Admin';
    })->middleware(['auth', 'role:admin']);

    Route::get('/mahasiswa/dashboard', function () {
        return 'Dashboard Mahasiswa';
    })->middleware(['auth', 'role:mahasiswa']);

    Route::get('/dosen/dashboard', function () {
        return 'Dashboard Dosen';
    })->middleware(['auth', 'role:dosen']);

    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
    Route::get('/users', [UserApiController::class, 'index']);
    Route::get('/mahasiswa', [MahasiswaApiController::class, 'index']);
    Route::get('/dosen', [DosenApiController::class, 'index']);

    Route::get('/matakuliah', [MataKuliahApiController::class, 'index']);
    Route::post('/matakuliah', [MataKuliahApiController::class, 'store']);
    Route::post('/user', [UserApiController::class, 'store']);
    Route::post('/mahasiswa', [MahasiswaApiController::class, 'store']);
    Route::post('/dosen', [DosenApiController::class, 'store']);
    Route::delete('/user/{id}', [UserApiController::class, 'destroy']);
    Route::delete('/matakuliah/{id}', [MataKuliahApiController::class, 'destroy']);
    Route::delete('/mahasiswa/{id}', [MahasiswaApiController::class, 'destroy']);
    Route::delete('/dosen/{id}', [DosenApiController::class, 'destroy']);
});