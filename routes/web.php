<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PreparationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Route Delete All Preparations (HARUS SEBELUM RESOURCE!)
    Route::delete('/preparations/delete-all', [PreparationController::class, 'deleteAll'])->name('preparations.deleteAll');
    
    // Preparations CRUD
    Route::resource('preparations', PreparationController::class);

    // Profile Routes (Breeze default)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';