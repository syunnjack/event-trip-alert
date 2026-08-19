<?php

use App\Http\Controllers\VenueController;
use Illuminate\Support\Facades\Route;

Route::get('/', [VenueController::class, 'index'])->name('venues.index');

Route::get('/areas/{areaSlug}', [VenueController::class, 'area'])
    ->whereAlpha('areaSlug')
    ->name('venues.area');

Route::get('/kinds/{kindSlug}', [VenueController::class, 'kind'])
    ->whereAlpha('kindSlug')
    ->name('venues.kind');

Route::get('/venues/{slug}', [VenueController::class, 'show'])
    ->where('slug', '[a-z]+-[nw]\\d+')
    ->name('venues.show');

Route::view('/about', 'about')->name('about');

Route::get('/sitemap.xml', [VenueController::class, 'sitemap'])->name('sitemap');

Route::get('/robots.txt', function () {
    $body = "User-agent: *\nAllow: /\n\nSitemap: ".url('/sitemap.xml')."\n";

    return response($body, 200, ['Content-Type' => 'text/plain']);
});
