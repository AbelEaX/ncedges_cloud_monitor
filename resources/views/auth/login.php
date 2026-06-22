<?php
/**
 * Login View
 * 
 * Redesigned login page with grain overlay background
 */
$error = $_GET['error'] ?? '';
$themeService = app(\App\Infrastructure\Logging\ThemeService::class);
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?= htmlspecialchars($themeService->getCurrentTheme()); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= config('app.name'); ?></title>
    <?= $themeService->getStyleTag(); ?>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background-color: #0f172a;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }
        
        /* Background Slideshow */
        .bg-slideshow {
            position: fixed;
            top: -10%;
            left: -10%;
            width: 120%;
            height: 120%;
            z-index: 0;
        }
        
        .bg-slideshow-item {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
            opacity: 0;
            animation: kenburns 32s infinite linear;
        }
        
        .bg-slideshow-item:nth-child(1) {
            background-image: url('/assets/1.jpg');
            animation-delay: 0s;
        }
        .bg-slideshow-item:nth-child(2) {
            background-image: url('/assets/2.jpg');
            animation-delay: 8s;
        }
        .bg-slideshow-item:nth-child(3) {
            background-image: url('/assets/3.jpg');
            animation-delay: 16s;
        }
        .bg-slideshow-item:nth-child(4) {
            background-image: url('/assets/4.jpg');
            animation-delay: 24s;
        }
        
        @keyframes kenburns {
            0% {
                opacity: 0;
                transform: scale(1) translateX(0);
            }
            10% { opacity: 1; }
            25% { opacity: 1; }
            35% { opacity: 0; }
            100% {
                opacity: 0;
                transform: scale(1.15) translateX(-40px);
            }
        }
        
        /* Pixelated Grain Overlay */
        .grain-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            /* Pixelated pattern (using white with screen/overlay works best) */
            background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="4" height="4"><rect width="2" height="2" fill="%23fff" opacity="0.08"/><rect x="2" y="2" width="2" height="2" fill="%23fff" opacity="0.08"/></svg>');
            pointer-events: none;
            mix-blend-mode: screen;
            z-index: 1;
        }
        
        /* Dark Gradient Overlay for readability */
        .dark-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(26, 26, 46, 0.8) 0%, rgba(22, 33, 62, 0.9) 100%);
            z-index: 1;
            pointer-events: none;
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
        
        /* Light Theme Overrides for Login */
        [data-theme="light"] body { background: var(--bg-color, #f5f5f5); }
        [data-theme="light"] body::before { opacity: 0.02; filter: invert(1); }
        [data-theme="light"] .login-container { background: var(--surface-color, #ffffff); border: 1px solid var(--border-color, #e0e0e0); }
        [data-theme="light"] .form-group input { background: var(--bg-color, #f9f9f9); color: var(--text-color, #333); border: 1px solid var(--border-color, #ccc); }
        [data-theme="light"] .form-group input:focus { background: #fff; box-shadow: 0 0 0 3px rgba(255, 193, 7, 0.2); }
        [data-theme="light"] .form-group label { color: var(--text-color, #333); }
        [data-theme="light"] .login-header p { color: var(--muted-color, #666); }
        [data-theme="light"] .demo-note { color: var(--muted-color, #666); border-top: 1px solid var(--border-color, #ddd); }
        [data-theme="light"] .demo-note strong { color: var(--primary-color, #ffc107); }
        [data-theme="light"] .error-message { background: rgba(239, 83, 80, 0.1); border: 1px solid rgba(239, 83, 80, 0.3); }
        
        .theme-toggle-btn { position: absolute; top: 20px; right: 20px; background: none; border: none; cursor: pointer; font-size: 24px; z-index: 10; opacity: 0.8; transition: opacity 0.3s; }
        .theme-toggle-btn:hover { opacity: 1; }
        [data-theme="dark"] .light-icon { display: inline !important; }
        [data-theme="dark"] .dark-icon { display: none !important; }
        [data-theme="light"] .dark-icon { display: inline !important; }
        [data-theme="light"] .light-icon { display: none !important; }
    </style>
</head>
<body>
    <button onclick="toggleTheme()" class="theme-toggle-btn" title="Toggle Theme">
        <span class="light-icon" style="display: none;">☀️</span>
        <span class="dark-icon" style="display: none;">🌙</span>
    </button>
    
    <!-- Background Slideshow -->
    <div class="bg-slideshow">
        <div class="bg-slideshow-item"></div>
        <div class="bg-slideshow-item"></div>
        <div class="bg-slideshow-item"></div>
        <div class="bg-slideshow-item"></div>
    </div>
    
    <!-- Overlays -->
    <div class="dark-overlay"></div>
    <div class="grain-overlay"></div>

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
    </div>
    <script>
        function toggleTheme() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', newTheme);
            document.cookie = "theme=" + newTheme + "; path=/; max-age=31536000";
        }
    </script>
</body>
</html>
