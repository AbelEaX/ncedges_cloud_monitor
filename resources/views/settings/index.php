<?php
/**
 * Settings Management View
 */
$themeService = app(\App\Infrastructure\Logging\ThemeService::class);
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(config('app.locale', 'en')); ?>" data-theme="<?= htmlspecialchars($themeService->getCurrentTheme()); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - <?= htmlspecialchars(config('app.name', 'Monitor')); ?></title>
    <?= $themeService->getStyleTag(); ?>
    <style>
        :root {
            --bg-color: var(--background, #f5f5f5);
            --surface-color: var(--surface, #ffffff);
            --border-color: var(--border, #e0e0e0);
            --text-color: var(--text, #333333);
            --primary-color: var(--primary, #ec1d63);
            --success-color: var(--success, #10b981);
            --danger-color: var(--danger, #ef4444);
            --warning-color: var(--warning, #f59e0b);
            --info-color: var(--info, #3b82f6);
            --muted-color: var(--muted, #666666);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: var(--font-family, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif);
            font-size: var(--base-size, 12px);
            line-height: var(--line-height, 1.5);
            background: var(--bg-color);
            color: var(--text-color);
        }
        .container { max-width: 1200px; margin: 0 auto; padding: var(--container-padding, 20px); }
        header {
            margin-bottom: 30px;
            background: var(--surface-color);
            padding: 24px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }
        h1 { font-size: 28px; }
        .content {
            display: grid;
            grid-template-columns: var(--sidebar-width, 180px) 1fr;
            gap: var(--container-padding, 15px);
        }
        .sidebar {
            background: var(--surface-color);
            padding: 24px;
            border-radius: 8px;
            height: fit-content;
            border: 1px solid var(--border-color);
        }
        .sidebar-item {
            padding: 12px 16px;
            cursor: pointer;
            border-left: 3px solid transparent;
            margin-bottom: 5px;
            border-radius: 4px;
            transition: all 0.3s ease;
            color: var(--muted-color);
        }
        .sidebar-item:hover {
            background: var(--bg-color);
            color: var(--text-color);
        }
        .sidebar-item.active {
            background: rgba(59, 130, 246, 0.1);
            border-left-color: var(--primary-color);
            font-weight: 600;
            color: var(--primary-color);
        }
        .settings-section {
            background: var(--surface-color);
            padding: 24px;
            border-radius: 8px;
            display: none;
            border: 1px solid var(--border-color);
        }
        .settings-section.active {
            display: block;
        }
        h2 {
            font-size: 20px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-color);
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-color);
        }
        input[type="text"],
        input[type="email"],
        input[type="number"],
        input[type="password"],
        select,
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-size: 14px;
            background: var(--bg-color);
            color: var(--text-color);
        }
        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="number"]:focus,
        input[type="password"]:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        input[type="checkbox"] {
            margin-right: 8px;
            accent-color: var(--primary-color);
        }
        .checkbox-label {
            display: flex;
            align-items: center;
            color: var(--text-color);
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        .btn-primary {
            background: var(--primary-color);
            color: #000000;
            font-weight: bold;
        }
        .btn-primary:hover {
            opacity: 0.9;
        }
        .btn-secondary {
            background: var(--muted-color);
            color: white;
        }
        .btn-secondary:hover {
            opacity: 0.9;
        }
        .alert {
            padding: 12px 16px;
            border-radius: 4px;
            margin-bottom: 20px;
            display: none;
        }
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #6ee7b7;
            display: block;
        }
        .alert-error {
            background: #fee2e2;
            color: #7f1d1d;
            border: 1px solid #fca5a5;
            display: block;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .theme-options {
            display: flex;
            gap: 20px;
            margin-top: 10px;
        }
        .theme-option {
            flex: 1;
            padding: 24px;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s ease;
            background: var(--bg-color);
        }
        .theme-option.selected {
            border-color: var(--primary-color);
            background: rgba(59, 130, 246, 0.1);
        }
        .theme-option h3 {
            margin-bottom: 10px;
            color: var(--text-color);
        }
        .theme-option p {
            color: var(--muted-color);
        }
        .test-email-button {
            margin-top: 10px;
        }
        .divider {
            border-top: 1px solid var(--border-color);
            margin: 30px 0;
        }
        .theme-toggle-btn { background: none; border: none; cursor: pointer; font-size: 18px; color: var(--text-color); margin-right: 15px; }
        [data-theme="dark"] .light-icon { display: inline !important; }
        [data-theme="dark"] .dark-icon { display: none !important; }
        [data-theme="light"] .dark-icon { display: inline !important; }
        [data-theme="light"] .light-icon { display: none !important; }
    </style>
</head>
<body>
    <?= component('nav', ['user' => $user ?? null]) ?>
    <div class="container">
        <header style="display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 15px;">
                <div>
                    <h1>Settings</h1>
                    <p style="color: #6b7280; margin-top: 5px;">Configure application settings and preferences</p>
                </div>
            </div>
        </header>

        <div id="alert" class="alert"></div>

        <div class="content">
            <div class="sidebar">
                <div class="sidebar-item active" onclick="switchTab('general')">General</div>
                <div class="sidebar-item" onclick="switchTab('theme')">Theme</div>
                <div class="sidebar-item" onclick="switchTab('smtp')">SMTP</div>
                <div class="sidebar-item" onclick="switchTab('notifications')">Notifications</div>
                <div class="sidebar-item" onclick="switchTab('monitoring')">Monitoring</div>
                <div class="sidebar-item" onclick="switchTab('security')">Security</div>
            </div>

            <div class="settings-content">
                <!-- General Settings -->
                <div id="general" class="settings-section active">
                    <h2>General Settings</h2>
                    <form onsubmit="handleSettingsSubmit(event, 'general')">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="appName">Application Name</label>
                                <input type="text" id="appName" name="app_name" value="<?= htmlspecialchars(config('app.name', 'Monitor System')); ?>">
                            </div>
                            <div class="form-group">
                                <label for="appUrl">Application URL</label>
                                <input type="text" id="appUrl" name="app_url" value="<?= htmlspecialchars(config('app.url', 'http://localhost')); ?>">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="timezone">Timezone</label>
                                <select id="timezone" name="timezone">
                                    <?php $tz = config('app.timezone', 'Africa/Kampala'); ?>
                                    <option value="UTC" <?= $tz === 'UTC' ? 'selected' : ''; ?>>UTC</option>
                                    <option value="Africa/Kampala" <?= $tz === 'Africa/Kampala' ? 'selected' : ''; ?>>Africa/Kampala</option>
                                    <option value="America/New_York" <?= $tz === 'America/New_York' ? 'selected' : ''; ?>>America/New_York</option>
                                    <option value="Europe/London" <?= $tz === 'Europe/London' ? 'selected' : ''; ?>>Europe/London</option>
                                    <option value="Asia/Tokyo" <?= $tz === 'Asia/Tokyo' ? 'selected' : ''; ?>>Asia/Tokyo</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="locale">Locale</label>
                                <select id="locale" name="locale">
                                    <?php $loc = config('app.locale', 'en'); ?>
                                    <option value="en" <?= $loc === 'en' ? 'selected' : ''; ?>>English</option>
                                    <option value="fr" <?= $loc === 'fr' ? 'selected' : ''; ?>>Français</option>
                                    <option value="es" <?= $loc === 'es' ? 'selected' : ''; ?>>Español</option>
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Save General Settings</button>
                    </form>
                </div>

                <!-- Theme Settings -->
                <div id="theme" class="settings-section">
                    <h2>Theme Settings</h2>
                    <form onsubmit="handleSettingsSubmit(event, 'theme')">
                        <div class="form-group">
                            <label>Choose Theme</label>
                            <div class="theme-options">
                                <?php $theme = config('theme.default', 'dark'); ?>
                                <div class="theme-option <?= $theme === 'light' ? 'selected' : ''; ?>" onclick="selectTheme('light', this)">
                                    <h3>☀️ Light</h3>
                                    <p>Bright and clean interface</p>
                                </div>
                                <div class="theme-option <?= $theme === 'dark' ? 'selected' : ''; ?>" onclick="selectTheme('dark', this)">
                                    <h3>🌙 Dark</h3>
                                    <p>Easy on the eyes</p>
                                </div>
                            </div>
                            <input type="hidden" id="selectedTheme" name="theme" value="<?= htmlspecialchars($theme); ?>">
                        </div>
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="user_can_change_theme" <?= config('theme.user_can_change', true) ? 'checked' : ''; ?>>
                                Allow users to change theme
                            </label>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Theme Settings</button>
                    </form>
                </div>

                <!-- SMTP Settings -->
                <div id="smtp" class="settings-section">
                    <h2>Email (SMTP) Configuration</h2>
                    <form onsubmit="handleSettingsSubmit(event, 'smtp')">
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" id="smtpEnabled" name="enabled" <?= config('smtp.enabled', true) ? 'checked' : ''; ?>>
                                Enable SMTP
                            </label>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="smtpHost">SMTP Host</label>
                                <input type="text" id="smtpHost" name="host" value="<?= htmlspecialchars(config('smtp.smtp.host', 'mail-gw.ncedges.com')); ?>">
                            </div>
                            <div class="form-group">
                                <label for="smtpPort">SMTP Port</label>
                                <input type="number" id="smtpPort" name="port" value="<?= htmlspecialchars(config('smtp.smtp.port', 465)); ?>">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="smtpUsername">Username</label>
                                <input type="text" id="smtpUsername" name="username" value="<?= htmlspecialchars(config('smtp.smtp.username', 'webadmin@ncedges.com')); ?>">
                            </div>
                            <div class="form-group">
                                <label for="smtpPassword">Password</label>
                                <input type="password" id="smtpPassword" name="password" placeholder="••••••••">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="smtpEncryption">Encryption</label>
                                <select id="smtpEncryption" name="encryption">
                                    <?php $enc = config('smtp.smtp.encryption', 'ssl'); ?>
                                    <option value="tls" <?= $enc === 'tls' ? 'selected' : ''; ?>>TLS</option>
                                    <option value="ssl" <?= $enc === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                                    <option value="none" <?= $enc === 'none' ? 'selected' : ''; ?>>None</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="smtpTimeout">Timeout (seconds)</label>
                                <input type="number" id="smtpTimeout" name="timeout" value="<?= htmlspecialchars(config('smtp.smtp.timeout', 10)); ?>">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="smtpFromAddress">From Address</label>
                                <input type="email" id="smtpFromAddress" name="from_address" value="<?= htmlspecialchars(config('smtp.from.address', 'webadmin@ncedges.com')); ?>">
                            </div>
                            <div class="form-group">
                                <label for="smtpFromName">From Name</label>
                                <input type="text" id="smtpFromName" name="from_name" value="<?= htmlspecialchars(config('smtp.from.name', 'Monitor System')); ?>">
                            </div>
                        </div>
                        <div class="form-row">
                            <button type="submit" class="btn btn-primary">Save SMTP Settings</button>
                            <button type="button" class="btn btn-secondary test-email-button" onclick="testEmailSettings()">Send Test Email</button>
                        </div>
                    </form>
                </div>

                <!-- Notifications Settings -->
                <div id="notifications" class="settings-section">
                    <h2>Notification Settings</h2>
                    <form onsubmit="handleSettingsSubmit(event, 'notifications')">
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="enabled" <?= config('notifications.enabled', true) ? 'checked' : ''; ?>>
                                Enable Notifications
                            </label>
                        </div>

                        <div class="divider"></div>

                        <h3>Notification Channels</h3>
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="email_enabled" <?= config('notifications.channels.email.enabled', true) ? 'checked' : ''; ?>>
                                Email Notifications
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="sms_enabled" <?= config('notifications.channels.sms.enabled', false) ? 'checked' : ''; ?>>
                                SMS Notifications
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="push_enabled" <?= config('notifications.channels.push.enabled', false) ? 'checked' : ''; ?>>
                                Push Notifications
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="in_app_enabled" <?= config('notifications.channels.in_app.enabled', true) ? 'checked' : ''; ?>>
                                In-App Notifications
                            </label>
                        </div>

                        <div class="divider"></div>

                        <h3>Throttling</h3>
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="throttle_enabled" <?= config('notifications.throttle.enabled', true) ? 'checked' : ''; ?>>
                                Enable Throttling (prevent notification spam)
                            </label>
                        </div>
                        <div class="form-group">
                            <label for="throttleMinutes">Throttle Duration (minutes)</label>
                            <input type="number" id="throttleMinutes" name="throttle_minutes" value="<?= htmlspecialchars(config('notifications.throttle.minutes', 30)); ?>" min="1">
                        </div>

                        <button type="submit" class="btn btn-primary">Save Notification Settings</button>
                    </form>
                </div>

                <!-- Monitoring Settings -->
                <div id="monitoring" class="settings-section">
                    <h2>Monitoring Configuration</h2>
                    <form onsubmit="handleSettingsSubmit(event, 'monitoring')">
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="enabled" <?= config('monitoring.health_check.enabled', true) ? 'checked' : ''; ?>>
                                Enable Monitoring
                            </label>
                        </div>

                        <div class="divider"></div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="refreshInterval">Refresh Interval (seconds)</label>
                                <input type="number" id="refreshInterval" name="refresh_interval" value="<?= htmlspecialchars(config('monitoring.refresh.interval', 30)); ?>" min="5">
                            </div>
                            <div class="form-group">
                                <label for="healthCheckTimeout">Health Check Timeout (seconds)</label>
                                <input type="number" id="healthCheckTimeout" name="health_check_timeout" value="<?= htmlspecialchars(config('monitoring.health_check.timeout', 3)); ?>" min="1">
                            </div>
                        </div>

                        <div class="divider"></div>

                        <h3>Alert Thresholds</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="alertAfter">Alert After (seconds)</label>
                                <input type="number" id="alertAfter" name="alert_after_seconds" value="<?= htmlspecialchars(config('monitoring.thresholds.alert_after_seconds', 300)); ?>" min="30">
                            </div>
                            <div class="form-group">
                                <label for="criticalAfter">Critical After (seconds)</label>
                                <input type="number" id="criticalAfter" name="critical_after_seconds" value="<?= htmlspecialchars(config('monitoring.thresholds.critical_after_seconds', 900)); ?>" min="60">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Save Monitoring Settings</button>
                    </form>
                </div>

                <!-- Security Settings -->
                <div id="security" class="settings-section">
                    <h2>Security Settings</h2>
                    <form onsubmit="handleSettingsSubmit(event, 'security')">
                        <h3>Session Management</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="sessionTimeout">Session Timeout (minutes)</label>
                                <input type="number" id="sessionTimeout" name="session_timeout" value="<?= htmlspecialchars(config('security.auth.session_timeout', 3600) / 60); ?>" min="5">
                            </div>
                        </div>

                        <div class="divider"></div>

                        <h3>Password Policy</h3>
                        <div class="form-group">
                            <label for="passwordMinLength">Minimum Password Length</label>
                            <input type="number" id="passwordMinLength" name="password_min_length" value="<?= htmlspecialchars(config('security.password.min_length', 8)); ?>" min="6">
                        </div>
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="password_require_uppercase" <?= config('security.password.require_uppercase', true) ? 'checked' : ''; ?>>
                                Require Uppercase Letters
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="password_require_numbers" <?= config('security.password.require_numbers', true) ? 'checked' : ''; ?>>
                                Require Numbers
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="password_require_special" <?= config('security.password.require_special', true) ? 'checked' : ''; ?>>
                                Require Special Characters
                            </label>
                        </div>

                        <div class="divider"></div>

                        <h3>Features</h3>
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="audit_logging_enabled" <?= config('app.features.audit_logging_enabled', true) ? 'checked' : ''; ?>>
                                Enable Audit Logging
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="activity_timeline_enabled" <?= config('app.features.activity_timeline_enabled', true) ? 'checked' : ''; ?>>
                                Enable Activity Timeline
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary">Save Security Settings</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function switchTab(tabName) {
            // Hide all sections
            document.querySelectorAll('.settings-section').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.sidebar-item').forEach(el => el.classList.remove('active'));

            // Show selected section
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
        }

        function selectTheme(theme, element) {
            document.querySelectorAll('.theme-option').forEach(el => el.classList.remove('selected'));
            element.classList.add('selected');
            document.getElementById('selectedTheme').value = theme;
        }

        function handleSettingsSubmit(event, section) {
            event.preventDefault();

            const form = event.target;
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);

            fetch(`/api/settings/update.php?section=${section}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message || 'Settings saved successfully', 'success');
                } else {
                    showAlert(data.message || 'Failed to save settings', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Failed to save settings', 'error');
            });
        }

        function testEmailSettings() {
            fetch('/api/settings/test-email.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    to: '<?php echo $_SESSION['user_email'] ?? 'admin@example.com'; ?>'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message || 'Test email sent successfully', 'success');
                } else {
                    showAlert(data.message || 'Failed to send test email', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Failed to send test email', 'error');
            });
        }

        function showAlert(message, type) {
            const alert = document.getElementById('alert');
            alert.textContent = message;
            alert.className = `alert alert-${type}`;
            setTimeout(() => {
                alert.style.display = 'none';
            }, 5000);
        }

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
