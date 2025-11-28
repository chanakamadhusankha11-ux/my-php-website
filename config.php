<?php
// config.php
$host = 'localhost';
$dbname = 'mysite_db';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database.");
}

// Fetch all settings into a global array
$settings_stmt = $pdo->query("SELECT setting_name, setting_value FROM settings");
$GLOBALS['settings'] = $settings_stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Check for maintenance mode
if ($GLOBALS['settings']['maintenance_mode'] == 'on') {
    echo '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Site Under Maintenance - CCCVVV</title>
        <style>
            :root {
                --primary-white: #FFFFFF;
                --primary-orange: #FFA500;
                --dark-orange: #FF8C00;
                --bg-black: #000000;
                --bg-dark: #1a1a1a;
                --text-light: #CCCCCC;
                --text-gray: #888888;
                --glass-bg: rgba(0, 0, 0, 0.8);
                --glass-border: rgba(255, 165, 0, 0.3);
                --glow-intensity: 0.3;
            }
            
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            html {
                font-size: 16px;
                height: 100%;
            }
            
            body {
                background: var(--bg-black);
                color: var(--primary-white);
                font-family: "Segoe UI", -apple-system, BlinkMacSystemFont, "San Francisco", "Helvetica Neue", Arial, sans-serif;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 1.25rem;
                background-image: 
                    radial-gradient(circle at 20% 80%, rgba(255, 165, 0, 0.15) 0%, transparent 50%),
                    radial-gradient(circle at 80% 20%, rgba(255, 165, 0, 0.08) 0%, transparent 50%),
                    linear-gradient(135deg, var(--bg-black) 0%, var(--bg-dark) 100%);
                overflow-x: hidden;
                line-height: 1.6;
            }
            
            .maintenance-container {
                text-align: center;
                width: 100%;
                max-width: 56.25rem; /* 900px */
                padding: clamp(2.5rem, 6vw, 4.5rem) clamp(1.5rem, 4vw, 3rem);
                border: 0.125rem solid var(--primary-orange);
                border-radius: 1.25rem;
                background: var(--glass-bg);
                backdrop-filter: blur(1.25rem);
                -webkit-backdrop-filter: blur(1.25rem);
                box-shadow: 
                    0 0 3.125rem rgba(255, 165, 0, var(--glow-intensity)),
                    inset 0 0 3.125rem rgba(255, 165, 0, 0.1);
                position: relative;
                z-index: 1;
                animation: containerGlow 3s ease-in-out infinite alternate;
                margin: 1rem;
            }
            
            @keyframes containerGlow {
                0% {
                    box-shadow: 
                        0 0 3.125rem rgba(255, 165, 0, var(--glow-intensity)),
                        inset 0 0 3.125rem rgba(255, 165, 0, 0.1);
                }
                100% {
                    box-shadow: 
                        0 0 4.375rem rgba(255, 165, 0, calc(var(--glow-intensity) + 0.2)),
                        inset 0 0 3.75rem rgba(255, 165, 0, 0.15);
                }
            }
            
            .site-logo {
                font-size: clamp(2.5rem, 8vw, 4.5rem);
                font-weight: 800;
                margin-bottom: clamp(2rem, 5vw, 3rem);
                letter-spacing: clamp(0.1rem, 2vw, 0.2rem);
                text-shadow: 0 0 1.25rem rgba(255, 165, 0, 0.6);
                line-height: 1.2;
            }
            
            .ccc {
                color: var(--primary-white);
                text-shadow: 0 0 0.625rem rgba(255, 255, 255, 0.4);
            }
            
            .vvv {
                color: var(--primary-orange);
                text-shadow: 0 0 0.9375rem rgba(255, 165, 0, 0.8);
            }
            
            .status-badge {
                display: inline-flex;
                align-items: center;
                gap: 0.75rem;
                padding: clamp(0.75rem, 2vw, 1rem) clamp(1.25rem, 3vw, 1.75rem);
                background: linear-gradient(45deg, rgba(255, 165, 0, 0.15), rgba(255, 165, 0, 0.05));
                border: 0.0625rem solid var(--primary-orange);
                border-radius: 1.875rem;
                margin-bottom: clamp(1.5rem, 4vw, 2.5rem);
                font-weight: 600;
                letter-spacing: 0.0625rem;
                font-size: clamp(0.8rem, 2.5vw, 0.9rem);
                position: relative;
                overflow: hidden;
            }
            
            .pulse-dot {
                width: clamp(0.75rem, 2vw, 1rem);
                height: clamp(0.75rem, 2vw, 1rem);
                background: var(--primary-orange);
                border-radius: 50%;
                animation: pulse 2s infinite;
                box-shadow: 0 0 0.9375rem var(--primary-orange);
                flex-shrink: 0;
            }
            
            @keyframes pulse {
                0%, 100% { 
                    opacity: 1; 
                    transform: scale(1);
                }
                50% { 
                    opacity: 0.7; 
                    transform: scale(1.3);
                }
            }
            
            h1 {
                color: var(--primary-orange);
                font-size: clamp(1.75rem, 6vw, 3rem);
                margin-bottom: clamp(1.5rem, 4vw, 2.5rem);
                text-shadow: 0 0 1.25rem rgba(255, 165, 0, 0.4);
                font-weight: 300;
                letter-spacing: 0.0625rem;
                line-height: 1.3;
            }
            
            .maintenance-message {
                font-size: clamp(1rem, 3vw, 1.25rem);
                line-height: 1.7;
                margin-bottom: clamp(2rem, 5vw, 3rem);
                color: var(--primary-white);
                max-width: 100%;
                margin-left: auto;
                margin-right: auto;
                padding: 0 0.5rem;
            }
            
            .progress-container {
                width: 100%;
                height: 0.375rem;
                background: rgba(255, 255, 255, 0.1);
                border-radius: 0.1875rem;
                margin: clamp(1.5rem, 4vw, 2.5rem) 0;
                overflow: hidden;
                position: relative;
            }
            
            .progress-bar {
                width: 65%;
                height: 100%;
                background: linear-gradient(90deg, var(--primary-orange), var(--dark-orange));
                border-radius: 0.1875rem;
                animation: progressAnimation 2s ease-in-out infinite alternate;
                position: relative;
            }
            
            @keyframes progressAnimation {
                0% { width: 65%; }
                100% { width: 72%; }
            }
            
            .features-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(min(100%, 10rem), 1fr));
                gap: clamp(0.75rem, 2vw, 1.25rem);
                margin: clamp(2rem, 5vw, 3rem) 0;
                width: 100%;
            }
            
            .feature-item {
                padding: clamp(1rem, 3vw, 1.5rem);
                background: rgba(255, 165, 0, 0.05);
                border: 0.0625rem solid rgba(255, 165, 0, 0.2);
                border-radius: 0.75rem;
                transition: all 0.3s ease;
                display: flex;
                flex-direction: column;
                align-items: center;
                min-height: 6rem;
            }
            
            .feature-item:hover {
                background: rgba(255, 165, 0, 0.1);
                border-color: rgba(255, 165, 0, 0.4);
                transform: translateY(-0.3125rem);
            }
            
            .feature-icon {
                font-size: clamp(1.5rem, 4vw, 2rem);
                margin-bottom: 0.5rem;
                color: var(--primary-orange);
            }
            
            .feature-text {
                font-size: clamp(0.8rem, 2vw, 0.9rem);
                color: var(--text-light);
                text-align: center;
                font-weight: 500;
            }
            
            .countdown {
                font-size: clamp(1rem, 3vw, 1.1rem);
                color: var(--primary-orange);
                font-weight: 600;
                margin: clamp(1rem, 3vw, 1.5rem) 0;
                text-shadow: 0 0 0.625rem rgba(255, 165, 0, 0.3);
                min-height: 1.5em;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .contact-info {
                color: var(--text-gray);
                font-size: clamp(0.8rem, 2.5vw, 0.9rem);
                margin-top: clamp(2rem, 5vw, 3rem);
                padding-top: clamp(1.5rem, 4vw, 2.5rem);
                border-top: 0.0625rem solid rgba(255, 165, 0, 0.3);
                line-height: 1.7;
            }
            
            .floating-elements {
                position: fixed;
                width: 100%;
                height: 100%;
                top: 0;
                left: 0;
                pointer-events: none;
                z-index: 0;
                overflow: hidden;
            }
            
            .floating-element {
                position: absolute;
                width: 0.25rem;
                height: 0.25rem;
                background: var(--primary-orange);
                border-radius: 50%;
                opacity: 0.4;
                animation: float 8s infinite ease-in-out;
            }
            
            @keyframes float {
                0%, 100% { 
                    transform: translateY(0) translateX(0) rotate(0deg); 
                }
                33% { 
                    transform: translateY(-1.25rem) translateX(0.625rem) rotate(120deg); 
                }
                66% { 
                    transform: translateY(0.625rem) translateX(-0.625rem) rotate(240deg); 
                }
            }
            
            /* Extra small devices (phones, 360px and down) */
            @media (max-width: 22.5rem) {
                body {
                    padding: 0.625rem;
                }
                
                .maintenance-container {
                    padding: 1.5rem 1rem;
                    margin: 0.5rem;
                    border-radius: 0.9375rem;
                }
                
                .features-grid {
                    grid-template-columns: 1fr;
                    gap: 0.75rem;
                }
                
                .feature-item {
                    min-height: 5rem;
                    padding: 1rem 0.75rem;
                }
            }
            
            /* Small devices (phones, 600px and down) */
            @media (max-width: 37.5rem) {
                .status-badge {
                    flex-direction: column;
                    gap: 0.5rem;
                    padding: 0.75rem 1rem;
                }
                
                .maintenance-message {
                    font-size: 1.1rem;
                    line-height: 1.6;
                }
            }
            
            /* Medium devices (tablets, 768px and up) */
            @media (min-width: 48rem) {
                .features-grid {
                    grid-template-columns: repeat(2, 1fr);
                }
            }
            
            /* Large devices (desktops, 1024px and up) */
            @media (min-width: 64rem) {
                .features-grid {
                    grid-template-columns: repeat(4, 1fr);
                }
                
                .maintenance-container {
                    max-width: 62.5rem;
                }
            }
            
            /* Extra large devices (large desktops, 1440px and up) */
            @media (min-width: 90rem) {
                body {
                    padding: 2rem;
                }
                
                .maintenance-container {
                    max-width: 75rem;
                    padding: 5rem 4rem;
                }
            }
            
            /* Landscape orientation for mobile */
            @media (max-height: 31.25rem) and (orientation: landscape) {
                body {
                    padding: 1rem;
                    align-items: flex-start;
                }
                
                .maintenance-container {
                    margin: 1rem auto;
                    padding: 2rem 1.5rem;
                    max-height: 90vh;
                    overflow-y: auto;
                }
                
                .site-logo {
                    margin-bottom: 1.5rem;
                }
                
                .features-grid {
                    grid-template-columns: repeat(4, 1fr);
                    gap: 0.75rem;
                    margin: 1.5rem 0;
                }
                
                .feature-item {
                    padding: 1rem 0.5rem;
                    min-height: auto;
                }
            }
            
            /* High DPI screens */
            @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
                .maintenance-container {
                    backdrop-filter: blur(1.5rem);
                    -webkit-backdrop-filter: blur(1.5rem);
                }
            }
            
            /* Reduced motion for accessibility */
            @media (prefers-reduced-motion: reduce) {
                * {
                    animation-duration: 0.01ms !important;
                    animation-iteration-count: 1 !important;
                    transition-duration: 0.01ms !important;
                }
            }
            
            /* Dark mode support (though were already dark) */
            @media (prefers-color-scheme: dark) {
                :root {
                    --glow-intensity: 0.4;
                }
            }
        </style>
    </head>
    <body>
        <div class="floating-elements">
            ' . str_repeat('<div class="floating-element" style="left:' . rand(0, 100) . '%; top:' . rand(0, 100) . '%; animation-delay: ' . rand(0, 8) . 's; animation-duration: ' . (rand(6, 12)) . 's;"></div>', 20) . '
        </div>
        
        <div class="maintenance-container">
            <div class="site-logo">
                <span class="ccc">PORN</span><span class="vvv">HUT</span>
            </div>
            
            <div class="status-badge">
                <div class="pulse-dot"></div>
                <span>MAINTENANCE IN PROGRESS</span>
            </div>
            
            <h1>Enhancing Your Experience</h1>
            
            <div class="maintenance-message">
                We\'re performing critical updates to bring you an improved video streaming experience. 
                Our team is working diligently to implement new features and ensure optimal performance for all devices.
            </div>
            
            <div class="progress-container">
                <div class="progress-bar"></div>
            </div>
            
            <div class="countdown" id="countdown">
                Estimated completion: Working actively
            </div>
            
            <div class="features-grid">
                <div class="feature-item">
                    <div class="feature-icon">⚡</div>
                    <div class="feature-text">Performance Upgrades</div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">🎬</div>
                    <div class="feature-text">Video Quality</div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">🔒</div>
                    <div class="feature-text">Security</div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">📱</div>
                    <div class="feature-text">Mobile Optimized</div>
                </div>
            </div>
            
            <div class="contact-info">
                <strong>Thank you for your patience and understanding.</strong><br>
                We\'re committed to providing the best streaming experience across all your devices.<br><br>
                &copy; ' . date('Y') . ' PORNHUT. All rights reserved.
            </div>
        </div>
        
        <script>
            // Enhanced responsive countdown
            let countdownElement = document.getElementById(\'countdown\');
            let messages = [
                "Estimated completion: Working actively",
                "Estimated completion: Almost there", 
                "Estimated completion: Final touches",
                "Estimated completion: Optimizing for all devices"
            ];
            let currentMessage = 0;
            
            function updateCountdown() {
                countdownElement.textContent = messages[currentMessage];
                currentMessage = (currentMessage + 1) % messages.length;
                
                // Adjust timing based on screen size
                const delay = window.innerWidth < 768 ? 2000 : 3000;
                setTimeout(updateCountdown, delay);
            }
            
            // Start the countdown
            updateCountdown();
            
            // Handle orientation changes
            window.addEventListener(\'orientationchange\', function() {
                // Small delay to allow orientation to complete
                setTimeout(() => {
                    window.scrollTo(0, 0);
                }, 100);
            });
            
            // Prevent zoom on double-tap (iOS)
            let lastTouchEnd = 0;
            document.addEventListener(\'touchend\', function(event) {
                const now = (new Date()).getTime();
                if (now - lastTouchEnd <= 300) {
                    event.preventDefault();
                }
                lastTouchEnd = now;
            }, false);
        </script>
    </body>
    </html>';
    exit;
}
?>