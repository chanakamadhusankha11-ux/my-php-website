<?php
// mysite/admin/edit_video.php
include 'includes/header.php'; // Includes auth.php and db.php

// --- 1. GET VIDEO ID AND FETCH DATA ---
$video_id = $_GET['id'] ?? 0;

if (!$video_id || !is_numeric($video_id)) {
    // If no ID is provided or it's not a number, redirect back
    header('Location: videos.php');
    exit;
}

// --- 2. HANDLE FORM SUBMISSION (UPDATE) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_to_update = $_POST['id'];
    $category_id = $_POST['category_id'];
    $title = $_POST['title'];
    $video_url = $_POST['video_url'];
    $thumbnail_url = $_POST['thumbnail_url'];
    $duration = $_POST['duration'];
    $views = $_POST['views'];

    $stmt = $pdo->prepare(
        "UPDATE videos SET 
        category_id = ?, 
        title = ?, 
        video_url = ?, 
        thumbnail_url = ?, 
        duration = ?, 
        views = ? 
        WHERE id = ?"
    );
    $stmt->execute([$category_id, $title, $video_url, $thumbnail_url, $duration, $views, $id_to_update]);

    // Redirect back to the search results to see the change
    header('Location: videos.php?search=' . urlencode($title) . '&status=updated');
    exit;
}

// --- 3. FETCH VIDEO DATA FOR THE FORM ---
$stmt = $pdo->prepare("SELECT * FROM videos WHERE id = ?");
$stmt->execute([$video_id]);
$video = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$video) {
    // If video with that ID doesn't exist, show an error
    echo "<h1>Error: Video not found!</h1>";
    include 'includes/footer.php';
    exit;
}

// Fetch all categories for the dropdown menu
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

?>

<h1>Edit Video: <?php echo htmlspecialchars($video['title']); ?></h1>

<div class="card">
    <h3>Update Video Details</h3>
    <form action="edit_video.php?id=<?php echo $video['id']; ?>" method="POST">
        <!-- Hidden input to pass the ID on submission -->
        <input type="hidden" name="id" value="<?php echo $video['id']; ?>">

        <label for="category_id">Category:</label>
        <select name="category_id" id="category_id" required>
            <?php foreach ($categories as $category): ?>
                <option value="<?php echo $category['id']; ?>" <?php echo ($category['id'] == $video['category_id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($category['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        
        <label for="title">Video Title:</label>
        <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($video['title']); ?>" required>
        
        <label for="video_url">Video URL (e.g., direct link or embed code):</label>
        <textarea name="video_url" id="video_url" required><?php echo htmlspecialchars($video['video_url']); ?></textarea>
        
        <label for="thumbnail_url">Thumbnail URL:</label>
        <input type="url" name="thumbnail_url" id="thumbnail_url" value="<?php echo htmlspecialchars($video['thumbnail_url']); ?>" required>
        
        <label for="duration">Duration (e.g., 10:35):</label>
        <input type="text" name="duration" id="duration" value="<?php echo htmlspecialchars($video['duration']); ?>" required>
        
        <label for="views">Views:</label>
        <input type="number" name="views" id="views" value="<?php echo htmlspecialchars($video['views']); ?>" required>
        
        <button type="submit">Update Video</button>
        <a href="videos.php" style="display: inline-block; margin-left: 15px; padding: 10px 20px; background-color: #7f8c8d; color: white; text-decoration: none; border-radius: 4px;">Cancel</a>
    </form>
</div>

<?php include 'includes/footer.php'; ?>