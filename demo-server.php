<?php
/**
 * Demo Server - Quick Test
 * 
 * Standalone server for testing login functionality
 * without complex routing or database requirements
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Simple routing
$action = $_GET['action'] ?? 'login';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'login-submit') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Demo credentials
    $validUsers = [
        'admin' => 'admin',
        'manager' => 'manager',
        'viewer' => 'viewer'
    ];
    
    if (isset($validUsers[$username]) && $validUsers[$username] === $password) {
        $_SESSION['logged_in'] = true;
        $_SESSION['user'] = $username;
        $_SESSION['role'] = $username === 'admin' ? 'Administrator' : ucfirst($username);
        header('Location: ?action=dashboard');
        exit;
    } else {
        $error = 'Invalid credentials. Try admin/admin or manager/manager';
    }
}

if ($action === 'dashboard' && !isset($_SESSION['logged_in'])) {
    header('Location: ?action=login');
    exit;
}

if ($action === 'logout') {
    session_destroy();
    header('Location: ?action=login');
    exit;
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $action === 'login' ? 'Login' : 'Dashboard'; ?> - Monitor Demo</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #1a1a2e; color: #e0e0e0; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .container { width: 100%; max-width: 500px; padding: 20px; }
        .box { background: #16213e; border: 1px solid #444; border-radius: 8px; padding: 40px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); }
        h1 { color: #ffc107; margin-bottom: 30px; font-size: 28px; text-align: center; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
        input { width: 100%; padding: 12px 16px; background: #0f3460; border: 1px solid #444; border-radius: 6px; color: #e0e0e0; font-size: 14px; }
        input:focus { outline: none; border-color: #ffc107; box-shadow: 0 0 0 3px rgba(255, 193, 7, 0.1); }
        button { width: 100%; padding: 12px; background: linear-gradient(135deg, #ffc107, #ffb300); color: #000; border: none; border-radius: 6px; font-weight: 700; cursor: pointer; font-size: 14px; margin-top: 10px; }
        button:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(255, 193, 7, 0.3); }
        .error { background: rgba(239, 83, 80, 0.2); border: 1px solid rgba(239, 83, 80, 0.5); color: #ef5350; padding: 12px; border-radius: 6px; margin-bottom: 20px; }
        .success { background: rgba(102, 187, 106, 0.2); border: 1px solid rgba(102, 187, 106, 0.5); color: #66bb6a; padding: 12px; border-radius: 6px; margin-bottom: 20px; }
        .demo-note { text-align: center; margin-top: 24px; padding-top: 24px; border-top: 1px solid #444; font-size: 12px; color: #a0a0a0; }
        .demo-note strong { color: #ffc107; display: block; margin-bottom: 8px; }
        .dashboard { }
        .dashboard-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .dashboard-header h2 { font-size: 24px; }
        .logout-btn { background: #ef5350; padding: 10px 20px; border-radius: 6px; border: none; color: white; cursor: pointer; font-weight: 600; }
        .logout-btn:hover { background: #e53935; }
        .stats { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; }
        .stat-card { background: #0f3460; padding: 20px; border-radius: 8px; border: 1px solid #444; }
        .stat-label { color: #a0a0a0; font-size: 12px; text-transform: uppercase; margin-bottom: 8px; }
        .stat-value { color: #ffc107; font-size: 28px; font-weight: 700; }
        .feature-list { background: #0f3460; padding: 20px; border-radius: 8px; border: 1px solid #444; }
        .feature-list h3 { color: #ffc107; margin-bottom: 16px; }
        .feature-list ul { list-style: none; }
        .feature-list li { padding: 8px 0; color: #e0e0e0; }
        .feature-list li:before { content: "✓ "; color: #66bb6a; font-weight: 700; margin-right: 8px; }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($action === 'login'): ?>
            <div class="box">
                <h1>🔐 Monitor Login</h1>
                
                <?php if (isset($error)): ?>
                    <div class="error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <input type="hidden" name="action" value="login-submit">
                    
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" placeholder="Enter username" required autofocus>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Enter password" required>
                    </div>
                    
                    <button type="submit">Login</button>
                </form>
                
                <div class="demo-note">
                    <strong>Demo Accounts</strong>
                    admin / admin<br>
                    manager / manager<br>
                    viewer / viewer
                </div>
            </div>
        <?php elseif ($action === 'dashboard' && isset($_SESSION['logged_in'])): ?>
            <div class="dashboard">
                <div class="dashboard-header">
                    <div>
                        <h1>📊 Monitor Dashboard</h1>
                        <p style="color: #a0a0a0; margin-top: 5px;">Welcome, <strong><?php echo htmlspecialchars($_SESSION['role']); ?></strong></p>
                    </div>
                    <form method="GET" style="display: inline;">
                        <input type="hidden" name="action" value="logout">
                        <button type="submit" class="logout-btn">Logout</button>
                    </form>
                </div>
                
                <div class="success">
                    ✅ Successfully logged in as <strong><?php echo htmlspecialchars($_SESSION['user']); ?></strong>
                </div>
                
                <div class="stats">
                    <div class="stat-card">
                        <div class="stat-label">Total Servers</div>
                        <div class="stat-value">7</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Online</div>
                        <div class="stat-value">6</div>
                    </div>
                </div>
                
                <div class="feature-list">
                    <h3>✨ Application Features</h3>
                    <ul>
                        <li>Clean Architecture Implementation</li>
                        <li>Server Management CRUD</li>
                        <li>Settings & Configuration</li>
                        <li>Reports & Analytics</li>
                        <li>Audit Logging</li>
                        <li>Role-Based Access Control</li>
                        <li>Activity Timeline</li>
                        <li>Email Notifications</li>
                    </ul>
                </div>
                
                <div style="margin-top: 30px; padding: 20px; background: #0f3460; border-radius: 8px; border: 1px solid #444; text-align: center;">
                    <p style="color: #a0a0a0; margin-bottom: 10px;">Login successful! The application is ready for testing.</p>
                    <p style="font-size: 12px; color: #757575;">This is a demo server. Full application features are available in the production build.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
