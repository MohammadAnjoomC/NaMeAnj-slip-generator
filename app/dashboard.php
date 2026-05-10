<?php
session_start();

// Database path
$user_db_path = __DIR__ . '/../database/users.json';

// 1. ACCESS SECURITY
if (!isset($_SESSION['user_logged_in'])) {
    header("Location: ../auth/login.php");
    exit();
}

$username = $_SESSION['username'];
$user_dir = "../user/" . $username;

// 2. MEMBERSHIP LOGIC (Fetching from database/users.json)
$membership_tier = "Free User";
$tier_color = "#64748b"; 

if (file_exists($user_db_path)) {
    $users = json_decode(file_get_contents($user_db_path), true);
    foreach ($users as $user) {
        if ($user['username'] === $username && isset($user['valid_until'])) {
            $today = new DateTime();
            $expiry = new DateTime($user['valid_until']);
            
            if ($expiry > $today) {
                $interval = $today->diff($expiry);
                $days_left = (int)$interval->format('%a');

                // Tier Logic based on requested months/days
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
                    $membership_tier = "👑 Legend"; 
                    $tier_color = "#ff1493"; 
                }
            } else {
                $membership_tier = "Expired";
                $tier_color = "#ef4444";
            }
            break;
        }
    }
}

// 3. PROFILE PICTURE LOGIC
$profile_pic = $user_dir . "/profile.jpg";
if (!file_exists($profile_pic)) {
    $profile_pic = "https://ui-avatars.com/api/?name=" . urlencode($username) . "&background=2193b0&color=fff&size=128";
}

// 4. STATS: Count PDFs and Get Recent
$pdf_count = 0;
$recent_pdfs = [];
if (is_dir($user_dir)) {
    $all_files = array_diff(scandir($user_dir), array('.', '..'));
    $pdf_files = [];
    foreach ($all_files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) == 'pdf') {
            $pdf_files[] = $file;
        }
    }
    $pdf_count = count($pdf_files);
    
    // Sort by modification time (descending)
    usort($pdf_files, function($a, $b) use ($user_dir) {
        return filemtime($user_dir . '/' . $b) - filemtime($user_dir . '/' . $a);
    });
    
    // Get top 3 recent PDFs
    $recent_pdfs = array_slice($pdf_files, 0, 3);
}

