<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Reset Password</title>
  <style>
    body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
    .wrapper { max-width: 520px; margin: 40px auto; background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.08); }
    .header { background: #6366f1; padding: 28px 32px; text-align: center; }
    .header h1 { color: #fff; margin: 0; font-size: 22px; }
    .body { padding: 32px; color: #374151; }
    .body p { font-size: 15px; line-height: 1.6; margin: 0 0 16px; }
    .token-box { background: #f0f0ff; border: 1px dashed #6366f1; border-radius: 8px; text-align: center; padding: 18px; margin: 24px 0; }
    .token-box span { font-size: 28px; font-weight: 700; letter-spacing: 6px; color: #4f46e5; }
    .note { font-size: 13px; color: #9ca3af; }
    .footer { background: #f9fafb; padding: 16px 32px; text-align: center; font-size: 12px; color: #9ca3af; }
  </style>
</head>
<body>
  <div class="wrapper">
    <div class="header">
      <h1>Password Reset</h1>
    </div>
    <div class="body">
      <p>Hi <strong>{{ $user->name }}</strong>,</p>
      <p>We received a request to reset your password. Use the code below in the app to set a new password:</p>
      <div class="token-box">
        <span>{{ $token }}</span>
      </div>
      <p>This code will expire in <strong>60 minutes</strong>.</p>
      <p class="note">If you did not request a password reset, you can safely ignore this email. Your password will not change.</p>
    </div>
    <div class="footer">
      &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
    </div>
  </div>
</body>
</html>
