<?php

use Illuminate\Support\Facades\Route;
use Shahnewaz\PermissibleNg\Http\Controllers\API\PermissibleUserController;

Route::namespace('Shahnewaz\PermissibleNg\Http\Controllers')->middleware(['api', 'auth'])->prefix('permissible/v1')->group(function () {

    // User data
    Route::get('/auth/token', [PermissibleUserController::class, 'postAuthenticate'])->name('permissible.auth.token');
    Route::get('/auth/logout', [PermissibleUserController::class, 'logout'])->name('permissible.auth.logout');
});