// 5. FETCH TEMPLATES (from nameslips.php)
$template_dir = "../assets/templates/";
$templates = glob($template_dir . "*.{jpg,jpeg,png,webp}", GLOB_BRACE);
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
    <title>Dashboard | NaMeAnj</title>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Quicksand:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { 
            --blue: #2193b0; 
            --rose: #ff1493; 
            --dark: #2d3748; 
            --light-bg: #f4f7f9;
        }

        /* Dark Mode Magic CSS */
        html.dark-mode { filter: invert(1) hue-rotate(180deg); background: #0a0a0a; }
        html.dark-mode img, html.dark-mode .card-icon { filter: invert(1) hue-rotate(180deg); }
        html.dark-mode .welcome-box { border: none; box-shadow: 0 0 30px rgba(255,255,255,0.05); }

        body { 
            font-family: 'Quicksand', sans-serif; 
            background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%);
            margin: 0; 
            padding: 20px; 
            user-select: none;
            min-height: 100vh;
        }
        
        .container { max-width: 900px; margin: 0 auto; }

        .top-bar { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            padding: 15px 0 25px; 
        }
        .logo { 
            font-family: 'Fredoka One', cursive; 
            color: var(--rose); 
            font-size: 2rem; 
            text-decoration: none; 
        }
        .logout-btn { 
            background: #fee2e2; 
            color: #991b1b; 
            text-decoration: none; 
            padding: 10px 20px; 
            border-radius: 12px; 
            font-weight: 700; 
            font-size: 0.85rem; 
            transition: 0.3s;
        }
        .logout-btn:hover { background: #f87171; color: white; }

        .dark-toggle {
            background: rgba(255,255,255,0.8);
            border: 2px solid var(--blue);
            color: var(--blue);
            padding: 8px 15px;
            border-radius: 12px;
            font-weight: 800;
            cursor: pointer;
            margin-right: 15px;
            transition: 0.3s;
        }
        .dark-toggle:hover { background: var(--blue); color: white; }

        .welcome-box { 
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            padding: 35px; 
            border-radius: 35px; 
            box-shadow: 0 15px 40px rgba(0,0,0,0.08); 
            margin-bottom: 30px; 
            border-bottom: 10px solid var(--blue);
            border: 1px solid rgba(255, 255, 255, 0.4);
            display: flex; 
            align-items: center; 
            gap: 25px;
            position: relative;
            transition: transform 0.3s;
        }
        .welcome-box:hover {
            transform: translateY(-2px);
        }
        .user-avatar { 
            width: 100px; 
            height: 100px; 
            border-radius: 50%; 
            object-fit: cover; 
            border: 4px solid var(--blue);
        }
        
        .tier-badge {
            display: inline-block; 
            padding: 6px 16px; 
            border-radius: 50px;
            font-size: 0.85rem; 
            font-weight: 700; 
            color: white;
            background: <?php echo $tier_color; ?>; 
            margin-top: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            text-transform: uppercase;
        }

        .welcome-text h1 { margin: 0; color: var(--dark); font-size: 1.8rem; }
        .welcome-text p { color: #718096; margin-top: 5px; font-size: 1rem; }

        .grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); 
            gap: 25px; 
        }
        
        .card { 
            background: rgba(255, 255, 255, 0.85); 
            backdrop-filter: blur(15px);
            padding: 40px 30px; 
            border-radius: 30px; 
            text-decoration: none; 
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); 
            box-shadow: 0 10px 25px rgba(0,0,0,0.05); 
            border: 1px solid rgba(255, 255, 255, 0.6);
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            text-align: center;
        }
        .card:hover { 
            transform: translateY(-10px); 
            border-color: var(--blue); 
            box-shadow: 0 15px 35px rgba(33, 147, 176, 0.1); 
        }

        .card-icon { font-size: 3.5rem; margin-bottom: 15px; }
        .card-title { font-weight: 800; font-size: 1.3rem; color: var(--dark); margin-bottom: 8px; }
        .card-desc { color: #a0aec0; font-size: 0.95rem; line-height: 1.4; }

        .stat-badge { 
            background: #e0f2fe; 
            color: #0369a1; 
            padding: 5px 15px; 
            border-radius: 15px; 
            font-size: 0.75rem; 
            font-weight: 800; 
            margin-top: 15px; 
        }

        /* TEMPLATE GRID STYLES */
        .template-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); 
            gap: 25px; 
            margin-top: 20px; 
        }

        .template-card { 
            background: rgba(255, 255, 255, 0.85); 
            backdrop-filter: blur(15px);
            padding: 15px; 
            border-radius: 35px; 
            cursor: pointer; 
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); 
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            border: 1px solid rgba(255, 255, 255, 0.6);
            text-align: center;
            position: relative;
            overflow: hidden;
            order: 2; /* Default order, favorites will be order 1 */
        }

        .template-card:hover { 
            transform: translateY(-12px); 
            border-color: var(--blue);
            box-shadow: 0 15px 35px rgba(33, 147, 176, 0.1); 
        }

        .fav-btn {
            position: absolute;
            top: 20px;
            left: 20px;
            background: white;
            border: none;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: 0.3s;
            z-index: 10;
        }
        .fav-btn:hover { transform: scale(1.1); }
        .upload-card { order: 0 !important; } /* Always first */

        .template-card img { 
            width: 100%; 
            aspect-ratio: 1.4;
            object-fit: cover; 
            border-radius: 25px; 
            background: #f8fafc;
        }

        .upload-card {
            border: 3px dashed #cbd5e1;
            background: rgba(248, 250, 252, 0.7);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        .upload-card:hover {
            background: rgba(239, 246, 255, 0.9);
            border-color: var(--blue);
        }

        .pro-label {
            position: absolute;
            top: 20px;
            right: 20px;
            background: var(--rose);
            color: white;
            font-size: 0.6rem;
            padding: 4px 10px;
            border-radius: 10px;
            font-weight: 900;
        }

        .badge {
            display: inline-block;
            background: linear-gradient(135deg, #eef2ff, #e0e7ff);
            color: var(--blue);
            font-size: 0.7rem;
            padding: 5px 15px;
            border-radius: 50px;
            font-weight: 800;
        }

        .section-title {
            font-family: 'Fredoka One', cursive;
            color: var(--dark);
            font-size: 1.8rem;
            margin-top: 50px;
            margin-bottom: 20px;
            text-align: left;
        }

        @media (max-width: 650px) {
            .welcome-box { flex-direction: column; text-align: center; padding: 30px; }
            .grid { grid-template-columns: 1fr; }
            .template-grid { grid-template-columns: 1fr 1fr; }
        }
    </style>
</head>
<body ondragstart="return false;" ondrop="return false;">

<div class="container">
    <div class="top-bar">
        <a href="../index.php" class="logo">NaMeAnj 🎨</a>
        <div>
            <button onclick="toggleDarkMode()" class="dark-toggle" id="darkBtn">🌙 Dark</button>
            <a href="../auth/logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <div class="welcome-box">
        <img src="<?php echo $profile_pic . '?' . time(); ?>" class="user-avatar" alt="User Profile">
        <div class="welcome-text">
            <h1>Hello, <?php echo htmlspecialchars($username); ?>! 👋</h1>
            <div class="tier-badge"><?php echo $membership_tier; ?> Member</div>
            <p>Welcome to your workspace. Let's create some slips today.</p>
        </div>
    </div>

    <div class="grid">
        <a href="bulk_generator.php" class="card">
            <div class="card-icon">🚀</div>
            <div class="card-title">Generator</div>
            <div class="card-desc">Design and create high-quality name slips for books.</div>
        </a>

        <a href="library.php" class="card">
            <div class="card-icon">📂</div>
            <div class="card-title">File Management</div>
            <div class="card-desc">Manage, rename, and download your generated files.</div>
            <div class="stat-badge"><?php echo $pdf_count; ?> Files Stored</div>
        </a>

        <a href="account.php" class="card">
            <div class="card-icon">👤</div>
            <div class="card-title">Account</div>
            <div class="card-desc">Update profile picture and check validity.</div>
        </a>
    </div>

    <!-- AFFILIATE / REFERRAL SYSTEM -->
    <div style="background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 99%, #fecfef 100%); padding: 25px; border-radius: 30px; margin-top: 25px; box-shadow: 0 10px 25px rgba(255, 20, 147, 0.15); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
        <div>
            <h3 style="margin: 0; color: #991b1b; font-family: 'Fredoka One', cursive; font-size: 1.5rem;">🎁 Earn Free Pro Days!</h3>
            <p style="margin: 5px 0 0 0; color: #7f1d1d; font-size: 0.95rem; font-weight: 600;">Share your unique link. Get <strong>+7 Days Free</strong> for every friend who signs up!</p>
        </div>
        <div style="background: white; padding: 10px 15px; border-radius: 15px; display: flex; gap: 10px; align-items: center; flex: 1; max-width: 400px;">
            <input type="text" id="refLink" value="https://<?php echo $_SERVER['HTTP_HOST']; ?>/auth/signup.php?ref=<?php echo urlencode($username); ?>" readonly style="border: none; background: transparent; width: 100%; outline: none; font-weight: 700; color: var(--blue); font-size: 0.85rem;">
            <button onclick="copyRef()" style="background: var(--blue); color: white; border: none; padding: 8px 15px; border-radius: 10px; font-weight: bold; cursor: pointer; white-space: nowrap;">Copy Link</button>
        </div>
    </div>
    
    <script>
        function copyRef() {
            var copyText = document.getElementById("refLink");
            copyText.select();
            copyText.setSelectionRange(0, 99999); 
            navigator.clipboard.writeText(copyText.value);
            alert("✅ Referral Link Copied! Share it with friends.");
        }
    </script>

    <h2 class="section-title">📄 Recent Slips</h2>
    <div class="recent-slips-container" style="background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(15px); padding: 25px; border-radius: 30px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); border: 1px solid rgba(255, 255, 255, 0.6); margin-bottom: 20px;">
        <?php if(empty($recent_pdfs)): ?>
            <p style="text-align: center; color: #a0aec0; font-weight: 600; margin: 10px 0;">No nameslips generated yet.</p>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <?php foreach($recent_pdfs as $pdf): 
                    $filePath = $user_dir . '/' . $pdf;
                    $displayDate = date("d M, Y", filemtime($filePath));
                ?>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 15px 20px; border: 1px solid #edf2f7; border-radius: 20px; background: white; transition: 0.3s; cursor: default;" onmouseover="this.style.borderColor='var(--blue)'; this.style.transform='translateY(-2px)';" onmouseout="this.style.borderColor='#edf2f7'; this.style.transform='translateY(0)';">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <span style="font-size: 1.8rem;">📄</span>
                        <div>
                            <span style="font-weight: 800; color: var(--dark); font-size: 1rem;"><?php echo htmlspecialchars($pdf); ?></span><br>
                            <span style="font-size: 0.8rem; color: #a0aec0; font-weight: 600;"><?php echo $displayDate; ?></span>
                        </div>
                    </div>
                    <a href="<?php echo $filePath; ?>" download style="background: #ebf8ff; color: #2b6cb0; padding: 10px 20px; border-radius: 12px; text-decoration: none; font-weight: 800; font-size: 0.85rem; transition: 0.3s;" onmouseover="this.style.background='#2b6cb0'; this.style.color='white';" onmouseout="this.style.background='#ebf8ff'; this.style.color='#2b6cb0';">Download</a>
                </div>
                <?php endforeach; ?>
            </div>
            <div style="text-align: right; margin-top: 15px;">
                <a href="library.php" style="color: var(--blue); font-weight: 800; text-decoration: none; font-size: 0.95rem; padding-right: 10px;">View All Library →</a>
            </div>
        <?php endif; ?>
    </div>
    
    <h2 class="section-title">✨ Create New Slip</h2>
    <div class="template-grid">
        <div class="template-card upload-card" onclick="document.getElementById('customBg').click()">
            <div style="font-size: 40px; margin-bottom: 10px;">📤</div>
            <p style="margin: 0; font-weight: 800;">Upload Background</p>
            <span style="font-size: 0.7rem; color: #64748b;">(JPG/PNG/WEBP)</span>
            <input type="file" id="customBg" style="display:none" accept="image/*" onchange="handleCustomUpload(this)">
        </div>

        <?php if (!empty($templates)): ?>
            <?php foreach($templates as $file): 
                $filename = basename($file);
                $displayName = str_replace(['.jpg', '.png', '.jpeg', '_'], ['','','',' '], $filename);
            ?>
            <div class="template-card" data-filename="<?php echo htmlspecialchars($filename); ?>">
                <button class="fav-btn" onclick="toggleFav('<?php echo htmlspecialchars($filename); ?>', this)">🤍</button>
                <div onclick="location.href='editor.php?temp=<?php echo urlencode($filename); ?>'">
                    <div class="pro-label">PRO HD</div>
                    <img src="<?php echo $template_dir . $filename; ?>" alt="Nameslip Design">
                    <p style="font-weight: 700; color: var(--dark); margin: 10px 0 5px 0;"><?php echo ucwords($displayName); ?></p>
                    <span class="badge">A3 & A4 Ready</span>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <p style="text-align: center; color: #cbd5e1; font-size: 0.8rem; margin-top: 50px;">
        &copy; 2026 NaMeAnj Tool | Created by Anju
    </p>
</div>

<script>
    // Handler: When uploading custom background
    function handleCustomUpload(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                // Pass to image editor via local storage (temporary)
                localStorage.setItem('custom_bg', e.target.result);
                window.location.href = 'editor.php?temp=custom';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Dark Mode Logic
    function toggleDarkMode() {
        const isDark = document.documentElement.classList.toggle('dark-mode');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
        document.getElementById('darkBtn').innerText = isDark ? '☀️ Light' : '🌙 Dark';
    }

    // Load saved theme
    if (localStorage.getItem('theme') === 'dark') {
        document.documentElement.classList.add('dark-mode');
        document.getElementById('darkBtn').innerText = '☀️ Light';
    }

    // FAVORITES SYSTEM
    let favorites = JSON.parse(localStorage.getItem('fav_templates') || '[]');
    
    function toggleFav(filename, btn) {
        event.stopPropagation(); // prevent opening editor
        if (favorites.includes(filename)) {
            favorites = favorites.filter(f => f !== filename);
            btn.innerText = '🤍';
            btn.closest('.template-card').style.order = '2';
        } else {
            favorites.push(filename);
            btn.innerText = '❤️';
            btn.closest('.template-card').style.order = '1';
        }
        localStorage.setItem('fav_templates', JSON.stringify(favorites));
    }

    // Initialize favorites on load
    document.querySelectorAll('.template-card[data-filename]').forEach(card => {
        const filename = card.getAttribute('data-filename');
        if (favorites.includes(filename)) {
            card.querySelector('.fav-btn').innerText = '❤️';
            card.style.order = '1';
        }
    });
</script>

</body>
</html>


