<?php
require_once 'config.php';

$query = $_GET['q'] ?? '';
$suggestions = [];

if (strlen($query) > 1) {
    // Search in categories
    $stmt = $pdo->prepare("SELECT id, name FROM categories WHERE name LIKE ? LIMIT 3");
    $stmt->execute(['%' . $query . '%']);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $suggestions[] = [
            'title' => 'Category: ' . $row['name'],
            'url' => 'category.php?id=' . $row['id']
        ];
    }

    // Search in videos
    $stmt = $pdo->prepare("SELECT id, title FROM videos WHERE title LIKE ? LIMIT 5");
    $stmt->execute(['%' . $query . '%']);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $suggestions[] = [
            'title' => $row['title'],
            'url' => 'video.php?id=' . $row['id']
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($suggestions);
?>