<?php
// admin/includes/db.php

// This file is now configured for Render.com's PostgreSQL
// It will automatically read the DATABASE_URL environment variable

try {
    // Get the database URL from Render's environment variables
    $database_url = getenv("DATABASE_URL");

    // If the DATABASE_URL is not set, it means we are likely on a local machine (XAMPP)
    if ($database_url === false) {
        // --- LOCAL XAMPP SETTINGS ---
        // For local development, use your original MySQL settings
        $host = 'localhost';
        $dbname = 'mysite_db';
        $user = 'root';
        $pass = ''; // XAMPP default password is empty
        $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8";

    } else {
        // --- RENDER.COM LIVE SERVER SETTINGS ---
        // Parse the database URL provided by Render
        $db_parts = parse_url($database_url);

        $host = $db_parts["host"];
        $port = $db_parts["port"];
        $user = $db_parts["user"];
        $pass = $db_parts["pass"];
        $dbname = ltrim($db_parts["path"], "/");
        
        // Create the DSN string for a PostgreSQL connection
        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    }
    
    // Create the PDO instance
    $pdo = new PDO($dsn, $user, $pass);

    // Set the PDO error mode to exception for better error handling
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    // If connection fails, stop the script and show an error message
    die("Database connection failed: " . $e->getMessage());
}
?>