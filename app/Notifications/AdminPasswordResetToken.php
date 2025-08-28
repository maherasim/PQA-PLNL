<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Illuminate\Notifications\Notification;

class AdminPasswordResetToken extends Notification implements ShouldQueue
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

		$resetUrl = URL::to('/api/admin/password/reset/verify?token=' . urlencode($this->token));
		$frontendUrl = rtrim((string) Config::get('app.frontend_password_reset_url', ''), '/');

		return (new MailMessage)
			->subject('Reset your password')
			->line('Use the link below to verify your password reset token.')
			->action('Verify Reset Token', $resetUrl)
			->line("This token will expire in {$expiresMinutes} minutes.")
			->line('If you did not request a password reset, no further action is required.');
	}
}