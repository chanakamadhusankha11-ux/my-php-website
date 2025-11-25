<?php
// mysite/admin/videos.php
include 'includes/header.php'; // Includes auth.php and db.php

// --- ACTION HANDLING ---

// Handle video DELETION
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_video_id'])) {
    $stmt = $pdo->prepare("DELETE FROM videos WHERE id = ?");
    $stmt->execute([$_POST['delete_video_id']]);
    // Redirect back with a status message and keep the search term if it exists
    $search_param = isset($_GET['search']) ? '?search=' . urlencode($_GET['search']) . '&status=deleted' : '?status=deleted';
    header("Location: videos.php" . $search_param); 
    exit;
}

// Handle video ADDITION
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload_video'])) {
    $category_id = $_POST['category_id'];
    $title = $_POST['title'];
    $video_url = $_POST['video_url'];
    $thumbnail_url = $_POST['thumbnail_url'];
    $duration = $_POST['duration'];
    $views = $_POST['views'];

    $stmt = $pdo->prepare("INSERT INTO videos (category_id, title, video_url, thumbnail_url, duration, views) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$category_id, $title, $video_url, $thumbnail_url, $duration, $views]);
    header("Location: videos.php?status=added"); 
    exit;
}

// --- DATA FETCHING & DISPLAY ---

// Fetch categories for the dropdown menu
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

// Handle status messages for user feedback
$status_message = '';
if (isset($_GET['status'])) {
    switch ($_GET['status']) {
        case 'added':
            $status_message = '<div class="status-msg success">Video successfully uploaded!</div>';
            break;
        case 'deleted':
            $status_message = '<div class="status-msg success">Video successfully deleted!</div>';
            break;
        case 'updated':
            $status_message = '<div class="status-msg success">Video successfully updated!</div>';
            break;
    }
}

// Search logic: Fetch videos based on search term
$search_term = $_GET['search'] ?? '';
$videos = [];
if (!empty($search_term)) {
    $stmt = $pdo->prepare("SELECT v.*, c.name as category_name FROM videos v JOIN categories c ON v.category_id = c.id WHERE v.title LIKE ? ORDER BY v.created_at DESC");
    $stmt->execute(['%' . $search_term . '%']);
    $videos = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!-- Some inline CSS for status messages -->
<style>
    .status-msg { padding: 15px; margin-bottom: 20px; border-radius: 5px; color: #fff; }
    .status-msg.success { background-color: #2ecc71; }
    .status-msg.error { background-color: #e74c3c; }
</style>

<h1>Manage Videos</h1>

<?php echo $status_message; // Display status message if it exists ?>

<!-- Video Upload Form -->
<div class="card">
    <h3>Upload New Video</h3>
    <form action="videos.php" method="POST">
        <input type="hidden" name="upload_video" value="1">

        <label for="category_id">Category:</label>
        <select name="category_id" id="category_id" required>
            <option value="" disabled selected>-- Select a Category --</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
            <?php endforeach; ?>
        </select>
        
        <label for="title">Video Title:</label>
        <input type="text" name="title" id="title" required>
        
        <label for="video_url">Video URL (or Embed Code):</label>
        <textarea name="video_url" id="video_url" required></textarea>
        
        <label for="thumbnail_url">Thumbnail URL:</label>
        <input type="url" name="thumbnail_url" id="thumbnail_url" required>
        
        <label for="duration">Duration (e.g., 10:35):</label>
        <input type="text" name="duration" id="duration" required>
        
        <label for="views">Initial Views:</label>
        <input type="number" name="views" id="views" value="0" required>
        
        <button type="submit">Upload Video</button>
    </form>
</div>

<!-- Edit/Delete Section -->
<div class="card">
    <h3>Edit or Delete a Video</h3>
    <form action="videos.php" method="GET">
        <label for="search">Search by Video Title:</label>
        <input type="text" name="search" id="search" placeholder="Enter video title to find..." value="<?php echo htmlspecialchars($search_term); ?>">
        <button type="submit">Search</button>
    </form>

    <?php if (!empty($search_term)): ?>
        <hr style="margin: 20px 0; border: 0; border-top: 1px solid #eee;">
        <h4>Search Results for "<?php echo htmlspecialchars($search_term); ?>"</h4>
        
        <?php if (count($videos) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Thumbnail</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($videos as $video): ?>
                    <tr>
                        <td><img src="<?php echo htmlspecialchars($video['thumbnail_url']); ?>" alt="Thumbnail" style="width: 120px; border-radius: 4px;"></td>
                        <td><?php echo htmlspecialchars($video['title']); ?></td>
                        <td><?php echo htmlspecialchars($video['category_name']); ?></td>
                        <td class="actions">
                            <!-- This is the completed Edit link -->
                            <a href="edit_video.php?id=<?php echo $video['id']; ?>" class="edit-btn">Edit</a>
                            
                            <!-- This is the completed Delete form -->
                            <form action="videos.php?search=<?php echo urlencode($search_term); ?>" method="POST" onsubmit="return confirm('Are you sure you want to delete this video? This action cannot be undone.');" style="display: inline;">
                                <input type="hidden" name="delete_video_id" value="<?php echo $video['id']; ?>">
                                <button type="submit" class="delete-btn">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="margin-top: 20px;">No videos found matching your search term.</p>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>