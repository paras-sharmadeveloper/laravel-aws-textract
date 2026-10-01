<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\UploadController;
use Illuminate\Support\Facades\Route;

Route::get('/php', function () {
    return phpinfo();
});
Route::get('/', [UploadController::class, 'index']);
Route::post('/upload', [UploadController::class, 'upload']);
Route::post('/upload/presign', [UploadController::class, 'presign'])->middleware('throttle:120,1');

/*
|--------------------------------------------------------------------------
| ADMIN PORTAL
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [Admin\AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [Admin\AuthController::class, 'login'])->middleware('throttle:5,1');
    });

    Route::middleware('auth')->group(function () {
        Route::post('/logout', [Admin\AuthController::class, 'logout'])->name('logout');

        Route::get('/', [Admin\LeadController::class, 'index'])->name('leads.index');
        Route::get('/leads/{lead}', [Admin\LeadController::class, 'show'])->name('leads.show');
        Route::get('/leads/{lead}/documents/{document}', [Admin\LeadController::class, 'document'])->name('leads.document');
        Route::post('/leads/{lead}/retry', [Admin\LeadController::class, 'retry'])->name('leads.retry');

        Route::get('/affiliates', [Admin\AffiliateController::class, 'index'])->name('affiliates.index');
        Route::post('/affiliates', [Admin\AffiliateController::class, 'store'])->name('affiliates.store');
        Route::get('/affiliates/{affiliate}/edit', [Admin\AffiliateController::class, 'edit'])->name('affiliates.edit');
        Route::put('/affiliates/{affiliate}', [Admin\AffiliateController::class, 'update'])->name('affiliates.update');
        Route::post('/affiliates/{affiliate}/toggle', [Admin\AffiliateController::class, 'toggle'])->name('affiliates.toggle');
        Route::delete('/affiliates/{affiliate}', [Admin\AffiliateController::class, 'destroy'])->name('affiliates.destroy');

        Route::get('/account', [Admin\AccountController::class, 'edit'])->name('account');
        Route::put('/account/password', [Admin\AccountController::class, 'updatePassword'])->name('account.password');
    });
});

/*
|--------------------------------------------------------------------------
| AFFILIATE REFERRAL LINKS — keep last, e.g. /jhonrocha
|--------------------------------------------------------------------------
*/
Route::get('/{slug}', [UploadController::class, 'affiliate'])->where('slug', '[A-Za-z0-9-]+');
