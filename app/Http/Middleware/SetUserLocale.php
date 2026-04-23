<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetUserLocale {
    public function handle(Request $request, Closure $next): Response {
        if ($request->is('livewire/*') || $request->hasHeader('X-Livewire')) {
            return $next($request); // Avoid interfering with livewire payloads
        }

        $locale = config('app.fallback_locale');
        if ($user = $request->user()) {
            $locale = $user->locale;
        }

        App::setLocale($locale);
        return $next($request);
    }
}
