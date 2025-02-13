<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Subscription;
use App\Models\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admins',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $admin = Admin::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = Auth::guard('admin')->attempt([
            'email' => $request->email,
            'password' => $request->password
        ]);

        return response()->json([
            'token' => $token,
            'admin' => $admin
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = Auth::guard('admin')->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return response()->json([
            'token' => $token,
            'admin' => Auth::guard('admin')->user()
        ]);
    }

    public function me()
    {
        return response()->json(Auth::guard('admin')->user());
    }

    public function logout()
    {
        try {
            Auth::guard('admin')->logout();
            return response()->json(['message' => 'Logged out successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to logout'], 500);
        }
    }

    public function dashboard()
    {
        $usersCount = User::count();
        $activeSubscriptions = Subscription::where('expiry_date', '>=', now())->count();
        $expiredSubscriptions = Subscription::where('expiry_date', '<', now())->count();

        return response()->json([
            'total_users' => $usersCount,
            'active_subscriptions' => $activeSubscriptions,
            'expired_subscriptions' => $expiredSubscriptions
        ]);
    }
}
