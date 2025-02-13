<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // ربط المريض بالمستخدم
            $table->string('name');
            $table->string('email')->nullable(); // جعل الإيميل اختياري
            $table->string('phone', 20);
            $table->date('dob');
            $table->enum('gender', ['male', 'female', 'other']); // دعم جنس "أخرى"
            $table->string('address', 500)->nullable();
            $table->timestamps();
            
            // جعل رقم الهاتف فريدًا لكل مستخدم فقط
            $table->unique(['user_id', 'phone']);
        });
    }
            
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('patients');
    }
};
