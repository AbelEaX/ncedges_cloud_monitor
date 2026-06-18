<?php
/**
 * @var array $servers List of servers
 * @var \App\Infrastructure\Authentication\AuthenticationService $auth
 * @var \App\Infrastructure\Logging\Logger $logger
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Management - Monitor</title>
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
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
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
            padding: 6px 12px;
            font-size: 13px;
        }
        .btn-secondary:hover {
            background: #4b5563;
        }
        .btn-danger {
            background: #ef4444;
            color: white;
            padding: 6px 12px;
            font-size: 13px;
        }
        .btn-danger:hover {
            background: #dc2626;
        }
        .table {
            width: 100%;
            background: white;
            border-collapse: collapse;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .table th {
            background: #f3f4f6;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            border-bottom: 1px solid #e5e7eb;
        }
        .table td {
            padding: 15px;
            border-bottom: 1px solid #e5e7eb;
        }
        .table tr:hover {
            background: #f9fafb;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-online {
            background: #d1fae5;
            color: #065f46;
        }
        .status-offline {
            background: #fee2e2;
            color: #7f1d1d;
        }
        .actions {
            display: flex;
            gap: 8px;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 8px;
        }
        .empty-state h2 {
            color: #6b7280;
            margin-bottom: 10px;
        }
        .empty-state p {
            color: #9ca3af;
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
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Server Management</h1>
            <button class="btn btn-primary" onclick="openCreateForm()">Add New Server</button>
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
    <div id="serverModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
        <div style="background: white; width: 90%; max-width: 500px; margin: 50px auto; padding: 30px; border-radius: 8px;">
            <h2 id="modalTitle">Add New Server</h2>
            <form id="serverForm" onsubmit="handleSubmit(event)">
                <input type="hidden" id="serverId">

                <div style="margin-bottom: 15px;">
                    <label for="serverName">Server Name *</label>
                    <input type="text" id="serverName" name="name" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; margin-top: 5px;">
                </div>

                <div style="margin-bottom: 15px;">
                    <label for="serverHostname">Hostname *</label>
                    <input type="text" id="serverHostname" name="hostname" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; margin-top: 5px;">
                </div>

                <div style="margin-bottom: 15px;">
                    <label for="serverIp">IP Address</label>
                    <input type="text" id="serverIp" name="ip_address" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; margin-top: 5px;">
                </div>

                <div style="margin-bottom: 15px;">
                    <label for="serverDescription">Description</label>
                    <textarea id="serverDescription" name="description" rows="4" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; margin-top: 5px;"></textarea>
                </div>

                <div style="margin-bottom: 20px;">
                    <label for="serverActive">
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
                        <button class="btn btn-primary" onclick="openCreateForm()">Add Server</button>
                    </div>
                `;
                return;
            }

            let html = `
                <table class="table">
                    <thead>
                        <tr>
                            <th>Server Name</th>
                            <th>Hostname</th>
                            <th>IP Address</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            servers.forEach(server => {
                const status = server.is_active ? 'online' : 'offline';
                const statusText = server.is_active ? 'Active' : 'Inactive';
                const created = new Date(server.created_at).toLocaleDateString();

                html += `
                    <tr>
                        <td><strong>${server.name}</strong></td>
                        <td>${server.hostname}</td>
                        <td>${server.ip_address || '-'}</td>
                        <td><span class="status-badge status-${status}">${statusText}</span></td>
                        <td>${created}</td>
                        <td>
                            <div class="actions">
                                <button class="btn btn-secondary" onclick="editServer(${server.id})">Edit</button>
                                <button class="btn btn-danger" onclick="deleteServer(${server.id})">Delete</button>
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
                        document.getElementById('serverHostname').value = server.hostname;
                        document.getElementById('serverIp').value = server.ip_address || '';
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
                method: 'DELETE'
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
    </script>
</body>
</html>
