<?php
// Secret key to verify requests from GitHub
$secret = 'MYSITE_SECRET_123!@#'; // IMPORTANT: Change this to your own secret key

// Path to your git repository
$repo_dir = 'C:/xampp/htdocs/mysite'; // Use forward slashes

// --- SCRIPT LOGIC (Do not change below this line) ---
$hub_signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
if (empty($hub_signature)) { http_response_code(403); die('Signature is missing.'); }

$payload = file_get_contents('php://input');
list($algo, $hash) = explode('=', $hub_signature, 2);
$payload_hash = hash_hmac($algo, $payload, $secret);

if (!hash_equals($hash, $payload_hash)) { http_response_code(403); die('Signature does not match.'); }

// Execute git pull command
$command = "cd {$repo_dir} && git pull 2>&1";
$output = shell_exec($command);

// Log the process
$log_message = "[" . date('Y-m-d H:i:s') . "] --- DEPLOYMENT TRIGGERED ---\n" . $output . "\n\n";
file_put_contents('deploy_log.txt', $log_message, FILE_APPEND);

http_response_code(200);
echo "Deployment successful.";
?>