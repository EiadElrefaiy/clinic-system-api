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
         Schema::create('appointments', function (Blueprint $table) {
             $table->id();
             $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // كل موعد تابع لمستخدم معين
             $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
             $table->date('registration_date');
             $table->date('appointment_date');
             $table->time('time');
             $table->text('reason_for_visit')->nullable();
             $table->enum('visit_status', ['first_time', 're_app'])->default('first_time');
             $table->timestamps();
         });
     }
             
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('appointments');
    }
};
