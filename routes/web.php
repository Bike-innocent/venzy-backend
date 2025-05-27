<?php

use App\Http\Controllers\Profile\EmailUpdateController;
use Illuminate\Support\Facades\Route;

// Home route
Route::get('/', function () {
    return view('welcome');
});

Route::get('/user/verify-email-change/{token}', [EmailUpdateController::class, 'verifyChange']);