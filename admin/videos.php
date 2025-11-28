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

// Get total videos count
$totalVideos = $pdo->query("SELECT COUNT(*) FROM videos")->fetchColumn();

// Handle status messages for user feedback
$status_message = '';
if (isset($_GET['status'])) {
    switch ($_GET['status']) {
        case 'added':
            $status_message = '<div class="alert alert-success">
                                <div class="alert-icon">✓</div>
                                <div class="alert-content">
                                    <strong>Success!</strong> Video successfully uploaded!
                                </div>
                              </div>';
            break;
        case 'deleted':
            $status_message = '<div class="alert alert-success">
                                <div class="alert-icon">✓</div>
                                <div class="alert-content">
                                    <strong>Success!</strong> Video successfully deleted!
                                </div>
                              </div>';
            break;
        case 'updated':
            $status_message = '<div class="alert alert-success">
                                <div class="alert-icon">✓</div>
                                <div class="alert-content">
                                    <strong>Success!</strong> Video successfully updated!
                                </div>
                              </div>';
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
} else {
    // If no search, show recent videos
    $videos = $pdo->query("SELECT v.*, c.name as category_name FROM videos v JOIN categories c ON v.category_id = c.id ORDER BY v.created_at DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
}
?>

<style>
/* Modern Dashboard Header */
.dashboard-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 30px;
    border-radius: 16px;
    margin-bottom: 30px;
    position: relative;
    overflow: hidden;
}
.dashboard-header::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 100%;
    height: 200%;
    background: rgba(255,255,255,0.1);
    transform: rotate(45deg);
}
.header-content {
    position: relative;
    z-index: 2;
}
.header-title {
    font-size: 2.2em;
    font-weight: 700;
    margin: 0 0 10px 0;
    display: flex;
    align-items: center;
    gap: 15px;
}
.header-subtitle {
    font-size: 1.1em;
    opacity: 0.9;
    margin: 0 0 25px 0;
}
.header-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 20px;
}
.stat-card {
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(10px);
    padding: 20px;
    border-radius: 12px;
    border: 1px solid rgba(255,255,255,0.2);
    text-align: center;
    transition: transform 0.3s ease;
}
.stat-card:hover {
    transform: translateY(-5px);
}
.stat-card h4 {
    margin: 0 0 8px 0;
    font-size: 0.9em;
    opacity: 0.9;
    font-weight: 500;
}
.stat-card p {
    margin: 0;
    font-size: 2.2em;
    font-weight: 700;
}

/* Enhanced Alert System */
.alert {
    display: flex;
    align-items: center;
    padding: 16px 20px;
    border-radius: 12px;
    margin-bottom: 25px;
    font-weight: 500;
    border: 1px solid;
    animation: slideIn 0.3s ease;
}
.alert-success {
    background: #f0f9f4;
    color: #0d6832;
    border-color: #b8e6cb;
}
.alert-error {
    background: #fdf2f2;
    color: #c81e1e;
    border-color: #fbd5d5;
}
.alert-warning {
    background: #fff8e6;
    color: #b54708;
    border-color: #fedf89;
}
.alert-icon {
    font-size: 20px;
    margin-right: 12px;
    font-weight: bold;
}
.alert-content {
    flex: 1;
}

/* Modern Card Design */
.card {
    background: #fff;
    padding: 30px;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    margin-bottom: 30px;
    border: 1px solid #f0f0f0;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.12);
}
.card h3 {
    margin-top: 0;
    color: #2c3e50;
    border-bottom: 2px solid #f8f9fa;
    padding-bottom: 20px;
    margin-bottom: 25px;
    font-size: 1.4em;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
}

