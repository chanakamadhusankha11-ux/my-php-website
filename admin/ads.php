<?php
// admin/ads.php
include 'includes/header.php';

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ads = [
        'ad_native' => $_POST['ad_native'] ?? '',
        'ad_popup' => $_POST['ad_popup'] ?? '',
        'ad_popunder' => $_POST['ad_popunder'] ?? '',
        'ad_socialbar' => $_POST['ad_socialbar'] ?? ''
    ];

    try {
        foreach ($ads as $name => $value) {
            $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_name = ?");
            $stmt->execute([$value, $name]);
        }
        $message = '<div class="alert alert-success">
                        <div class="alert-icon">✓</div>
                        <div class="alert-content">
                            <strong>Success!</strong> Ad codes updated successfully.
                        </div>
                    </div>';
    } catch (Exception $e) {
        $message = '<div class="alert alert-error">
                        <div class="alert-icon">⚠</div>
                        <div class="alert-content">
                            <strong>Error!</strong> Failed to update ad codes: ' . $e->getMessage() . '
                        </div>
                    </div>';
    }
}

// Get current ad codes
$ad_codes = [];
try {
    $stmt = $pdo->query("SELECT setting_name, setting_value FROM settings WHERE setting_name LIKE 'ad_%'");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $ad_codes[$row['setting_name']] = $row['setting_value'];
    }
} catch (Exception $e) {
    $message = '<div class="alert alert-error">
                    <div class="alert-icon">⚠</div>
                    <div class="alert-content">
                        <strong>Error!</strong> Failed to load ad codes: ' . $e->getMessage() . '
                    </div>
                </div>';
}

// Count active ads
$active_ads = 0;
foreach ($ad_codes as $code) {
    if (!empty(trim($code))) {
        $active_ads++;
    }
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
    margin-bottom: 30px;
}
.form-group label {
    display: block;
    margin-bottom: 12px;
    font-weight: 600;
    color: #2c3e50;
    font-size: 1em;
    display: flex;
    align-items: center;
    gap: 8px;
}
.form-group textarea {
    width: 100%;
    padding: 16px 20px;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    font-size: 14px;
    font-family: 'Courier New', monospace;
    transition: all 0.3s ease;
    background: #fafbfc;
    resize: vertical;
    min-height: 120px;
}
.form-group textarea:focus {
    outline: none;
    border-color: #667eea;
    background: #fff;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    transform: translateY(-1px);
}

/* Ad Status Indicators */
.ad-status {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.85em;
    font-weight: 600;
    margin-left: 10px;
}
.ad-active {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}
.ad-inactive {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
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
}
.btn-secondary:hover {
    background: #5a6268;
    transform: translateY(-2px);
}

/* Ad Preview Section */
.ad-previews {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 30px;
}
.ad-preview {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 12px;
    border: 2px dashed #dee2e6;
    text-align: center;
}
.ad-preview h4 {
    margin: 0 0 15px 0;
    color: #495057;
    font-size: 1.1em;
}
.preview-placeholder {
    background: white;
    padding: 30px;
    border-radius: 8px;
    border: 1px solid #e9ecef;
    color: #6c757d;
    font-style: italic;
}

/* Code Helper */
.code-helper {
    background: #2c3e50;
    color: #ecf0f1;
    padding: 15px;
    border-radius: 8px;
    margin-top: 10px;
    font-size: 0.9em;
    font-family: 'Courier New', monospace;
}
.code-helper h5 {
    margin: 0 0 10px 0;
    color: #3498db;
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
    .ad-previews {
        grid-template-columns: 1fr;
    }
    .form-group textarea {
        min-height: 100px;
    }
}
</style>

<!-- Modern Dashboard Header -->
<div class="dashboard-header">
    <div class="header-content">
        <h1 class="header-title">
            💰 Ads Manager
        </h1>
        <p class="header-subtitle">Manage Adsterra ad codes and monetization settings</p>
        <div class="header-stats">
            <div class="stat-card">
                <h4>Active Ads</h4>
                <p><?php echo $active_ads; ?>/4</p>
            </div>
            <div class="stat-card">
                <h4>Ad Types</h4>
                <p>4</p>
            </div>
            <div class="stat-card">
                <h4>Status</h4>
                <p><?php echo $active_ads > 0 ? 'LIVE' : 'SETUP'; ?></p>
            </div>
        </div>
    </div>
</div>

<?php echo $message; ?>

