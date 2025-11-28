<?php
// admin/categories.php
include 'includes/header.php';

// Handle form submissions (Add, Edit, Delete)
$edit_mode = false;
$category_to_edit = null;
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle DELETE
    if (isset($_POST['delete_id'])) {
        try {
            $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->execute([$_POST['delete_id']]);
            $message = '<div class="alert alert-success">
                            <div class="alert-icon">✓</div>
                            <div class="alert-content">
                                <strong>Success!</strong> Category deleted successfully.
                            </div>
                        </div>';
        } catch (Exception $e) {
            $message = '<div class="alert alert-error">
                            <div class="alert-icon">⚠</div>
                            <div class="alert-content">
                                <strong>Error!</strong> ' . $e->getMessage() . '
                            </div>
                        </div>';
        }
    }

    // Handle ADD/UPDATE
    if (isset($_POST['name']) && isset($_POST['image_url'])) {
        $cat_name = trim($_POST['name']);
        $cat_image = trim($_POST['image_url']);

        if (!empty($cat_name) && !empty($cat_image)) {
            try {
                if (isset($_POST['update_id'])) { // UPDATE
                    $stmt = $pdo->prepare("UPDATE categories SET name = ?, image_url = ? WHERE id = ?");
                    $stmt->execute([$cat_name, $cat_image, $_POST['update_id']]);
                    $message = '<div class="alert alert-success">
                                    <div class="alert-icon">✓</div>
                                    <div class="alert-content">
                                        <strong>Success!</strong> Category updated successfully.
                                    </div>
                                </div>';
                } else { // ADD
                    $stmt = $pdo->prepare("INSERT INTO categories (name, image_url) VALUES (?, ?)");
                    $stmt->execute([$cat_name, $cat_image]);
                    $message = '<div class="alert alert-success">
                                    <div class="alert-icon">✓</div>
                                    <div class="alert-content">
                                        <strong>Success!</strong> Category added successfully.
                                    </div>
                                </div>';
                }
            } catch (Exception $e) {
                $message = '<div class="alert alert-error">
                                <div class="alert-icon">⚠</div>
                                <div class="alert-content">
                                    <strong>Error!</strong> ' . $e->getMessage() . '
                                </div>
                            </div>';
            }
        } else {
            $message = '<div class="alert alert-warning">
                            <div class="alert-icon">ℹ</div>
                            <div class="alert-content">
                                <strong>Warning!</strong> Please fill in all fields.
                            </div>
                        </div>';
        }
    }
}

// Handle EDIT request (show data in form)
if (isset($_GET['edit_id'])) {
    $edit_mode = true;
    try {
        $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([$_GET['edit_id']]);
        $category_to_edit = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$category_to_edit) {
            $message = '<div class="alert alert-error">
                            <div class="alert-icon">⚠</div>
                            <div class="alert-content">
                                <strong>Error!</strong> Category not found.
                            </div>
                        </div>';
            $edit_mode = false;
        }
    } catch (Exception $e) {
        $message = '<div class="alert alert-error">
                        <div class="alert-icon">⚠</div>
                        <div class="alert-content">
                            <strong>Error!</strong> ' . $e->getMessage() . '
                        </div>
                    </div>';
        $edit_mode = false;
    }
}

// Fetch all categories to display
try {
    $categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $message = '<div class="alert alert-error">
                    <div class="alert-icon">⚠</div>
                    <div class="alert-content">
                        <strong>Error!</strong> ' . $e->getMessage() . '
                    </div>
                </div>';
    $categories = [];
}

// Get category statistics
$totalCategories = count($categories);
?>

<style>
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

/* Enhanced Card Design */
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

