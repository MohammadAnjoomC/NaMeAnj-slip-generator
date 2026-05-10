<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboard.php");
    exit();
}

// Logic to generate a new captcha if one doesn't exist or after a failed attempt
if (!isset($_SESSION['captcha_result']) || $_SERVER["REQUEST_METHOD"] != "POST") {
    $num1 = rand(1, 9);
    $num2 = rand(1, 9);
    $_SESSION['captcha_num1'] = $num1;
    $_SESSION['captcha_num2'] = $num2;
    $_SESSION['captcha_result'] = $num1 + $num2;
}

$db_folder = __DIR__ . '/../database';
$admin_db = $db_folder . '/admin.json';

if (!is_dir($db_folder)) {
    mkdir($db_folder, 0777, true);
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $user_captcha = isset($_POST['captcha']) ? intval($_POST['captcha']) : 0;

    // 1. Check Captcha First
    if ($user_captcha !== $_SESSION['captcha_result']) {
        $error = "Captcha incorrect! Are you a human?";
        // Generate new numbers for next attempt
        $num1 = rand(1, 9);
        $num2 = rand(1, 9);
        $_SESSION['captcha_num1'] = $num1;
        $_SESSION['captcha_num2'] = $num2;
        $_SESSION['captcha_result'] = $num1 + $num2;
    } else {
        // 2. Check Admin Credentials
        if (file_exists($admin_db)) {
            $admin_data = json_decode(file_get_contents($admin_db), true);
            
            if ($email === $admin_data['email'] && $password === $admin_data['password']) {
                session_regenerate_id();
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_user'] = "Anju";
                
                // Clear captcha after success
                unset($_SESSION['captcha_result']);
                
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Access Denied! Incorrect email or password.";
            }
        } else {
            $error = "Critical Error: admin.json is missing.";
        }
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
    <title>Admin Login | NaMeAnj</title>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Quicksand:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --admin-blue: #2193b0; --admin-pink: #ff1493; }
        body {
            font-family: 'Quicksand', sans-serif;
            background: linear-gradient(135deg, #a1c4fd, #c2e9fb, #ffafbd, #ffc3a0);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            height: 100vh; display: flex; justify-content: center; align-items: center; margin: 0;
            user-select: none;
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
            padding: 40px; border-radius: 25px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            width: 100%; max-width: 380px; text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.5); 
            position: relative;
            border-bottom: 4px solid var(--admin-pink);
            transition: transform 0.3s ease;
        }

        .login-box:hover {
            transform: translateY(-5px);
        }

        .login-box::before {
            content: '🔒'; font-size: 2rem; position: absolute;
            top: -25px; left: 50%; transform: translateX(-50%);
            background: rgba(255, 255, 255, 0.9); padding: 5px 10px;
            border-radius: 50%; box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        h2 { font-family: 'Fredoka One', cursive; color: var(--admin-pink); margin-bottom: 25px; margin-top: 10px; }

        .form-group { text-align: left; margin-bottom: 15px; }
        label { display: block; margin-bottom: 8px; font-weight: 700; color: #444; font-size: 0.9rem; }
        input { 
            width: 100%; padding: 12px; border: 2px solid #eee; border-radius: 12px; 
            box-sizing: border-box; transition: 0.3s;
        }
        input:focus { border-color: var(--admin-blue); outline: none; background: #f9ffff; }

        /* Captcha Styling */
        .captcha-row {
            display: flex; align-items: center; gap: 10px;
            background: #fdf2f5; padding: 10px; border-radius: 12px;
            margin-bottom: 20px; border: 1px dashed var(--admin-pink);
        }
        .captcha-question {
            font-weight: 700; color: var(--admin-pink); font-size: 1.1rem;
            min-width: 80px; text-align: center;
        }
        .captcha-input { width: 80px !important; text-align: center; }

        .btn-login {
            background: var(--admin-pink); color: white; border: none; padding: 14px;
            width: 100%; border-radius: 12px; font-weight: bold; cursor: pointer;
            box-shadow: 0 4px 10px rgba(255, 20, 147, 0.3);
        }
        .btn-login:hover { background: #d0107a; transform: translateY(-2px); }

        .error-box {
            background: #fff0f0; color: #d32f2f; padding: 12px;
            border-radius: 10px; margin-bottom: 20px; font-size: 0.85rem;
            border-left: 5px solid #d32f2f; text-align: left;
        }
        .footer-links { margin-top: 25px; display: flex; justify-content: center; gap: 15px; font-size: 0.85rem; }
        .footer-links a { color: var(--admin-blue); text-decoration: none; font-weight: 600; }
    </style>
</head>
<body ondragstart="return false;" ondrop="return false;">

<div class="login-box">
    <h2>Admin Login</h2>
    
    <?php if ($error): ?>
        <div class="error-box"><?php echo $error; ?></div>
    <?php endif; ?>

    <form action="" method="POST" autocomplete="off">
        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" placeholder="admin@anj.ct.ws" required>
        </div>
        
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" placeholder="••••••••" required>
        </div>

        <label style="text-align: left; display: block; font-size: 0.85rem; font-weight: 700; color: #666;">Verification</label>
        <div class="captcha-row">
            <div class="captcha-question">
                <?php echo $_SESSION['captcha_num1'] . " + " . $_SESSION['captcha_num2'] . " = "; ?>
            </div>
            <input type="number" name="captcha" class="captcha-input" placeholder="?" required>
        </div>
        
        <button type="submit" class="btn-login">Open Dashboard</button>
    </form>

    <div class="footer-links">
        <a href="../index.php">← Home</a>
        <a href="../terms.php">T&C</a>
    </div>
</div>

</body>
</html>

