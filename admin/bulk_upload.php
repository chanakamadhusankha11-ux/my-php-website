<?php
session_start();
include '../db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$categories_result = mysqli_query($conn, "SELECT id, name FROM categories ORDER BY name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk Video Upload</title>
    <link rel="stylesheet" href="css/style.css"> 
</head>
<body>
    <div class="container">
        
        <?php include 'admin-include/sidebar.php'; // ඔයාගේ හරි path එක ?>
        
        <div class="main-content">
            <div class="header">
                <h3>Bulk Video Upload From Excel</h3>
            </div>
            
            <div class="content-wrapper">
                <div class="form-container">
                    
                    <div class="instructions">
                        <h4>උපදෙස්:</h4>
                        <p>1. පළමුව, Videos ටික Upload කල යුතු Category එක තෝරන්න.</p>
                        <p>2. දෙවනුව, නිවැරදි Format එකට සෑදූ Excel (.xlsx) ගොනුව තෝරන්න.</p>
                        <p><b>Excel Columns:</b> Video Link | Thumbnail Link | Title | Duration</p>
                    </div>

                    <?php
                    if (isset($_SESSION['upload_message'])) {
                        $msg_class = (strpos(strtolower($_SESSION['upload_message']), 'error') !== false) ? 'msg-error' : 'msg-success';
                        echo '<div class="message ' . $msg_class . '">' . $_SESSION['upload_message'] . '</div>';
                        unset($_SESSION['upload_message']);
                    }
                    ?>
                    
                    <form action="upload_process.php" method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="category_id">Category එක තෝරන්න:</label>
                            <select name="category_id" id="category_id" required>
                                <option value="">-- Choose a Category --</option>
                                <?php
                                while ($category = mysqli_fetch_assoc($categories_result)) {
                                    echo '<option value="' . $category['id'] . '">' . htmlspecialchars($category['name']) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="excel_file">Excel ගොනුව (.xlsx) තෝරන්න:</label>
                            <input type="file" name="excel_file" id="excel_file" accept=".xlsx, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required>
                        </div>
                        <button type="submit" name="upload_btn" class="btn">Upload and Process</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<style>
/* මේ style ටික ඔයාගේ style.css එකටම දාගන්නත් පුළුවන් */
.instructions { background: #333; padding: 15px; border-radius: 5px; margin-bottom: 20px; border-left: 3px solid #ff8c00; color: #f1f1f1; font-size: 14px; }
.message { padding: 10px 15px; border-radius: 5px; margin-bottom: 20px; color: #fff; }
.msg-success { background-color: #28a745; }
.msg-error { background-color: #dc3545; }
/* ඔයාගේ style.css එකේ මේ class එක නැති නිසා මම මෙතනට දානවා */
.form-container { background: #2a2a2a; padding: 20px; border-radius: 5px; } 
</style>