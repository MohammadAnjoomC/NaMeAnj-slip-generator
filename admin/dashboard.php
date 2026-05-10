<?php
session_start();

// 1. Security Guard
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$db_path = __DIR__ . '/../database/users.json';
if (!is_dir(__DIR__ . '/../database')) mkdir(__DIR__ . '/../database', 0777, true);
if (!file_exists($db_path)) file_put_contents($db_path, json_encode([]));

$users = json_decode(file_get_contents($db_path), true);

$total_users = count($users);
$active_users = 0;
$expired_users = 0;
$total_pdfs = 0;

$today_date = new DateTime();
foreach ($users as $u) {
    if (strtotime($u['valid_until']) >= $today_date->getTimestamp()) {
        $active_users++;
    } else {
        $expired_users++;
    }
}

// Count total PDFs
$user_dir_path = __DIR__ . '/user/';
if (is_dir($user_dir_path)) {
    $user_dirs = array_diff(scandir($user_dir_path), ['.', '..']);
    foreach ($user_dirs as $udir) {
        if (is_dir($user_dir_path . $udir)) {
            $pdfs = glob($user_dir_path . $udir . '/*.pdf');
            if ($pdfs) $total_pdfs += count($pdfs);
        }
    }
}

$message = "";
$should_refresh = false;

// 2. Action: Add New Member
if (isset($_POST['add_user'])) {
    $username = htmlspecialchars($_POST['username']);
    $email = htmlspecialchars($_POST['email']);
    $password = $_POST['password']; 
    $expiry = $_POST['valid_until'];

    $users[] = [
        'id' => uniqid(),
        'username' => $username,
        'email' => $email,
        'password' => $password,
        'valid_until' => $expiry,
        'created_at' => date("Y-m-d")
    ];
    file_put_contents($db_path, json_encode($users, JSON_PRETTY_PRINT), LOCK_EX);
    $message = "User created! Refreshing list...";
    $should_refresh = true;
}

// 3. Action: Renew Membership
if (isset($_POST['renew_user'])) {
    $id = $_POST['user_id'];
    $months = (int)$_POST['months'];
    foreach ($users as &$user) {
        if ($user['id'] === $id) {
            $current_expiry = new DateTime($user['valid_until']); 
            $today = new DateTime();
            if ($current_expiry < $today) {
                $current_expiry = $today;
            }
            $current_expiry->modify("+$months months");
            $user['valid_until'] = $current_expiry->format('Y-m-d');
            break;
        }
    }
    file_put_contents($db_path, json_encode(array_values($users), JSON_PRETTY_PRINT), LOCK_EX);
    $message = "Renewed! Refreshing list...";
    $should_refresh = true;
}

