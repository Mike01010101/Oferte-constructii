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

    // Grup de rute pentru situațiile de plată asociate unei oferte
    Route::prefix('oferte/{offer}/situatii-plata')->name('oferte.situatii-plata.')->group(function () {
    Route::get('/', [App\Http\Controllers\PaymentStatementController::class, 'index'])->name('index');
    Route::get('/creeaza', [App\Http\Controllers\PaymentStatementController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\PaymentStatementController::class, 'store'])->name('store');
    Route::get('/{statement}/editeaza', [App\Http\Controllers\PaymentStatementController::class, 'edit'])->name('edit');
    Route::put('/{statement}', [App\Http\Controllers\PaymentStatementController::class, 'update'])->name('update');
    Route::get('/{statement}/pdf', [App\Http\Controllers\PaymentStatementController::class, 'downloadPDF'])->name('pdf');
    Route::delete('/{statement}', [App\Http\Controllers\PaymentStatementController::class, 'destroy'])->name('destroy');
    });

    Route::get('/rapoarte', [App\Http\Controllers\ReportController::class, 'index'])->name('rapoarte.index');

    // Grup pentru managementul firmei, accesibil doar de Owner și Administrator
    Route::middleware(['role:Owner|Administrator'])->group(function () {
        Route::resource('utilizatori', App\Http\Controllers\UserController::class);
    });
    Route::patch('/oferte/{offer}/status', [App\Http\Controllers\OfferController::class, 'updateStatus'])->name('oferte.updateStatus');
    //  Rută pentru alocarea unui utilizator
    Route::patch('/oferte/{offer}/assign', [App\Http\Controllers\OfferController::class, 'assignUser'])->name('oferte.assignUser');
    Route::get('/oferte/{offer}/verifica-situatie-plata', [App\Http\Controllers\OfferController::class, 'checkPaymentStatement'])->name('oferte.checkPaymentStatement');
});
