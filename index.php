<?php 
require_once 'config.php';
$page_title = 'MYSITE - Home'; // For the <title> tag
include 'includes/header.php';

$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <h1 class="section-title">Explore Categories</h1>
    
    <div class="item-grid">
        <?php if (!empty($categories)): ?>
            <?php foreach ($categories as $category): ?>
                <div class="item-card">
                    <a href="category.php?id=<?php echo $category['id']; ?>" class="loader-trigger">
                        <div class="item-thumbnail">
                            <img src="<?php echo htmlspecialchars($category['image_url']); ?>" alt="<?php echo htmlspecialchars($category['name']); ?>" loading="lazy">
                        </div>
                        <div class="item-details">
                            <h4 class="item-title"><?php echo htmlspecialchars($category['name']); ?></h4>
                            <!-- No views count for categories -->
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="grid-column: 1 / -1; text-align: center;">No categories found.</p>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>