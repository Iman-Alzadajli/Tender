<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Password; // ❗️ استيراد مهم جداً

class SetupAccountNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // 1. إنشاء توكن إعادة تعيين كلمة المرور للمستخدم
        $token = Password::createToken($notifiable);

        // 2. بناء الرابط الذي يأخذ التوكن
        // سيقوم بإنشاء رابط مثل: http://tender.test/reset-password/some-long-token?email=user@example.com
        $url = url(route('password.reset', [
            'token' => $token,
            'email' => $notifiable->getEmailForPasswordReset( ),
        ], false));

        // 3. تخصيص رسالة البريد الإلكتروني
        return (new MailMessage)
                    ->subject('Setup Your Account')
                    ->line('You have been invited to create an account. Please click the button below to set up your password.')
                    ->action('Set Up Account', $url) // هذا هو الزر الذي سيضغط عليه المستخدم
                    ->line('This password setup link will expire in :count minutes.', ['count' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire')])
                    ->line('If you did not expect to receive this email, no further action is required.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
