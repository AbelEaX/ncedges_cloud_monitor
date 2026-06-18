<?php
ob_start();
session_start();
$config = require __DIR__ . '/config.php';
$error = '';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    $user = trim($_POST['username'] ?? '');
    $pass = trim($_POST['password'] ?? '');
    if (hash_equals($_SESSION['csrf_token'], $token) && $user === $config['auth']['username'] && $pass === $config['auth']['password']) {
        $_SESSION['logged_in'] = true;
        header('Location: index.php');
        exit;
    } else {
        $error = 'Invalid username or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Login - <?= htmlspecialchars($config['company_name']); ?></title>
    <style>
        body{font-family:Inter,system-ui;background:var(--bg);display:flex;align-items:center;justify-content:center;height:100vh;margin:0}
        .box{background:var(--card);border-radius:4px;box-shadow:0 10px 25px -5px rgba(0,0,0,.5);width:350px;overflow:hidden;border-top:4px solid var(--accent)}
        .header{background:var(--card);padding:20px;color:var(--text);text-align:center;border-bottom:1px solid var(--border)}
        .header h2{margin:0;font-size:18px;text-transform:uppercase;letter-spacing:1px}
        .content{padding:24px}
        input{width:100%;padding:12px;margin:8px 0;border:1px solid var(--border);border-radius:4px;box-sizing:border-box;font-size:14px;background:var(--bg);color:var(--text)}
        button{width:100%;padding:12px;background:var(--accent);color:#000;border:none;border-radius:4px;font-weight:700;cursor:pointer;margin-top:10px}
        button:hover{background:#eaaa00}
        .error{background:rgba(239,83,80,0.2);color:var(--red);padding:10px;border-radius:4px;font-size:12px;margin-bottom:15px}
    </style>
</head>
<body>
<div class="box">
    <div class="header"><h2>Service Portal</h2></div>
    <div class="content">
        <?php if ($error): ?><div class="error"><?= htmlspecialchars($error); ?></div><?php endif; ?>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
            <input name="username" placeholder="Username" required autofocus>
            <input name="password" type="password" placeholder="Password" required>
            <button type="submit">LOGIN TO MONITOR</button>
        </form>
    </div>
</div>
</body>
</html>
