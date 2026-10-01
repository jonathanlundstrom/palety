<?php

namespace App\Providers;

use App\Models\Pallet;
use App\Models\Parcel;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider {
    /**
     * Register any application services.
     */
    public function register(): void {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void {
        Relation::morphMap([
            'pallet' => Pallet::class,
            'parcel' => Parcel::class,
        ]);

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
