<?php

namespace App\Http\Middleware;

use App\Enumerables\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin {
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response {
        if (!$request->user() || $request->user()->role !== UserRole::ADMIN) {
            abort(403);
        }

        return $next($request);
    }
}
