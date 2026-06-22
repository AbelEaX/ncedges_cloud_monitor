<?php
/**
 * @var array $servers List of servers
 * @var \App\Infrastructure\Authentication\AuthenticationService $auth
 * @var \App\Infrastructure\Logging\Logger $logger
 */
$themeService = app(\App\Infrastructure\Logging\ThemeService::class);
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(config('app.locale', 'en')); ?>" data-theme="<?= htmlspecialchars($themeService->getCurrentTheme()); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Management - <?= htmlspecialchars(config('app.name', 'Monitor')); ?></title>
    <?= $themeService->getStyleTag(); ?>
    <style>
        :root {
            --bg-color: var(--background, #f5f5f5);
            --surface-color: var(--surface, #ffffff);
            --border-color: var(--border, #e0e0e0);
            --text-color: var(--text, #333333);
            --primary-color: var(--primary, #3b82f6);
            --success-color: var(--success, #10b981);
            --danger-color: var(--danger, #ef4444);
            --warning-color: var(--warning, #f59e0b);
            --info-color: var(--info, #3b82f6);
            --muted-color: var(--muted, #6b7280);
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
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background: var(--surface-color);
            padding: 24px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }
        h1 { font-size: 28px; }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
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
            padding: 6px 12px;
            font-size: 13px;
        }
        .btn-secondary:hover {
            opacity: 0.9;
        }
        .btn-danger {
            background: var(--danger-color);
            color: white;
            padding: 6px 12px;
            font-size: 13px;
        }
        .btn-danger:hover {
            opacity: 0.9;
        }
        .table {
            width: 100%;
            background: var(--surface-color);
            border-collapse: collapse;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid var(--border-color);
        }
        .table th {
            background: var(--bg-color);
            padding: 15px;
            text-align: left;
            font-weight: 600;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-color);
        }
        .table td {
            padding: 15px;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-color);
        }
        .table tr:hover {
            background: rgba(255, 255, 255, 0.02);
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-online {
            background: rgba(16, 185, 129, 0.15);
            color: var(--success-color);
        }
        .status-offline {
            background: rgba(239, 68, 68, 0.15);
            color: var(--danger-color);
        }
        .actions {
            display: flex;
            gap: 8px;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: var(--surface-color);
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }
        .empty-state h2 {
            color: var(--muted-color);
            margin-bottom: 10px;
        }
        .empty-state p {
            color: var(--muted-color);
            margin-bottom: 20px;
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
        .modal-content {
            background: var(--surface-color);
            color: var(--text-color);
            border: 1px solid var(--border-color);
        }
        .form-input {
            width: 100%;
            padding: 8px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            margin-top: 5px;
            background: var(--bg-color);
            color: var(--text-color);
        }
        .form-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
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
        <header>
            <div style="display: flex; align-items: center; gap: 15px;">
                <div>
                    <h1>Server Management</h1>
                    <p style="color: var(--muted-color); margin-top: 5px;">Manage your monitored servers</p>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 15px;">
                <?php if ($auth->hasPermission('server.create')): ?>
                <button class="btn btn-primary" onclick="openCreateForm()">Add New Server</button>
                <?php endif; ?>
            </div>
        </header>

        <div id="alert" class="alert"></div>

        <div id="servers-list">
            <!-- Servers will be loaded here -->
            <div style="text-align: center; padding: 40px;">
                <p>Loading servers...</p>
            </div>
        </div>
    </div>

    <!-- Modal for Create/Edit -->
    <div id="serverModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 1000;">
        <div class="modal-content" style="width: 90%; max-width: 500px; margin: 50px auto; padding: 24px; border-radius: 8px;">
            <h2 id="modalTitle" style="margin-bottom: 20px;">Add New Server</h2>
            <form id="serverForm" onsubmit="handleSubmit(event)">
                <input type="hidden" id="serverId">

                <div style="margin-bottom: 15px;">
                    <label for="serverName">Server Name *</label>
                    <input type="text" id="serverName" name="name" required class="form-input">
                </div>

                <div style="margin-bottom: 15px;">
                    <label for="serverHost">Host / IP Address *</label>
                    <input type="text" id="serverHost" name="host" required class="form-input">
                </div>

                <div style="margin-bottom: 15px;">
                    <label for="serverPort">Port *</label>
                    <input type="number" id="serverPort" name="port" required value="443" class="form-input">
                </div>

                <div style="margin-bottom: 15px;">
                    <label for="serverDescription">Description</label>
                    <textarea id="serverDescription" name="description" rows="4" class="form-input"></textarea>
                </div>

                <div style="margin-bottom: 20px;">
                    <label for="serverActive" class="checkbox-label">
                        <input type="checkbox" id="serverActive" name="is_active" checked>
                        Active
                    </label>
                </div>

                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Server</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const canCreateServer = <?= $auth->hasPermission('server.create') ? 'true' : 'false' ?>;
        const canEditServer = <?= $auth->hasPermission('server.edit') ? 'true' : 'false' ?>;
        const canDeleteServer = <?= $auth->hasPermission('server.delete') ? 'true' : 'false' ?>;

        // Load servers on page load
        document.addEventListener('DOMContentLoaded', loadServers);

        function loadServers() {
            fetch('/api/servers/list.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayServers(data.data || []);
                    } else {
                        showAlert(data.message || 'Failed to load servers', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('Failed to load servers', 'error');
                });
        }

        function displayServers(servers) {
            const container = document.getElementById('servers-list');

            if (servers.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <h2>No Servers Yet</h2>
                        <p>Start by adding your first server to monitor</p>
                        ${canCreateServer ? '<button class="btn btn-primary" onclick="openCreateForm()">Add Server</button>' : ''}
                    </div>
                `;
                return;
            }

            let html = `
                <table class="table">
                    <thead>
                        <tr>
                            <th>Server Name</th>
                            <th>Host / IP Address</th>
                            <th>Port</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            servers.forEach(server => {
                const isMonitoring = server.is_active ? true : false;
                
                // Real-time health status
                let status = 'unknown';
                let statusText = 'Unknown';
                let badgeClass = 'status-offline';
                
                if (!isMonitoring) {
                    statusText = 'Monitoring Disabled';
                    badgeClass = 'status-offline';
                } else if (server.status === 'online') {
                    status = 'online';
                    statusText = 'Online';
                    badgeClass = 'status-online';
                } else if (server.status === 'offline') {
                    status = 'offline';
                    statusText = 'Offline';
                    badgeClass = 'status-offline'; // Assuming red badge
                } else {
                    statusText = 'Pending Check';
                }

                const created = new Date(server.created_at).toLocaleDateString();

                let actionButtons = '';
                if (canEditServer) {
                    actionButtons += `<button class="btn btn-secondary" onclick="editServer(${server.id})">Edit</button>`;
                }
                if (canDeleteServer) {
                    actionButtons += ` <button class="btn btn-danger" onclick="deleteServer(${server.id})">Delete</button>`;
                }

                html += `
                    <tr>
                        <td><strong>${server.name}</strong></td>
                        <td>${server.host}</td>
                        <td>${server.port || '443'}</td>
                        <td><span class="status-badge ${badgeClass}">${statusText}</span></td>
                        <td>${created}</td>
                        <td>
                            <div class="actions">
                                ${actionButtons}
                            </div>
                        </td>
                    </tr>
                `;
            });

            html += `
                    </tbody>
                </table>
            `;

            container.innerHTML = html;
        }

        function openCreateForm() {
            document.getElementById('modalTitle').textContent = 'Add New Server';
            document.getElementById('serverId').value = '';
            document.getElementById('serverForm').reset();
            document.getElementById('serverModal').style.display = 'block';
        }

        function editServer(id) {
            fetch(`/api/servers/get.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const server = data.data;
                        document.getElementById('modalTitle').textContent = 'Edit Server';
                        document.getElementById('serverId').value = server.id;
                        document.getElementById('serverName').value = server.name;
                        document.getElementById('serverHost').value = server.host;
                        document.getElementById('serverPort').value = server.port || 443;
                        document.getElementById('serverDescription').value = server.description || '';
                        document.getElementById('serverActive').checked = server.is_active;
                        document.getElementById('serverModal').style.display = 'block';
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        function closeModal() {
            document.getElementById('serverModal').style.display = 'none';
        }

        function handleSubmit(event) {
            event.preventDefault();

            const serverId = document.getElementById('serverId').value;
            const formData = new FormData(document.getElementById('serverForm'));
            const data = Object.fromEntries(formData);
            data.is_active = document.getElementById('serverActive').checked ? 1 : 0;

            const endpoint = serverId ? `/api/servers/update.php?id=${serverId}` : '/api/servers/create.php';
            const method = serverId ? 'PUT' : 'POST';

            fetch(endpoint, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message || 'Server saved successfully', 'success');
                    closeModal();
                    loadServers();
                } else {
                    showAlert(data.message || 'Failed to save server', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Failed to save server', 'error');
            });
        }

        function deleteServer(id) {
            if (!confirm('Are you sure you want to delete this server?')) return;

            fetch(`/api/servers/delete.php?id=${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message || 'Server deleted successfully', 'success');
                    loadServers();
                } else {
                    showAlert(data.message || 'Failed to delete server', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Failed to delete server', 'error');
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

        // Close modal when clicking outside
        document.getElementById('serverModal').addEventListener('click', function(event) {
            if (event.target === this) {
                closeModal();
            }
        });
        
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
