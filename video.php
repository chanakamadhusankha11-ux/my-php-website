<?php
require_once 'config.php';

$video_id = $_GET['id'] ?? 0;
if (!$video_id) { header('Location: index.php'); exit; }

// Increment view count
$pdo->prepare("UPDATE videos SET views = views + 1 WHERE id = ?")->execute([$video_id]);

// Fetch the main video's details
$stmt = $pdo->prepare("SELECT * FROM videos WHERE id = ?");
$stmt->execute([$video_id]);
$video = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$video) { 
    // If video not found, show a message and exit gracefully
    $page_title = 'Video Not Found';
    include 'includes/header.php';
    echo '<div class="container"><p>Sorry, the requested video could not be found.</p></div>';
    include 'includes/footer.php';
    exit;
}

// Fetch related videos (from the same category, excluding the current one)
$related_stmt = $pdo->prepare(
    "SELECT * FROM videos WHERE category_id = ? AND id != ? ORDER BY RAND() LIMIT 8"
);
$related_stmt->execute([$video['category_id'], $video_id]);
$related_videos = $related_stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = htmlspecialchars($video['title']) . ' - MYSITE'; // Set the page title
include 'includes/header.php'; // Include the header
?>

<div class="video-page-container">
    <div class="video-main-content">
        <!-- Main Video Player -->
        <div class="video-player-wrapper">
            <?php
            // Check if the URL is an iframe (like YouTube embed) or a direct video link
            if (strpos($video['video_url'], '<iframe') !== false) {
                // For iframe, we need to make it responsive
                // Extract src from iframe
                preg_match('/src="([^"]+)"/', $video['video_url'], $matches);
                $iframe_src = $matches[1] ?? '';
                // Add necessary params for autoplay and better embedding
                if ($iframe_src) {
                    echo '<iframe src="' . $iframe_src . '?autoplay=1&rel=0" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
                }
            } else {
                // It's a direct link, use a <video> tag
                echo '<video controls autoplay src="' . htmlspecialchars($video['video_url']) . '"></video>';
            }
            ?>
        </div>

        <!-- Video Info Section -->
        <div class="video-info-box">
            <h1 class="video-main-title"><?php echo htmlspecialchars($video['title']); ?></h1>
            <div class="video-stats">
                <span><?php echo number_format($video['views']); ?> views</span>
                <span>•</span>
                <span><?php echo date("M d, Y", strtotime($video['created_at'])); ?></span>
            </div>
        </div>

        <!-- Native Ad banner can go here -->
        <div class="ad-spot">
            <?php echo $GLOBALS['settings']['ad_native'] ?? ''; ?>
        </div>

    </div>

    <!-- Related Videos Sidebar -->
    <aside class="related-videos-sidebar">
        <h3>Related Videos</h3>
        <?php if (!empty($related_videos)): ?>
            <?php foreach ($related_videos as $related_video): ?>
                <div class="related-video-card">
                    <a href="video.php?id=<?php echo $related_video['id']; ?>">
                        <div class="related-thumbnail">
                            <img src="<?php echo htmlspecialchars($related_video['thumbnail_url']); ?>" alt="<?php echo htmlspecialchars($related_video['title']); ?>" loading="lazy">
                        </div>
                        <div class="related-details">
                            <h4 class="related-title"><?php echo htmlspecialchars($related_video['title']); ?></h4>
                            <p class="related-views"><?php echo number_format($related_video['views']); ?> views</p>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No related videos found.</p>
        <?php endif; ?>
    </aside>
</div>

<?php 
// This is the FIX for your error. It should be 'footer.php', not 'footer_template.php'
include 'includes/footer.php'; 
?>