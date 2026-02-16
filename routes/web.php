<?php

use App\Http\Controllers\PNL\DashboardController;
use App\Http\Controllers\PNL\MasterDataController;
use App\Http\Controllers\PNL\NettInvoiceController;
use App\Http\Controllers\PNL\NonRegulerController;
use App\Http\Controllers\PNL\RegulerController;
use App\Http\Controllers\User\AuthController;
use App\Http\Controllers\Utilities\MailSenderController;
use App\Http\Controllers\Utilities\SettingController;
use App\Http\Middleware\AuthnCheck;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->group(function () {
    Route::get('/login', 'index')->name('login')->withoutMiddleware([AuthnCheck::class]);
    Route::post('/login', 'login')->name('login.post')->withoutMiddleware([AuthnCheck::class]);
    Route::get('/logout', 'logout')->name('logout')->withoutMiddleware([AuthnCheck::class]);
});

Route::controller(DashboardController::class)->group(function () {
    Route::get('/', 'index')->name('dashboard.main')->middleware([AuthnCheck::class]);
    Route::get('/pnl', 'index')->name('dashboard')->middleware([AuthnCheck::class]);
    Route::get('/pnl/dashboard', 'index')->name('dashboard.index')->middleware([AuthnCheck::class]);
});

// ... (previous routes)

Route::controller(RegulerController::class)->group(function () {
    // Pajak Keluaran
    Route::middleware('menu.access:reguler-pajak-keluaran')->group(function () {
        Route::get('/pnl/reguler/pajak-keluaran', 'pkIndex')->name('pnl.reguler.pajak-keluaran.index')->middleware([AuthnCheck::class]);
        Route::post('/pnl/reguler/pajak-keluaran/dtdata', 'dtPKGetData')->name('pnl.reguler.pajak-keluaran.dtdata');
        Route::get('/pnl/reguler/pajak-keluaran-db', 'pkDbIndex')->name('pnl.reguler.pajak-keluaran-db.index')->middleware([AuthnCheck::class]);
        Route::post('/pnl/reguler/pajak-keluaran-db/dtdata', 'dtPKDbGetData')->name('pnl.reguler.pajak-keluaran-db.dtdata');
        Route::post('/pnl/reguler/pajak-keluaran/update-checked', 'updateChecked')->name('pnl.reguler.pajak-keluaran.updateChecked');
        Route::post('/pnl/reguler/pajak-keluaran/update-move2', 'updateMove2')->name('pnl.reguler.pajak-keluaran.updateMove2');
        Route::get('/pnl/reguler/pajak-keluaran/export', 'download')->name('pnl.reguler.pajak-keluaran.download');
        Route::get('/pnl/reguler/pajak-keluaran-db/export', 'downloadDb')->name('pnl.reguler.pajak-keluaran-db.download');
        Route::post('/pnl/reguler/pajak-keluaran/count', 'count')->name('pnl.reguler.pajak-keluaran.count');
        Route::post('/pnl/reguler/pajak-keluaran/available-dates', 'getAvailableDates')->name('pnl.reguler.pajak-keluaran.available-dates');
    });

    // Pajak Masukan
    Route::middleware('menu.access:reguler-pajak-masukan')->group(function () {
        Route::get('/pnl/reguler/pajak-masukan', 'pmIndex')->name('pnl.reguler.pajak-masukan.index')->middleware([AuthnCheck::class]);
    });

    // Upload CSV
    Route::middleware('menu.access:reguler-upload-csv')->group(function () {
        Route::get('/pnl/reguler/uploadcsv', 'pmUploadCsvIndex')->name('pnl.reguler.pajak-masukan.uploadcsv')->middleware([AuthnCheck::class]);
        Route::post('/pnl/reguler/uploadcsv', 'uploadPMCoretax')->name('pnl.reguler.pajak-masukan.uploadcsv.process');
    });
});

