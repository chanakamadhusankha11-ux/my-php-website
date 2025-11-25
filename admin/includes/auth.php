<?php
// admin/includes/auth.php
session_start();
if (!isset($_SESSION['admin_loggedin'])) {
    header('Location: index.php');
    exit;
}
?>