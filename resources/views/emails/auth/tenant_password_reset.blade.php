<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Password Reset</title>
    <style>
      body { font-family: Arial, sans-serif; background: #f7fafc; margin:0; padding:24px; }
      .container { max-width: 560px; margin: 0 auto; background:#ffffff; border-radius:8px; padding:24px; border:1px solid #e5e7eb; }
      h1 { font-size: 18px; margin:0 0 12px; color:#111827; }
      p { color:#374151; line-height: 1.6; }
      .btn { display:inline-block; background:#2563eb; color:#ffffff; text-decoration:none; padding:10px 16px; border-radius:6px; margin:16px 0; }
      .muted { color:#6b7280; font-size: 12px; }
      .token { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; background:#f3f4f6; padding:4px 6px; border-radius:4px; }
    </style>
  </head>
  <body>
    <div class="container">
      <h1>Reset your password</h1>
      <p>Hello,</p>
      <p>We received a request to reset your password for {{ $appName }}. This token will expire in {{ $expiresMinutes }} minutes.</p>
      <p>
        <a class="btn" href="{{ $resetUrl }}" target="_blank" rel="noopener">Reset Password</a>
      </p>
      <p>Or use this token in the app: <span class="token">{{ $token }}</span></p>
      @if(!empty($frontendUrl))
        <p>If clicking the button doesn't work, you can paste the token in the reset screen at: <a href="{{ $frontendUrl }}" target="_blank" rel="noopener">{{ $frontendUrl }}</a></p>
      @endif
      <p class="muted">If you didn't request this, you can safely ignore this email.</p>
    </div>
  </body>
  </html>

