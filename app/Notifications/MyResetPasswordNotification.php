<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Lang;

class MyResetPasswordNotification extends ResetPassword
{
    /**
     * The password reset token.
     *
     * @var string
     */
    public $token;

    /**
     * Create a new notification instance.
     *
     * @param  string  $token
     * @return void
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ["mail"];
    }

    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject(Lang::get(
                        'Smart Developer Password Reset'
                    ))
                    ->greeting(Lang::get(
                        'Hello!'
                    ))
                    ->line(Lang::get(
                        'You are receiving this email because we received a password reset request for your account.'
                    ))
                    ->action(Lang::get(
                        'Reset Password'
                    ), $this->resetUrl($notifiable))
                    ->line(Lang::get(
                        'This password reset link will expire in :count minutes.',
                        ['count' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire')]
                    ))
                    ->line(Lang::get(
                        'If you did not request a password reset, no further action is required.'
                    ))
                    ->salutation(Lang::get(
                        'Regards,Smart Developer Team'
                    ))
                    ->attach(public_path('imgs/logo2.png'), ['as' => 'logo.png', 'mime' => 'image/png']); // Add this line for the logo
    }

    /**
     * Get the reset URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    public function resetUrl($notifiable)
    {
        return url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
