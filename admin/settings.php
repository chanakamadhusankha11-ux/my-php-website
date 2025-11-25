<?php
// admin/settings.php
include 'includes/header.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $mode = isset($_POST['maintenance_mode']) ? 'on' : 'off';
    $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_name = 'maintenance_mode'");
    $stmt->execute([$mode]);
    header("Location: settings.php?status=updated"); exit;
}

// Get current status
$stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_name = 'maintenance_mode'");
$stmt->execute();
$current_mode = $stmt->fetchColumn();
?>

<h1>Site Settings</h1>

<div class="card">
    <h3>Maintenance Mode</h3>
    <p>When turned on, users will see a "Under Maintenance" page and won't be able to access the site.</p>
    <form action="settings.php" method="POST">
        <label class="switch">
            <input type="checkbox" name="maintenance_mode" <?php echo ($current_mode == 'on') ? 'checked' : ''; ?>>
            <span class="slider round"></span>
        </label>
        <span style="margin-left: 10px; vertical-align: middle; font-size: 18px;">
            Maintenance Mode is currently <strong><?php echo strtoupper($current_mode); ?></strong>
        </span>
        <br><br>
        <button type="submit">Save Settings</button>
    </form>
</div>

<!-- CSS for the toggle switch -->
<style>
.switch { position: relative; display: inline-block; width: 60px; height: 34px; }
.switch input { opacity: 0; width: 0; height: 0; }
.slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; }
.slider:before { position: absolute; content: ""; height: 26px; width: 26px; left: 4px; bottom: 4px; background-color: white; transition: .4s; }
input:checked + .slider { background-color: #e74c3c; }
input:checked + .slider:before { transform: translateX(26px); }
.slider.round { border-radius: 34px; }
.slider.round:before { border-radius: 50%; }
</style>


<?php include 'includes/footer.php'; ?>