Route::controller(NettInvoiceController::class)->group(function () {
    Route::middleware('menu.access:reguler-nett-invoice')->group(function () {
        Route::get('/pnl/reguler/nett-invoice', 'index')->name('pnl.reguler.nett-invoice.index')->middleware([AuthnCheck::class]);
        Route::post('/pnl/reguler/nett-invoice/data', 'getData')->name('pnl.reguler.nett-invoice.data');
        Route::post('/pnl/reguler/nett-invoice/detail', 'getInvoiceDetail')->name('pnl.reguler.nett-invoice.detail');
        Route::post('/pnl/reguler/nett-invoice/retur-list', 'getReturList')->name('pnl.reguler.nett-invoice.retur-list');
        Route::post('/pnl/reguler/nett-invoice/process', 'processNett')->name('pnl.reguler.nett-invoice.process');
        Route::post('/pnl/reguler/nett-invoice/npkp-list', 'getNonPkpList')->name('pnl.reguler.nett-invoice.npkp-list');
        Route::post('/pnl/reguler/nett-invoice/available-dates', 'getAvailableDates')
            ->middleware('throttle:30,1')
            ->name('pnl.reguler.nett-invoice.available-dates');
        Route::post('/pnl/reguler/nett-invoice/history', 'getNettHistory')->name('pnl.reguler.nett-invoice.history');
        Route::get('/pnl/reguler/nett-invoice/export', 'exportData')->name('pnl.reguler.nett-invoice.export');
    });
});

Route::controller(NonRegulerController::class)->group(function () {
    Route::get('/pnl/non-reguler/pajak-keluaran', 'pkIndex')
        ->name('pnl.non-reguler.pajak-keluaran.index')
        ->middleware([AuthnCheck::class, 'menu.access:non-reguler-pajak-keluaran']);

    Route::get('/pnl/non-reguler/pajak-masukan', 'pmIndex')
        ->name('pnl.non-reguler.pajak-masukan.index')
        ->middleware([AuthnCheck::class, 'menu.access:non-reguler-pajak-masukan']);
});

Route::controller(MasterDataController::class)->group(function () {
    Route::get('/pnl/master-data/brands', 'getBrands')->name('pnl.master-data.brands'); // Helper routes usually public or shared? Kept public/auth only
    Route::get('/pnl/master-data/depos', 'getDepo')->name('pnl.master-data.depos');
    Route::get('/pnl/master-data/companies', 'getCompanies')->name('pnl.master-data.companies');
    Route::get('/pnl/master-data/users', 'getUsers')->name('pnl.master-data.users');

    Route::middleware('menu.access:master-data-import-pkp')->group(function () {
        Route::get('/pnl/master-data/import/master-pkp', 'indexMasterPKP')->name('pnl.master-data.index.master-pkp');
        Route::post('/pnl/master-data/import/master-pkp', 'importMasterPKP')->name('pnl.master-data.import.master-pkp');
        Route::get('/pnl/master-data/import/master-pkp/{id}', 'showMasterPKP')->name('pnl.master-data.show.master-pkp');
        Route::put('/pnl/master-data/import/master-pkp/{id}', 'updateMasterPKP')->name('pnl.master-data.update.master-pkp');
        Route::delete('/pnl/master-data/import/master-pkp/{id}', 'deleteMasterPKP')->name('pnl.master-data.delete.master-pkp');
        Route::patch('/pnl/master-data/import/master-pkp/{id}/toggle', 'toggleMasterPKP')->name('pnl.master-data.toggle.master-pkp');
    });

    Route::middleware([AuthnCheck::class, 'menu.access:master-data-referensi'])->group(function () {
        Route::get('/pnl/master-data/referensi', 'indexReferensi')->name('pnl.master-data.index.referensi');
        Route::post('/pnl/master-data/referensi/{type}', 'storeReferensi')
            ->whereIn('type', ['tipe', 'kode-transaksi', 'keterangan-tambahan', 'id-pembeli', 'satuan-ukur', 'kode-negara'])
            ->name('pnl.master-data.store.referensi');
        Route::post('/pnl/master-data/referensi/{type}/import', 'importReferensi')
            ->whereIn('type', ['tipe', 'kode-transaksi', 'keterangan-tambahan', 'id-pembeli', 'satuan-ukur', 'kode-negara'])
            ->name('pnl.master-data.import.referensi');
        Route::get('/pnl/master-data/referensi/{type}/{id}', 'showReferensi')
            ->whereIn('type', ['tipe', 'kode-transaksi', 'keterangan-tambahan', 'id-pembeli', 'satuan-ukur', 'kode-negara'])
            ->whereNumber('id')
            ->name('pnl.master-data.show.referensi');
        Route::put('/pnl/master-data/referensi/{type}/{id}', 'updateReferensi')
            ->whereIn('type', ['tipe', 'kode-transaksi', 'keterangan-tambahan', 'id-pembeli', 'satuan-ukur', 'kode-negara'])
            ->whereNumber('id')
            ->name('pnl.master-data.update.referensi');
        Route::patch('/pnl/master-data/referensi/{type}/{id}/toggle', 'toggleReferensi')
            ->whereIn('type', ['tipe', 'kode-transaksi', 'keterangan-tambahan', 'id-pembeli', 'satuan-ukur', 'kode-negara'])
            ->whereNumber('id')
            ->name('pnl.master-data.toggle.referensi');
    });
});

