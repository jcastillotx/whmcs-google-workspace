<div class="googleworkspace-panel">
    <style>
        .googleworkspace-panel {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        .gw-card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
            margin-bottom: 20px;
            overflow: hidden;
        }
        .gw-card-header {
            background: linear-gradient(135deg, #4285f4 0%, #34a853 100%);
            color: #fff;
            padding: 15px 20px;
            font-size: 16px;
            font-weight: 500;
        }
        .gw-card-body {
            padding: 20px;
        }
        .gw-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        .gw-info-item {
            padding: 10px;
            background: #f8f9fa;
            border-radius: 4px;
        }
        .gw-info-label {
            font-size: 12px;
            color: #6c757d;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .gw-info-value {
            font-size: 14px;
            color: #212529;
            font-weight: 500;
        }
        .gw-status-active { color: #28a745; }
        .gw-status-suspended { color: #ffc107; }
        .gw-status-pending { color: #17a2b8; }
        .gw-status-terminated { color: #dc3545; }
        .gw-btn {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: background-color 0.2s;
        }
        .gw-btn-primary {
            background: #4285f4;
            color: #fff;
        }
        .gw-btn-primary:hover {
            background: #3367d6;
        }
        .gw-btn-success {
            background: #34a853;
            color: #fff;
        }
        .gw-btn-success:hover {
            background: #2d9148;
        }
        .gw-btn-danger {
            background: #ea4335;
            color: #fff;
        }
        .gw-btn-danger:hover {
            background: #d33426;
        }
        .gw-btn-sm {
            padding: 4px 8px;
            font-size: 12px;
        }
        .gw-table {
            width: 100%;
            border-collapse: collapse;
        }
        .gw-table th,
        .gw-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }
        .gw-table th {
            background: #f8f9fa;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            color: #6c757d;
        }
        .gw-table tbody tr:hover {
            background: #f8f9fa;
        }
        .gw-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
        }
        .gw-modal-content {
            background: #fff;
            margin: 10% auto;
            padding: 0;
            width: 90%;
            max-width: 500px;
            border-radius: 8px;
            overflow: hidden;
        }
        .gw-modal-header {
            padding: 15px 20px;
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .gw-modal-header h3 {
            margin: 0;
            font-size: 16px;
        }
        .gw-modal-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #6c757d;
        }
        .gw-modal-body {
            padding: 20px;
        }
        .gw-form-group {
            margin-bottom: 15px;
        }
        .gw-form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            font-size: 14px;
        }
        .gw-form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }
        .gw-form-control:focus {
            outline: none;
            border-color: #4285f4;
            box-shadow: 0 0 0 3px rgba(66,133,244,0.15);
        }
        .gw-alert {
            padding: 12px 15px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .gw-alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .gw-alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .gw-alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        .gw-loading {
            display: none;
            text-align: center;
            padding: 20px;
        }
        .gw-loading.active {
            display: block;
        }
        .gw-spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #4285f4;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: gw-spin 1s linear infinite;
            margin: 0 auto;
        }
        @keyframes gw-spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .gw-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .gw-quick-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 15px;
        }
    </style>

    {* Alert Messages *}
    <div id="gw-alerts"></div>

    {* Subscription Information *}
    <div class="gw-card">
        <div class="gw-card-header">
            <i class="fab fa-google"></i> Google Workspace Subscription
        </div>
        <div class="gw-card-body">
            <div class="gw-info-grid">
                <div class="gw-info-item">
                    <div class="gw-info-label">Domain</div>
                    <div class="gw-info-value">{$serviceData.customer_domain|default:'Not configured'}</div>
                </div>
                <div class="gw-info-item">
                    <div class="gw-info-label">Plan</div>
                    <div class="gw-info-value">
                        {if isset($skuNames[$serviceData.sku_id])}
                            {$skuNames[$serviceData.sku_id]}
                        {else}
                            {$serviceData.sku_id|default:'N/A'}
                        {/if}
                    </div>
                </div>
                <div class="gw-info-item">
                    <div class="gw-info-label">Billing Type</div>
                    <div class="gw-info-value">
                        {if isset($planNames[$serviceData.plan_type])}
                            {$planNames[$serviceData.plan_type]}
                        {else}
                            {$serviceData.plan_type|default:'N/A'}
                        {/if}
                    </div>
                </div>
                <div class="gw-info-item">
                    <div class="gw-info-label">Licenses</div>
                    <div class="gw-info-value">{$serviceData.licenses|default:'0'}</div>
                </div>
                <div class="gw-info-item">
                    <div class="gw-info-label">Status</div>
                    <div class="gw-info-value gw-status-{$serviceData.status|lower|default:'pending'}">
                        {$serviceData.status|ucfirst|default:'Pending'}
                    </div>
                </div>
                <div class="gw-info-item">
                    <div class="gw-info-label">Admin Email</div>
                    <div class="gw-info-value">{$serviceData.admin_email|default:'N/A'}</div>
                </div>
            </div>

            <div class="gw-quick-actions">
                <a href="https://admin.google.com/{$serviceData.customer_domain}" target="_blank" class="gw-btn gw-btn-primary">
                    <i class="fas fa-external-link-alt"></i> Google Admin Console
                </a>
                <a href="https://mail.google.com/a/{$serviceData.customer_domain}" target="_blank" class="gw-btn gw-btn-success">
                    <i class="fas fa-envelope"></i> Gmail
                </a>
                <a href="https://drive.google.com/a/{$serviceData.customer_domain}" target="_blank" class="gw-btn gw-btn-primary">
                    <i class="fab fa-google-drive"></i> Drive
                </a>
            </div>
        </div>
    </div>

    {* Admin Credentials *}
    {if $serviceData.admin_email}
    <div class="gw-card">
        <div class="gw-card-header">
            <i class="fas fa-user-shield"></i> Admin Credentials
        </div>
        <div class="gw-card-body">
            <div class="gw-info-grid">
                <div class="gw-info-item">
                    <div class="gw-info-label">Admin Username</div>
                    <div class="gw-info-value">{$serviceData.admin_email}</div>
                </div>
                <div class="gw-info-item">
                    <div class="gw-info-label">Admin Password</div>
                    <div class="gw-info-value">
                        {if $showPassword}
                            <span id="admin-password">{$adminPassword}</span>
                            <button type="button" class="gw-btn gw-btn-sm gw-btn-primary" onclick="copyPassword()">
                                <i class="fas fa-copy"></i>
                            </button>
                        {else}
                            ********
                        {/if}
                    </div>
                </div>
            </div>
        </div>
    </div>
    {/if}

    {* User Management *}
    <div class="gw-card">
        <div class="gw-card-header">
            <i class="fas fa-users"></i> User Management
        </div>
        <div class="gw-card-body">
            <div class="gw-toolbar">
                <button type="button" class="gw-btn gw-btn-success" onclick="openAddUserModal()">
                    <i class="fas fa-plus"></i> Add User
                </button>
                <button type="button" class="gw-btn gw-btn-primary" onclick="loadUsers()">
                    <i class="fas fa-sync"></i> Refresh
                </button>
            </div>

            <div class="gw-loading" id="users-loading">
                <div class="gw-spinner"></div>
                <p>Loading users...</p>
            </div>

            <div id="users-table-container">
                <table class="gw-table">
                    <thead>
                        <tr>
                            <th>Email</th>
                            <th>Name</th>
                            <th>Admin</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="users-list">
                        {if $users}
                            {foreach $users as $user}
                            <tr>
                                <td>{$user.primaryEmail}</td>
                                <td>{$user.name.fullName|default:"{$user.name.givenName} {$user.name.familyName}"}</td>
                                <td>
                                    {if $user.isAdmin}
                                        <span class="gw-status-active"><i class="fas fa-check"></i> Yes</span>
                                    {else}
                                        No
                                    {/if}
                                </td>
                                <td>
                                    {if $user.suspended}
                                        <span class="gw-status-suspended">Suspended</span>
                                    {else}
                                        <span class="gw-status-active">Active</span>
                                    {/if}
                                </td>
                                <td>
                                    <button type="button" class="gw-btn gw-btn-sm gw-btn-primary" onclick="resetUserPassword('{$user.primaryEmail}')">
                                        <i class="fas fa-key"></i>
                                    </button>
                                    {if !$user.isAdmin}
                                    <button type="button" class="gw-btn gw-btn-sm gw-btn-danger" onclick="deleteUser('{$user.primaryEmail}')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    {/if}
                                </td>
                            </tr>
                            {/foreach}
                        {else}
                            <tr>
                                <td colspan="5" style="text-align: center; color: #6c757d;">
                                    No users found. Click "Add User" to create one.
                                </td>
                            </tr>
                        {/if}
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {* Add User Modal *}
    <div id="addUserModal" class="gw-modal">
        <div class="gw-modal-content">
            <div class="gw-modal-header">
                <h3>Add New User</h3>
                <button type="button" class="gw-modal-close" onclick="closeModal('addUserModal')">&times;</button>
            </div>
            <div class="gw-modal-body">
                <form id="addUserForm" onsubmit="addUser(event)">
                    <div class="gw-form-group">
                        <label for="userEmail">Email Address</label>
                        <div style="display: flex;">
                            <input type="text" id="userEmail" class="gw-form-control" required style="border-radius: 4px 0 0 4px;">
                            <span style="padding: 10px; background: #e9ecef; border: 1px solid #ced4da; border-left: 0; border-radius: 0 4px 4px 0;">@{$serviceData.customer_domain}</span>
                        </div>
                    </div>
                    <div class="gw-form-group">
                        <label for="userFirstName">First Name</label>
                        <input type="text" id="userFirstName" class="gw-form-control" required>
                    </div>
                    <div class="gw-form-group">
                        <label for="userLastName">Last Name</label>
                        <input type="text" id="userLastName" class="gw-form-control" required>
                    </div>
                    <div class="gw-form-group">
                        <label for="userPassword">Password (leave empty to auto-generate)</label>
                        <input type="password" id="userPassword" class="gw-form-control" placeholder="Auto-generated if empty">
                    </div>
                    <button type="submit" class="gw-btn gw-btn-success">
                        <i class="fas fa-plus"></i> Create User
                    </button>
                </form>
            </div>
        </div>
    </div>

    {* Reset Password Modal *}
    <div id="resetPasswordModal" class="gw-modal">
        <div class="gw-modal-content">
            <div class="gw-modal-header">
                <h3>Reset Password</h3>
                <button type="button" class="gw-modal-close" onclick="closeModal('resetPasswordModal')">&times;</button>
            </div>
            <div class="gw-modal-body">
                <form id="resetPasswordForm" onsubmit="submitResetPassword(event)">
                    <input type="hidden" id="resetPasswordEmail">
                    <div class="gw-form-group">
                        <label>Email: <span id="resetPasswordEmailDisplay"></span></label>
                    </div>
                    <div class="gw-form-group">
                        <label for="newPassword">New Password (leave empty to auto-generate)</label>
                        <input type="password" id="newPassword" class="gw-form-control" placeholder="Auto-generated if empty">
                    </div>
                    <button type="submit" class="gw-btn gw-btn-primary">
                        <i class="fas fa-key"></i> Reset Password
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        var serviceId = {$serviceid};
        var customerDomain = '{$serviceData.customer_domain}';

        function showAlert(message, type) {
            var alertHtml = '<div class="gw-alert gw-alert-' + type + '">' + message + '</div>';
            document.getElementById('gw-alerts').innerHTML = alertHtml;
            setTimeout(function() {
                document.getElementById('gw-alerts').innerHTML = '';
            }, 5000);
        }

        function openAddUserModal() {
            document.getElementById('addUserModal').style.display = 'block';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function loadUsers() {
            document.getElementById('users-loading').classList.add('active');

            fetch('clientarea.php?action=productdetails&id=' + serviceId + '&modop=custom&a=getUsers', {
                method: 'GET',
                credentials: 'same-origin'
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                document.getElementById('users-loading').classList.remove('active');
                if (data.success && data.users) {
                    renderUsersTable(data.users);
                }
            })
            .catch(function(error) {
                document.getElementById('users-loading').classList.remove('active');
                showAlert('Failed to load users', 'danger');
            });
        }

        function renderUsersTable(users) {
            var tbody = document.getElementById('users-list');
            var html = '';

            if (users.length === 0) {
                html = '<tr><td colspan="5" style="text-align: center; color: #6c757d;">No users found.</td></tr>';
            } else {
                users.forEach(function(user) {
                    html += '<tr>';
                    html += '<td>' + user.primaryEmail + '</td>';
                    html += '<td>' + (user.name.fullName || user.name.givenName + ' ' + user.name.familyName) + '</td>';
                    html += '<td>' + (user.isAdmin ? '<span class="gw-status-active"><i class="fas fa-check"></i> Yes</span>' : 'No') + '</td>';
                    html += '<td>' + (user.suspended ? '<span class="gw-status-suspended">Suspended</span>' : '<span class="gw-status-active">Active</span>') + '</td>';
                    html += '<td>';
                    html += '<button type="button" class="gw-btn gw-btn-sm gw-btn-primary" onclick="resetUserPassword(\'' + user.primaryEmail + '\')"><i class="fas fa-key"></i></button> ';
                    if (!user.isAdmin) {
                        html += '<button type="button" class="gw-btn gw-btn-sm gw-btn-danger" onclick="deleteUser(\'' + user.primaryEmail + '\')"><i class="fas fa-trash"></i></button>';
                    }
                    html += '</td>';
                    html += '</tr>';
                });
            }

            tbody.innerHTML = html;
        }

        function addUser(event) {
            event.preventDefault();

            var email = document.getElementById('userEmail').value + '@' + customerDomain;
            var firstName = document.getElementById('userFirstName').value;
            var lastName = document.getElementById('userLastName').value;
            var password = document.getElementById('userPassword').value;

            var formData = new FormData();
            formData.append('email', email);
            formData.append('firstName', firstName);
            formData.append('lastName', lastName);
            formData.append('password', password);

            fetch('clientarea.php?action=productdetails&id=' + serviceId + '&modop=custom&a=addUser', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success) {
                    showAlert('User created successfully', 'success');
                    closeModal('addUserModal');
                    document.getElementById('addUserForm').reset();
                    loadUsers();
                } else {
                    showAlert('Failed to create user', 'danger');
                }
            })
            .catch(function(error) {
                showAlert('Error creating user', 'danger');
            });
        }

        function deleteUser(email) {
            if (!confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
                return;
            }

            var formData = new FormData();
            formData.append('email', email);

            fetch('clientarea.php?action=productdetails&id=' + serviceId + '&modop=custom&a=deleteUser', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success) {
                    showAlert('User deleted successfully', 'success');
                    loadUsers();
                } else {
                    showAlert('Failed to delete user', 'danger');
                }
            })
            .catch(function(error) {
                showAlert('Error deleting user', 'danger');
            });
        }

        function resetUserPassword(email) {
            document.getElementById('resetPasswordEmail').value = email;
            document.getElementById('resetPasswordEmailDisplay').textContent = email;
            document.getElementById('resetPasswordModal').style.display = 'block';
        }

        function submitResetPassword(event) {
            event.preventDefault();

            var email = document.getElementById('resetPasswordEmail').value;
            var password = document.getElementById('newPassword').value;

            var formData = new FormData();
            formData.append('email', email);
            formData.append('password', password);

            fetch('clientarea.php?action=productdetails&id=' + serviceId + '&modop=custom&a=resetUserPassword', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success) {
                    var msg = 'Password reset successfully.';
                    if (data.password) {
                        msg += ' New password: ' + data.password;
                    }
                    showAlert(msg, 'success');
                    closeModal('resetPasswordModal');
                    document.getElementById('resetPasswordForm').reset();
                } else {
                    showAlert('Failed to reset password', 'danger');
                }
            })
            .catch(function(error) {
                showAlert('Error resetting password', 'danger');
            });
        }

        function copyPassword() {
            var password = document.getElementById('admin-password').textContent;
            navigator.clipboard.writeText(password).then(function() {
                showAlert('Password copied to clipboard', 'info');
            });
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('gw-modal')) {
                event.target.style.display = 'none';
            }
        };
    </script>
</div>
