<?php
/**
 * Navigation Bar Component
 */
if (!isset($user)) {
    try {
        $auth = app(\App\Infrastructure\Authentication\AuthenticationService::class);
        $user = $auth->user();
    } catch (\Exception $e) {
        $user = null;
    }
}

// Generate CSRF token for the frontend to use in APIs
$csrfToken = '';
try {
    $auth = app(\App\Infrastructure\Authentication\AuthenticationService::class);
    $csrfToken = $auth->generateCsrfToken();
} catch (\Exception $e) {}

$currentUri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
$currentPath = basename($currentUri, '.php');
if ($currentPath === '' || $currentPath === 'index') {
    $currentPath = 'dashboard';
}
?>
<meta name="csrf-token" content="<?= htmlspecialchars($csrfToken) ?>">
<style>
    nav {
        background: var(--surface-color);
        padding: 0 20px;
        position: sticky;
        top: 0;
        z-index: 100;
        border-bottom: 1px solid var(--border-color);
    }
    .nav-content {
        max-width: 1200px;
        margin: 0 auto;
        display: flex;
        justify-content: space-between;
        align-items: center;
        height: 60px;
    }
    .nav-logo {
        font-size: 20px;
        font-weight: bold;
        color: var(--primary-color);
        text-decoration: none;
    }
    .nav-links {
        display: flex;
        gap: 30px;
        align-items: center;
    }
    .nav-links a {
        text-decoration: none;
        color: var(--muted-color);
        font-size: 14px;
        transition: color 0.3s ease;
        padding-bottom: 4px;
        border-bottom: 2px solid transparent;
    }
    .nav-links a:hover, .nav-links a.active {
        color: var(--primary-color);
    }
    .nav-links a.active {
        font-weight: 600;
        border-bottom: 2px solid var(--primary-color);
    }
    .nav-user {
        display: flex;
        align-items: center;
        gap: 15px;
        color: var(--text-color);
    }
    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: var(--primary-color);
        color: #000000;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
    }
    .logout-btn {
        background: none;
        border: none;
        color: var(--danger-color);
        cursor: pointer;
        font-size: 14px;
        text-decoration: underline;
    }
    @media (max-width: 768px) {
        .nav-content {
            flex-direction: column;
            height: auto;
            padding: 15px 0;
            gap: 15px;
        }
        .nav-links {
            gap: 15px;
            flex-wrap: wrap;
            justify-content: center;
        }
    }
</style>

<nav>
    <div class="nav-content">
        <a href="/dashboard.php" class="nav-logo">📡 Monitor</a>
        <div class="nav-links">
            <a href="/dashboard.php" class="<?= $currentPath === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
            <a href="/servers.php" class="<?= $currentPath === 'servers' ? 'active' : '' ?>">Servers</a>
            <a href="/settings.php" class="<?= $currentPath === 'settings' ? 'active' : '' ?>">Settings</a>
            <a href="/reports.php" class="<?= $currentPath === 'reports' ? 'active' : '' ?>">Reports</a>
        </div>
        <div class="nav-user">
            <button onclick="toggleTheme()" class="theme-toggle-btn" title="Toggle Theme">
                <span class="light-icon" style="display: none;">☀️</span>
                <span class="dark-icon" style="display: none;">🌙</span>
            </button>
            <?php if ($user): ?>
            <div class="user-avatar"><?php echo strtoupper(substr($user->getUsername() ?? 'U', 0, 1)); ?></div>
            <div style="font-size: 14px;">
                <div style="font-weight: 500;"><?php echo htmlspecialchars($user->getUsername() ?? 'User'); ?></div>
                <div style="color: var(--muted-color); font-size: 12px;"><?php echo ucfirst($user->getRole()); ?></div>
            </div>
            <?php endif; ?>
            <form action="/api/auth/logout.php" method="POST" style="margin: 0;">
                <button type="submit" class="logout-btn">Logout</button>
            </form>
        </div>
    </div>
</nav>
