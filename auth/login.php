<?php
session_start();

/** * NaMeAnj - Secure Student Login
 * Allows expired users to log in but sets a session flag for restricted access.
 */

// 1. AUTO-LOGIN REDIRECT
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    header("Location: ../app/dashboard.php");
    exit();
}

$user_db = __DIR__ . '/../database/users.json';
$error = "";

// 2. GENERATE NEW CAPTCHA
if (!isset($_SESSION['captcha_ans']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $n1 = rand(1, 9);
    $n2 = rand(1, 9);
    $_SESSION['captcha_ans'] = $n1 + $n2;
    $_SESSION['captcha_text'] = "$n1 + $n2 = ?";
}

// 3. PROCESS LOGIN ATTEMPT
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $user_captcha = $_POST['captcha'] ?? '';

    // Verify Captcha
    if ((int)$user_captcha !== $_SESSION['captcha_ans']) {
        $error = "Incorrect captcha answer!";
    } else {
        if (file_exists($user_db)) {
            $users = json_decode(file_get_contents($user_db), true);
            $found = false;
            $users_updated = false;
            
            foreach ($users as &$user) {
                if ($user['username'] === $username) {
                    // Check if password matches (either plaintext for old users or hashed for new)
                    if (password_verify($password, $user['password']) || $user['password'] === $password) {
                        
                        // Seamless upgrade: if it was plaintext, hash it and save!
                        if ($user['password'] === $password && !password_get_info($user['password'])['algo']) {
                            $user['password'] = password_hash($password, PASSWORD_DEFAULT);
                            $users_updated = true;
                        }

                        $found = true;
                        
                        // Check Membership Status
                        $today = date('Y-m-d');
                        $is_expired = ($user['valid_until'] < $today);

                        // Prevent Session Fixation
                        session_regenerate_id(true);

                        // Set Sessions
                        $_SESSION['user_logged_in'] = true;
                        $_SESSION['username'] = $username;
                        $_SESSION['is_expired'] = $is_expired;

                        // Save DB if we upgraded a password
                        if ($users_updated) {
                            file_put_contents($user_db, json_encode($users, JSON_PRETTY_PRINT), LOCK_EX);
                        }

                        header("Location: ../app/dashboard.php");
                        exit();
                    }
                }
            }
            if (!$found) $error = "Invalid username or password!";
        } else {
            $error = "Critical Error: User database not found.";
        }
    }
    
    // Refresh captcha after attempt
    $n1 = rand(1, 9);
    $n2 = rand(1, 9);
    $_SESSION['captcha_ans'] = $n1 + $n2;
    $_SESSION['captcha_text'] = "$n1 + $n2 = ?";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://nameanj.ct.ws/">
    <meta property="og:description" content="Create & generate ultra-high resolution A3 PDFs for school slips. Features include cloud storing for easy access from any device and simple management.">
    <meta property="og:image" content="https://nameanj.ct.ws/images/share-banner.png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:description" content="Generate high-resolution A3 PDFs for school slips with cloud storage and simple management.">
    <meta name="twitter:image" content="https://nameanj.ct.ws/images/share-banner.png">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/images/icon.png">
    <title>Login | NaMeAnj</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Quicksand:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root { 
            --blue: #2193b0; 
            --rose: #ff1493; 
            --light: #f0f4f8; 
        }
        
        body { 
            font-family: 'Quicksand', sans-serif; 
            background: linear-gradient(135deg, #ffafbd, #ffc3a0, #2193b0, #6dd5ed);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh; 
            margin: 0; 
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .login-box { 
            background: rgba(255, 255, 255, 0.85); 
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            padding: 40px; 
            border-radius: 35px; 
            box-shadow: 0 15px 35px rgba(0,0,0,0.1); 
            width: 90%; 
            max-width: 380px; 
            text-align: center; 
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-bottom: 8px solid var(--blue);
            transition: transform 0.3s ease;
        }

        .login-box:hover {
            transform: translateY(-5px);
        }

        .logo { 
            font-family: 'Fredoka One', cursive; 
            color: var(--rose); 
            font-size: 2.2rem; 
            margin-bottom: 5px; 
        }

        .subtitle { 
            color: #888; 
            font-size: 0.9rem; 
            margin-bottom: 20px; 
            font-weight: 600; 
        }
        
        .info-text { 
            font-size: 0.85rem; 
            color: #64748b; 
            line-height: 1.5; 
            margin-bottom: 20px; 
            text-align: left; 
            background: #f1f5f9; 
            padding: 12px; 
            border-radius: 15px; 
        }

        input { 
            width: 100%; 
            padding: 14px; 
            margin: 8px 0; 
            border: 2px solid #edf2f7; 
            border-radius: 15px; 
            outline: none; 
            box-sizing: border-box; 
            font-size: 1rem; 
            font-family: inherit;
        }

        input:focus { border-color: var(--blue); background: #f9ffff; }

        .captcha-row { 
            display: flex; 
            align-items: center; 
            gap: 10px; 
            margin: 10px 0;
            background: #f8fafc; 
            padding: 10px; 
            border-radius: 15px; 
            border: 1px dashed var(--blue);
        }

        .captcha-question { 
            font-weight: 700; 
            color: var(--blue); 
            font-size: 1.1rem; 
            min-width: 80px; 
        }

        button { 
            width: 100%; 
            padding: 16px; 
            border: none; 
            border-radius: 15px; 
            background: var(--blue); 
            color: white; 
            font-weight: bold; 
            font-size: 1rem;
            cursor: pointer; 
            margin-top: 10px; 
            transition: 0.3s;
        }

        button:hover { background: #1a7a94; transform: translateY(-2px); }

        .footer-links { 
            margin-top: 20px; 
            border-top: 1px solid #eee; 
            padding-top: 15px; 
            display: flex; 
            flex-direction: column; 
            gap: 10px; 
        }

        .link { text-decoration: none; color: var(--blue); font-weight: 700; font-size: 0.9rem; }
        .link:hover { color: var(--rose); }

        .error { 
            color: var(--rose); 
            font-size: 0.85rem; 
            margin: 10px 0; 
            font-weight: 700; 
            background: #fff5f5;
            padding: 8px;
            border-radius: 10px;
        }
    </style>
</head>
<body>

<div class="login-box">
    <div class="logo">NaMeAnj</div>
    <p class="subtitle">Student Portal Login</p>

    <div class="info-text">
        Welcome back! Log in to generate your creative school name slips and manage your library.
    </div>

    <form method="POST">
        <input type="text" name="username" placeholder="Username" required autocomplete="username">
        <input type="password" name="password" placeholder="Password" required autocomplete="current-password">
        
        <div class="captcha-row">
            <div class="captcha-question"><?php echo $_SESSION['captcha_text']; ?></div>
            <input type="number" name="captcha" placeholder="Answer" required>
        </div>

        <?php if ($error): ?> 
            <div class="error">⚠️ <?php echo htmlspecialchars($error); ?></div> 
        <?php endif; ?>

        <button type="submit">Unlock Access</button>
    </form>

    <div class="footer-links">
        <a href="signup.php" class="link" style="color: var(--rose);">✨ Create New Account</a>
        <a href="../index.php" class="link">🏠 Back to Home</a>
        <a href="developer.php" class="link">👨‍💻 Contact Admin</a>
    </div>
</div>

</body>
</html>


