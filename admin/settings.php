<?php
session_start();

// 1. Security Guard
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$db_path = __DIR__ . '/../database/admin.json';
$admin_data = file_exists($db_path) ? json_decode(file_get_contents($db_path), true) : ['email' => '', 'password' => ''];

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_admin'])) {
    $new_email = htmlspecialchars($_POST['email']);
    $new_password = htmlspecialchars($_POST['password']);
    
    $admin_data['email'] = $new_email;
    $admin_data['password'] = $new_password;
    
    file_put_contents($db_path, json_encode($admin_data, JSON_PRETTY_PRINT));
    $message = "Admin credentials updated successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Settings | NaMeAnj</title>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Quicksand:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --rose: #ff1493; --blue: #2193b0; --bg: #f9fbfd; }
        body { font-family: 'Quicksand', sans-serif; background-color: var(--bg); margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 50px auto; background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border-top: 5px solid var(--rose); }
        .header-nav { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .header-nav a { text-decoration: none; color: var(--blue); font-weight: 800; }
        h2 { color: var(--rose); font-family: 'Fredoka One', cursive; margin-top: 0; }
        .form-group { margin-bottom: 20px; }
        label { display: block; font-weight: 700; margin-bottom: 8px; color: #475569; }
        input { width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 12px; box-sizing: border-box; font-family: inherit; font-size: 1rem; }
        input:focus { border-color: var(--blue); outline: none; background: #f8fafc; }
        .btn-save { background: var(--blue); color: white; border: none; padding: 15px; width: 100%; border-radius: 12px; font-weight: 800; font-size: 1.1rem; cursor: pointer; transition: 0.3s; }
        .btn-save:hover { background: #1a7a94; transform: translateY(-2px); }
        .alert { background: #dcfce7; color: #166534; padding: 15px; border-radius: 12px; margin-bottom: 20px; font-weight: 700; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-nav">
            <a href="dashboard.php">← Back to Dashboard</a>
        </div>
        <h2>Admin Settings</h2>
        
        <?php if ($message): ?>
            <div class="alert">✅ <?php echo $message; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Admin Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($admin_data['email']); ?>" required>
            </div>
            <div class="form-group">
                <label>Admin Password</label>
                <input type="text" name="password" value="<?php echo htmlspecialchars($admin_data['password']); ?>" required>
            </div>
            <button type="submit" name="update_admin" class="btn-save">Save Changes</button>
        </form>
    </div>
</body>
</html>


