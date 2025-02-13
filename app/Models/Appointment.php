<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;
    protected $connection = 'tenant'; // تأكد أن الاتصال مضبوط على التينانت
    protected $table = 'appointments';

    protected $fillable = [
        'user_id', // ✅ ضروري عشان يحدد المستخدم المالك للموعد
        'patient_id',
        'registration_date',
        'appointment_date',
        'time',
        'reason_for_visit',
        'visit_status',
    ];

    // ✅ ربط الموعد بالمريض
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    // ✅ ربط الموعد بالمستخدم (صاحب البيانات)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ✅ فلترة البيانات بحيث المستخدم يشوف فقط مواعيده
    protected static function booted()
    {
        static::addGlobalScope('userAppointments', function ($query) {
            if (auth()->check()) {
                $query->where('user_id', auth()->id());
            }
        });
    }
}
