<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\SubscriptionController;


// 🔹 مسارات تسجيل الدخول والخروج للأدمن
Route::post('/register', [AdminController::class, 'register'])->name('admin.register');
Route::post('/login', [AdminController::class, 'login'])->name('admin.login');

// 🔹 المسارات المحمية بـ middleware الأدمن
Route::middleware(['auth.admin'])->group(function () {
    Route::get('/me', [AdminController::class, 'me'])->name('admin.me');
    Route::post('/logout', [AdminController::class, 'logout'])->name('admin.logout');

    // ✅ لوحة تحكم الأدمن
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

    // ✅ إدارة المستخدمين
    Route::get('/users', [UserController::class, 'index'])->name('admin.users.index');
    Route::get('/users/{id}', [UserController::class, 'show'])->name('admin.users.show');

    // ✅ إدارة الاشتراكات
    Route::get('/subscriptions', [SubscriptionController::class, 'index'])->name('admin.subscriptions.index');
    Route::post('/subscriptions', [SubscriptionController::class, 'new'])->name('admin.subscriptions.new');
    Route::put('/subscriptions/{id}', [SubscriptionController::class, 'update'])->name('admin.subscriptions.update');
    Route::delete('/subscriptions/{id}', [SubscriptionController::class, 'delete'])->name('admin.subscriptions.destroy');
});
