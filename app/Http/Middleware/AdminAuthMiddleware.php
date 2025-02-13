<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Auth\AuthenticationException;

class AdminAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        try {
            // حاول استخراج التوكن والتأكد منه
            $admin = auth('admin')->user();

            if (!$admin) {
                return response()->json(['error' => 'Admin not found'], 401);
            }
        } catch (TokenExpiredException $e) {
            return response()->json(['error' => 'Token has expired'], 401);
        } catch (TokenInvalidException $e) {
            return response()->json(['error' => 'Invalid token'], 401);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Token is missing'], 401);
        } catch (AuthenticationException $e) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        return $next($request);
    }
}