/* Modern Form Styling */
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
.form-group input {
    width: 100%;
    padding: 14px 18px;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    font-size: 16px;
    transition: all 0.3s ease;
    background: #fafbfc;
}
.form-group input:focus {
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
    margin-left: 12px;
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

/* Enhanced Category Image */
.category-image {
    width: 80px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}
.category-image:hover {
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
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
    box-shadow: 0 2px 10px rgba(245, 87, 108, 0.3);
}
.edit-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(245, 87, 108, 0.4);
}
.delete-btn {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
    box-shadow: 0 2px 10px rgba(231, 76, 60, 0.3);
}
.delete-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(231, 76, 60, 0.4);
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
.empty-state p {
    font-size: 1.1em;
    opacity: 0.8;
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
    .table-container {
        overflow-x: auto;
    }
}
</style>

<!-- Modern Dashboard Header -->
<div class="dashboard-header">
    <div class="header-content">
        <h1 class="header-title">
            Category Management
        </h1>
        <p class="header-subtitle">Manage and organize your video categories with ease</p>
        <div class="header-stats">
            <div class="stat-card">
                <h4>Total Categories</h4>
                <p><?php echo $totalCategories; ?></p>
            </div>
            <div class="stat-card">
                <h4>Active Status</h4>
                <p><?php echo $totalCategories; ?></p>
            </div>
            <div class="stat-card">
                <h4>Ready to Use</h4>
                <p><?php echo $totalCategories; ?></p>
            </div>
        </div>
    </div>
</div>

<?php echo $message; ?>

<!-- Add/Edit Category Card -->
<div class="card">
    <h3><?php echo $edit_mode ? '✏️ Edit Category' : '➕ Add New Category'; ?></h3>
    <form action="categories.php" method="POST">
        <?php if ($edit_mode): ?>
            <input type="hidden" name="update_id" value="<?php echo $category_to_edit['id']; ?>">
        <?php endif; ?>
        
        <div class="form-group">
            <label for="name">Category Name</label>
            <input type="text" id="name" name="name" 
                   value="<?php echo $edit_mode ? htmlspecialchars($category_to_edit['name']) : ''; ?>" 
                   placeholder="Enter a descriptive category name" required>
        </div>
        
        <div class="form-group">
            <label for="image_url">Category Image URL</label>
            <input type="url" id="image_url" name="image_url" 
                   value="<?php echo $edit_mode ? htmlspecialchars($category_to_edit['image_url']) : ''; ?>" 
                   placeholder="https://example.com/category-image.jpg" required>
        </div>
        
        <button type="submit" class="btn btn-primary">
            <?php echo $edit_mode ? '🔄 Update Category' : '➕ Add Category'; ?>
        </button>
        <?php if ($edit_mode): ?>
            <a href="categories.php" class="btn btn-secondary">❌ Cancel Edit</a>
        <?php endif; ?>
    </form>
</div>

<!-- Categories List Card -->
<div class="card">
    <h3>📂 Existing Categories</h3>
    
    <?php if (empty($categories)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">📁</div>
            <h4>No Categories Found</h4>
            <p>Get started by adding your first category above!</p>
        </div>
    <?php else: ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>🖼️ Image</th>
                        <th>📛 Category Name</th>
                        <th>⚡ Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $category): ?>
                    <tr>
                        <td>
                            <img src="<?php echo htmlspecialchars($category['image_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($category['name']); ?>" 
                                 class="category-image"
                                 onerror="this.src='https://via.placeholder.com/80x60/667eea/ffffff?text=Image'">
                        </td>
                        <td>
                            <strong style="color: #2c3e50; font-size: 1.1em;">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </strong>
                        </td>
                        <td class="actions">
                            <a href="categories.php?edit_id=<?php echo $category['id']; ?>" class="edit-btn">
                                ✏️ Edit
                            </a>
                            <form action="categories.php" method="POST" style="display: inline;" 
                                  onsubmit="return confirm('⚠️ Are you sure you want to delete \"<?php echo addslashes($category['name']); ?>\"? This action cannot be undone.');">
                                <input type="hidden" name="delete_id" value="<?php echo $category['id']; ?>">
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
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>