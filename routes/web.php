<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::view('/', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('/', 'dashboard')->name('dashboard');
    Route::livewire('/parcels', 'pages::parcels')->name('parcels');
    Route::livewire('/recipients', 'pages::recipients')->name('recipients');
});

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::livewire('settings/profile', 'pages::settings.profile')->name('settings.profile');
    Route::livewire('settings/password', 'pages::settings.password')->name('settings.password');
    Route::livewire('settings/appearance', 'pages::settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';
