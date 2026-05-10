<?php
session_start();

// Security Guard
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$user_base_dir = __DIR__ . '/../user/';

// Delete logic
if (isset($_GET['delete_file']) && isset($_GET['username'])) {
    $file_to_delete = $user_base_dir . basename($_GET['username']) . '/' . basename($_GET['delete_file']);
    if (file_exists($file_to_delete)) {
        unlink($file_to_delete);
    }
    header("Location: user_storage.php?view=" . urlencode($_GET['username']));
    exit();
}

$selected_user = isset($_GET['view']) ? basename($_GET['view']) : null;
$users = [];

if (is_dir($user_base_dir)) {
    $dirs = array_diff(scandir($user_base_dir), ['.', '..']);
    foreach ($dirs as $dir) {
        if (is_dir($user_base_dir . $dir)) {
            $users[] = $dir;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage User Files | Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Quicksand:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --rose: #ff1493; --blue: #2193b0; --bg: #f8fafc; }
        body { font-family: 'Quicksand', sans-serif; background: var(--bg); margin: 0; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .header-nav { display: flex; justify-content: space-between; margin-bottom: 25px; border-bottom: 2px solid #f1f5f9; padding-bottom: 20px; }
        .header-nav a { text-decoration: none; color: var(--blue); font-weight: 800; font-size: 1.1rem; }
        h2 { font-family: 'Fredoka One', cursive; color: var(--rose); margin-top: 0; }
        
        .user-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px; margin-bottom: 30px; }
        .user-card { background: #f1f5f9; padding: 15px; border-radius: 15px; text-decoration: none; color: #1e293b; font-weight: 700; text-align: center; border: 2px solid transparent; transition: 0.2s; }
        .user-card:hover, .user-card.active { border-color: var(--blue); background: #e0f2fe; }
        
        .file-list { margin-top: 20px; }
        .file-item { display: flex; justify-content: space-between; align-items: center; padding: 15px; border: 1px solid #e2e8f0; border-radius: 15px; margin-bottom: 10px; background: #fff; }
        .file-item .info { display: flex; align-items: center; gap: 15px; }
        .file-item .actions a { padding: 8px 15px; border-radius: 8px; text-decoration: none; font-size: 0.85rem; font-weight: 800; }
        .btn-view { background: #e0f2fe; color: #0284c7; }
        .btn-del { background: #fee2e2; color: #b91c1c; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-nav">
            <a href="dashboard.php">🏠 Dashboard</a>
            <h2>User Files</h2>
        </div>

        <h3 style="color: #475569;">Select User</h3>
        <div class="user-grid">
            <?php foreach ($users as $u): ?>
                <a href="?view=<?php echo urlencode($u); ?>" class="user-card <?php echo ($selected_user === $u) ? 'active' : ''; ?>">
                    👤 <?php echo htmlspecialchars($u); ?>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if ($selected_user): ?>
            <h3 style="color: var(--blue); border-top: 2px solid #f1f5f9; padding-top: 20px;">
                Files for "<?php echo htmlspecialchars($selected_user); ?>"
            </h3>
            
            <div class="file-list">
                <?php 
                $target_dir = $user_base_dir . $selected_user;
                $has_files = false;
                
                if (is_dir($target_dir)) {
                    $files = array_diff(scandir($target_dir), ['.', '..']);
                    foreach ($files as $file) {
                        if (pathinfo($file, PATHINFO_EXTENSION) === 'pdf') {
                            $has_files = true;
                            $file_url = "../user/" . urlencode($selected_user) . "/" . urlencode($file);
                            $date = date("d M Y H:i", filemtime($target_dir . '/' . $file));
                            ?>
                            <div class="file-item">
                                <div class="info">
                                    <span style="font-size: 1.5rem;">📄</span>
                                    <div>
                                        <div style="font-weight: 800; color: #1e293b;"><?php echo htmlspecialchars($file); ?></div>
                                        <div style="font-size: 0.8rem; color: #64748b;"><?php echo $date; ?></div>
                                    </div>
                                </div>
                                <div class="actions">
                                    <a href="<?php echo $file_url; ?>" target="_blank" class="btn-view">View</a>
                                    <a href="?view=<?php echo urlencode($selected_user); ?>&delete_file=<?php echo urlencode($file); ?>&username=<?php echo urlencode($selected_user); ?>" class="btn-del" onclick="return confirm('Delete this PDF?');">Delete</a>
                                </div>
                            </div>
                            <?php
                        }
                    }
                }
                if (!$has_files): ?>
                    <p style="color: #94a3b8; font-style: italic; text-align: center; padding: 20px;">No PDFs found for this user.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>


