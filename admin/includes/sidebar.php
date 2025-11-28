<aside class="sidebar">
    <h3>Control Panel</h3>
    <nav>
        <ul>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="categories.php">Categories</a></li>
            <li><a href="videos.php">Videos</a></li>
            <li><a href="settings.php">Site Settings</a></li>
            <li><a href="ads.php">Ads Manager</a></li>
            
            <?php 
            // This link will ONLY be visible if the logged-in user's role is 'superadmin'
            if (isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'superadmin'): 
            ?>
                <li style="border-top: 1px solid #34495e; margin-top: 10px; padding-top: 10px;">
                    <a href="manage_admins.php">Manage Admins</a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
</aside>