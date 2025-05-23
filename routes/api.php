<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
// Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
// Route::post('/login', [AuthController::class, 'login'])->name('login.post');
// Route::get('/admin/dashboard', function () {
//     return 'Dashboard Admin';
// })->middleware(['auth', 'role:admin']);

// Route::get('/mahasiswa/dashboard', function () {
//     return 'Dashboard Mahasiswa';
// })->middleware(['auth', 'role:mahasiswa']);

// Route::get('/dosen/dashboard', function () {
//     return 'Dashboard Dosen';
// })->middleware(['auth', 'role:dosen']);
// Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
// Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
// Route::post('/register', [AuthController::class, 'register'])->name('register.post');