Route::controller(SettingController::class)->group(function () {
    Route::middleware('menu.access:user-manager')->group(function () {
        Route::get('/pnl/setting/userman', 'usermanIndex')->name('pnl.setting.userman.index')->middleware([AuthnCheck::class]);
        Route::post('/pnl/setting/userman/changepass', 'usermanChangePassword')->name('pnl.setting.userman.changepassword')->middleware([AuthnCheck::class]);
        Route::post('/pnl/setting/userman/show', 'usermanShow')->name('pnl.setting.userman.show')->middleware([AuthnCheck::class]);
        Route::post('/pnl/setting/userman', 'usermanStore')->name('pnl.setting.userman.store')->middleware([AuthnCheck::class]);
        Route::put('/pnl/setting/userman', 'usermanUpdate')->name('pnl.setting.userman.update')->middleware([AuthnCheck::class]);
        Route::delete('/pnl/setting/userman', 'usermanDelete')->name('pnl.setting.userman.destroy')->middleware([AuthnCheck::class]);
    });

    Route::middleware('menu.access:coretax-scraping')->group(function () {
        Route::get('/coba-webdriver', 'cobaScraping')->name('pnl.setting.coba.webdriver')->middleware([AuthnCheck::class]);
        Route::post('/pnl/setting/coretax-gathering', 'coretaxScraping')->name('pnl.setting.coretax.gathering')->middleware([AuthnCheck::class]);
        Route::post('/pnl/setting/coretax-captcha', 'coretaxCaptcha')->name('pnl.setting.coretax.captcha')->middleware([AuthnCheck::class]);
        Route::get('/pnl/setting/coretax-captcha-preview', 'coretaxCaptchaPreview')->name('pnl.setting.coretax.captcha.preview')->middleware([AuthnCheck::class]);
    });

    Route::get('/pnl/setting/generate-csrf-token', 'generateCsrfToken')->name('pnl.setting.generate.csrf.token');
});

Route::controller(MailSenderController::class)->group(function () {
    Route::middleware('menu.access:utilities-mail-sender')->group(function () {
        Route::get('/utilities/mail-sender', 'index')->name('utilities.mail-sender.index')->middleware([AuthnCheck::class]);
        Route::post('/utilities/mail-sender', 'send')->name('utilities.mail-sender.send')->middleware([AuthnCheck::class]);
    });
});

// Access Group Management Routes
use App\Http\Controllers\Admin\AccessGroupController;
use App\Http\Controllers\Admin\MenuController;

Route::prefix('admin')->middleware([AuthnCheck::class])->group(function () {

    // Access Group Routes
    Route::controller(AccessGroupController::class)->prefix('access-groups')->middleware('menu.access:admin-access-groups')->group(function () {
        Route::get('/', 'index')->name('admin.access-groups.index');
        Route::post('/', 'store')->name('admin.access-groups.store');
        Route::get('/{id}', 'show')->name('admin.access-groups.show');
        Route::put('/{id}', 'update')->name('admin.access-groups.update');
        Route::delete('/{id}', 'destroy')->name('admin.access-groups.destroy');
        Route::post('/{id}/users', 'assignUser')->name('admin.access-groups.assign-user');
        Route::delete('/{id}/users/{userId}', 'removeUser')->name('admin.access-groups.remove-user');
        Route::post('/{id}/menus', 'assignMenu')->name('admin.access-groups.assign-menu');
        Route::delete('/{id}/menus/{menuId}', 'removeMenu')->name('admin.access-groups.remove-menu');
        Route::get('/levels/all', 'getAccessLevels')->name('admin.access-groups.levels');
    });

    // Menu Routes
    Route::controller(MenuController::class)->prefix('menus')->middleware('menu.access:admin-menu-management')->group(function () {
        Route::get('/', 'index')->name('admin.menus.index');
        Route::post('/', 'store')->name('admin.menus.store');
        Route::get('/hierarchy', 'getHierarchy')->name('admin.menus.hierarchy');
        Route::get('/{id}', 'show')->name('admin.menus.show');
        Route::put('/{id}', 'update')->name('admin.menus.update');
        Route::delete('/{id}', 'destroy')->name('admin.menus.destroy');
    });
});

// Get accessible menus for current user (available to all authenticated users)
Route::get('/api/user/menus', [MenuController::class, 'getAccessibleMenus'])
    ->middleware([AuthnCheck::class])
    ->name('api.user.menus');
