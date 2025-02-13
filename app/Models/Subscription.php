<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'license_number', 'expiry_date'];

    /**
     * العلاقة مع المستخدم
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * التحقق مما إذا كان الاشتراك لا يزال صالحًا
     */
    public function isActive()
    {
        return $this->expiry_date >= now();
    }
}
