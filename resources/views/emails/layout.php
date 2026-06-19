<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Monitor Alert') ?></title>
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            color: #333333;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        .header {
            padding: 30px 40px;
            text-align: center;
        }
        .header.danger {
            background-color: #ef4444;
            color: #ffffff;
        }
        .header.success {
            background-color: #10b981;
            color: #ffffff;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .content {
            padding: 40px;
        }
        .footer {
            background-color: #f1f5f9;
            padding: 20px 40px;
            text-align: center;
            font-size: 13px;
            color: #64748b;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #3b82f6;
            color: #ffffff;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            margin-top: 20px;
        }
        .detail-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
        }
        .detail-row {
            margin-bottom: 10px;
        }
        .detail-row:last-child {
            margin-bottom: 0;
        }
        .detail-label {
            font-weight: 600;
            color: #475569;
            width: 120px;
            display: inline-block;
        }
        .detail-value {
            color: #1e293b;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header <?= htmlspecialchars($headerClass ?? 'danger') ?>">
            <h1><?= htmlspecialchars($title ?? 'Monitor Alert') ?></h1>
        </div>
        
        <div class="content">
            <?= $slot ?? '' ?>
        </div>
        
        <div class="footer">
            <p>This is an automated message from the Monitor System.</p>
            <p>&copy; <?= date('Y') ?> NC Edges. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
