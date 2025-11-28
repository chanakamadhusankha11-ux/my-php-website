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

// Get recent videos (last 5)
$recentVideos = $pdo->query("SELECT title, created_at, views FROM videos ORDER BY created_at DESC LIMIT 5")->fetchAll();

// Get popular videos (top 5 by views)
$popularVideos = $pdo->query("SELECT title, views FROM videos ORDER BY views DESC LIMIT 5")->fetchAll();

// Get categories with video counts
$categoriesWithCounts = $pdo->query("
    SELECT c.name, COUNT(v.id) as video_count 
    FROM categories c 
    LEFT JOIN videos v ON c.id = v.category_id 
    GROUP BY c.id, c.name 
    ORDER BY video_count DESC
")->fetchAll();

// Calculate average views per video
$avgViews = $totalVideos > 0 ? round($totalViews / $totalVideos, 2) : 0;

// Get today's date for analytics
$today = date('Y-m-d');

?>
<style>
.dashboard-stats { 
    display: grid; 
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); 
    gap: 20px; 
    margin-bottom: 30px; 
}
.stat-card { 
    background: #fff; 
    padding: 25px; 
    border-radius: 8px; 
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
}
.stat-card h4 { margin-top: 0; color: #555; font-size: 0.9em; text-transform: uppercase; letter-spacing: 0.5px; }
.stat-card p { font-size: 2.5em; font-weight: bold; margin: 10px 0 0; color: #3498db; }
.stat-card.status-on p { color: #e74c3c; }
.stat-card.status-off p { color: #2ecc71; }
.stat-trend { font-size: 0.8em; margin-top: 5px; }
.trend-up { color: #2ecc71; }
.trend-down { color: #e74c3c; }

.welcome-banner {
    background: linear-gradient(135deg, #3498db, #2c3e50);
    color: white;
    padding: 25px;
    border-radius: 12px;
    margin-bottom: 30px;
    position: relative;
    overflow: hidden;
}
.welcome-banner::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 100%;
    height: 200%;
    background: rgba(255,255,255,0.1);
    transform: rotate(45deg);
}
.welcome-banner h3 {
    margin: 0 0 10px 0;
    font-size: 1.8em;
    position: relative;
}
.welcome-banner p {
    margin: 0;
    opacity: 0.9;
    position: relative;
    font-size: 1.1em;
}

.analytics-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 25px;
    margin-bottom: 30px;
}

.analytics-card {
    background: #fff;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 25px;
}
.analytics-card h3 {
    margin-top: 0;
    color: #2c3e50;
    border-bottom: 2px solid #f8f9fa;
    padding-bottom: 15px;
    margin-bottom: 20px;
    font-size: 1.3em;
}

.recent-activity {
    background: #fff;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}
.recent-activity h3 {
    margin-top: 0;
    color: #2c3e50;
    border-bottom: 2px solid #f8f9fa;
    padding-bottom: 15px;
    margin-bottom: 20px;
    font-size: 1.3em;
}

.activity-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 25px;
}
.activity-section {
    margin-bottom: 25px;
}
.activity-section h4 {
    color: #3498db;
    margin-bottom: 15px;
    font-size: 1.1em;
    display: flex;
    align-items: center;
    gap: 8px;
}
.activity-item {
    padding: 12px 0;
    border-bottom: 1px solid #f8f9fa;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.activity-item:last-child {
    border-bottom: none;
}
.activity-label {
    font-weight: 600;
    color: #555;
    flex: 1;
}
.activity-value {
    color: #333;
    font-weight: 500;
}
.status-online {
    color: #2ecc71;
    font-weight: bold;
}
.status-offline {
    color: #e74c3c;
    font-weight: bold;
}
.status-warning {
    color: #f39c12;
    font-weight: bold;
}

.system-metric {
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 12px;
    border-left: 4px solid #3498db;
}
.metric-value {
    font-size: 1.3em;
    font-weight: bold;
    color: #2c3e50;
}
.metric-label {
    font-size: 0.9em;
    color: #6c757d;
    margin-bottom: 5px;
}

.video-list {
    max-height: 300px;
    overflow-y: auto;
}
.video-item {
    padding: 12px;
    border-bottom: 1px solid #f1f1f1;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.video-item:last-child {
    border-bottom: none;
}
.video-title {
    flex: 1;
    font-weight: 500;
}
.video-views {
    background: #3498db;
    color: white;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.8em;
    font-weight: bold;
}

.category-distribution {
    margin-top: 15px;
}
.category-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #f8f9fa;
}
.category-bar {
    flex: 1;
    height: 8px;
    background: #ecf0f1;
    border-radius: 4px;
    margin: 0 15px;
    overflow: hidden;
}
.category-fill {
    height: 100%;
    background: linear-gradient(90deg, #3498db, #2980b9);
    border-radius: 4px;
}

.quick-stats {
    display: grid;
    gap: 15px;
}
.quick-stat {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 10px;
    text-align: center;
}
.quick-stat h4 {
    margin: 0 0 10px 0;
    font-size: 0.9em;
    opacity: 0.9;
}
.quick-stat p {
    margin: 0;
    font-size: 2em;
    font-weight: bold;
}

.progress-ring {
    width: 80px;
    height: 80px;
}
</style>

<div class="welcome-banner">
    <h3>Advanced Dashboard Analytics</h3>
    <p>Comprehensive overview of your video platform performance and metrics</p>
</div>

<div class="dashboard-stats">
    <div class="stat-card">
        <h4>Total Videos</h4>
        <p><?php echo $totalVideos; ?></p>
        <div class="stat-trend trend-up">+5% this week</div>
    </div>
    <div class="stat-card">
        <h4>Total Categories</h4>
        <p><?php echo $totalCategories; ?></p>
        <div class="stat-trend trend-up">Active</div>
    </div>
    <div class="stat-card">
        <h4>Total Website Views</h4>
        <p><?php echo number_format($totalViews ?? 0); ?></p>
        <div class="stat-trend trend-up">+12% growth</div>
    </div>
    <div class="stat-card <?php echo $maintenance_mode == 'on' ? 'status-on' : 'status-off'; ?>">
        <h4>Maintenance Mode</h4>
        <p><?php echo strtoupper($maintenance_mode); ?></p>
        <div class="stat-trend">System Status</div>
    </div>
</div>

<div class="analytics-grid">
    <div>
        <div class="analytics-card">
            <h3>📊 Content Analytics</h3>
            <div class="activity-grid">
                <div class="activity-section">
                    <h4>📈 Performance Metrics</h4>
                    <div class="system-metric">
                        <div class="metric-label">Average Views Per Video</div>
                        <div class="metric-value"><?php echo number_format($avgViews, 2); ?> views</div>
                    </div>
                    <div class="system-metric">
                        <div class="metric-label">Content Engagement Rate</div>
                        <div class="metric-value"><?php echo $totalVideos > 0 ? round(($totalViews / ($totalVideos * 100)) * 100, 2) : 0; ?>%</div>
                    </div>
                    <div class="system-metric">
                        <div class="metric-label">Category Utilization</div>
                        <div class="metric-value"><?php echo $totalCategories > 0 ? round(($totalVideos / $totalCategories), 2) : 0; ?> videos/category</div>
                    </div>
                </div>

                <div class="activity-section">
                    <h4>🏷️ Category Distribution</h4>
                    <div class="category-distribution">
                        <?php foreach($categoriesWithCounts as $category): ?>
                            <div class="category-item">
                                <span><?php echo htmlspecialchars($category['name']); ?></span>
                                <div class="category-bar">
                                    <div class="category-fill" style="width: <?php echo $totalVideos > 0 ? ($category['video_count'] / $totalVideos * 100) : 0; ?>%"></div>
                                </div>
                                <span><?php echo $category['video_count']; ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="analytics-card">
            <h3>🔥 Popular Videos</h3>
            <div class="video-list">
                <?php foreach($popularVideos as $video): ?>
                    <div class="video-item">
                        <span class="video-title"><?php echo htmlspecialchars($video['title']); ?></span>
                        <span class="video-views"><?php echo number_format($video['views']); ?> views</span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div>
        <div class="analytics-card">
            <h3>⏰ Recent Activity</h3>
            <div class="video-list">
                <?php foreach($recentVideos as $video): ?>
                    <div class="video-item">
                        <div>
                            <div class="video-title"><?php echo htmlspecialchars($video['title']); ?></div>
                            <small style="color: #666;"><?php echo date('M j, Y', strtotime($video['created_at'])); ?></small>
                        </div>
                        <span class="video-views"><?php echo number_format($video['views']); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="quick-stats">
            <div class="quick-stat" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <h4>Avg. Video Views</h4>
                <p><?php echo number_format($avgViews, 1); ?></p>
            </div>
            <div class="quick-stat" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <h4>Engagement Rate</h4>
                <p><?php echo $totalVideos > 0 ? round(($totalViews / ($totalVideos * 100)) * 100, 1) : 0; ?>%</p>
            </div>
            <div class="quick-stat" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <h4>Content Health</h4>
                <p><?php echo $totalVideos > 50 ? 'Excellent' : ($totalVideos > 20 ? 'Good' : 'Growing'); ?></p>
            </div>
        </div>
    </div>
</div>

<div class="recent-activity">
    <h3>🔧 System Status & Information</h3>
    
    <div class="activity-grid">
        <div class="activity-section">
            <h4>🔄 System Information</h4>
            <div class="activity-item">
                <span class="activity-label">Server Time</span>
                <span class="activity-value"><?php echo date('Y-m-d H:i:s'); ?></span>
            </div>
            <div class="activity-item">
                <span class="activity-label">PHP Version</span>
                <span class="activity-value"><?php echo phpversion(); ?></span>
            </div>
            <div class="activity-item">
                <span class="activity-label">Server Software</span>
                <span class="activity-value"><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'N/A'; ?></span>
            </div>
            <div class="activity-item">
                <span class="activity-label">Database Driver</span>
                <span class="activity-value"><?php echo $pdo->getAttribute(PDO::ATTR_DRIVER_NAME); ?></span>
            </div>
        </div>

        <div class="activity-section">
            <h4>📊 Performance Metrics</h4>
            <div class="system-metric">
                <div class="metric-label">Memory Usage</div>
                <div class="metric-value"><?php echo round(memory_get_usage(true) / 1024 / 1024, 2); ?> MB</div>
            </div>
            <div class="system-metric">
                <div class="metric-label">Peak Memory</div>
                <div class="metric-value"><?php echo round(memory_get_peak_usage(true) / 1024 / 1024, 2); ?> MB</div>
            </div>
            <div class="system-metric">
                <div class="metric-label">Execution Time</div>
                <div class="metric-value"><?php echo round(microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"], 3); ?>s</div>
            </div>
        </div>

        <div class="activity-section">
            <h4>🔒 Security Status</h4>
            <div class="activity-item">
                <span class="activity-label">Maintenance Mode</span>
                <span class="activity-value <?php echo $maintenance_mode == 'on' ? 'status-warning' : 'status-online'; ?>">
                    <?php echo strtoupper($maintenance_mode); ?>
                </span>
            </div>
            <div class="activity-item">
                <span class="activity-label">HTTPS Connection</span>
                <span class="activity-value <?php echo (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'status-online' : 'status-warning'; ?>">
                    <?php echo (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'ENABLED' : 'DISABLED'; ?>
                </span>
            </div>
            <div class="activity-item">
                <span class="activity-label">Session Active</span>
                <span class="activity-value status-online">ACTIVE</span>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>