/* Enhanced Form Styling */
.form-group {
    margin-bottom: 25px;
}
.form-group label {
    display: block;
    margin-bottom: 10px;
    font-weight: 600;
    color: #2c3e50;
    font-size: 0.95em;
}
.form-group input, .form-group select, .form-group textarea {
    width: 100%;
    padding: 14px 18px;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    font-size: 16px;
    transition: all 0.3s ease;
    background: #fafbfc;
    font-family: inherit;
}
.form-group input:focus, .form-group select:focus, .form-group textarea:focus {
    outline: none;
    border-color: #667eea;
    background: #fff;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    transform: translateY(-1px);
}

/* Enhanced Button Design */
.btn {
    padding: 14px 28px;
    border: none;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    justify-content: center;
}
.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}
.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}
.btn-secondary {
    background: #6c757d;
    color: white;
}
.btn-secondary:hover {
    background: #5a6268;
    transform: translateY(-2px);
}

/* Modern Table Design */
.table-container {
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 15px rgba(0,0,0,0.08);
    margin-top: 20px;
}
table {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
}
table th {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 18px 20px;
    text-align: left;
    font-weight: 600;
    font-size: 0.95em;
}
table td {
    padding: 18px 20px;
    border-bottom: 1px solid #f1f3f4;
    vertical-align: middle;
    transition: background 0.3s ease;
}
table tr:last-child td {
    border-bottom: none;
}
table tr:hover td {
    background: #f8f9fa;
}

/* Enhanced Video Thumbnail */
.video-thumbnail {
    width: 120px;
    height: 80px;
    object-fit: cover;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}
.video-thumbnail:hover {
    transform: scale(1.05);
}

/* Modern Action Buttons */
.actions {
    display: flex;
    gap: 10px;
}
.edit-btn, .delete-btn {
    padding: 10px 18px;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.edit-btn {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    color: white;
    box-shadow: 0 2px 10px rgba(79, 172, 254, 0.3);
}
.edit-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(79, 172, 254, 0.4);
}
.delete-btn {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
    box-shadow: 0 2px 10px rgba(245, 87, 108, 0.3);
}
.delete-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(245, 87, 108, 0.4);
}

/* Search Form Styling */
.search-form {
    display: flex;
    gap: 12px;
    align-items: end;
}
.search-form .form-group {
    flex: 1;
    margin-bottom: 0;
}

/* Empty State Design */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #6c757d;
}
.empty-state-icon {
    font-size: 4em;
    margin-bottom: 20px;
    opacity: 0.5;
}
.empty-state h4 {
    margin-bottom: 15px;
    color: #495057;
    font-size: 1.3em;
}

/* Animations */
@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive Design */
@media (max-width: 768px) {
    .dashboard-header {
        padding: 25px 20px;
    }
    .header-title {
        font-size: 1.8em;
    }
    .header-stats {
        grid-template-columns: 1fr;
    }
    .card {
        padding: 20px;
    }
    .actions {
        flex-direction: column;
    }
    .search-form {
        flex-direction: column;
    }
    .table-container {
        overflow-x: auto;
    }
}
</style>

<!-- Modern Dashboard Header -->
<div class="dashboard-header">
    <div class="header-content">
        <h1 class="header-title">
            Video Management
        </h1>
        <p class="header-subtitle">Upload, manage, and organize your video content</p>
        <div class="header-stats">
            <div class="stat-card">
                <h4>Total Videos</h4>
                <p><?php echo $totalVideos; ?></p>
            </div>
            <div class="stat-card">
                <h4>Categories</h4>
                <p><?php echo count($categories); ?></p>
            </div>
            <div class="stat-card">
                <h4>Ready to Use</h4>
                <p><?php echo $totalVideos; ?></p>
            </div>
        </div>
    </div>
</div>

<?php echo $status_message; ?>

