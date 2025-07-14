<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PNL\DashboardController;
use App\Http\Controllers\PNL\MasterDataController;
use App\Http\Controllers\PNL\NonRegulerController;
use App\Http\Controllers\PNL\RegulerController;
use App\Http\Controllers\User\AuthController;
use App\Http\Controllers\Utilities\SettingController;
use App\Http\Middleware\AuthnCheck;

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

Route::controller(RegulerController::class)->group(function () {
    Route::get('/pnl/reguler/pajak-keluaran', 'pkIndex')->name('pnl.reguler.pajak-keluaran.index')->middleware([AuthnCheck::class]);
    Route::post('/pnl/reguler/pajak-keluaran/dtdata', 'dtPKGetData')->name('pnl.reguler.pajak-keluaran.dtdata');
    Route::post('/pnl/reguler/pajak-keluaran/update-checked', 'updateChecked')->name('pnl.reguler.pajak-keluaran.updateChecked');
    Route::post('/pnl/reguler/pajak-keluaran/update-move2', 'updateMove2')->name('pnl.reguler.pajak-keluaran.updateMove2');
    Route::get('/pnl/reguler/pajak-keluaran/export', 'download')->name('pnl.reguler.pajak-keluaran.download');
    Route::post('/pnl/reguler/pajak-keluaran/count', 'count')->name('pnl.reguler.pajak-keluaran.count');
    Route::get('/pnl/reguler/pajak-masukan', 'pmIndex')->name('pnl.reguler.pajak-masukan.index')->middleware([AuthnCheck::class]);
    Route::get('/pnl/reguler/uploadcsv', 'pmUploadCsvIndex')->name('pnl.reguler.pajak-masukan.uploadcsv')->middleware([AuthnCheck::class]);
});

Route::controller(NonRegulerController::class)->group(function () {
    Route::get('/pnl/non-reguler/pajak-keluaran', 'pkIndex')->name('pnl.non-reguler.pajak-keluaran.index')->middleware([AuthnCheck::class]);
    Route::get('/pnl/non-reguler/pajak-masukan', 'pmIndex')->name('pnl.non-reguler.pajak-masukan.index')->middleware([AuthnCheck::class]);
});

Route::controller(MasterDataController::class)->group(function () {
    Route::get('/pnl/master-data/brands', 'getBrands')->name('pnl.master-data.brands');
    Route::get('/pnl/master-data/depos', 'getDepo')->name('pnl.master-data.depos');
    Route::get('/pnl/master-data/companies', 'getCompanies')->name('pnl.master-data.companies');
    Route::get('/pnl/master-data/users', 'getUsers')->name('pnl.master-data.users');
    Route::get('/pnl/master-data/import/master-pkp', 'indexMasterPKP')->name('pnl.master-data.index.master-pkp');
    Route::post('/pnl/master-data/import/master-pkp', 'importMasterPKP')->name('pnl.master-data.import.master-pkp');
});


Route::controller(SettingController::class)->group(function () {
    Route::get('/pnl/setting/userman', 'usermanIndex')->name('pnl.setting.userman.index')->middleware([AuthnCheck::class]);
    Route::post('/pnl/setting/userman/changepass', 'usermanChangePassword')->name('pnl.setting.userman.changepassword')->middleware([AuthnCheck::class]);
    Route::post('/pnl/setting/userman/show', 'usermanShow')->name('pnl.setting.userman.show')->middleware([AuthnCheck::class]);
    Route::post('/pnl/setting/userman', 'usermanStore')->name('pnl.setting.userman.store')->middleware([AuthnCheck::class]);
    Route::put('/pnl/setting/userman', 'usermanUpdate')->name('pnl.setting.userman.update')->middleware([AuthnCheck::class]);
    Route::delete('/pnl/setting/userman', 'usermanDelete')->name('pnl.setting.userman.destroy')->middleware([AuthnCheck::class]);
});

