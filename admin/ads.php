<?php
// admin/ads.php
include 'includes/header.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ads = [
        'ad_native' => $_POST['ad_native'],
        'ad_popup' => $_POST['ad_popup'],
        'ad_popunder' => $_POST['ad_popunder'],
        'ad_socialbar' => $_POST['ad_socialbar']
    ];

    foreach ($ads as $name => $value) {
        $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_name = ?");
        $stmt->execute([$value, $name]);
    }
    header("Location: ads.php?status=updated"); exit;
}

// Get current ad codes
$ad_codes = [];
$stmt = $pdo->query("SELECT setting_name, setting_value FROM settings WHERE setting_name LIKE 'ad_%'");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $ad_codes[$row['setting_name']] = $row['setting_value'];
}
?>

<h1>Ads Manager</h1>

<div class="card">
    <h3>Adsterra Ad Codes</h3>
    <p>Paste the ad codes from Adsterra into the fields below. These will be added to all user-facing pages.</p>
    <form action="ads.php" method="POST">
        
        <label for="ad_native">Native Banner Code:</label>
        <textarea name="ad_native" id="ad_native"><?php echo htmlspecialchars($ad_codes['ad_native'] ?? ''); ?></textarea>
        
        <label for="ad_popup">Popup / Popunder Code:</label>
        <textarea name="ad_popup" id="ad_popup"><?php echo htmlspecialchars($ad_codes['ad_popup'] ?? ''); ?></textarea>
        
        <label for="ad_popunder">Direct Link (for Popunder on click):</label>
        <textarea name="ad_popunder" id="ad_popunder"><?php echo htmlspecialchars($ad_codes['ad_popunder'] ?? ''); ?></textarea>
        
        <label for="ad_socialbar">Social Bar Code:</label>
        <textarea name="ad_socialbar" id="ad_socialbar"><?php echo htmlspecialchars($ad_codes['ad_socialbar'] ?? ''); ?></textarea>
        
        <button type="submit">Save Ad Codes</button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>