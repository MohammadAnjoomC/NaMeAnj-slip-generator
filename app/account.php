<?php
session_start();
if (!isset($_SESSION['user_logged_in'])) { header("Location: ../auth/login.php"); exit(); }

$username = $_SESSION['username'];
$user_db_path = __DIR__ . '/../database/users.json';
$user_dir = "../user/" . $username;
$profile_pic = $user_dir . "/profile.jpg";

// Initialize variables
$membership_tier = "Free User";
$days_left = 0;
$expiry_display = "No Active Membership";
$tier_color = "#64748b"; 

if (file_exists($user_db_path)) {
    $users = json_decode(file_get_contents($user_db_path), true);
    foreach ($users as $user) {
        // MATCHING YOUR JSON KEY: 'valid_until'
        if ($user['username'] === $username && isset($user['valid_until'])) {
            $today = new DateTime();
            $expiry = new DateTime($user['valid_until']);
            
            if ($expiry > $today) {
                $interval = $today->diff($expiry);
                // Total days remaining
                $days_left = (int)$interval->format('%a'); 
                $expiry_display = $expiry->format('d M Y');

                // MEMBERSHIP TIER LOGIC
                if ($days_left <= 30) {
                    $membership_tier = "🥉 Bronze";
                    $tier_color = "#cd7f32";
                } elseif ($days_left <= 60) {
                    $membership_tier = "🥈 Silver";
                    $tier_color = "#9ca3af";
                } elseif ($days_left <= 90) {
                    $membership_tier = "🥇 Gold";
                    $tier_color = "#fbbf24";
                } elseif ($days_left <= 150) {
                    $membership_tier = "💎 Platinum";
                    $tier_color = "#60a5fa";
                } elseif ($days_left <= 180) {
                    $membership_tier = "⚔️ Master";
                    $tier_color = "#8b5cf6";
                } else {
                    // This covers Grandmaster, Challenger, Legend etc.
                    $membership_tier = "👑 Legend"; 
                    $tier_color = "#ff1493";
                }
            } else {
                $expiry_display = "Membership Expired";
                $membership_tier = "Expired";
            }
            break;
        }
    }
}

// Profile pic fallback
if (!file_exists($profile_pic)) {
    $profile_pic = "https://ui-avatars.com/api/?name=" . urlencode($username) . "&background=2193b0&color=fff&size=128";
}

