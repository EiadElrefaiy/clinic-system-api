<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;

class DatabaseHelper
{
    public static function createTenantDatabase($userId)
    {
        $databaseName = "tenant_{$userId}";

        // إنشاء قاعدة البيانات لو مش موجودة
        DB::statement("CREATE DATABASE IF NOT EXISTS `$databaseName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");

        // تشغيل الـ Migrations الخاصة بالـ Tenants فقط
        config(['database.connections.tenant.database' => $databaseName]);
        DB::purge('tenant');

        Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path' => 'database/migrations/tenants',
            '--force' => true,
        ]);
    }

    public static function switchDatabase($databaseName)
    {
        Config::set('database.connections.tenant.database', $databaseName);
        DB::purge('tenant');
        DB::reconnect('tenant');
    }

}