<!-- Video Upload Form -->
<div class="card">
    <h3>📤 Upload New Video</h3>
    <form action="videos.php" method="POST">
        <input type="hidden" name="upload_video" value="1">

        <div class="form-group">
            <label for="category_id">📂 Category</label>
            <select name="category_id" id="category_id" required>
                <option value="" disabled selected>-- Select a Category --</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="title">📝 Video Title</label>
            <input type="text" name="title" id="title" placeholder="Enter video title" required>
        </div>
        
        <div class="form-group">
            <label for="video_url">🎥 Video URL (or Embed Code)</label>
            <textarea name="video_url" id="video_url" placeholder="Paste video URL or embed code" required rows="3"></textarea>
        </div>
        
        <div class="form-group">
            <label for="thumbnail_url">🖼️ Thumbnail URL</label>
            <input type="url" name="thumbnail_url" id="thumbnail_url" placeholder="https://example.com/thumbnail.jpg" required>
        </div>
        
        <div class="form-group">
            <label for="duration">⏱️ Duration (e.g., 10:35)</label>
            <input type="text" name="duration" id="duration" placeholder="00:00" required>
        </div>
        
        <div class="form-group">
            <label for="views">👀 Initial Views</label>
            <input type="number" name="views" id="views" value="0" required>
        </div>
        
        <button type="submit" class="btn btn-primary">🚀 Upload Video</button>
    </form>
</div>

<!-- Edit/Delete Section -->
<div class="card">
    <h3>🔍 Manage Videos</h3>
    
    <form action="videos.php" method="GET" class="search-form">
        <div class="form-group">
            <label for="search">Search by Video Title</label>
            <input type="text" name="search" id="search" placeholder="Enter video title to find..." value="<?php echo htmlspecialchars($search_term); ?>">
        </div>
        <button type="submit" class="btn btn-primary" style="align-self: end;">🔍 Search</button>
    </form>

    <?php if (!empty($search_term) || empty($search_term)): ?>
        <div style="margin-top: 25px;">
            <h4 style="color: #2c3e50; margin-bottom: 20px;">
                <?php if (!empty($search_term)): ?>
                    🔎 Search Results for "<?php echo htmlspecialchars($search_term); ?>"
                <?php else: ?>
                    📺 Recent Videos
                <?php endif; ?>
            </h4>
            
            <?php if (count($videos) > 0): ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>🖼️ Thumbnail</th>
                                <th>📛 Title</th>
                                <th>📂 Category</th>
                                <th>⏱️ Duration</th>
                                <th>👀 Views</th>
                                <th>⚡ Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($videos as $video): ?>
                            <tr>
                                <td>
                                    <img src="<?php echo htmlspecialchars($video['thumbnail_url']); ?>" 
                                         alt="Thumbnail" 
                                         class="video-thumbnail"
                                         onerror="this.src='https://via.placeholder.com/120x80/667eea/ffffff?text=Thumbnail'">
                                </td>
                                <td>
                                    <strong style="color: #2c3e50;">
                                        <?php echo htmlspecialchars($video['title']); ?>
                                    </strong>
                                </td>
                                <td>
                                    <span style="background: #f8f9fa; padding: 4px 8px; border-radius: 6px; font-size: 0.9em;">
                                        <?php echo htmlspecialchars($video['category_name']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($video['duration']); ?></td>
                                <td>
                                    <strong style="color: #667eea;">
                                        <?php echo number_format($video['views']); ?>
                                    </strong>
                                </td>
                                <td class="actions">
                                    <a href="edit_video.php?id=<?php echo $video['id']; ?>" class="edit-btn">
                                        ✏️ Edit
                                    </a>
                                    <form action="videos.php?search=<?php echo urlencode($search_term); ?>" method="POST" 
                                          onsubmit="return confirm('⚠️ Are you sure you want to delete \"<?php echo addslashes($video['title']); ?>\"? This action cannot be undone.');" 
                                          style="display: inline;">
                                        <input type="hidden" name="delete_video_id" value="<?php echo $video['id']; ?>">
                                        <button type="submit" class="delete-btn">
                                            🗑️ Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">🎬</div>
                    <h4>No Videos Found</h4>
                    <p><?php echo !empty($search_term) ? 'Try adjusting your search terms' : 'Start by uploading your first video above!'; ?></p>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>