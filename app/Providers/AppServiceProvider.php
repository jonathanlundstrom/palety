<?php

namespace App\Providers;

use App\Models\Pallet;
use App\Models\Parcel;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Blade;
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
        $this->configureMorphMap();
        $this->configureDateEchoing();
        $this->configureUrls();
    }

    /**
     * Map polymorphic relation types to their models.
     */
    protected function configureMorphMap(): void {
        Relation::morphMap([
            'pallet' => Pallet::class,
            'parcel' => Parcel::class,
        ]);
    }

    /**
     * Force HTTPS for generated URLs in production.
     */
    protected function configureUrls(): void {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }

    /**
     * Echo dates in Blade ({{ $date }}) in the user's timezone, for presentation only.
     * Dates formatted explicitly with ->format() are left untouched.
     */
    protected function configureDateEchoing(): void {
        Blade::stringable(Carbon::class, fn (Carbon $date): string => $date
            ->copy()
            ->setTimezone(auth()->user()?->timezone ?? config('app.timezone'))
            ->format('Y-m-d, H:i'));
    }
}
