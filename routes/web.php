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
use App\Http\Controllers\HistoryController; 
use App\Http\Controllers\RunningTextController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\ShippingController;
use App\Http\Controllers\KanbanTmminsController;
use App\Http\Controllers\AdvertisementController;
use App\Http\Controllers\TokenController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::prefix('api/token')->group(function () {
    Route::post('/validate', [TokenController::class, 'validateToken'])->name('api.token.validate');
    Route::get('/is-expired', [TokenController::class, 'isSystemExpired'])->name('api.token.is-expired');
    Route::get('/expiry', [TokenController::class, 'getSystemExpiry'])->name('api.token.expiry');
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
    Route::get('/preparations/scan', [PreparationController::class, 'scan'])->name('preparations.scan');
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
        Route::get('/delay-data', [DeliveryController::class, 'getDelayData'])->name('getDelayData');
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
        Route::get('/delay-data', [MilkrunController::class, 'getDelayData'])->name('getDelayData');
        
        // Route untuk DN list - HARUS SEBELUM {milkrun} routes
        Route::get('/{milkrun}/dns', [MilkrunController::class, 'getDnList'])->name('dns');
        Route::get('/{milkrun}/edit', [MilkrunController::class, 'edit'])->name('edit');
        Route::put('/{milkrun}', [MilkrunController::class, 'update'])->name('update');
        Route::delete('/{milkrun}', [MilkrunController::class, 'destroy'])->name('destroy');
        
        Route::patch('/{milkrun}/arrival', [MilkrunController::class, 'updateArrival'])->name('updateArrival');
        Route::patch('/{milkrun}/departure', [MilkrunController::class, 'updateDeparture'])->name('updateDeparture');
    });

    // History Routes
    Route::prefix('histories')->name('histories.')->group(function () {
        // Index - list all
        Route::get('/', [HistoryController::class, 'index'])->name('index');
        
        // Delete ALL - HARUS SEBELUM /{history}
        Route::delete('/delete-all', [HistoryController::class, 'deleteAll'])->name('deleteAll');
        
        // Scan to history (dipanggil dari halaman Delivery)
        Route::post('/scan-to-history', [HistoryController::class, 'scanToHistory'])->name('scanToHistory');
        
        // Parameter routes - HARUS DI PALING BAWAH
        Route::get('/{history}', [HistoryController::class, 'show'])->name('show');
        Route::delete('/{history}', [HistoryController::class, 'destroy'])->name('destroy');
        Route::get('/{history}/print', [HistoryController::class, 'print'])->name('print');
    });

    Route::prefix('kanbantmmins')->name('kanbantmmins.')->group(function () {
        Route::get('/', [KanbanTmminsController::class, 'index'])->name('index');
        Route::get('/by-dn', [KanbanTmminsController::class, 'indexByDn'])->name('indexByDn');
        Route::post('/import', [KanbanTmminsController::class, 'importTxt'])->name('import');
        Route::get('/print/{id}', [KanbanTmminsController::class, 'print'])->name('print');
        Route::get('/printall', [KanbanTmminsController::class, 'printAll'])->name('printall');
        Route::get('/print-selected', [KanbanTmminsController::class, 'printSelected'])->name('printselected');
        Route::post('/get-plant-counts', [KanbanTmminsController::class, 'getPlantCounts'])->name('getPlantCounts');
        // Route::get('/print-plant', [KanbanTmminsController::class, 'printByPlant'])->name('printplant');
        Route::get('/print-group', [KanbanTmminsController::class, 'printGroup'])->name('printgroup');
        Route::delete('/destroy-group/{manifest_no}', [KanbanTmminsController::class, 'destroyGroup'])->where('manifest_no', '.*')->name('destroygroup');
        Route::delete('/{id}', [KanbanTmminsController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('advertisements')->name('advertisements.')->group(function () {
        Route::get('/', [AdvertisementController::class, 'index'])->name('index');
        Route::post('/', [AdvertisementController::class, 'store'])->name('store');
        Route::get('/{advertisement}/edit', [AdvertisementController::class, 'edit'])->name('edit');
        Route::put('/{advertisement}', [AdvertisementController::class, 'update'])->name('update');
        Route::delete('/{advertisement}', [AdvertisementController::class, 'destroy'])->name('destroy');
        Route::post('/{advertisement}/toggle', [AdvertisementController::class, 'toggleActive'])->name('toggle');
    });

    // Import Excel
    Route::get('addresses/import', [AddressController::class, 'importPage'])->name('addresses.import.page');
    Route::post('addresses/import', [AddressController::class, 'import'])->name('addresses.import');

    // Address CRUD (tanpa show)
    Route::resource('addresses', AddressController::class)->except(['show']);

    // Running Text Routes
    Route::get('/running-text/data', [RunningTextController::class, 'getData'])->name('running-text.data');
    Route::post('/running-text/update', [RunningTextController::class, 'update'])->name('running-text.update')->middleware('auth');
    

    // Profile Routes (Breeze default)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

    Route::get('/andon/preparations', [PreparationController::class, 'andon'])->name('andon.preparations');
    Route::get('/andon/shippings', [ShippingController::class, 'andon'])->name('andon.shippings');
    Route::get('/andon/shippings-group', [ShippingController::class, 'andonReverse'])->name('andon.shippings.group');
    Route::get('/andon/deliveries', [DeliveryController::class, 'andon'])->name('andon.deliveries');
    Route::get('/andon/deliveries/group', [DeliveryController::class, 'andonReverse'])->name('andon.deliveries.group');
    Route::get('/andon/milkruns', [MilkrunController::class, 'andon'])->name('andon.milkruns');

    Route::get('/api/advertisements/current', [AdvertisementController::class, 'checkCurrentAd'])->name('api.advertisements.current');

require __DIR__.'/auth.php';