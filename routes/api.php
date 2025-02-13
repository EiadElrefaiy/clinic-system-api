<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SubscriptionController;




// 🛂 تسجيل الدخول والتسجيل
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/verify-license', [AuthController::class, 'verifyLicense']); // ✅ التحقق من الـ License

Route::prefix('subscriptions')->group(function () {
    Route::get('/', [SubscriptionController::class, 'index']);
    Route::get('/{id}', [SubscriptionController::class, 'show']); // بيانات الاشتراك
    Route::post('/', [SubscriptionController::class, 'new']); // تجديد الاشتراك
});

// 🛡️ راوتات محمية بالـ Middleware "auth:api" و "tenant"
Route::middleware(['auth:api', 'license', 'tenant'])->group(function () {

    // 🆔 بيانات المستخدم
    Route::get('me', [AuthController::class, 'me'])->name('me');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('refresh', [AuthController::class, 'refresh'])->name('refresh');

    // 🏠 الصفحة الرئيسية
    Route::get('/home', [HomeController::class, 'index']);

    // 👨‍⚕️ إدارة المرضى (Patients)
    Route::prefix('patients')->group(function () {
        Route::get('/', [PatientController::class, 'index']); // عرض جميع المرضى
        Route::post('/', [PatientController::class, 'store']); // إنشاء مريض جديد
        Route::get('/{id}', [PatientController::class, 'show']); // عرض مريض معين
        Route::put('/{id}', [PatientController::class, 'update']); // تحديث بيانات مريض
        Route::delete('/{id}', [PatientController::class, 'destroy']); // حذف مريض
    });

    // 📅 إدارة المواعيد (Appointments)
    Route::prefix('appointments')->group(function () {
        Route::get('/', [AppointmentController::class, 'index']); // عرض جميع المواعيد
        Route::post('/', [AppointmentController::class, 'store']); // إنشاء موعد جديد
        Route::get('/{id}', [AppointmentController::class, 'show']); // عرض موعد معين
        Route::put('/{id}', [AppointmentController::class, 'update']); // تحديث موعد
        Route::delete('/{id}', [AppointmentController::class, 'destroy']); // حذف موعد
    });

});
