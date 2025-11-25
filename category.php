<?php 
require_once 'config.php';

$category_id = $_GET['id'] ?? 0;
if (!$category_id) { header('Location: index.php'); exit; }

$stmt = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
$stmt->execute([$category_id]);
$category_name = $stmt->fetchColumn();
if (!$category_name) { header('Location: index.php'); exit; }

$page_title = htmlspecialchars($category_name) . ' - MYSITE'; // Set page title
include 'includes/header.php';

$stmt = $pdo->prepare("SELECT * FROM videos WHERE category_id = ? ORDER BY created_at DESC");
$stmt->execute([$category_id]);
$videos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <h1 class="section-title"><?php echo htmlspecialchars($category_name); ?></h1>

    <div class="item-grid">
        <?php if (!empty($videos)): ?>
            <?php foreach ($videos as $video): ?>
                <div class="item-card">
                    <a href="video.php?id=<?php echo $video['id']; ?>" target="_blank">
                        <div class="item-thumbnail">
                            <img src="<?php echo htmlspecialchars($video['thumbnail_url']); ?>" alt="<?php echo htmlspecialchars($video['title']); ?>" loading="lazy">
                            <?php if (!empty($video['duration'])): ?>
                                <span class="duration"><?php echo htmlspecialchars($video['duration']); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="item-details">
                            <h4 class="item-title"><?php echo htmlspecialchars($video['title']); ?></h4>
                            <p class="item-views"><?php echo number_format($video['views']); ?> views</p>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="grid-column: 1 / -1; text-align: center; padding: 50px 0;">No videos in this category yet.</p>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>