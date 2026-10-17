<?php

use App\Enumerables\Permission;
use App\Http\Controllers\TransportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('/', 'pages::dashboard')
        ->name('dashboard');

    Route::livewire('/parcels', 'pages::parcels')
        ->name('parcels')
        ->can(Permission::VIEW_PARCELS);

    Route::livewire('/pallets', 'pages::pallets')
        ->name('pallets')
        ->can(Permission::VIEW_PALLETS);

    Route::livewire('/transports', 'pages::transports')
        ->name('transports')
        ->can(Permission::VIEW_TRANSPORTS);

    Route::livewire('/recipients', 'pages::recipients')
        ->name('recipients')
        ->can(Permission::VIEW_RECIPIENTS);

    Route::livewire('/content', 'pages::content')
        ->name('content')
        ->can(Permission::VIEW_CONTENT);

    Route::livewire('/users', 'pages::users')
        ->name('users')
        ->can(Permission::VIEW_USERS);

    Route::get('/transports/{transport}/packing-list/download/pdf', [TransportController::class, 'downloadPackingListPDF'])
        ->name('transports.packing-list.pdf')
        ->can(Permission::DOWNLOAD_DECLARATIONS);

    Route::get('/transports/{transport}/packing-list/download/xlsx', [TransportController::class, 'downloadPackingListXlsx'])
        ->name('transports.packing-list.xlsx')
        ->can(Permission::DOWNLOAD_DECLARATIONS);

    Route::get('/transports/{transport}/import-list/download/xlsx', [TransportController::class, 'downloadImportListXlsx'])
        ->name('transports.import-list.xlsx')
        ->can(Permission::DOWNLOAD_DECLARATIONS);

    // Route for getting information about a pallet or parcel:
    Route::get('/qr/{type}/{id}', fn () => 'Hello, world!')
        ->whereIn('type', ['pallet', 'parcel'])
        ->whereNumber('id')
        ->name('details');
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