// 4. Action: Delete Member
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $users = array_filter($users, function($u) use ($id) { return $u['id'] !== $id; });
    file_put_contents($db_path, json_encode(array_values($users), JSON_PRETTY_PRINT), LOCK_EX);
    header("Location: dashboard.php");
    exit();
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

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if ($should_refresh): ?>
        <meta http-equiv="refresh" content="2;url=dashboard.php">
    <?php endif; ?>
    <link rel="icon" type="image/png" href="/images/icon.png">
    <title>Admin Panel | NaMeAnj</title>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Quicksand:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --rose: #ff1493; --blue: #2193b0; --bg: #f9fbfd; }
        body { font-family: 'Quicksand', sans-serif; background-color: var(--bg); margin: 0; display: flex; flex-direction: row; min-height: 100vh; }
        
        /* Sidebar Navigation */
        #nav-toggle { display: none; }
        .sidebar { 
            width: 260px; background: linear-gradient(180deg, var(--rose), #ff69b4); 
            min-height: 100vh; color: white; padding: 30px 20px; 
            position: fixed; z-index: 100; transition: 0.3s; 
        }
        .sidebar h2 { font-family: 'Fredoka One', cursive; border-bottom: 2px dashed rgba(255,255,255,0.4); padding-bottom: 10px; margin-top: 0; }
        .sidebar a { color: white; text-decoration: none; display: block; padding: 12px; border-radius: 10px; margin-bottom: 10px; font-weight: 600; transition: 0.3s; }
        .sidebar a:hover { background: rgba(255,255,255,0.2); }

        /* Mobile View Header */
        .mobile-header { 
            display: none; background: var(--rose); color: white; 
            padding: 15px; width: 100%; position: fixed; top: 0; z-index: 101; 
            justify-content: space-between; align-items: center; box-sizing: border-box;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .menu-icon { font-size: 1.5rem; cursor: pointer; }

        .content { margin-left: 300px; padding: 40px; width: 100%; box-sizing: border-box; transition: 0.3s; }
        
        .card { 
            background: white; padding: 25px; border-radius: 20px; 
            box-shadow: 0 5px 20px rgba(0,0,0,0.05); margin-bottom: 30px; 
            border-top: 5px solid var(--blue); 
        }
        h3 { color: var(--blue); margin-top: 0; font-family: 'Fredoka One', cursive; margin-bottom: 15px; }

        /* Search Bar Styles */
        .search-bar { 
            width: 100%; padding: 15px; margin-bottom: 20px; border: 2px solid #e2e8f0; 
            border-radius: 15px; font-size: 1rem; font-family: inherit; outline: none; 
            transition: 0.3s; box-sizing: border-box;
        }
        .search-bar:focus { border-color: var(--rose); background: #fff9fb; }

        /* Form Layout */
        .form-grid { display: flex; gap: 10px; flex-wrap: wrap; }
        input, select { padding: 12px; border: 2px solid #eee; border-radius: 10px; outline: none; flex: 1; min-width: 150px; font-family: inherit; }
        
        .btn-add { background: var(--blue); color: white; border: none; padding: 12px 25px; border-radius: 10px; cursor: pointer; font-weight: bold; width: 100%; transition: 0.3s; }
        .btn-add:hover { background: #1a7a94; }
        
        .btn-renew { background: #10b981; color: white; border: none; padding: 8px 12px; border-radius: 8px; cursor: pointer; font-weight: bold; }

        /* Table Responsive Styles */
        .table-container { overflow-x: auto; border-radius: 15px; border: 1px solid #f1f5f9; }
        table { width: 100%; border-collapse: collapse; min-width: 650px; }
        th { text-align: left; background: #f8fafc; padding: 15px; color: #64748b; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px; }
        td { padding: 15px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        
        .status { padding: 5px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: bold; text-transform: uppercase; }
        .active { background: #dcfce7; color: #166534; }
        .expired { background: #fee2e2; color: #991b1b; }
        
        .pass-view { font-family: monospace; color: #777; background: #f0f0f0; padding: 3px 6px; border-radius: 5px; font-size: 0.85rem; }

        @media (max-width: 992px) {
            .sidebar { left: -300px; width: 250px; }
            .mobile-header { display: flex; }
            .content { margin-left: 0; padding: 85px 20px 20px; }
            #nav-toggle:checked ~ .sidebar { left: 0; }
            #nav-toggle:checked ~ .content { filter: blur(2px); pointer-events: none; }
        }
    </style>
</head>
<body>

<input type="checkbox" id="nav-toggle">

<div class="mobile-header">
    <span style="font-family: 'Fredoka One'; letter-spacing: 1px;">NaMeAnj ADMIN</span>
    <label for="nav-toggle" class="menu-icon">☰</label>
</div>

<div class="sidebar">
    <h2>NaMeAnj</h2>
    <p style="font-size: 0.85rem; opacity: 0.8; margin-bottom: 30px;">Logged in: <b>Admin</b></p>
    <a href="dashboard.php">📊 Manage Members</a>
    <a href="templates.php">🖼️ Manage Templates</a>
    <a href="user_storage.php">📂 User Files</a>
    <a href="settings.php">⚙️ Admin Settings</a>
    <a href="../index.php">🏠 View Site</a>
    <a href="../logout.php" style="margin-top: 50px; background: rgba(0,0,0,0.15);">🚪 Logout</a>
</div>

<div class="content">
    <h3 style="margin-bottom: 25px; font-size: 1.5rem;">Dashboard Overview</h3>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div style="background: white; padding: 20px; border-radius: 15px; border-left: 5px solid var(--blue); box-shadow: 0 5px 15px rgba(0,0,0,0.05);">
            <div style="color: #64748b; font-weight: 700; font-size: 0.9rem;">Total Members</div>
            <div style="font-size: 2rem; font-family: 'Fredoka One'; color: #1e293b;"><?php echo $total_users; ?></div>
        </div>
        <div style="background: white; padding: 20px; border-radius: 15px; border-left: 5px solid #10b981; box-shadow: 0 5px 15px rgba(0,0,0,0.05);">
            <div style="color: #64748b; font-weight: 700; font-size: 0.9rem;">Active Subscriptions</div>
            <div style="font-size: 2rem; font-family: 'Fredoka One'; color: #1e293b;"><?php echo $active_users; ?></div>
        </div>
        <div style="background: white; padding: 20px; border-radius: 15px; border-left: 5px solid #ef4444; box-shadow: 0 5px 15px rgba(0,0,0,0.05);">
            <div style="color: #64748b; font-weight: 700; font-size: 0.9rem;">Expired Members</div>
            <div style="font-size: 2rem; font-family: 'Fredoka One'; color: #1e293b;"><?php echo $expired_users; ?></div>
        </div>
        <div style="background: white; padding: 20px; border-radius: 15px; border-left: 5px solid var(--rose); box-shadow: 0 5px 15px rgba(0,0,0,0.05);">
            <div style="color: #64748b; font-weight: 700; font-size: 0.9rem;">Total Slips Generated</div>
            <div style="font-size: 2rem; font-family: 'Fredoka One'; color: #1e293b;"><?php echo $total_pdfs; ?></div>
        </div>
    </div>

    <?php if ($message): ?>
        <div style="background: #dcfce7; color: #166534; padding: 15px; border-radius: 15px; margin-bottom: 25px; font-weight: 600; border-left: 5px solid #10b981;">
            ✅ <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <h3>✨ Quick Add User</h3>
        <form class="form-grid" method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email Address" required>
            <input type="text" name="password" placeholder="Password" required>
            <input type="date" name="valid_until" value="<?php echo date('Y-m-d', strtotime('+1 month')); ?>" required>
            <button type="submit" name="add_user" class="btn-add">Create Account</button>
        </form>
    </div>

    <div class="card">
        <h3>👥 Registered Members</h3>
        <input type="text" id="userSearch" class="search-bar" placeholder="🔍 Search by username..." onkeyup="filterUsers()">
        
        <div class="table-container">
            <table id="userTable">
                <thead>
                    <tr>
                        <th>User Details</th>
                        <th>Expiry Date</th>
                        <th>Status</th>
                        <th>Renew Plan</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): 
                        $is_expired = strtotime($user['valid_until']) < time();
                    ?>
                    <tr>
                        <td>
                            <b style="font-size: 1rem;"><?php echo htmlspecialchars($user['username']); ?></b><br>
                            <?php if (!empty($user['email'])): ?><span style="font-size: 0.8rem; color: #64748b; font-weight: 600;"><?php echo htmlspecialchars($user['email']); ?></span><br><?php endif; ?>
                            <span class="pass-view"><?php echo htmlspecialchars($user['password']); ?></span>
                        </td>
                        <td style="font-weight: 600; color: #475569;"><?php echo $user['valid_until']; ?></td>
                        <td>
                            <span class="status <?php echo $is_expired ? 'expired' : 'active'; ?>">
                                <?php echo $is_expired ? 'Expired' : 'Active'; ?>
                            </span>
                        </td>
                        <td>
                            <form method="POST" style="display:flex; gap:5px;">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <select name="months" style="min-width: 80px; padding: 5px; font-size: 0.85rem;">
                                    <option value="1">+1 Mo</option>
                                    <option value="6">+6 Mo</option>
                                    <option value="12">+1 Yr</option>
                                </select>
                                <button type="submit" name="renew_user" class="btn-renew">Go</button>
                            </form>
                        </td>
                        <td>
                            <a href="?delete=<?php echo $user['id']; ?>" style="text-decoration: none; font-size: 1.2rem;" onclick="return confirm('Delete this user?')">🗑️</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Search Functionality: Filters the first column (Username)
function filterUsers() {
    let input = document.getElementById('userSearch');
    let filter = input.value.toLowerCase();
    let table = document.getElementById('userTable');
    let tr = table.getElementsByTagName('tr');

    for (let i = 1; i < tr.length; i++) {
        let td = tr[i].getElementsByTagName('td')[0];
        if (td) {
            let txtValue = td.textContent || td.innerText;
            if (txtValue.toLowerCase().indexOf(filter) > -1) {
                tr[i].style.display = "";
            } else {
                tr[i].style.display = "none";
            }
        }
    }
}
</script>

</body>
</html>


