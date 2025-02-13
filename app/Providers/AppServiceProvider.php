<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use App\Helpers\DatabaseHelper;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        if (Auth::check()) { // تأكد أن المستخدم مسجل دخول
            $tenantDatabase = "tenant_" . Auth::id();
            DatabaseHelper::switchDatabase($tenantDatabase); // تبديل الاتصال
        }
    
    }
}