// Fetch Default Texts
$default_l1 = "";
$default_l2 = "";
$default_l3 = "";
if (file_exists($user_db_path)) {
    $users = json_decode(file_get_contents($user_db_path), true);
    foreach ($users as $user) {
        if ($user['username'] === $username) {
            $default_l1 = $user['default_l1'] ?? '';
            $default_l2 = $user['default_l2'] ?? '';
            $default_l3 = $user['default_l3'] ?? '';
            
            // Also update session for editor
            $_SESSION['default_l1'] = $default_l1;
            $_SESSION['default_l2'] = $default_l2;
            $_SESSION['default_l3'] = $default_l3;
            break;
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
    <title>Account Info | NaMeAnj</title>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Quicksand:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --blue: #2193b0; --rose: #ff1493; --bg: #f8fafc; }
        body { font-family: 'Quicksand', sans-serif; background: var(--bg); margin: 0; padding: 20px; display: flex; justify-content: center; align-items: center; min-height: 100vh; user-select: none; }
        
        .container { 
            width: 100%; max-width: 400px; background: white; padding: 35px; 
            border-radius: 40px; box-shadow: 0 20px 50px rgba(0,0,0,0.05); 
            text-align: center; border-bottom: 10px solid var(--blue);
            box-sizing: border-box;
        }

        .back-link { display: block; text-align: left; text-decoration: none; color: var(--blue); font-weight: 700; margin-bottom: 20px; }

        /* Membership Badge */
        .membership-card {
            background: #f1f5f9; border-radius: 20px; padding: 15px; margin-bottom: 25px;
            border: 1px solid #e2e8f0;
        }
        .tier-name { 
            font-family: 'Fredoka One', cursive; font-size: 1.5rem; 
            color: <?php echo $tier_color; ?>; margin: 5px 0;
        }
        .validity-text { font-size: 0.8rem; color: #64748b; font-weight: 700; text-transform: uppercase; }

        .profile-container { position: relative; width: 120px; height: 120px; margin: 0 auto 25px; }
        .profile-img { 
            width: 120px; height: 120px; border-radius: 50%; object-fit: cover; 
            border: 4px solid white; box-shadow: 0 5px 15px rgba(0,0,0,0.1); 
        }
        .edit-overlay { 
            position: absolute; bottom: 0; right: 0; background: var(--blue); 
            color: white; width: 35px; height: 35px; border-radius: 50%; 
            display: flex; align-items: center; justify-content: center; 
            cursor: pointer; border: 3px solid white;
        }

        .form-group { text-align: left; margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: 700; color: #64748b; font-size: 0.8rem; }
        input { 
            width: 100%; padding: 12px; border: 2px solid #f1f5f9; border-radius: 12px; 
            background: #f8fafc; color: #94a3b8; outline: none; cursor: not-allowed;
            box-sizing: border-box;
        }

        .save-btn { 
            background: var(--blue); color: white; border: none; padding: 15px; 
            width: 100%; border-radius: 15px; font-weight: bold; cursor: pointer; 
            margin-top: 10px; transition: 0.3s;
        }
        .save-btn:hover { background: #1a7a94; }
        
        .editable-input { background: white !important; cursor: text !important; border-color: #e2e8f0 !important; color: var(--dark) !important; }
        .editable-input:focus { border-color: var(--blue) !important; box-shadow: 0 0 10px rgba(33, 147, 176, 0.1); }
        .alert { background: #dcfce7; color: #166534; padding: 10px; border-radius: 10px; font-size: 0.85rem; font-weight: 700; margin-bottom: 15px; display: none; }
    </style>
</head>
<body ondragstart="return false;" ondrop="return false;">

<div class="container">
    <a href="dashboard.php" class="back-link">🏠 Dashboard</a>
    
    <div class="membership-card">
        <span class="validity-text">Membership Status</span>
        <div class="tier-name"><?php echo $membership_tier; ?></div>
        <span class="validity-text" style="font-size: 0.7rem;">Expires: <?php echo $expiry_display; ?></span>
    </div>

    <form action="account_action.php" method="POST" enctype="multipart/form-data">
        <div class="profile-container">
            <img src="<?php echo $profile_pic . '?' . time(); ?>" class="profile-img" id="preview">
            <label for="profile_pic" class="edit-overlay">📸</label>
            <input type="file" name="profile_pic" id="profile_pic" hidden accept="image/*" onchange="previewImage(this)">
        </div>

        <div class="form-group">
            <label>Username</label>
            <input type="text" value="<?php echo htmlspecialchars($username); ?>" readonly>
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" value="********" readonly>
        </div>

        <h3 style="font-size: 1.1rem; color: var(--blue); text-align: left; margin-top: 30px; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px;">⚡ Auto-Fill Presets</h3>
        <p style="font-size: 0.75rem; color: #64748b; text-align: left; margin-top: -5px;">Save time! These details will automatically fill out when creating a new Name Slip.</p>

        <div class="form-group">
            <label>Default Line 1 (e.g. Class / Div)</label>
            <input type="text" name="default_l1" class="editable-input" value="<?php echo htmlspecialchars($default_l1); ?>" placeholder="Class............... Div...............">
        </div>
        
        <div class="form-group">
            <label>Default Line 2 (e.g. Subject)</label>
            <input type="text" name="default_l2" class="editable-input" value="<?php echo htmlspecialchars($default_l2); ?>" placeholder="Subject.......................................">
        </div>

        <div class="form-group">
            <label>Default Line 3 (e.g. School)</label>
            <input type="text" name="default_l3" class="editable-input" value="<?php echo htmlspecialchars($default_l3); ?>" placeholder="School Name...................................">
        </div>

        <button type="submit" class="save-btn" id="saveBtn">✅ Save Changes</button>
    </form>
</div>

<script>
    // Show success alert if redirected back with success
    const urlParams = new URLSearchParams(window.location.search);
    if(urlParams.get('msg') === 'success') {
        const alertBox = document.createElement('div');
        alertBox.className = 'alert';
        alertBox.innerText = '✅ Settings saved successfully!';
        alertBox.style.display = 'block';
        document.querySelector('form').insertBefore(alertBox, document.querySelector('.profile-container'));
        
        // Remove from url
        window.history.replaceState({}, document.title, window.location.pathname);
        setTimeout(() => { alertBox.style.display = 'none'; }, 3000);
    }
    function previewImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('preview').src = e.target.result;
                document.getElementById('saveBtn').style.display = 'block';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>

</body>
</html>


