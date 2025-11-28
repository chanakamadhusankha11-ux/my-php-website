<?php
include 'includes/header.php'; // Includes auth check and DB connection

// =================================================================
// SECURITY CHECK: ONLY SUPER ADMINS CAN ACCESS THIS PAGE
// =================================================================
if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'superadmin') {
    // If not a superadmin, show an error and stop execution
    echo '<div class="dashboard-header" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);">
            <div class="header-content">
                <h1 class="header-title">🚫 Access Denied</h1>
                <p class="header-subtitle">You do not have permission to access this page.</p>
            </div>
          </div>';
    include 'includes/footer.php';
    exit;
}

// --- HANDLE POST REQUESTS (ADD, UPDATE, DELETE) ---
$message = '';

// 1. HANDLE DELETE
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_id'])) {
    $id_to_delete = $_POST['delete_id'];
    
    // CRITICAL: Prevent a user from deleting their own account
    if ($id_to_delete == $_SESSION['admin_id']) {
        $message = '<div class="alert alert-error">
                        <div class="alert-icon">⚠</div>
                        <div class="alert-content">
                            <strong>Error!</strong> You cannot delete your own account.
                        </div>
                    </div>';
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM admins WHERE id = ?");
            $stmt->execute([$id_to_delete]);
            $message = '<div class="alert alert-success">
                            <div class="alert-icon">✓</div>
                            <div class="alert-content">
                                <strong>Success!</strong> Admin deleted successfully.
                            </div>
                        </div>';
        } catch (Exception $e) {
            $message = '<div class="alert alert-error">
                            <div class="alert-icon">⚠</div>
                            <div class="alert-content">
                                <strong>Error!</strong> Failed to delete admin: ' . $e->getMessage() . '
                            </div>
                        </div>';
        }
    }
}

// 2. HANDLE ADD/UPDATE
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['username'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    
    // Check if it's an UPDATE or a new ADD
    if (isset($_POST['update_id']) && !empty($_POST['update_id'])) {
        // --- UPDATE an existing admin ---
        $id_to_update = $_POST['update_id'];
        
        try {
            if (!empty($password)) {
                // If a new password is provided, hash it and update it
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE admins SET username = ?, password = ?, role = ? WHERE id = ?");
                $stmt->execute([$username, $hashedPassword, $role, $id_to_update]);
            } else {
                // If password field is empty, do NOT update the password
                $stmt = $pdo->prepare("UPDATE admins SET username = ?, role = ? WHERE id = ?");
                $stmt->execute([$username, $role, $id_to_update]);
            }
            $message = '<div class="alert alert-success">
                            <div class="alert-icon">✓</div>
                            <div class="alert-content">
                                <strong>Success!</strong> Admin updated successfully.
                            </div>
                        </div>';
        } catch (Exception $e) {
            $message = '<div class="alert alert-error">
                            <div class="alert-icon">⚠</div>
                            <div class="alert-content">
                                <strong>Error!</strong> Failed to update admin: ' . $e->getMessage() . '
                            </div>
                        </div>';
        }

    } else {
        // --- ADD a new admin ---
        if (empty($username) || empty($password)) {
            $message = '<div class="alert alert-error">
                            <div class="alert-icon">⚠</div>
                            <div class="alert-content">
                                <strong>Error!</strong> Username and Password are required to add a new admin.
                            </div>
                        </div>';
        } else {
            try {
                // Check if username already exists
                $stmt = $pdo->prepare("SELECT id FROM admins WHERE username = ?");
                $stmt->execute([$username]);
                if ($stmt->fetch()) {
                    $message = '<div class="alert alert-error">
                                    <div class="alert-icon">⚠</div>
                                    <div class="alert-content">
                                        <strong>Error!</strong> This username already exists.
                                    </div>
                                </div>';
                } else {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO admins (username, password, role) VALUES (?, ?, ?)");
                    $stmt->execute([$username, $hashedPassword, $role]);
                    $message = '<div class="alert alert-success">
                                    <div class="alert-icon">✓</div>
                                    <div class="alert-content">
                                        <strong>Success!</strong> New admin added successfully.
                                    </div>
                                </div>';
                }
            } catch (Exception $e) {
                $message = '<div class="alert alert-error">
                                <div class="alert-icon">⚠</div>
                                <div class="alert-content">
                                    <strong>Error!</strong> Failed to add admin: ' . $e->getMessage() . '
                                </div>
                            </div>';
            }
        }
    }
}

