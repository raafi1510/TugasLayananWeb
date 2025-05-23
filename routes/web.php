<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/admin/dashboard', function () {
    return 'Dashboard Admin';
})->middleware(['auth', 'role:admin']);

Route::get('/mahasiswa/dashboard', function () {
    return 'Dashboard Mahasiswa';
})->middleware(['auth', 'role:mahasiswa']);

Route::get('/dosen/dashboard', function () {
    return 'Dashboard Dosen';
})->middleware(['auth', 'role:dosen']);