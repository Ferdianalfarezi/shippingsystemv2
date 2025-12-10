<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PreparationController;
use App\Http\Controllers\ImportExcelController;
use App\Http\Controllers\LpConfigController;
use App\Http\Controllers\ImportTmminController;
use App\Http\Controllers\ImportAdmController;
use App\Http\Controllers\AdmLeadTimeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\MilkrunController;

use App\Http\Controllers\ShippingController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {
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
    Route::get('/preparations/find-by-dn', [App\Http\Controllers\PreparationController::class, 'findByDn'])
    ->name('preparations.findByDn');

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


    Route::prefix('shippings')->name('shippings.')->group(function () {
        // Existing routes... (pastikan sudah ada)
        Route::get('/', [ShippingController::class, 'index'])->name('index');
        Route::get('/reverse', [ShippingController::class, 'indexReverse'])->name('indexReverse');
        Route::get('/checking-lp', [ShippingController::class, 'checkingLp'])->name('checkingLp');
        
        Route::get('/{shipping}/edit', [ShippingController::class, 'edit'])->name('edit');
        Route::put('/{shipping}', [ShippingController::class, 'update'])->name('update');
        Route::delete('/{shipping}', [ShippingController::class, 'destroy'])->name('destroy');
        
        Route::delete('/delete-all', [ShippingController::class, 'deleteAll'])->name('deleteAll');
        Route::post('/move-from-preparation', [ShippingController::class, 'moveFromPreparation'])->name('moveFromPreparation');
        
        Route::post('/check-route', [ShippingController::class, 'checkRoute'])->name('checkRoute');
        Route::post('/scan-route', [ShippingController::class, 'scanRoute'])->name('scanRoute');
        Route::get('/get-by-route', [ShippingController::class, 'getByRoute'])->name('getByRoute');
        
        // NEW: Routes untuk move to delivery
        Route::post('/move-to-delivery', [ShippingController::class, 'moveToDelivery'])->name('moveToDelivery');
        Route::post('/scan-to-delivery', [ShippingController::class, 'scanToDelivery'])->name('scanToDelivery');
        Route::post('/move-to-delivery-by-route', [ShippingController::class, 'moveToDeliveryByRoute'])->name('moveToDeliveryByRoute');
        Route::get('/find-by-dn', [ShippingController::class, 'findByDn'])->name('findByDn');
    });

    // Delivery Routes
    Route::prefix('deliveries')->name('deliveries.')->group(function () {
        // Index views
        Route::get('/', [DeliveryController::class, 'index'])->name('index');
        Route::get('/reverse', [DeliveryController::class, 'indexReverse'])->name('indexReverse');
        
        // CRUD operations
        Route::get('/{delivery}/edit', [DeliveryController::class, 'edit'])->name('edit');
        Route::put('/{delivery}', [DeliveryController::class, 'update'])->name('update');
        Route::delete('/{delivery}', [DeliveryController::class, 'destroy'])->name('destroy');
        
        // Bulk operations
        Route::delete('/delete-all', [DeliveryController::class, 'deleteAll'])->name('deleteAll');
        
        // Move from Shipping
        Route::post('/move-from-shipping', [DeliveryController::class, 'moveFromShipping'])->name('moveFromShipping');
        Route::post('/move-by-route', [DeliveryController::class, 'moveByRoute'])->name('moveByRoute');
        
        // Status update
        Route::patch('/{delivery}/status', [DeliveryController::class, 'updateStatus'])->name('updateStatus');
        
        // Find by DN
        Route::get('/find-by-dn', [DeliveryController::class, 'findByDn'])->name('findByDn');
    });

    Route::prefix('milkruns')->name('milkruns.')->group(function () {
        Route::get('/', [MilkrunController::class, 'index'])->name('index');
        Route::delete('/delete-all', [MilkrunController::class, 'deleteAll'])->name('deleteAll');
        Route::post('/scan-arrival', [MilkrunController::class, 'scanArrival'])->name('scanArrival');
        
        // Route untuk DN list - HARUS SEBELUM {milkrun} routes
        Route::get('/{milkrun}/dns', [MilkrunController::class, 'getDnList'])->name('dns');
        Route::get('/{milkrun}/edit', [MilkrunController::class, 'edit'])->name('edit');
        Route::put('/{milkrun}', [MilkrunController::class, 'update'])->name('update');
        Route::delete('/{milkrun}', [MilkrunController::class, 'destroy'])->name('destroy');
        
        Route::patch('/{milkrun}/arrival', [MilkrunController::class, 'updateArrival'])->name('updateArrival');
        Route::patch('/{milkrun}/departure', [MilkrunController::class, 'updateDeparture'])->name('updateDeparture');
    });

    // Profile Routes (Breeze default)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';