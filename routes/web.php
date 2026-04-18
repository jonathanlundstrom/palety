<?php

use App\Http\Controllers\TransportController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('/', 'dashboard')->name('dashboard');
    Route::livewire('/parcels', 'pages::parcels')->name('parcels');
    Route::livewire('/pallets', 'pages::pallets')->name('pallets');
    Route::livewire('/transports', 'pages::transports')->name('transports');

    Route::middleware(['admin'])->group(function () {
        Route::livewire('/recipients', 'pages::recipients')->name('recipients');
        Route::livewire('/content', 'pages::content')->name('content');
        Route::livewire('/users', 'pages::users')->name('users');
    });

    // Download packing list PDF for transport:
    Route::get('/transports/{transport}/packing-list/download', [TransportController::class, 'printPackingList'])
        ->name('transports.packing-list.pdf');
});

// Signed URL access for packing list HTML (used by Browsershot, no auth required):
Route::get('/transports/{transport}/packing-list', [TransportController::class, 'showPackingList'])
    ->name('transports.packing-list.show')
    ->middleware(['signed']);

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');
    Route::livewire('settings/profile', 'pages::settings.profile')->name('settings.profile');
    Route::livewire('settings/password', 'pages::settings.password')->name('settings.password');
    Route::livewire('settings/appearance', 'pages::settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';
