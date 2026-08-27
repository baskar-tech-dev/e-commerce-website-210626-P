<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password - Maya Sree Fashion</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #fdfbf7;
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #2D3748;
            -webkit-font-smoothing: antialiased;
        }
        .wrapper {
            width: 100%;
            background-color: #fdfbf7;
            padding: 40px 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
            border: 1px solid #f1e9df;
        }
        .header {
            background: linear-gradient(135deg, #5B163A 0%, #3B0D24 100%);
            padding: 32px 24px;
            text-align: center;
            color: #ffffff;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: 0.05em;
            color: #D4AF37;
            font-family: 'Playfair Display', Georgia, serif;
            text-transform: uppercase;
        }
        .header p {
            margin: 6px 0 0 0;
            font-size: 13px;
            color: #e2d1d9;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }
        .content {
            padding: 36px 32px;
            line-height: 1.6;
        }
        .greeting {
            font-size: 18px;
            font-weight: 600;
            color: #5B163A;
            margin-bottom: 12px;
        }
        .btn-container {
            text-align: center;
            margin: 32px 0;
        }
        .btn-primary {
            display: inline-block;
            background-color: #5B163A;
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 15px;
            letter-spacing: 0.02em;
            box-shadow: 0 4px 12px rgba(91, 22, 58, 0.25);
        }
        .security-note {
            background-color: #fdfaf3;
            border-left: 4px solid #D4AF37;
            padding: 14px 16px;
            border-radius: 0 6px 6px 0;
            font-size: 13px;
            color: #715822;
            margin-top: 24px;
        }
        .footer {
            background-color: #f8f5f0;
            padding: 24px;
            text-align: center;
            font-size: 12px;
            color: #8C827A;
            border-top: 1px solid #ede5da;
        }
        .footer a {
            color: #5B163A;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <h1>Maya Sree Fashion</h1>
                <p>Signature Ethnic Collection</p>
            </div>

            <div class="content">
                <div class="greeting">Hello {{ $user->name ?: 'Valued Customer' }},</div>
                <p>We received a request to reset the password for your Maya Sree Fashion account (<strong>{{ $user->email }}</strong>).</p>
                <p>You can reset your password by clicking the button below:</p>

                <div class="btn-container">
                    <a href="{{ $resetUrl }}" class="btn-primary" target="_blank">Reset My Password</a>
                </div>

                <div class="security-note">
                    🔒 <strong>Security Notice:</strong> This password reset link is valid for <strong>60 minutes</strong>. If you did not make this request, please safely ignore this email or contact support if you have concerns.
                </div>

                <p style="margin-top: 24px; font-size: 13px; color: #718096;">
                    If you are having trouble clicking the button, copy and paste the URL below into your web browser:<br>
                    <a href="{{ $resetUrl }}" style="color: #5B163A; word-break: break-all; font-size: 12px;">{{ $resetUrl }}</a>
                </p>
            </div>

            <div class="footer">
                &copy; {{ date('Y') }} Maya Sree Fashion. All Rights Reserved.<br>
                Tirupur, Tamil Nadu, India | <a href="https://mayasreefashion.com">mayasreefashion.com</a>
            </div>
        </div>
    </div>
</body>
</html>
