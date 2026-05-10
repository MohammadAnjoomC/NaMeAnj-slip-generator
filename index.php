<?php
session_start();

/** * NaMeAnj - Landing Page Logic
 * Detects if a user session is active to display personal info in the header.
 */
$is_logged_in = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;

if ($is_logged_in) {
    $username = $_SESSION['username'];
    $user_dir = "user/" . $username;
    $profile_pic = $user_dir . "/profile.jpg";
    
    // Fallback avatar if no profile picture exists
    if (!file_exists($profile_pic)) {
        $profile_pic = "https://ui-avatars.com/api/?name=" . urlencode($username) . "&background=ffafcc&color=fff";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta property="og:type" content="website">
<meta property="og:url" content="https://nameanj.ct.ws/">
<meta property="og:description" content="Create & generate ultra-high resolution A3 PDFs for school slips. Features include cloud storing for easy access from any device and a management system designed to be very simple for parents and teachers. Fast and mobile-friendly interface.">

<meta property="og:image" content="https://nameanj.ct.ws/images/share-banner.png">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:description" content="Generate high-resolution A3 PDFs for school slips with cloud storage and simple management.">
<meta name="twitter:image" content="https://nameanj.ct.ws/images/share-banner.png">

<link rel="icon" type="image/png" href="/images/icon.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NaMeAnj | Cute School Slips Tool</title>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Quicksand:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --rose: #ffafbd;
            --soft-pink: #ffc3a0;
            --accent: #ff1493;
            --white: #ffffff;
            --wa-green: #25D366;
        }

        body {
            margin: 0;
            font-family: 'Quicksand', sans-serif;
            /* Animated Pink Gradient */
            background: linear-gradient(135deg, var(--rose), var(--soft-pink), #eef2f3);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            color: #333;
            user-select: none; /* Security: Disable text selection */
            overflow-x: hidden;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Navbar Styling */
        nav {
            padding: 15px 8%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255,255,255,0.3);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .logo {
            font-family: 'Fredoka One', cursive;
            font-size: 1.8rem;
            color: var(--accent);
            text-decoration: none;
            text-shadow: 1px 1px white;
        }

        /* Nav Action Buttons */
        .profile-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            background: white;
            padding: 5px 15px 5px 5px;
            border-radius: 50px;
            text-decoration: none;
            color: #444;
            font-weight: 700;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            transition: 0.3s;
        }
        .profile-btn:hover { transform: translateY(-2px); }

        .nav-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--rose);
        }

        .login-link {
            background: var(--accent);
            color: white;
            padding: 10px 25px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 700;
            box-shadow: 0 4px 15px rgba(255, 20, 147, 0.3);
            transition: 0.3s;
        }

        /* Hero Content */
        .hero {
            height: 65vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 0 20px;
        }

        .hero h1 {
            font-family: 'Fredoka One', cursive;
            font-size: clamp(2.5rem, 8vw, 4.2rem);
            color: #444;
            margin: 0;
        }

        .btn-main {
            text-decoration: none;
            padding: 18px 50px;
            border-radius: 50px;
            background: var(--accent);
            color: white;
            font-weight: 700;
            font-size: 1.2rem;
            margin-top: 35px;
            transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 10px 25px rgba(255, 20, 147, 0.4);
        }
        .btn-main:hover { transform: scale(1.08); }

        /* Footer & Developer Info */
        .footer-info {
            background: white;
            padding: 50px 20px;
            text-align: center;
            border-radius: 60px 60px 0 0;
            box-shadow: 0 -10px 40px rgba(0,0,0,0.03);
        }

        .membership-card {
            display: inline-block;
            background: #fff5f7;
            padding: 20px 30px;
            border-radius: 25px;
            border: 2px dashed var(--rose);
            margin-bottom: 30px;
        }

        .wa-link {
            color: var(--wa-green);
            text-decoration: none;
            font-weight: 800;
            font-size: 1.1rem;
            display: block;
            margin-top: 5px;
        }

        .dev-link {
            color: var(--accent);
            text-decoration: none;
            font-weight: 700;
            transition: 0.3s;
        }
        .dev-link:hover { text-decoration: underline; }

        .bottom-nav {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            gap: 20px;
            font-size: 0.9rem;
        }
        .bottom-nav a { color: #aaa; text-decoration: none; }
    </style>
</head>
<body ondragstart="return false;" ondrop="return false;">

    <nav>
        <a href="index.php" class="logo">NaMeAnj 🎨</a>
        <div>
            <?php if ($is_logged_in): ?>
                <a href="app/dashboard.php" class="profile-btn">
                    <img src="<?php echo $profile_pic; ?>" class="nav-avatar">
                    <span class="user-txt"><?php echo htmlspecialchars($username); ?></span>
                </a>
            <?php else: ?>
                <a href="auth/login.php" class="login-link">Login</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="hero">
        <h1>Beautiful School Slips!</h1>
        <p style="font-size: 1.2rem; color: #666; max-width: 500px;">Create Ultra High Quality name slips for your books in seconds. Fast, easy, and super cute!</p>
        
        <a href="<?php echo $is_logged_in ? 'app/dashboard.php' : 'auth/login.php'; ?>" class="btn-main">
            <?php echo $is_logged_in ? 'Go to Dashboard 🚀' : 'Start Designing 🚀'; ?>
        </a>
    </div>

    <footer class="footer-info">
        <div class="membership-card">
            <p style="margin:0; font-weight: 700; color: var(--accent);">📢 NaMeAnj Membership Access</p>
            <a href="https://wa.me/918848643715?text=Hello%20Anjoom,%20I%20want%20to%20get%20NaMeAnj%20Membership" 
               class="wa-link" target="_blank">
                WhatsApp: +91 8848643715 💬
            </a>
        </div>

        <p>Developed by <a href="pages/developer.php" class="dev-link">Mohammad Anjoom C</a></p>
        
        <div class="bottom-nav">
            <a href="pages/terms.php">Terms</a>
            <a href="pages/about.php">About Website</a>
            <a href="admin/login.php" style="font-size: 0.7rem; color: #eee;">Admin</a>
        </div>
        
        <p style="font-size: 0.75rem; color: #ccc; margin-top: 30px;">&copy; 2026 NaMeAnj Portal | All rights reserved.</p>
    </footer>

</body>
</html>


