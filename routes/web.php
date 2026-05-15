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
use App\Http\Controllers\KanbanHpmController;
use App\Http\Controllers\HpmAddressController;
use App\Http\Controllers\SlipHpmController;
use App\Http\Controllers\PullingMatrixController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Root
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => redirect()->route('login'));

/*
|--------------------------------------------------------------------------
| Public API
|--------------------------------------------------------------------------
*/
Route::prefix('api')->group(function () {

    Route::prefix('token')->name('api.token.')->group(function () {
        Route::post('/validate',   [TokenController::class, 'validateToken'])  ->name('validate');
        Route::get('/is-expired',  [TokenController::class, 'isSystemExpired'])->name('is-expired');
        Route::get('/expiry',      [TokenController::class, 'getSystemExpiry'])->name('expiry');
    });

    Route::get('/advertisements/current', [AdvertisementController::class, 'checkCurrentAd'])
        ->name('api.advertisements.current');
});

/*
|--------------------------------------------------------------------------
| Andon (public, no auth required)
|--------------------------------------------------------------------------
*/
Route::prefix('andon')->name('andon.')->group(function () {
    Route::get('/preparations',      [PreparationController::class, 'andon'])        ->name('preparations');
    Route::get('/shippings',         [ShippingController::class,    'andon'])        ->name('shippings');
    Route::get('/shippings-group',   [ShippingController::class,    'andonReverse']) ->name('shippings.group');
    Route::get('/deliveries',        [DeliveryController::class,    'andon'])        ->name('deliveries');
    Route::get('/deliveries/group',  [DeliveryController::class,    'andonReverse']) ->name('deliveries.group');
    Route::get('/milkruns',          [MilkrunController::class,     'andon'])        ->name('milkruns');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    /*
    |----------------------------------------------------------------------
    | Dashboard
    |----------------------------------------------------------------------
    */
    Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');

    /*
    |----------------------------------------------------------------------
    | Profile (Breeze)
    |----------------------------------------------------------------------
    */
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/',    [ProfileController::class, 'edit'])   ->name('edit');
        Route::patch('/',  [ProfileController::class, 'update']) ->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    });

    /*
    |----------------------------------------------------------------------
    | Users
    |----------------------------------------------------------------------
    */
    Route::resource('users', UserController::class);

    /*
    |----------------------------------------------------------------------
    | Preparations
    |----------------------------------------------------------------------
    */
    // Aksi khusus HARUS di atas Route::resource supaya tidak tertutupi
    Route::delete('/preparations/delete-all', [PreparationController::class,  'deleteAll'])->name('preparations.deleteAll');
    Route::get('/preparations/find-by-dn',    [PreparationController::class,  'findByDn']) ->name('preparations.findByDn');
    Route::get('/preparations/scan',          [PreparationController::class,  'scan'])     ->name('preparations.scan');

    Route::post('/preparations/import',       [ImportExcelController::class,  'import'])   ->name('preparations.import');
    Route::post('/preparations/import-tmmin', [ImportTmminController::class,  'import'])   ->name('preparations.import-tmmin');
    Route::post('/preparations/import-adm',   [ImportAdmController::class,    'import'])   ->name('preparations.import-adm');

    Route::get('/import-excel/download-template', [ImportExcelController::class, 'downloadTemplate'])
        ->name('import-excel.download-template');

    Route::resource('preparations', PreparationController::class);

    /*
    |----------------------------------------------------------------------
    | LP Config
    |----------------------------------------------------------------------
    */
    Route::resource('lp-configs', LpConfigController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::post('lp-configs/batch-save', [LpConfigController::class, 'batchSave'])->name('lp-configs.batch-save');

    /*
    |----------------------------------------------------------------------
    | ADM Lead Time
    |----------------------------------------------------------------------
    */
    Route::prefix('adm-lead-time')->name('adm-lead-time.')->group(function () {
        Route::get('/',            [AdmLeadTimeController::class, 'index'])    ->name('index');
        Route::post('/',           [AdmLeadTimeController::class, 'store'])    ->name('store');
        Route::post('/batch-save', [AdmLeadTimeController::class, 'batchSave'])->name('batch-save');
        Route::put('/{id}',        [AdmLeadTimeController::class, 'update'])   ->name('update');
        Route::delete('/{id}',     [AdmLeadTimeController::class, 'destroy'])  ->name('destroy');
    });

    /*
    |----------------------------------------------------------------------
    | Pulling Matrix
    |----------------------------------------------------------------------
    */
    Route::prefix('pulling-matrix')->name('pulling-matrix.')->group(function () {
        Route::get('/',            [PullingMatrixController::class, 'index'])    ->name('index');
        Route::post('/',           [PullingMatrixController::class, 'store'])    ->name('store');
        Route::post('/batch-save', [PullingMatrixController::class, 'batchSave'])->name('batch-save');
        Route::put('/{id}',        [PullingMatrixController::class, 'update'])   ->name('update');
        Route::delete('/{id}',     [PullingMatrixController::class, 'destroy'])  ->name('destroy');
    });

    /*
    |----------------------------------------------------------------------
    | Shippings
    |----------------------------------------------------------------------
    */
    Route::prefix('shippings')->name('shippings.')->group(function () {
        Route::get('/',         [ShippingController::class, 'index'])       ->name('index');
        Route::get('/reverse',  [ShippingController::class, 'indexReverse'])->name('indexReverse');
        Route::get('/checking-lp', [ShippingController::class, 'checkingLp'])->name('checkingLp');

        // Aksi khusus — HARUS sebelum /{shipping}
        Route::delete('/delete-all',              [ShippingController::class, 'deleteAll'])            ->name('deleteAll');
        Route::post('/move-from-preparation',     [ShippingController::class, 'moveFromPreparation'])  ->name('moveFromPreparation');
        Route::post('/move-to-delivery',          [ShippingController::class, 'moveToDelivery'])       ->name('moveToDelivery');
        Route::post('/scan-to-delivery',          [ShippingController::class, 'scanToDelivery'])       ->name('scanToDelivery');
        Route::post('/move-to-delivery-by-route', [ShippingController::class, 'moveToDeliveryByRoute'])->name('moveToDeliveryByRoute');
        Route::post('/check-route',               [ShippingController::class, 'checkRoute'])           ->name('checkRoute');
        Route::post('/scan-route',                [ShippingController::class, 'scanRoute'])            ->name('scanRoute');
        Route::get('/get-by-route',               [ShippingController::class, 'getByRoute'])           ->name('getByRoute');
        Route::get('/find-by-dn',                 [ShippingController::class, 'findByDn'])             ->name('findByDn');

        // Parameter routes — HARUS di bawah aksi khusus
        Route::get('/{shipping}/edit', [ShippingController::class, 'edit'])   ->name('edit');
        Route::put('/{shipping}',      [ShippingController::class, 'update']) ->name('update');
        Route::delete('/{shipping}',   [ShippingController::class, 'destroy'])->name('destroy');
    });

    /*
    |----------------------------------------------------------------------
    | Deliveries
    |----------------------------------------------------------------------
    */
    Route::prefix('deliveries')->name('deliveries.')->group(function () {
        Route::get('/',        [DeliveryController::class, 'index'])       ->name('index');
        Route::get('/reverse', [DeliveryController::class, 'indexReverse'])->name('indexReverse');

        // Aksi khusus — HARUS sebelum /{delivery}
        Route::delete('/delete-all',         [DeliveryController::class, 'deleteAll'])      ->name('deleteAll');
        Route::post('/move-from-shipping',   [DeliveryController::class, 'moveFromShipping'])->name('moveFromShipping');
        Route::post('/move-by-route',        [DeliveryController::class, 'moveByRoute'])     ->name('moveByRoute');
        Route::get('/find-by-dn',            [DeliveryController::class, 'findByDn'])        ->name('findByDn');
        Route::get('/delay-data',            [DeliveryController::class, 'getDelayData'])    ->name('getDelayData');

        // Parameter routes
        Route::get('/{delivery}/edit',        [DeliveryController::class, 'edit'])         ->name('edit');
        Route::put('/{delivery}',             [DeliveryController::class, 'update'])       ->name('update');
        Route::delete('/{delivery}',          [DeliveryController::class, 'destroy'])      ->name('destroy');
        Route::patch('/{delivery}/status',    [DeliveryController::class, 'updateStatus']) ->name('updateStatus');
    });

    /*
    |----------------------------------------------------------------------
    | Milkruns
    |----------------------------------------------------------------------
    */
    Route::prefix('milkruns')->name('milkruns.')->group(function () {
        Route::get('/', [MilkrunController::class, 'index'])->name('index');

        // Aksi khusus — HARUS sebelum /{milkrun}
        Route::delete('/delete-all',   [MilkrunController::class, 'deleteAll'])  ->name('deleteAll');
        Route::post('/scan-arrival',   [MilkrunController::class, 'scanArrival'])->name('scanArrival');
        Route::get('/delay-data',      [MilkrunController::class, 'getDelayData'])->name('getDelayData');

        // Parameter routes
        Route::get('/{milkrun}/dns',        [MilkrunController::class, 'getDnList'])       ->name('dns');
        Route::get('/{milkrun}/edit',       [MilkrunController::class, 'edit'])            ->name('edit');
        Route::put('/{milkrun}',            [MilkrunController::class, 'update'])          ->name('update');
        Route::delete('/{milkrun}',         [MilkrunController::class, 'destroy'])         ->name('destroy');
        Route::patch('/{milkrun}/arrival',  [MilkrunController::class, 'updateArrival'])   ->name('updateArrival');
        Route::patch('/{milkrun}/departure',[MilkrunController::class, 'updateDeparture']) ->name('updateDeparture');
    });

    /*
    |----------------------------------------------------------------------
    | Histories
    |----------------------------------------------------------------------
    */
    Route::prefix('histories')->name('histories.')->group(function () {
        Route::get('/', [HistoryController::class, 'index'])->name('index');

        // Aksi khusus — HARUS sebelum /{history}
        Route::delete('/delete-all',    [HistoryController::class, 'deleteAll'])   ->name('deleteAll');
        Route::post('/scan-to-history', [HistoryController::class, 'scanToHistory'])->name('scanToHistory');

        // Parameter routes
        Route::get('/{history}',       [HistoryController::class, 'show'])   ->name('show');
        Route::delete('/{history}',    [HistoryController::class, 'destroy'])->name('destroy');
        Route::get('/{history}/print', [HistoryController::class, 'print'])  ->name('print');
    });

    /*
    |----------------------------------------------------------------------
    | Kanban TMMIN
    |----------------------------------------------------------------------
    */
    Route::prefix('kanbantmmins')->name('kanbantmmins.')->group(function () {
        Route::get('/',              [KanbanTmminsController::class, 'index'])            ->name('index');
        Route::get('/by-dn',         [KanbanTmminsController::class, 'indexByDn'])        ->name('indexByDn');
        Route::get('/plant-counts',  [KanbanTmminsController::class, 'getPlantCounts'])   ->name('getPlantCounts');
        Route::get('/printall',      [KanbanTmminsController::class, 'printAll'])         ->name('printall');
        Route::get('/print-selected',[KanbanTmminsController::class, 'printSelected'])    ->name('printselected');
        Route::get('/print-group',   [KanbanTmminsController::class, 'printGroup'])       ->name('printgroup');
        Route::get('/plant-counts-by-ids', [KanbanTmminsController::class, 'getPlantCountsByIds'])->name('plantcountsbyids');

        Route::post('/import',       [KanbanTmminsController::class, 'importTxt'])        ->name('import');

        // Parameter routes
        Route::get('/print/{id}',    [KanbanTmminsController::class, 'print'])            ->name('print');
        Route::delete('/destroy-group/{manifest_no}', [KanbanTmminsController::class, 'destroyGroup'])
            ->where('manifest_no', '.*')
            ->name('destroygroup');
        Route::delete('/{id}',       [KanbanTmminsController::class, 'destroy'])          ->name('destroy');
    });

    /*
    |----------------------------------------------------------------------
    | Kanban HPM
    |----------------------------------------------------------------------
    */
    Route::prefix('kanbanhpms')->name('kanbanhpms.')->group(function () {
        Route::get('/',              [KanbanHpmController::class, 'index'])         ->name('index');
        Route::get('/printall',      [KanbanHpmController::class, 'printAll'])      ->name('printall');
        Route::match(['get', 'post'], '/print-filtered', [KanbanHpmController::class, 'printFiltered'])->name('printFiltered');

        Route::post('/import',       [KanbanHpmController::class, 'importTxt'])     ->name('import');
        Route::post('/adjust-weekly',[KanbanHpmController::class, 'adjustWeekly'])  ->name('adjustWeekly');

        Route::delete('/{id}',       [KanbanHpmController::class, 'destroy'])       ->name('destroy');
    });

    /*
    |----------------------------------------------------------------------
    | Slip HPM
    |----------------------------------------------------------------------
    */
    Route::prefix('sliphpms')->name('sliphpms.')->group(function () {
        Route::get('/',               [SlipHpmController::class, 'index'])         ->name('index');
        Route::get('/print-filtered', [SlipHpmController::class, 'printFiltered']) ->name('printFiltered');

        Route::post('/import',        [SlipHpmController::class, 'import'])        ->name('import');

        Route::delete('/{sliphpm}',   [SlipHpmController::class, 'destroy'])       ->name('destroy');
    });

    /*
    |----------------------------------------------------------------------
    | Advertisements
    |----------------------------------------------------------------------
    */
    Route::prefix('advertisements')->name('advertisements.')->group(function () {
        Route::get('/',                      [AdvertisementController::class, 'index'])       ->name('index');
        Route::post('/',                     [AdvertisementController::class, 'store'])       ->name('store');
        Route::get('/{advertisement}/edit',  [AdvertisementController::class, 'edit'])        ->name('edit');
        Route::put('/{advertisement}',       [AdvertisementController::class, 'update'])      ->name('update');
        Route::delete('/{advertisement}',    [AdvertisementController::class, 'destroy'])     ->name('destroy');
        Route::post('/{advertisement}/toggle',[AdvertisementController::class, 'toggleActive'])->name('toggle');
    });

    /*
    |----------------------------------------------------------------------
    | Addresses
    |----------------------------------------------------------------------
    */
    Route::prefix('addresses')->name('addresses.')->group(function () {
        Route::get('/import',       [AddressController::class, 'importPage']) ->name('import.page');
        Route::post('/import',      [AddressController::class, 'import'])     ->name('import');
        Route::post('/import-rack', [AddressController::class, 'importRack']) ->name('import-rack');
    });
    Route::resource('addresses', AddressController::class)->except(['show']);

    /*
    |----------------------------------------------------------------------
    | HPM Addresses
    |----------------------------------------------------------------------
    */
    Route::prefix('hpm-addresses')->name('hpm-addresses.')->group(function () {
        Route::get('/',                    [HpmAddressController::class, 'index'])  ->name('index');
        Route::post('/',                   [HpmAddressController::class, 'store'])  ->name('store');
        Route::get('/{hpmAddress}/edit',   [HpmAddressController::class, 'edit'])   ->name('edit');
        Route::put('/{hpmAddress}',        [HpmAddressController::class, 'update']) ->name('update');
        Route::delete('/{hpmAddress}',     [HpmAddressController::class, 'destroy'])->name('destroy');
        Route::post('/import',             [HpmAddressController::class, 'import']) ->name('import');
    });

    /*
    |----------------------------------------------------------------------
    | Running Text
    |----------------------------------------------------------------------
    */
    Route::prefix('running-text')->name('running-text.')->group(function () {
        Route::get('/data',    [RunningTextController::class, 'getData'])->name('data');
        Route::post('/update', [RunningTextController::class, 'update']) ->name('update');
    });

}); // end middleware auth

require __DIR__.'/auth.php';