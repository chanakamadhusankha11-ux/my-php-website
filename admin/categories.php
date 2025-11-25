<?php
// admin/categories.php
include 'includes/header.php';

// Handle form submissions (Add, Edit, Delete)
$edit_mode = false;
$category_to_edit = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle DELETE
    if (isset($_POST['delete_id'])) {
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$_POST['delete_id']]);
        header("Location: categories.php"); exit;
    }

    // Handle ADD/UPDATE
    $cat_name = $_POST['name'];
    $cat_image = $_POST['image_url'];

    if (isset($_POST['update_id'])) { // UPDATE
        $stmt = $pdo->prepare("UPDATE categories SET name = ?, image_url = ? WHERE id = ?");
        $stmt->execute([$cat_name, $cat_image, $_POST['update_id']]);
    } else { // ADD
        $stmt = $pdo->prepare("INSERT INTO categories (name, image_url) VALUES (?, ?)");
        $stmt->execute([$cat_name, $cat_image]);
    }
    header("Location: categories.php"); exit;
}

// Handle EDIT request (show data in form)
if (isset($_GET['edit_id'])) {
    $edit_mode = true;
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$_GET['edit_id']]);
    $category_to_edit = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fetch all categories to display
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<h1>Manage Categories</h1>

<div class="card">
    <h3><?php echo $edit_mode ? 'Edit Category' : 'Add New Category'; ?></h3>
    <form action="categories.php" method="POST">
        <?php if ($edit_mode): ?>
            <input type="hidden" name="update_id" value="<?php echo $category_to_edit['id']; ?>">
        <?php endif; ?>
        
        <label for="name">Category Name:</label>
        <input type="text" id="name" name="name" value="<?php echo $edit_mode ? htmlspecialchars($category_to_edit['name']) : ''; ?>" required>
        
        <label for="image_url">Category Image URL:</label>
        <input type="url" id="image_url" name="image_url" value="<?php echo $edit_mode ? htmlspecialchars($category_to_edit['image_url']) : ''; ?>" required>
        
        <button type="submit"><?php echo $edit_mode ? 'Update Category' : 'Add Category'; ?></button>
        <?php if ($edit_mode): ?>
            <a href="categories.php" style="margin-left: 10px; text-decoration: none; color: #777;">Cancel Edit</a>
        <?php endif; ?>
    </form>
</div>

<div class="card">
    <h3>Existing Categories</h3>
    <table>
        <thead>
            <tr>
                <th>Image</th>
                <th>Name</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $category): ?>
            <tr>
                <td><img src="<?php echo htmlspecialchars($category['image_url']); ?>" alt="<?php echo htmlspecialchars($category['name']); ?>" style="width: 100px; height: auto;"></td>
                <td><?php echo htmlspecialchars($category['name']); ?></td>
                <td class="actions">
                    <a href="categories.php?edit_id=<?php echo $category['id']; ?>" class="edit-btn">Edit</a>
                    <form action="categories.php" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this category? All videos in it will also be deleted!');">
                        <input type="hidden" name="delete_id" value="<?php echo $category['id']; ?>">
                        <button type="submit" class="delete-btn">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'; ?>