<!-- Ads Configuration Card -->
<div class="card">
    <h3>⚙️ Adsterra Configuration</h3>
    <p style="color: #6c757d; margin-bottom: 25px; line-height: 1.6;">
        Paste your Adsterra ad codes below. These will be automatically integrated into all user-facing pages. 
        Make sure to use the correct code format provided by Adsterra.
    </p>
    
    <form action="ads.php" method="POST">
        <div class="form-group">
            <label for="ad_native">
                🏷️ Native Banner Code
                <span class="ad-status <?php echo !empty(trim($ad_codes['ad_native'] ?? '')) ? 'ad-active' : 'ad-inactive'; ?>">
                    <?php echo !empty(trim($ad_codes['ad_native'] ?? '')) ? 'ACTIVE' : 'INACTIVE'; ?>
                </span>
            </label>
            <textarea name="ad_native" id="ad_native" placeholder="Paste Adsterra Native Banner code here..."><?php echo htmlspecialchars($ad_codes['ad_native'] ?? ''); ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="ad_popup">
                🪟 Popup / Popunder Code
                <span class="ad-status <?php echo !empty(trim($ad_codes['ad_popup'] ?? '')) ? 'ad-active' : 'ad-inactive'; ?>">
                    <?php echo !empty(trim($ad_codes['ad_popup'] ?? '')) ? 'ACTIVE' : 'INACTIVE'; ?>
                </span>
            </label>
            <textarea name="ad_popup" id="ad_popup" placeholder="Paste Adsterra Popup/Popunder code here..."><?php echo htmlspecialchars($ad_codes['ad_popup'] ?? ''); ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="ad_popunder">
                🔗 Direct Link (Popunder on click)
                <span class="ad-status <?php echo !empty(trim($ad_codes['ad_popunder'] ?? '')) ? 'ad-active' : 'ad-inactive'; ?>">
                    <?php echo !empty(trim($ad_codes['ad_popunder'] ?? '')) ? 'ACTIVE' : 'INACTIVE'; ?>
                </span>
            </label>
            <textarea name="ad_popunder" id="ad_popunder" placeholder="Paste direct popunder link code here..."><?php echo htmlspecialchars($ad_codes['ad_popunder'] ?? ''); ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="ad_socialbar">
                👥 Social Bar Code
                <span class="ad-status <?php echo !empty(trim($ad_codes['ad_socialbar'] ?? '')) ? 'ad-active' : 'ad-inactive'; ?>">
                    <?php echo !empty(trim($ad_codes['ad_socialbar'] ?? '')) ? 'ACTIVE' : 'INACTIVE'; ?>
                </span>
            </label>
            <textarea name="ad_socialbar" id="ad_socialbar" placeholder="Paste Adsterra Social Bar code here..."><?php echo htmlspecialchars($ad_codes['ad_socialbar'] ?? ''); ?></textarea>
        </div>
        
        <button type="submit" class="btn btn-primary">💾 Save All Ad Codes</button>
    </form>
</div>

<!-- Ad Previews Card -->
<div class="card">
    <h3>👀 Ad Placement Preview</h3>
    <p style="color: #6c757d; margin-bottom: 25px;">
        Preview of where your ads will appear on the website
    </p>
    
    <div class="ad-previews">
        <div class="ad-preview">
            <h4>🏷️ Native Banner</h4>
            <div class="preview-placeholder">
                Native ads blend with content<br>
                <small>Appears in video listings</small>
            </div>
        </div>
        <div class="ad-preview">
            <h4>🪟 Popup/Popunder</h4>
            <div class="preview-placeholder">
                Interstitial ads<br>
                <small>Shows between page visits</small>
            </div>
        </div>
        <div class="ad-preview">
            <h4>👥 Social Bar</h4>
            <div class="preview-placeholder">
                Social media style ads<br>
                <small>Floating social bar</small>
            </div>
        </div>
    </div>
</div>

<!-- Quick Tips Card -->
<div class="card">
    <h3>💡 Ad Integration Tips</h3>
    <div class="code-helper">
        <h5>🔧 Best Practices:</h5>
        • Use responsive ad codes for better mobile experience<br>
        • Test ads on different devices and browsers<br>
        • Monitor performance in Adsterra dashboard<br>
        • Ensure codes are properly formatted without extra spaces<br>
        • Use HTTPS compatible codes for secure sites
    </div>
</div>

<?php include 'includes/footer.php'; ?>