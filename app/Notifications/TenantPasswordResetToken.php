<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Illuminate\Notifications\Notification;

class TenantPasswordResetToken extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $token)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $expiresMinutes = 30;
        $expiresAt = Carbon::now()->addMinutes($expiresMinutes);

        // Backend verify-and-redirect URL within the tenant app
        $resetUrl = URL::to('/password/reset/' . $this->token);

        // Optional: Frontend URL (if provided), otherwise consumers can follow the backend URL
        $frontendUrl = rtrim((string) Config::get('app.frontend_password_reset_url', ''), '/');

        return (new MailMessage)
            ->subject('Reset your password')
            ->view('emails.auth.tenant_password_reset', [
                'appName' => Config::get('app.name', 'App'),
                'token' => $this->token,
                'resetUrl' => $resetUrl,
                'frontendUrl' => $frontendUrl,
                'expiresMinutes' => $expiresMinutes,
                'expiresAt' => $expiresAt,
            ]);
    }
}

