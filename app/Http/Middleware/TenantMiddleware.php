<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class TenantMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        // حدد قاعدة بيانات التينانت بناءً على الـ user_id
        $tenantDatabase = "tenant_" . $user->id;

        // تبديل قاعدة البيانات
        Config::set('database.connections.tenant.database', $tenantDatabase);
        DB::purge('tenant');
        DB::reconnect('tenant');
        
        // ✅ إجبار Laravel على استخدام اتصال التينانت
        DB::setDefaultConnection('tenant');

        return $next($request);
    }
}
