<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'DM Sans', sans-serif; background-color: #f8f9fc; padding: 40px; text-align: center; }
        .container { background-color: #ffffff; padding: 40px; border-radius: 12px; box-shadow: 0 4px 24px rgba(26,110,255,0.08); max-width: 500px; margin: 0 auto; }
        .logo { color: #1A6EFF; font-family: 'Playfair Display', serif; font-size: 24px; font-weight: 700; margin-bottom: 20px; }
        .code-box { background-color: #e8f0ff; color: #1258d6; font-size: 36px; padding: 20px; border-radius: 12px; letter-spacing: 12px; font-weight: 700; margin: 30px 0; }
        .footer { color: #6b7280; font-size: 13px; margin-top: 30px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">CoastalCharmz</div>
        <h2>Sign In Verification</h2>
        <p>Use the following 4-digit code to securely log in to your account. This code is valid for 10 minutes.</p>
        
        <div class="code-box">
            {{ $code }}
        </div>
        
        <p>If you didn't request this code, please ignore this email.</p>
        <div class="footer">
            &copy; {{ date('Y') }} CoastalCharmz. All rights reserved.
        </div>
    </div>
</body>
</html>
