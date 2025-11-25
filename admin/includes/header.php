<?php
// This will be at the top of every admin page
require_once 'auth.php'; // Check if admin is logged in
require_once 'db.php';   // Connect to DB
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - MYSITE</title>
    <link rel="stylesheet" href="../assets/css/admin_style.css">
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'sidebar.php'; ?>
        <div class="admin-content">
            <header class="admin-header">
                <h2>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>!</h2>
                <a href="logout.php" class="logout-btn">Logout</a>
            </header>
            <main class="main-content">