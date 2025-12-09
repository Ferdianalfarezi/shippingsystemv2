<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PreparationController;
use App\Http\Controllers\ImportExcelController;
use App\Http\Controllers\LpConfigController;
use App\Http\Controllers\ImportTmminController;
use App\Http\Controllers\ImportAdmController;
use App\Http\Controllers\AdmLeadTimeController;
use App\Http\Controllers\UserController;

use App\Http\Controllers\ShippingController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::resource('users', UserController::class);

    // Route Delete All Preparations (HARUS SEBELUM RESOURCE!)
    Route::delete('/preparations/delete-all', [PreparationController::class, 'deleteAll'])->name('preparations.deleteAll');
    Route::post('/preparations/import', [ImportExcelController::class, 'import'])->name('preparations.import');
    
    Route::get('/import-excel/download-template', [ImportExcelController::class, 'downloadTemplate'])->name('import-excel.download-template');
    Route::post('/preparations/import-tmmin', [ImportTmminController::class, 'import'])
    ->name('preparations.import-tmmin');

    Route::post('/preparations/import-adm', [App\Http\Controllers\ImportAdmController::class, 'import'])->name('preparations.import-adm');

    // Preparations CRUD
    Route::resource('preparations', PreparationController::class);

    Route::resource('lp-configs', LpConfigController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::post('lp-configs/batch-save', [LpConfigController::class, 'batchSave'])->name('lp-configs.batch-save');


    Route::prefix('adm-lead-time')->name('adm-lead-time.')->group(function () {
        Route::get('/', [AdmLeadTimeController::class, 'index'])->name('index');
        Route::post('/', [AdmLeadTimeController::class, 'store'])->name('store');
        Route::post('/batch-save', [AdmLeadTimeController::class, 'batchSave'])->name('batch-save');
        Route::put('/{id}', [AdmLeadTimeController::class, 'update'])->name('update');
        Route::delete('/{id}', [AdmLeadTimeController::class, 'destroy'])->name('destroy');
    });


    // Shipping Routes
    Route::prefix('shippings')->name('shippings.')->group(function () {
        Route::get('/', [ShippingController::class, 'index'])->name('index');
        Route::get('/reverse', [ShippingController::class, 'indexReverse'])->name('indexReverse');
        Route::post('/move-from-preparation', [ShippingController::class, 'moveFromPreparation'])->name('moveFromPreparation');
        Route::get('/{shipping}/edit', [ShippingController::class, 'edit'])->name('edit');
        Route::put('/{shipping}', [ShippingController::class, 'update'])->name('update');
        Route::delete('/{shipping}', [ShippingController::class, 'destroy'])->name('destroy');
        Route::delete('/delete/all', [ShippingController::class, 'deleteAll'])->name('deleteAll');
        
        // Checking LP Routes
        Route::get('/checking-lp', [ShippingController::class, 'checkingLp'])->name('checkingLp');
        Route::post('/check-route', [ShippingController::class, 'checkRoute'])->name('checkRoute');
        Route::post('/scan-route', [ShippingController::class, 'scanRoute'])->name('scanRoute');
        Route::get('/by-route', [ShippingController::class, 'getByRoute'])->name('byRoute');
    });

    // Profile Routes (Breeze default)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';