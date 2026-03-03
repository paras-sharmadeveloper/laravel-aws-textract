<?php

use App\Http\Controllers\UploadController;
use Illuminate\Support\Facades\Route;

Route::get('/php', function () {
    return phpinfo();
});
Route::get('/', [UploadController::class, 'index']);
Route::post('/upload', [UploadController::class, 'upload']);
