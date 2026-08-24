<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMTP Test Notification - Maya Sree Fashion</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #FAF8F5;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #1E293B;
        }
        .wrapper {
            width: 100%;
            background-color: #FAF8F5;
            padding: 30px 15px;
            box-sizing: border-box;
        }
        .container {
            max-width: 550px;
            margin: 0 auto;
            background: #FFFFFF;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #E8DDD3;
            box-shadow: 0 4px 16px rgba(91, 22, 58, 0.05);
        }
        .header {
            background: linear-gradient(135deg, #5B163A 0%, #3D0E26 100%);
            color: #FFFFFF;
            padding: 24px;
            text-align: center;
        }
        .brand-title {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 22px;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin: 0;
            color: #FAF8F5;
        }
        .content {
            padding: 24px;
            text-align: center;
        }
        .success-icon {
            font-size: 44px;
            margin-bottom: 12px;
        }
        .heading {
            font-size: 18px;
            font-weight: 700;
            color: #5B163A;
            margin: 0 0 8px 0;
        }
        .description {
            font-size: 14px;
            color: #64748B;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        .info-card {
            background: #FAF8F5;
            border: 1px solid #E8DDD3;
            border-radius: 8px;
            padding: 14px;
            text-align: left;
            font-size: 13px;
        }
        .info-item {
            margin-bottom: 6px;
        }
        .info-item:last-child {
            margin-bottom: 0;
        }
        .footer {
            background-color: #FAF8F5;
            border-top: 1px solid #E8DDD3;
            padding: 16px;
            text-align: center;
            font-size: 12px;
            color: #94A3B8;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <h1 class="brand-title">Maya Sree Fashion</h1>
            </div>
            <div class="content">
                <div class="success-icon">✉️ ✨</div>
                <h2 class="heading">SMTP Email Test Successful!</h2>
                <p class="description">
                    This is a test notification confirming that your mail server settings and order alert notification configurations are functioning perfectly.
                </p>
                <div class="info-card">
                    <div class="info-item"><strong>Recipient:</strong> {{ $recipientEmail }}</div>
                    <div class="info-item"><strong>Timestamp:</strong> {{ date('d M Y, h:i:s A') }}</div>
                    <div class="info-item"><strong>Environment:</strong> {{ app()->environment() }}</div>
                </div>
            </div>
            <div class="footer">
                Maya Sree Fashion &bull; Store Admin Notification System
            </div>
        </div>
    </div>
</body>
</html>
