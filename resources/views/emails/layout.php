<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Monitor Alert') ?></title>
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #0f172a;
            margin: 0;
            padding: 0;
            color: #f8fafc;
            -webkit-font-smoothing: antialiased;
        }
        .wrapper {
            width: 100%;
            background-color: #0f172a;
            padding: 40px 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #1e293b;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5), 0 8px 10px -6px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .header {
            padding: 35px 40px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        .header.danger {
            background: linear-gradient(135deg, #7f1d1d 0%, #450a0a 100%);
            border-bottom: 1px solid #991b1b;
        }
        .header.success {
            background: linear-gradient(135deg, #065f46 0%, #022c22 100%);
            border-bottom: 1px solid #059669;
        }
        .header.info {
            background: linear-gradient(135deg, #1e3a8a 0%, #172554 100%);
            border-bottom: 1px solid #2563eb;
        }
        .header h1 {
            margin: 0;
            font-size: 26px;
            font-weight: 700;
            letter-spacing: -0.5px;
            color: #ffffff;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        .content {
            padding: 40px;
            line-height: 1.6;
            color: #cbd5e1;
            font-size: 15px;
        }
        .content h2 {
            color: #f8fafc;
            font-size: 20px;
            margin-top: 0;
            margin-bottom: 16px;
        }
        .footer {
            background-color: #0b1120;
            padding: 24px 40px;
            text-align: center;
            font-size: 12px;
            color: #64748b;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
        }
        .button {
            display: inline-block;
            padding: 14px 28px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin-top: 24px;
            text-align: center;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3);
        }
        .detail-box {
            background-color: #0f172a;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 24px;
            margin: 24px 0;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.2);
        }
        .detail-row {
            margin-bottom: 12px;
            display: table;
            width: 100%;
        }
        .detail-row:last-child {
            margin-bottom: 0;
        }
        .detail-label {
            font-weight: 600;
            color: #94a3b8;
            display: table-cell;
            width: 130px;
            vertical-align: top;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
            padding-top: 3px;
        }
        .detail-value {
            color: #f8fafc;
            display: table-cell;
            font-weight: 500;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-red { background-color: rgba(239, 68, 68, 0.2); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.4); }
        .badge-green { background-color: rgba(16, 185, 129, 0.2); color: #6ee7b7; border: 1px solid rgba(16, 185, 129, 0.4); }
    </style>
</head>
<body>
    <div class="wrapper">
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
    </div>
</body>
</html>
