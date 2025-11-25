<?php
// config.php
$host = 'localhost';
$dbname = 'mysite_db';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database.");
}

// Fetch all settings into a global array
$settings_stmt = $pdo->query("SELECT setting_name, setting_value FROM settings");
$GLOBALS['settings'] = $settings_stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Check for maintenance mode
if ($GLOBALS['settings']['maintenance_mode'] == 'on') {
    // This will redirect all pages to a maintenance message
    // Make sure 'maintenance.php' does not include this config file to avoid a loop
    // A simple HTML page is better
    echo '
    <!DOCTYPE html>
    <html lang="en">
    <head><meta charset="UTF-8"><title>Under Maintenance</title>
    <style>body{text-align: center; padding: 150px; font-family: sans-serif; background: #222; color: #fff;} h1{font-size: 50px;} p{font-size: 20px;}</style>
    </head>
    <body>
    <h1>Site Under Maintenance</h1>
    <p>We are currently performing scheduled maintenance. We will be back shortly. Thank you for your patience.</p>
    </body>
    </html>';
    exit;
}
?>