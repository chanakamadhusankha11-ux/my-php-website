<?php
// admin/settings.php
include 'includes/header.php';

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $mode = isset($_POST['maintenance_mode']) ? 'on' : 'off';
    try {
        $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_name = 'maintenance_mode'");
        $stmt->execute([$mode]);
        $message = '<div class="alert alert-success">
                        <div class="alert-icon">✓</div>
                        <div class="alert-content">
                            <strong>Success!</strong> Settings updated successfully.
                        </div>
                    </div>';
    } catch (Exception $e) {
        $message = '<div class="alert alert-error">
                        <div class="alert-icon">⚠</div>
                        <div class="alert-content">
                            <strong>Error!</strong> Failed to update settings: ' . $e->getMessage() . '
                        </div>
                    </div>';
    }
}

// Get current status
try {
    $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_name = 'maintenance_mode'");
    $stmt->execute();
    $current_mode = $stmt->fetchColumn();
} catch (Exception $e) {
    $current_mode = 'off';
    $message = '<div class="alert alert-error">
                    <div class="alert-icon">⚠</div>
                    <div class="alert-content">
                        <strong>Error!</strong> Failed to load settings: ' . $e->getMessage() . '
                    </div>
                </div>';
}

// Get additional settings if they exist
$site_title = 'My Video Site';
$site_description = 'Amazing video sharing platform';
try {
    $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_name = 'site_title'");
    $stmt->execute();
    $site_title_result = $stmt->fetchColumn();
    if ($site_title_result) $site_title = $site_title_result;
    
    $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_name = 'site_description'");
    $stmt->execute();
    $site_description_result = $stmt->fetchColumn();
    if ($site_description_result) $site_description = $site_description_result;
} catch (Exception $e) {
    // Use default values if settings don't exist
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
.form-group input, .form-group textarea {
    width: 100%;
    padding: 14px 18px;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    font-size: 16px;
    transition: all 0.3s ease;
    background: #fafbfc;
    font-family: inherit;
}
.form-group input:focus, .form-group textarea:focus {
    outline: none;
    border-color: #667eea;
    background: #fff;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    transform: translateY(-1px);
}

/* Modern Toggle Switch */
.toggle-group {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 25px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 12px;
    border: 2px solid #e9ecef;
}
.toggle-label {
    flex: 1;
}
.toggle-label h4 {
    margin: 0 0 8px 0;
    color: #2c3e50;
    font-size: 1.1em;
}
.toggle-label p {
    margin: 0;
    color: #6c757d;
    font-size: 0.95em;
}
.switch {
    position: relative;
    display: inline-block;
    width: 68px;
    height: 38px;
    flex-shrink: 0;
}
.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}
.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, #95a5a6, #7f8c8d);
    transition: .4s;
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
}
.slider:before {
    position: absolute;
    content: "";
    height: 30px;
    width: 30px;
    left: 4px;
    bottom: 4px;
    background: white;
    transition: .4s;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}
input:checked + .slider {
    background: linear-gradient(135deg, #e74c3c, #c0392b);
}
input:checked + .slider:before {
    transform: translateX(30px);
}
.slider.round {
    border-radius: 34px;
}
.slider.round:before {
    border-radius: 50%;
}
.status-indicator {
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.9em;
    margin-left: 15px;
}
.status-on {
    background: #e74c3c;
    color: white;
}
.status-off {
    background: #27ae60;
    color: white;
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

/* Settings Grid */
.settings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 25px;
    margin-top: 30px;
}
.setting-item {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 12px;
    border: 2px solid #e9ecef;
    transition: all 0.3s ease;
}
.setting-item:hover {
    border-color: #667eea;
    transform: translateY(-2px);
}
.setting-item h4 {
    margin: 0 0 10px 0;
    color: #2c3e50;
    font-size: 1.1em;
}
.setting-item p {
    margin: 0;
    color: #6c757d;
    font-size: 0.9em;
}
.setting-value {
    margin-top: 10px;
    font-weight: 600;
    color: #667eea;
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
    .toggle-group {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
    .settings-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<!-- Modern Dashboard Header -->
<div class="dashboard-header">
    <div class="header-content">
        <h1 class="header-title">
            ⚙️ Site Settings
        </h1>
        <p class="header-subtitle">Manage your website configuration and maintenance settings</p>
        <div class="header-stats">
            <div class="stat-card">
                <h4>Current Mode</h4>
                <p><?php echo strtoupper($current_mode); ?></p>
            </div>
            <div class="stat-card">
                <h4>Site Status</h4>
                <p><?php echo $current_mode == 'on' ? 'MAINT' : 'LIVE'; ?></p>
            </div>
            <div class="stat-card">
                <h4>Settings</h4>
                <p>3</p>
            </div>
        </div>
    </div>
</div>

<?php echo $message; ?>

<!-- Maintenance Mode Card -->
<div class="card">
    <h3>🔧 Maintenance Mode</h3>
    <form action="settings.php" method="POST">
        <div class="toggle-group">
            <div class="toggle-label">
                <h4>Maintenance Mode</h4>
                <p>When enabled, users will see a maintenance page and won't be able to access the site.</p>
            </div>
            <label class="switch">
                <input type="checkbox" name="maintenance_mode" <?php echo ($current_mode == 'on') ? 'checked' : ''; ?>>
                <span class="slider round"></span>
            </label>
            <span class="status-indicator <?php echo $current_mode == 'on' ? 'status-on' : 'status-off'; ?>">
                <?php echo $current_mode == 'on' ? 'ACTIVE' : 'INACTIVE'; ?>
            </span>
        </div>
        
        <button type="submit" class="btn btn-primary">💾 Save Settings</button>
    </form>
</div>

<!-- Additional Settings -->
<div class="card">
    <h3>📋 Current Configuration</h3>
    <div class="settings-grid">
        <div class="setting-item">
            <h4>🌐 Site Title</h4>
            <p>The main title of your website</p>
            <div class="setting-value"><?php echo htmlspecialchars($site_title); ?></div>
        </div>
        <div class="setting-item">
            <h4>📝 Site Description</h4>
            <p>Brief description of your website</p>
            <div class="setting-value"><?php echo htmlspecialchars($site_description); ?></div>
        </div>
        <div class="setting-item">
            <h4>🛠️ Maintenance Status</h4>
            <p>Current system maintenance mode</p>
            <div class="setting-value" style="color: <?php echo $current_mode == 'on' ? '#e74c3c' : '#27ae60'; ?>;">
                <?php echo $current_mode == 'on' ? '🛑 MAINTENANCE ACTIVE' : '✅ SYSTEM LIVE'; ?>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="card">
    <h3>🚀 Quick Actions</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
        <a href="../index.php" target="_blank" class="btn" style="background: #3498db; color: white; text-align: center;">
            👀 View Site
        </a>
        <a href="dashboard.php" class="btn" style="background: #2ecc71; color: white; text-align: center;">
            📊 Dashboard
        </a>
        <a href="videos.php" class="btn" style="background: #9b59b6; color: white; text-align: center;">
            🎬 Manage Videos
        </a>
        <a href="categories.php" class="btn" style="background: #e67e22; color: white; text-align: center;">
            📂 Categories
        </a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>