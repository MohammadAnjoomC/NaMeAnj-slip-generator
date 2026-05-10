<?php
session_start();

if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    header("Location: ../app/dashboard.php");
    exit();
}

$user_db = __DIR__ . '/../database/users.json';
$error = "";
$success = "";

// Generate Captcha
if (!isset($_SESSION['signup_captcha_ans']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $n1 = rand(1, 9);
    $n2 = rand(1, 9);
    $_SESSION['signup_captcha_ans'] = $n1 + $n2;
    $_SESSION['signup_captcha_text'] = "$n1 + $n2 = ?";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $user_captcha = $_POST['captcha'] ?? '';

    if ((int)$user_captcha !== $_SESSION['signup_captcha_ans']) {
        $error = "Incorrect captcha answer!";
    } elseif (empty($username) || empty($email) || empty($password)) {
        $error = "Please fill all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
        $error = "Username must be 3-20 characters (letters, numbers, underscores).";
    } else {
        $users = [];
        if (file_exists($user_db)) {
            $users = json_decode(file_get_contents($user_db), true) ?? [];
        }
        
        $username_exists = false;
        foreach ($users as $u) {
            if (strtolower($u['username']) === strtolower($username)) {
                $username_exists = true;
                break;
            }
        }
        
        if ($username_exists) {
            $error = "Username already taken! Please choose another.";
        } else {
            // Affiliate System: Reward the referrer with 7 free days
            $ref = trim($_POST['ref'] ?? '');
            if (!empty($ref) && strtolower($ref) !== strtolower($username)) {
                foreach ($users as &$u) {
                    if (strtolower($u['username']) === strtolower($ref)) {
                        if (isset($u['valid_until'])) {
                            $curr_expiry = new DateTime($u['valid_until']);
                            $today = new DateTime();
                            // If expired, start from today
                            if ($curr_expiry < $today) {
                                $curr_expiry = clone $today;
                            }
                            $curr_expiry->modify('+7 days');
                            $u['valid_until'] = $curr_expiry->format('Y-m-d');
                        }
                        break;
                    }
                }
            }

            // Add new user but expired immediately (valid_until = yesterday)
            $users[] = [
                'id' => uniqid(),
                'username' => $username,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'valid_until' => date('Y-m-d', strtotime('-1 day')),
                'created_at' => date("Y-m-d")
            ];
            
            // Create user directory
            $user_dir = __DIR__ . '/../user/' . $username;
            if (!is_dir($user_dir)) {
                mkdir($user_dir, 0777, true);
            }
            
            file_put_contents($user_db, json_encode($users, JSON_PRETTY_PRINT), LOCK_EX);
            $success = "Account created successfully! Please login.";
        }
    }
    
    // Refresh captcha
    $n1 = rand(1, 9);
    $n2 = rand(1, 9);
    $_SESSION['signup_captcha_ans'] = $n1 + $n2;
    $_SESSION['signup_captcha_text'] = "$n1 + $n2 = ?";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | NaMeAnj</title>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Quicksand:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --blue: #2193b0; --rose: #ff1493; }
        body { 
            font-family: 'Quicksand', sans-serif; 
            background: linear-gradient(135deg, #6dd5ed, #2193b0, #ffc3a0, #ffafbd);
            background-size: 400% 400%; animation: gradientBG 15s ease infinite;
            display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; 
        }
        @keyframes gradientBG { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
        
        .signup-box { 
            background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            padding: 40px; border-radius: 35px; box-shadow: 0 15px 35px rgba(0,0,0,0.1); width: 90%; max-width: 380px; 
            text-align: center; border: 1px solid rgba(255, 255, 255, 0.5); border-bottom: 8px solid var(--rose);
        }
        .logo { font-family: 'Fredoka One', cursive; color: var(--blue); font-size: 2.2rem; margin-bottom: 5px; }
        .subtitle { color: #888; font-size: 0.9rem; margin-bottom: 20px; font-weight: 600; }
        
        input { width: 100%; padding: 14px; margin: 8px 0; border: 2px solid #edf2f7; border-radius: 15px; outline: none; box-sizing: border-box; font-size: 1rem; font-family: inherit; }
        input:focus { border-color: var(--rose); background: #fff5f5; }
        
        .captcha-row { display: flex; align-items: center; gap: 10px; margin: 10px 0; background: #f8fafc; padding: 10px; border-radius: 15px; border: 1px dashed var(--rose); }
        .captcha-question { font-weight: 700; color: var(--rose); font-size: 1.1rem; min-width: 80px; }
        
        button { width: 100%; padding: 16px; border: none; border-radius: 15px; background: var(--rose); color: white; font-weight: bold; font-size: 1rem; cursor: pointer; margin-top: 10px; transition: 0.3s; }
        button:hover { background: #d0107a; transform: translateY(-2px); }
        
        .footer-links { margin-top: 20px; border-top: 1px solid #eee; padding-top: 15px; display: flex; flex-direction: column; gap: 10px; }
        .link { text-decoration: none; color: var(--rose); font-weight: 700; font-size: 0.9rem; }
        .link:hover { color: var(--blue); }
        
        .error { color: #c53030; font-size: 0.85rem; margin: 10px 0; font-weight: 700; background: #fff5f5; padding: 8px; border-radius: 10px; }
        .success { color: #2f855a; font-size: 0.85rem; margin: 10px 0; font-weight: 700; background: #c6f6d5; padding: 8px; border-radius: 10px; }
    </style>
</head>
<body>
    <div class="signup-box">
        <div class="logo">NaMeAnj</div>
        <p class="subtitle">Create Your Account</p>

        <?php if ($success): ?>
            <div class="success">✅ <?php echo htmlspecialchars($success); ?></div>
            <a href="login.php" style="display:inline-block; margin-top: 15px; text-decoration:none; color: white; background: var(--blue); padding: 10px 20px; border-radius: 10px; font-weight: bold;">Go to Login</a>
        <?php else: ?>
            <p style="font-size: 0.8rem; color: #64748b; margin-bottom: 20px;">
                Note: New accounts require admin approval for generation features.
            </p>
            <form method="POST">
                <input type="hidden" name="ref" value="<?php echo htmlspecialchars($_GET['ref'] ?? ''); ?>">
                <input type="text" name="username" placeholder="Choose Username" required>
                <input type="email" name="email" placeholder="Email Address" required>
                <input type="password" name="password" placeholder="Create Password" required>
                
                <div class="captcha-row">
                    <div class="captcha-question"><?php echo $_SESSION['signup_captcha_text']; ?></div>
                    <input type="number" name="captcha" placeholder="Answer" required>
                </div>

                <?php if ($error): ?> 
                    <div class="error">⚠️ <?php echo htmlspecialchars($error); ?></div> 
                <?php endif; ?>

                <button type="submit">Sign Up</button>
            </form>
        <?php endif; ?>

        <div class="footer-links">
            <a href="login.php" class="link">Already have an account? Login here</a>
            <a href="../index.php" class="link">🏠 Back to Home</a>
        </div>
    </div>
</body>
</html>


