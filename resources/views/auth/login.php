<?php
/**
 * Login View
 * 
 * Redesigned login page with grain overlay background
 */
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= config('app.name'); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        
        /* Grain Overlay */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100"><filter id="noiseFilter"><feTurbulence type="fractalNoise" baseFrequency="0.9" numOctaves="4" result="noise" /></filter><rect width="100" height="100" fill="%23000" filter="url(%23noiseFilter)" opacity="0.05"/></svg>');
            background-repeat: repeat;
            pointer-events: none;
            z-index: 1;
        }
        
        .login-container {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 400px;
            padding: 40px;
            background: rgba(30, 35, 50, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            backdrop-filter: blur(10px);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 32px;
        }
        
        .login-header h1 {
            font-size: 24px;
            font-weight: 700;
            color: #ffc107;
            margin-bottom: 8px;
        }
        
        .login-header p {
            font-size: 14px;
            color: #a0a0a0;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #e0e0e0;
            text-transform: uppercase;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 16px;
            background: rgba(60, 70, 100, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 6px;
            color: #e0e0e0;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #ffc107;
            background: rgba(60, 70, 100, 0.8);
            box-shadow: 0 0 0 3px rgba(255, 193, 7, 0.1);
        }
        
        .form-group input::placeholder {
            color: #757575;
        }
        
        .login-button {
            width: 100%;
            padding: 12px 16px;
            background: linear-gradient(135deg, #ffc107 0%, #ffb300 100%);
            color: #000;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            margin-top: 12px;
        }
        
        .login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(255, 193, 7, 0.3);
        }
        
        .login-button:active {
            transform: translateY(0);
        }
        
        .error-message {
            background: rgba(239, 83, 80, 0.1);
            border: 1px solid rgba(239, 83, 80, 0.5);
            border-radius: 6px;
            padding: 12px 16px;
            color: #ef5350;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .error-message::before {
            content: '⚠';
            font-weight: 700;
        }
        
        .demo-note {
            text-align: center;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 12px;
            color: #a0a0a0;
        }
        
        .demo-note strong {
            color: #ffc107;
            display: block;
            margin-bottom: 4px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1><?= config('app.name'); ?></h1>
            <p>Server Monitoring & Health Checks</p>
        </div>
        
        <?php if ($error): ?>
        <div class="error-message">
            <?= htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="/api/auth/login">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(app(\App\Infrastructure\Authentication\AuthenticationService::class)->generateCsrfToken()); ?>">
            <div class="form-group">
                <label for="username">Username</label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    placeholder="Enter your username"
                    required 
                    autofocus
                />
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    placeholder="Enter your password"
                    required
                />
            </div>
            
            <button type="submit" class="login-button">Login</button>
        </form>
        
        <div class="demo-note">
            <strong>Demo Credentials</strong>
            Username: admin<br>
            Password: admin
        </div>
    </div>
</body>
</html>