// --- PREPARE FOR DISPLAY ---

// Check if we are in "edit mode"
$edit_mode = false;
$admin_to_edit = null;
if (isset($_GET['edit_id'])) {
    $edit_mode = true;
    try {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
        $stmt->execute([$_GET['edit_id']]);
        $admin_to_edit = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$admin_to_edit) {
            $message = '<div class="alert alert-error">
                            <div class="alert-icon">⚠</div>
                            <div class="alert-content">
                                <strong>Error!</strong> Admin not found.
                            </div>
                        </div>';
            $edit_mode = false;
        }
    } catch (Exception $e) {
        $message = '<div class="alert alert-error">
                        <div class="alert-icon">⚠</div>
                        <div class="alert-content">
                            <strong>Error!</strong> Failed to load admin data: ' . $e->getMessage() . '
                        </div>
                    </div>';
        $edit_mode = false;
    }
}

// Fetch all admins to display in the list
try {
    $all_admins = $pdo->query("SELECT id, username, role FROM admins ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
    $totalAdmins = count($all_admins);
    $superAdminCount = 0;
    $adminCount = 0;
    
    foreach ($all_admins as $admin) {
        if ($admin['role'] === 'superadmin') {
            $superAdminCount++;
        } else {
            $adminCount++;
        }
    }
} catch (Exception $e) {
    $message = '<div class="alert alert-error">
                    <div class="alert-icon">⚠</div>
                    <div class="alert-content">
                        <strong>Error!</strong> Failed to load admin list: ' . $e->getMessage() . '
                    </div>
                </div>';
    $all_admins = [];
    $totalAdmins = 0;
    $superAdminCount = 0;
    $adminCount = 0;
}
?>

<style>
/* Modern Dashboard Header */
.dashboard-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 30px;
    border-radius: 16px;
    margin-bottom: 30px;
    position: relative;
    overflow: hidden;
}
.dashboard-header::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 100%;
    height: 200%;
    background: rgba(255,255,255,0.1);
    transform: rotate(45deg);
}
.header-content {
    position: relative;
    z-index: 2;
}
.header-title {
    font-size: 2.2em;
    font-weight: 700;
    margin: 0 0 10px 0;
    display: flex;
    align-items: center;
    gap: 15px;
}
.header-subtitle {
    font-size: 1.1em;
    opacity: 0.9;
    margin: 0 0 25px 0;
}
.header-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 20px;
}
.stat-card {
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(10px);
    padding: 20px;
    border-radius: 12px;
    border: 1px solid rgba(255,255,255,0.2);
    text-align: center;
    transition: transform 0.3s ease;
}
.stat-card:hover {
    transform: translateY(-5px);
}
.stat-card h4 {
    margin: 0 0 8px 0;
    font-size: 0.9em;
    opacity: 0.9;
    font-weight: 500;
}
.stat-card p {
    margin: 0;
    font-size: 2.2em;
    font-weight: 700;
}

/* Enhanced Alert System */
.alert {
    display: flex;
    align-items: center;
    padding: 16px 20px;
    border-radius: 12px;
    margin-bottom: 25px;
    font-weight: 500;
    border: 1px solid;
    animation: slideIn 0.3s ease;
}
.alert-success {
    background: #f0f9f4;
    color: #0d6832;
    border-color: #b8e6cb;
}
.alert-error {
    background: #fdf2f2;
    color: #c81e1e;
    border-color: #fbd5d5;
}
.alert-warning {
    background: #fff8e6;
    color: #b54708;
    border-color: #fedf89;
}
.alert-icon {
    font-size: 20px;
    margin-right: 12px;
    font-weight: bold;
}
.alert-content {
    flex: 1;
}

/* Modern Card Design */
.card {
    background: #fff;
    padding: 30px;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    margin-bottom: 30px;
    border: 1px solid #f0f0f0;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.12);
}
.card h3 {
    margin-top: 0;
    color: #2c3e50;
    border-bottom: 2px solid #f8f9fa;
    padding-bottom: 20px;
    margin-bottom: 25px;
    font-size: 1.4em;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
}

