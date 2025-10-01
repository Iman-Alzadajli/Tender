<?php

namespace App\Models;

// 1. إضافة الواجهة (Interface) لتفعيل الميزة
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use App\Notifications\MyResetPasswordNotification;
use App\Notifications\SetupAccountNotification;

// CanResetPassword مدمج في Authenticatable

// 2. إضافة 'implements MustVerifyEmail' إلى تعريف الكلاس
class User extends Authenticatable implements MustVerifyEmail
{
    // 3. دمج كل الـ Traits المطلوبة
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'token', // ✅ تم الإبقاء على حقل التوكن كما هو
        'status',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'token', // ✅ تم الإبقاء على حقل التوكن كما هو
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'last_login_at' => 'datetime',
    ];

    /**
     * Send the email verification notification.
     * 
     * نقوم هنا بتجاوز الدالة الافتراضية لـ Laravel
     * لإرسال إشعار إعداد الحساب المخصص الخاص بنا.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new SetupAccountNotification);
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new MyResetPasswordNotification($token));
    }
}
