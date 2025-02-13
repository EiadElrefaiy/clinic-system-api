<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LicenseMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (!$user || !$user->subscription || $user->subscription->expiry_date < now()) {
            return response()->json(["license_verified" => false ,'error' => 'License expired'], 403);
        }

        return $next($request);
    }
}