/* Enhanced Form Styling */
.form-group {
    margin-bottom: 25px;
}
.form-group label {
    display: block;
    margin-bottom: 10px;
    font-weight: 600;
    color: #2c3e50;
    font-size: 0.95em;
}
.form-group input, .form-group select {
    width: 100%;
    padding: 14px 18px;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    font-size: 16px;
    transition: all 0.3s ease;
    background: #fafbfc;
}
.form-group input:focus, .form-group select:focus {
    outline: none;
    border-color: #667eea;
    background: #fff;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    transform: translateY(-1px);
}

/* Enhanced Button Design */
.btn {
    padding: 14px 28px;
    border: none;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    justify-content: center;
}
.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}
.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}
.btn-secondary {
    background: #6c757d;
    color: white;
    margin-left: 12px;
}
.btn-secondary:hover {
    background: #5a6268;
    transform: translateY(-2px);
}

/* Modern Table Design */
.table-container {
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 15px rgba(0,0,0,0.08);
    margin-top: 20px;
}
table {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
}
table th {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 18px 20px;
    text-align: left;
    font-weight: 600;
    font-size: 0.95em;
}
table td {
    padding: 18px 20px;
    border-bottom: 1px solid #f1f3f4;
    vertical-align: middle;
    transition: background 0.3s ease;
}
table tr:last-child td {
    border-bottom: none;
}
table tr:hover td {
    background: #f8f9fa;
}

