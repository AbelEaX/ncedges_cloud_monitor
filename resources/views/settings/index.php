<?php
/**
 * Settings Management View
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Monitor</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f5f5f5;
            color: #333;
            padding: 20px;
        }
        .container { max-width: 1200px; margin: 0 auto; }
        header {
            margin-bottom: 30px;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        h1 { font-size: 28px; }
        .content {
            display: grid;
            grid-template-columns: 200px 1fr;
            gap: 20px;
        }
        .sidebar {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            height: fit-content;
        }
        .sidebar-item {
            padding: 12px 16px;
            cursor: pointer;
            border-left: 3px solid transparent;
            margin-bottom: 5px;
            border-radius: 4px;
            transition: all 0.3s ease;
        }
        .sidebar-item:hover {
            background: #f3f4f6;
        }
        .sidebar-item.active {
            background: #eff6ff;
            border-left-color: #3b82f6;
            font-weight: 600;
            color: #3b82f6;
        }
        .settings-section {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            display: none;
        }
        .settings-section.active {
            display: block;
        }
        h2 {
            font-size: 20px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e5e7eb;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        input[type="text"],
        input[type="email"],
        input[type="number"],
        input[type="password"],
        select,
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        input[type="checkbox"] {
            margin-right: 8px;
        }
        .checkbox-label {
            display: flex;
            align-items: center;
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
            background: #3b82f6;
            color: white;
        }
        .btn-primary:hover {
            background: #2563eb;
        }
        .btn-secondary {
            background: #6b7280;
            color: white;
        }
        .btn-secondary:hover {
            background: #4b5563;
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
            padding: 20px;
            border: 2px solid #ddd;
            border-radius: 8px;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s ease;
        }
        .theme-option.selected {
            border-color: #3b82f6;
            background: #eff6ff;
        }
        .theme-option h3 {
            margin-bottom: 10px;
        }
        .test-email-button {
            margin-top: 10px;
        }
        .divider {
            border-top: 1px solid #e5e7eb;
            margin: 30px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Settings</h1>
            <p style="color: #6b7280; margin-top: 5px;">Configure application settings and preferences</p>
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
                                <input type="text" id="appName" name="app_name" value="Monitor System">
                            </div>
                            <div class="form-group">
                                <label for="appUrl">Application URL</label>
                                <input type="text" id="appUrl" name="app_url" value="http://localhost">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="timezone">Timezone</label>
                                <select id="timezone" name="timezone">
                                    <option value="UTC">UTC</option>
                                    <option value="Africa/Kampala" selected>Africa/Kampala</option>
                                    <option value="America/New_York">America/New_York</option>
                                    <option value="Europe/London">Europe/London</option>
                                    <option value="Asia/Tokyo">Asia/Tokyo</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="locale">Locale</label>
                                <select id="locale" name="locale">
                                    <option value="en" selected>English</option>
                                    <option value="fr">Français</option>
                                    <option value="es">Español</option>
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
                                <div class="theme-option selected" onclick="selectTheme('light', this)">
                                    <h3>☀️ Light</h3>
                                    <p>Bright and clean interface</p>
                                </div>
                                <div class="theme-option" onclick="selectTheme('dark', this)">
                                    <h3>🌙 Dark</h3>
                                    <p>Easy on the eyes</p>
                                </div>
                            </div>
                            <input type="hidden" id="selectedTheme" name="theme" value="light">
                        </div>
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="user_can_change_theme" checked>
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
                                <input type="checkbox" id="smtpEnabled" name="enabled" checked>
                                Enable SMTP
                            </label>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="smtpHost">SMTP Host</label>
                                <input type="text" id="smtpHost" name="host" value="mail-gw.ncedges.com">
                            </div>
                            <div class="form-group">
                                <label for="smtpPort">SMTP Port</label>
                                <input type="number" id="smtpPort" name="port" value="465">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="smtpUsername">Username</label>
                                <input type="text" id="smtpUsername" name="username" value="webadmin@ncedges.com">
                            </div>
                            <div class="form-group">
                                <label for="smtpPassword">Password</label>
                                <input type="password" id="smtpPassword" name="password">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="smtpEncryption">Encryption</label>
                                <select id="smtpEncryption" name="encryption">
                                    <option value="tls">TLS</option>
                                    <option value="ssl" selected>SSL</option>
                                    <option value="none">None</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="smtpTimeout">Timeout (seconds)</label>
                                <input type="number" id="smtpTimeout" name="timeout" value="10">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="smtpFromAddress">From Address</label>
                                <input type="email" id="smtpFromAddress" name="from_address" value="webadmin@ncedges.com">
                            </div>
                            <div class="form-group">
                                <label for="smtpFromName">From Name</label>
                                <input type="text" id="smtpFromName" name="from_name" value="Monitor System">
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
                                <input type="checkbox" name="enabled" checked>
                                Enable Notifications
                            </label>
                        </div>

                        <div class="divider"></div>

                        <h3>Notification Channels</h3>
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="email_enabled" checked>
                                Email Notifications
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="sms_enabled">
                                SMS Notifications
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="push_enabled">
                                Push Notifications
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="in_app_enabled" checked>
                                In-App Notifications
                            </label>
                        </div>

                        <div class="divider"></div>

                        <h3>Throttling</h3>
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="throttle_enabled" checked>
                                Enable Throttling (prevent notification spam)
                            </label>
                        </div>
                        <div class="form-group">
                            <label for="throttleMinutes">Throttle Duration (minutes)</label>
                            <input type="number" id="throttleMinutes" name="throttle_minutes" value="30" min="1">
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
                                <input type="checkbox" name="enabled" checked>
                                Enable Monitoring
                            </label>
                        </div>

                        <div class="divider"></div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="refreshInterval">Refresh Interval (seconds)</label>
                                <input type="number" id="refreshInterval" name="refresh_interval" value="30" min="5">
                            </div>
                            <div class="form-group">
                                <label for="healthCheckTimeout">Health Check Timeout (seconds)</label>
                                <input type="number" id="healthCheckTimeout" name="health_check_timeout" value="3" min="1">
                            </div>
                        </div>

                        <div class="divider"></div>

                        <h3>Alert Thresholds</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="alertAfter">Alert After (seconds)</label>
                                <input type="number" id="alertAfter" name="alert_after_seconds" value="300" min="30">
                            </div>
                            <div class="form-group">
                                <label for="criticalAfter">Critical After (seconds)</label>
                                <input type="number" id="criticalAfter" name="critical_after_seconds" value="900" min="60">
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
                                <input type="number" id="sessionTimeout" name="session_timeout" value="60" min="5">
                            </div>
                        </div>

                        <div class="divider"></div>

                        <h3>Password Policy</h3>
                        <div class="form-group">
                            <label for="passwordMinLength">Minimum Password Length</label>
                            <input type="number" id="passwordMinLength" name="password_min_length" value="8" min="6">
                        </div>
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="password_require_uppercase" checked>
                                Require Uppercase Letters
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="password_require_numbers" checked>
                                Require Numbers
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="password_require_special" checked>
                                Require Special Characters
                            </label>
                        </div>

                        <div class="divider"></div>

                        <h3>Features</h3>
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="audit_logging_enabled" checked>
                                Enable Audit Logging
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="activity_timeline_enabled" checked>
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
    </script>
</body>
</html>
