<?php

use App\Http\Controllers\AdminSessionController;
use App\Http\Controllers\AdminUserController;
use App\Http\Middleware\EnsureAdmin;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function () {
    Route::view('/admin/login', 'admin.login')->name('login');
    Route::post('/admin/login', [AdminSessionController::class, 'store'])
        ->middleware('throttle:30,1');
});

Route::post('/admin/logout', [AdminSessionController::class, 'destroy'])
    ->middleware('auth')->name('logout');

Route::prefix('admin')->middleware(['auth', 'auth.session', EnsureAdmin::class])->group(function () {
    Route::get('/', fn () => to_route('admin.users.index'))->name('admin.home');
    Route::get('/users', [AdminUserController::class, 'index'])->name('admin.users.index');
    Route::post('/users', [AdminUserController::class, 'store'])->name('admin.users.store');
    Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('admin.users.edit');
    Route::patch('/users/{user}', [AdminUserController::class, 'update'])->name('admin.users.update');
});
