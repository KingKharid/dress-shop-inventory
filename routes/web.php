<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DressController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});



Route::middleware(['auth', 'role:admin,super_admin'])->group(function () {
    Route::resource('dresses', DressController::class)->except(['edit', 'update', 'destroy']);
    Route::post('/dresses/{dress}/mark-sold', [DressController::class, 'markAsSold']);
    Route::patch('/dresses/{id}/sold', [\App\Http\Controllers\DressController::class, 'markAsSold'])->name('dresses.sold');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

Route::middleware(['auth', 'role:super_admin'])->group(function () {
    Route::delete('dresses/{dress}', [DressController::class, 'destroy'])->name('dresses.destroy');
});



require __DIR__.'/auth.php';
