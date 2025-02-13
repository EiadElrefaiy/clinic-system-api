<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use App\Helpers\DatabaseHelper;

class AuthController extends Controller
{
   public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);
    
        // إنشاء المستخدم في قاعدة البيانات العامة
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);
    
        // إنشاء قاعدة بيانات خاصة بالمستخدم
        DatabaseHelper::createTenantDatabase($user->id);
    
        // إنشاء اشتراك له مباشرة
        Subscription::create([
            'user_id' => $user->id,
            'license_number' => 'LIC-' . strtoupper(uniqid()),
            'expiry_date' => now()->addYear(),
        ]);
    
        $token = JWTAuth::fromUser($user);
    
        return response()->json([
            'token' => $token,
            'user' => $user
        ], 201);
    }
        

    public function login(Request $request)
    {
        // ✅ التأكد من استخدام قاعدة البيانات الرئيسية
        Config::set('database.default', 'mysql'); 
        DB::purge('mysql');
        DB::reconnect('mysql');
    
        $credentials = $request->only('email', 'password');
    
        if (!$token = Auth::attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    
        $user = Auth::user();
        $subscription = $user->subscription;
    
        // ✅ التحقق من صلاحية الاشتراك
        if (!$subscription || $subscription->expiry_date < now()) {
            Auth::logout(); // تسجيل خروج المستخدم لضمان عدم استخدام التوكن
            return response()->json(['license_verified' => false , 'error' => 'License expired'], 403);
        }
    
        // ✅ إصدار توكن بناءً على صلاحية الترخيص
        $newToken = JWTAuth::claims(['verified_license' => true])->fromUser($user);
    
        return response()->json([
            'token' => $newToken,
            'user' => $user,
            'subscription' => $subscription,
            'license_verified' => true
        ]);
    }
    
    public function verifyLicense(Request $request)
    {
        Config::set('database.default', 'mysql'); 
        DB::purge('mysql');
        DB::reconnect('mysql');
    
        $request->validate([
            'license' => 'required|string|exists:subscriptions,license_number'
        ]);
    
        $user = Auth::user();
        $subscription = $user->subscription;
    
        // ✅ التحقق من صلاحية الترخيص
        if (!$subscription || $subscription->license_number !== $request->license || $subscription->expiry_date < now()) {
            return response()->json(['error' => 'Invalid or expired license'], 403);
        }
    
        // ✅ إصدار توكن جديد مؤكد
        $newToken = JWTAuth::claims(['verified_license' => true])->fromUser($user);
    
        return response()->json([
            'token' => $newToken,
            'user' => $user,
            'subscription' => $subscription,
            'license_verified' => true
        ]);
    }
     
    public function me()
    {
        $user = Auth::user();
    
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    
        return response()->json([
            'user' => $user,
            'subscription' => $user->subscription // إرجاع بيانات الاشتراك أيضًا
        ]);
    }
    
    public function logout()
    {
        Auth::logout();
    
        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }
    
    public function refresh()
    {
        $token = Auth::refresh();
        $user = Auth::user();
    
        return response()->json([
            'token' => $token,
            'user' => $user,
            'subscription' => $user->subscription // تحديث بيانات الاشتراك مع التوكن الجديد
        ]);
    }

}
