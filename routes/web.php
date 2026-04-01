<?php

use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SiteController::class, 'redirectRoot'])->name('site.root');

Route::prefix('{locale}')
    ->whereIn('locale', ['en', 'ru', 'ja'])
    ->group(function () {
        Route::get('/', [SiteController::class, 'home'])->name('site.home');
        Route::get('/properties', [SiteController::class, 'properties'])->name('site.properties');
        Route::get('/properties/{listing}', [SiteController::class, 'listing'])->name('site.listing');
        Route::get('/about', [SiteController::class, 'about'])->name('site.about');
        Route::get('/contact', [SiteController::class, 'contact'])->name('site.contact');
        Route::get('/faqs', [SiteController::class, 'faqs'])->name('site.faqs');
    });
