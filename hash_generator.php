<?php
// The password you want to hash
$passwordToHash = 'admin123';

// Hash the password using PHP's recommended algorithm
$hashedPassword = password_hash($passwordToHash, PASSWORD_DEFAULT);

// Display the result
echo "<h1>Your New Password Hash</h1>";
echo "<p>Password: " . htmlspecialchars($passwordToHash) . "</p>";
echo "<p>Copy the hash below:</p>";
echo "<textarea rows='3' cols='80' onclick='this.select();' readonly>" . htmlspecialchars($hashedPassword) . "</textarea>";
echo "<hr>";
echo "<h2>IMPORTANT:</h2>";
echo "<p>1. Copy the ENTIRE hash string from the text box above.</p>";
echo "<p>2. Go to phpMyAdmin -> mysite_db -> admins table.</p>";
echo "<p>3. Edit the 'admin' user and paste this new hash into the 'password' field.</p>";
echo "<p>4. <strong>After you are done, DELETE this 'hash_generator.php' file!</strong></p>";
?>

<style>
    body { font-family: sans-serif; padding: 20px; background-color: #f0f2f5; }
    textarea { padding: 10px; font-family: monospace; font-size: 14px; border: 2px solid #007bff; }
    h1, h2 { color: #333; }
    p { line-height: 1.6; }
</style>