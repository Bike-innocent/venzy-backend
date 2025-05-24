<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\SitemapController;
// Route to generate the sitemap
Route::get('/generate-sitemap', [SitemapController::class, 'generateSitemap']);

// Home route
Route::get('/', function () {
    return view('welcome');
});

