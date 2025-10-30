<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Grup de rute care necesită autentificare
Route::middleware(['auth'])->group(function () {
    Route::get('/profil', [App\Http\Controllers\CompanyProfileController::class, 'show'])->name('profile.show');
    Route::post('/profil', [App\Http\Controllers\CompanyProfileController::class, 'update'])->name('profile.update');
    
    Route::get('/setari-ofertare', [App\Http\Controllers\OfferSettingController::class, 'show'])->name('offer-settings.show');
    Route::post('/setari-ofertare', [App\Http\Controllers\OfferSettingController::class, 'update'])->name('offer-settings.update');

    Route::get('/creator-sabloane', [App\Http\Controllers\TemplateSettingController::class, 'show'])->name('template.show');
    Route::post('/creator-sabloane', [App\Http\Controllers\TemplateSettingController::class, 'update'])->name('template.update');

    Route::resource('clienti', App\Http\Controllers\ClientController::class);
    Route::resource('oferte', App\Http\Controllers\OfferController::class);
    Route::get('/oferte/{oferte}/pdf', [App\Http\Controllers\OfferController::class, 'downloadPDF'])->name('oferte.pdf');

    Route::get('/rapoarte', [App\Http\Controllers\ReportController::class, 'index'])->name('rapoarte.index');
    
    // Grup pentru managementul firmei, accesibil doar de Owner și Administrator
    Route::middleware(['role:Owner|Administrator'])->group(function () {
        Route::resource('utilizatori', App\Http\Controllers\UserController::class);
    });
});
