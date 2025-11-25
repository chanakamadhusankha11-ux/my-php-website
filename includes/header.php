<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'MYSITE'; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Ad Codes -->
    <?php 
        if (isset($GLOBALS['settings'])) {
            echo $GLOBALS['settings']['ad_popup'] ?? '';
            echo $GLOBALS['settings']['ad_socialbar'] ?? '';
        }
    ?>
</head>
<body>

    <!-- ======================================================= -->
    <!-- AGE VERIFICATION POPUP - THIS IS THE CORRECT LOCATION -->
    <!-- ======================================================= -->
    <div class="popup-overlay" id="agreementPopup" style="display: none;">
        <div class="popup-content">
            <h2>Welcome to MYSITE</h2>
            <p>By clicking "Agree", you confirm you are over 18 and agree to our <a href="terms.php" target="_blank">Terms & Conditions</a>.</p>
            <div class="popup-buttons">
                <button id="agreeBtn" class="agree-btn">Agree & Enter</button>
                <button id="disagreeBtn" class="disagree-btn">Disagree</button>
            </div>
        </div>
    </div>
    
    <!-- Loading Animation -->
    <div class="loader-wrapper" id="loader"><div class="loader"></div></div>

    <header class="main-header">
        <div class="site-name">
            <a href="index.php">MY<span>SITE</span></a>
        </div>
        
        <nav class="main-nav">
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="terms.php" target="_blank">Terms</a></li>
            </ul>
        </nav>

        <div class="header-right">
            <div class="search-bar">
                <input type="text" id="searchInput" placeholder="Search..." autocomplete="off">
                <div id="suggestions"></div>
            </div>
            <button class="menu-toggle" id="menu-toggle">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </header>
    <main>