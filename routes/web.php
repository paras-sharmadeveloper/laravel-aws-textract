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

        // Open to every signed-in user; sends them on to a page they're allowed to see
        Route::get('/', [Admin\LeadController::class, 'index'])->name('leads.index');

        Route::middleware('can:leads.view')->group(function () {
            Route::get('/leads/{lead}', [Admin\LeadController::class, 'show'])->name('leads.show');
            Route::get('/leads/{lead}/documents/{document}', [Admin\LeadController::class, 'document'])->name('leads.document');
        });
        Route::post('/leads/{lead}/retry', [Admin\LeadController::class, 'retry'])->middleware('can:leads.retry')->name('leads.retry');

        Route::middleware('can:affiliates.manage')->group(function () {
            Route::get('/affiliates', [Admin\AffiliateController::class, 'index'])->name('affiliates.index');
            Route::post('/affiliates', [Admin\AffiliateController::class, 'store'])->name('affiliates.store');
            Route::get('/affiliates/{affiliate}/edit', [Admin\AffiliateController::class, 'edit'])->name('affiliates.edit');
            Route::put('/affiliates/{affiliate}', [Admin\AffiliateController::class, 'update'])->name('affiliates.update');
            Route::post('/affiliates/{affiliate}/toggle', [Admin\AffiliateController::class, 'toggle'])->name('affiliates.toggle');
            Route::delete('/affiliates/{affiliate}', [Admin\AffiliateController::class, 'destroy'])->name('affiliates.destroy');
        });

        Route::middleware('can:users.manage')->group(function () {
            Route::get('/users', [Admin\UserController::class, 'index'])->name('users.index');
            Route::post('/users', [Admin\UserController::class, 'store'])->name('users.store');
            Route::get('/users/{user}/edit', [Admin\UserController::class, 'edit'])->name('users.edit');
            Route::put('/users/{user}', [Admin\UserController::class, 'update'])->name('users.update');
            Route::delete('/users/{user}', [Admin\UserController::class, 'destroy'])->name('users.destroy');

            Route::get('/roles', [Admin\RoleController::class, 'index'])->name('roles.index');
            Route::post('/roles', [Admin\RoleController::class, 'store'])->name('roles.store');
            Route::get('/roles/{role}/edit', [Admin\RoleController::class, 'edit'])->name('roles.edit');
            Route::put('/roles/{role}', [Admin\RoleController::class, 'update'])->name('roles.update');
            Route::delete('/roles/{role}', [Admin\RoleController::class, 'destroy'])->name('roles.destroy');
        });

        Route::middleware('can:integrations.view')->group(function () {
            Route::get('/integrations', [Admin\IntegrationController::class, 'index'])->name('integrations');
            Route::post('/integrations/{integration}/test', [Admin\IntegrationController::class, 'test'])
                ->middleware('throttle:30,1')->name('integrations.test');
        });

        Route::get('/account', [Admin\AccountController::class, 'edit'])->name('account');
        Route::put('/account/password', [Admin\AccountController::class, 'updatePassword'])->name('account.password');
    });
});

/*
|--------------------------------------------------------------------------
| AFFILIATE REFERRAL LINKS — keep last, e.g. /jhonsmith
|--------------------------------------------------------------------------
*/
Route::get('/{slug}', [UploadController::class, 'affiliate'])->where('slug', '[A-Za-z0-9-]+');
