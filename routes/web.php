<?php

use App\Http\Controllers\AcceptQuotationController;
use App\Http\Controllers\Admin\ImpersonationController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\ClientQuotationController;
use App\Livewire\Admin\Dashboard as AdminDashboard;
use App\Livewire\Admin\Studios\Create as CreateStudio;
use App\Livewire\Admin\Studios\Edit as EditStudio;
use App\Livewire\Admin\Studios\Index as StudioIndex;
use App\Livewire\Auth\Login;
use App\Livewire\Studio\Portfolio as StudioPortfolio;
use App\Livewire\Studio\PriceList as StudioPriceList;
use App\Livewire\Studio\Quotations\Edit as QuotationEdit;
use App\Livewire\Studio\Quotations\Index as QuotationIndex;
use App\Livewire\Studio\Settings as StudioSettings;
use App\Livewire\Studio\Team as StudioTeam;
use App\Livewire\Studio\Terms as StudioTerms;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::get('/q/{token}', [ClientQuotationController::class, 'show'])
    ->where('token', '[A-Za-z0-9]{40}')
    ->name('quotations.public');

Route::get('/q/{token}/pdf', [ClientQuotationController::class, 'pdf'])
    ->where('token', '[A-Za-z0-9]{40}')
    ->name('quotations.pdf');

Route::post('/q/{token}/accept', AcceptQuotationController::class)
    ->where('token', '[A-Za-z0-9]{40}')
    ->middleware('throttle:10,1')
    ->name('quotations.accept');

Route::middleware('guest')->group(function () {
    Route::livewire('/login', Login::class)->name('login');
});

Route::middleware(['auth', 'can.sign.in'])->group(function () {
    Route::post('/logout', LogoutController::class)->name('logout');
    Route::post('/impersonate/stop', [ImpersonationController::class, 'stop'])->name('impersonate.stop');

    Route::middleware('role:super_admin')->prefix('admin')->name('admin.')->group(function () {
        Route::livewire('/', AdminDashboard::class)->name('dashboard');
        Route::livewire('/studios', StudioIndex::class)->name('studios.index');
        Route::livewire('/studios/create', CreateStudio::class)->name('studios.create');
        Route::livewire('/studios/{studio}', EditStudio::class)->name('studios.edit');
        Route::post('/studios/{studio}/impersonate', [ImpersonationController::class, 'start'])->name('studios.impersonate');
    });

    Route::middleware('role:studio_admin,staff')->prefix('studio')->name('studio.')->group(function () {
        Route::livewire('/', QuotationIndex::class)->name('dashboard');
        Route::livewire('/quotations/{quotation}', QuotationEdit::class)->name('quotations.edit');

        Route::middleware('role:studio_admin')->group(function () {
            Route::livewire('/settings', StudioSettings::class)->name('settings');
            Route::livewire('/team', StudioTeam::class)->name('team');
            Route::livewire('/price-list', StudioPriceList::class)->name('price-list');
            Route::livewire('/portfolio', StudioPortfolio::class)->name('portfolio');
            Route::livewire('/terms', StudioTerms::class)->name('terms');
        });
    });
});
