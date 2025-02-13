<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;
    
    protected $connection = 'tenant'; // تأكد أن الاتصال مضبوط على التينانت
    protected $table = 'patients';

    protected $fillable = [
        'user_id', // ✅ ضروري عشان يحدد المستخدم المالك للمريض
        'name', 
        'email', 
        'phone',  
        'dob',  
        'address',  
        'gender'
    ];

    // ✅ ربط المريض بالمستخدم (صاحب البيانات)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ✅ ربط المريض بالمواعيد
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    // ✅ فلترة البيانات بحيث المستخدم يشوف فقط مرضاه
    protected static function booted()
    {
        static::addGlobalScope('userPatients', function ($query) {
            if (auth()->check()) {
                $query->where('user_id', auth()->id());
            }
        });
    }
}