/* Enhanced Role Badges */
.role-badge {
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.85em;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.role-superadmin {
    background: linear-gradient(135deg, #e74c3c, #c0392b);
    color: white;
    box-shadow: 0 2px 8px rgba(231, 76, 60, 0.3);
}
.role-admin {
    background: linear-gradient(135deg, #3498db, #2980b9);
    color: white;
    box-shadow: 0 2px 8px rgba(52, 152, 219, 0.3);
}

/* Modern Action Buttons */
.actions {
    display: flex;
    gap: 10px;
}
.edit-btn, .delete-btn {
    padding: 10px 18px;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.edit-btn {
    background: linear-gradient(135deg, #f39c12, #e67e22);
    color: white;
    box-shadow: 0 2px 10px rgba(243, 156, 18, 0.3);
}
.edit-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(243, 156, 18, 0.4);
}
.delete-btn {
    background: linear-gradient(135deg, #e74c3c, #c0392b);
    color: white;
    box-shadow: 0 2px 10px rgba(231, 76, 60, 0.3);
}
.delete-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(231, 76, 60, 0.4);
}

/* Current User Indicator */
.current-user {
    background: linear-gradient(135deg, #2ecc71, #27ae60) !important;
    color: white;
    font-weight: bold;
}

/* Animations */
@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive Design */
@media (max-width: 768px) {
    .dashboard-header {
        padding: 25px 20px;
    }
    .header-title {
        font-size: 1.8em;
    }
    .header-stats {
        grid-template-columns: 1fr;
    }
    .card {
        padding: 20px;
    }
    .actions {
        flex-direction: column;
    }
    .table-container {
        overflow-x: auto;
    }
}
</style>

<!-- Modern Dashboard Header -->
<div class="dashboard-header">
    <div class="header-content">
        <h1 class="header-title">
            👥 Admin Management
        </h1>
        <p class="header-subtitle">Manage administrator accounts and permissions</p>
        <div class="header-stats">
            <div class="stat-card">
                <h4>Total Admins</h4>
                <p><?php echo $totalAdmins; ?></p>
            </div>
            <div class="stat-card">
                <h4>Super Admins</h4>
                <p><?php echo $superAdminCount; ?></p>
            </div>
            <div class="stat-card">
                <h4>Admins</h4>
                <p><?php echo $adminCount; ?></p>
            </div>
        </div>
    </div>
</div>

<?php echo $message; ?>

<!-- ADD/EDIT FORM CARD -->
<div class="card">
    <h3><?php echo $edit_mode ? '✏️ Edit Admin' : '➕ Add New Admin'; ?></h3>
    <form action="manage_admins.php" method="POST">
        <?php if ($edit_mode): ?>
            <input type="hidden" name="update_id" value="<?php echo $admin_to_edit['id']; ?>">
        <?php endif; ?>
        
        <div class="form-group">
            <label for="username">👤 Username</label>
            <input type="text" id="username" name="username" 
                   value="<?php echo $edit_mode ? htmlspecialchars($admin_to_edit['username']) : ''; ?>" 
                   placeholder="Enter admin username" required>
        </div>
        
        <div class="form-group">
            <label for="password">🔒 Password</label>
            <input type="password" id="password" name="password" 
                   placeholder="<?php echo $edit_mode ? 'Leave blank to keep current password' : 'Enter secure password'; ?>" 
                   <?php echo !$edit_mode ? 'required' : ''; ?>>
        </div>

        <div class="form-group">
            <label for="role">🎯 Role</label>
            <select name="role" id="role" required>
                <option value="admin" <?php echo ($edit_mode && $admin_to_edit['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                <option value="superadmin" <?php echo ($edit_mode && $admin_to_edit['role'] == 'superadmin') ? 'selected' : ''; ?>>Super Admin</option>
            </select>
        </div>
        
        <button type="submit" class="btn btn-primary">
            <?php echo $edit_mode ? '🔄 Update Admin' : '➕ Add Admin'; ?>
        </button>
        <?php if ($edit_mode): ?>
            <a href="manage_admins.php" class="btn btn-secondary">❌ Cancel Edit</a>
        <?php endif; ?>
    </form>
</div>

<!-- EXISTING ADMINS LIST CARD -->
<div class="card">
    <h3>📋 Existing Admins (<?php echo $totalAdmins; ?>)</h3>
    
    <?php if (empty($all_admins)): ?>
        <div style="text-align: center; padding: 40px; color: #6c757d;">
            <h4>No admins found</h4>
            <p>Start by adding your first admin above</p>
        </div>
    <?php else: ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>👤 Username</th>
                        <th>🎯 Role</th>
                        <th>⚡ Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_admins as $admin): ?>
                    <tr <?php echo $admin['id'] == $_SESSION['admin_id'] ? 'class="current-user"' : ''; ?>>
                        <td><strong><?php echo $admin['id']; ?></strong></td>
                        <td>
                            <?php echo htmlspecialchars($admin['username']); ?>
                            <?php if ($admin['id'] == $_SESSION['admin_id']): ?>
                                <span style="color: #27ae60; font-weight: 600; margin-left: 8px;">(You)</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="role-badge role-<?php echo $admin['role']; ?>">
                                <?php if ($admin['role'] === 'superadmin'): ?>
                                    ⭐ 
                                <?php else: ?>
                                    🔧 
                                <?php endif; ?>
                                <?php echo ucfirst($admin['role']); ?>
                            </span>
                        </td>
                        <td class="actions">
                            <a href="manage_admins.php?edit_id=<?php echo $admin['id']; ?>" class="edit-btn">
                                ✏️ Edit
                            </a>
                            <?php 
                            // Show delete button ONLY if it's not the currently logged-in user
                            if ($admin['id'] != $_SESSION['admin_id']): 
                            ?>
                                <form action="manage_admins.php" method="POST" style="display: inline;" 
                                      onsubmit="return confirm('⚠️ Are you sure you want to delete admin \"<?php echo addslashes($admin['username']); ?>\"? This action cannot be undone.');">
                                    <input type="hidden" name="delete_id" value="<?php echo $admin['id']; ?>">
                                    <button type="submit" class="delete-btn">
                                        🗑️ Delete
                                    </button>
                                </form>
                            <?php else: ?>
                                <span style="color: #7f8c8d; font-size: 0.9em;">Current User</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Security Notice -->
<div class="card">
    <h3>🔒 Security Notice</h3>
    <div style="background: #fff8e6; padding: 20px; border-radius: 12px; border: 1px solid #fedf89;">
        <h4 style="color: #b54708; margin-top: 0;">Important Security Guidelines:</h4>
        <ul style="color: #b54708; margin-bottom: 0;">
            <li>Only Super Admins can access this page</li>
            <li>You cannot delete your own account</li>
            <li>Use strong passwords for all admin accounts</li>
            <li>Regularly review admin access permissions</li>
            <li>Limit Super Admin access to trusted personnel only</li>
        </ul>
    </div>
</div>

<?php include 'includes/footer.php'; ?>