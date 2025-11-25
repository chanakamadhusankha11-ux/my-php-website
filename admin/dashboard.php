<?php
// admin/dashboard.php
include 'includes/header.php';

// Get total videos
$totalVideos = $pdo->query("SELECT COUNT(*) FROM videos")->fetchColumn();

// Get total categories
$totalCategories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();

// Get total site views (sum of all video views)
$totalViews = $pdo->query("SELECT SUM(views) FROM videos")->fetchColumn();

// Get maintenance mode status
$stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_name = ?");
$stmt->execute(['maintenance_mode']);
$maintenance_mode = $stmt->fetchColumn();

?>
<style>
.dashboard-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; }
.stat-card { background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
.stat-card h4 { margin-top: 0; color: #555; }
.stat-card p { font-size: 2.5em; font-weight: bold; margin: 10px 0 0; color: #3498db; }
.stat-card.status-on p { color: #e74c3c; }
.stat-card.status-off p { color: #2ecc71; }
</style>

<h1>Dashboard Overview</h1>

<div class="dashboard-stats">
    <div class="stat-card">
        <h4>Total Videos</h4>
        <p><?php echo $totalVideos; ?></p>
    </div>
    <div class="stat-card">
        <h4>Total Categories</h4>
        <p><?php echo $totalCategories; ?></p>
    </div>
    <div class="stat-card">
        <h4>Total Website Views</h4>
        <p><?php echo number_format($totalViews ?? 0); ?></p>
    </div>
    <div class="stat-card <?php echo $maintenance_mode == 'on' ? 'status-on' : 'status-off'; ?>">
        <h4>Maintenance Mode</h4>
        <p><?php echo strtoupper($maintenance_mode); ?></p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>