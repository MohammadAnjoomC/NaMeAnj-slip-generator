<?php
session_start();
$user_db = __DIR__ . '/../database/users.json';

// 1. ACCESS SECURITY
if (!isset($_SESSION['user_logged_in'])) {
    header("Location: ../auth/login.php");
    exit();
}

$username = $_SESSION['username'];
$user_dir = "../user/" . $username;

// 2. DELETE LOGIC
if (isset($_GET['delete'])) {
    $filename = basename($_GET['delete']);
    $target_file = $user_dir . "/" . $filename;

    if (file_exists($target_file)) {
        unlink($target_file);
        header("Location: library.php?msg=deleted");
    } else {
        header("Location: library.php?msg=error");
    }
    exit();
}

// 2.5 RENAME LOGIC
if (isset($_GET['rename']) && isset($_GET['new'])) {
    $old_name = basename($_GET['rename']);
    $new_name = basename($_GET['new']);
    
    // Ensure it ends in .pdf
    if (pathinfo($new_name, PATHINFO_EXTENSION) !== 'pdf') {
        $new_name .= '.pdf';
    }

    $old_target = $user_dir . "/" . $old_name;
    $new_target = $user_dir . "/" . $new_name;

    if (file_exists($old_target) && !file_exists($new_target)) {
        rename($old_target, $new_target);
        header("Location: library.php?msg=renamed");
    } else {
        header("Location: library.php?msg=error");
    }
    exit();
}

// 3. FETCH AND SORT FILES (Newest First)
$files = [];
if (is_dir($user_dir)) {
    // Get PDF files only
    $files = array_filter(scandir($user_dir), function($item) use ($user_dir) {
        return is_file($user_dir . '/' . $item) && pathinfo($item, PATHINFO_EXTENSION) == 'pdf';
    });
    
    // Sort by creation/modified date (Descending)
    usort($files, function($a, $b) use ($user_dir) {
        return filemtime($user_dir . '/' . $b) - filemtime($user_dir . '/' . $a);
    });
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
    <title>File Management | NaMeAnj</title>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Quicksand:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root { --blue: #2193b0; --rose: #ff1493; --light: #f0f4f8; }
        body { font-family: 'Quicksand', sans-serif; background: var(--light); margin: 0; padding: 20px; }
        
        .container { max-width: 650px; margin: 0 auto; background: white; border-radius: 30px; padding: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        
        /* Header & Search */
        .nav-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .logo-text { font-family: 'Fredoka One', cursive; color: var(--rose); font-size: 1.8rem; text-decoration: none; }
        .dash-link { text-decoration: none; color: var(--blue); font-weight: 600; font-size: 0.9rem; }

        .search-container { position: relative; margin-bottom: 25px; }
        .search-box { 
            width: 100%; padding: 15px 15px 15px 45px; border: 2px solid #edf2f7; 
            border-radius: 15px; outline: none; transition: 0.3s; font-size: 1rem; box-sizing: border-box;
        }
        .search-box:focus { border-color: var(--blue); box-shadow: 0 0 10px rgba(33, 147, 176, 0.1); }
        .search-icon { position: absolute; left: 18px; top: 17px; color: #a0aec0; }

        /* File Cards */
        .file-list { margin-top: 10px; }
        .file-card { 
            display: flex; justify-content: space-between; align-items: center; 
            background: #fff; border: 1px solid #edf2f7; border-radius: 20px; 
            padding: 18px; margin-bottom: 12px; transition: 0.3s;
        }
        .file-card:hover { border-color: var(--blue); transform: translateY(-2px); }
        
        .file-details { display: flex; align-items: center; gap: 15px; }
        .file-name { font-weight: 600; color: #2d3748; display: block; max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .file-date { font-size: 0.75rem; color: #a0aec0; }

        .actions { display: flex; gap: 8px; }
        .btn { text-decoration: none; font-size: 0.8rem; font-weight: bold; padding: 10px 14px; border-radius: 12px; transition: 0.2s; }
        .btn-download { background: #ebf8ff; color: #2b6cb0; }
        .btn-share { background: #25D366; color: white; }
        .btn-rename { background: #f7fafc; color: #4a5568; border: 1px solid #e2e8f0; }
        .btn-delete { background: #fff5f5; color: #c53030; }
        .btn-download:hover { background: #2b6cb0; color: #fff; }
        .btn-share:hover { background: #128C7E; color: white; }
        .btn-rename:hover { background: #e2e8f0; color: #2d3748; }
        .btn-delete:hover { background: #c53030; color: #fff; }

        .empty-msg { text-align: center; padding: 40px; color: #a0aec0; }
        .btn-create { display: inline-block; background: var(--blue); color: #fff; padding: 12px 20px; border-radius: 15px; text-decoration: none; font-weight: bold; margin-top: 15px; }

        /* Alerts */
        .alert { padding: 12px; border-radius: 12px; margin-bottom: 20px; text-align: center; font-weight: 600; font-size: 0.9rem; }
        .alert-success { background: #c6f6d5; color: #2f855a; }
    </style>
</head>
<body>

<div class="container">
    <div class="nav-header">
        <a href="dashboard.php" class="dash-link">🏠 Dashboard</a>
        <a href="bulk_generator.php" class="logo-text">NaMeAnj</a>
        <a href="bulk_generator.php" class="dash-link" style="color:var(--rose)">+ New</a>
    </div>

    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
        <div class="alert alert-success" style="background: #fff5f5; color: #c53030;">File removed from storage.</div>
    <?php elseif (isset($_GET['msg']) && $_GET['msg'] == 'renamed'): ?>
        <div class="alert alert-success">File renamed successfully!</div>
    <?php elseif (isset($_GET['msg']) && $_GET['msg'] == 'error'): ?>
        <div class="alert" style="background: #fed7d7; color: #c53030;">Action failed. File may already exist or not be found.</div>
    <?php endif; ?>

    <div class="search-container">
        <span class="search-icon">🔍</span>
        <input type="text" id="searchInput" class="search-box" placeholder="Search your PDFs..." onkeyup="filterPDFs()">
    </div>

    <div class="file-list" id="fileList">
        <?php if (empty($files)): ?>
            <div class="empty-msg">
                <p style="font-size: 3rem; margin-bottom: 10px;">📂</p>
                <p>Your storage is empty.</p>
                <a href="bulk_generator.php" class="btn-create">Create Your First PDF</a>
            </div>
        <?php else: ?>
            <?php foreach ($files as $file): 
                $filePath = $user_dir . '/' . $file;
                $displayDate = date("d M, Y • H:i", filemtime($filePath));
            ?>
                <div class="file-card">
                    <div class="file-details">
                        <div style="font-size: 1.5rem;">📄</div>
                        <div>
                            <span class="file-name" title="<?php echo htmlspecialchars($file); ?>">
                                <?php echo htmlspecialchars($file); ?>
                            </span>
                            <span class="file-date"><?php echo $displayDate; ?></span>
                        </div>
                    </div>
                    <div class="actions">
                        <button onclick="shareWhatsApp('<?php echo $filePath; ?>')" class="btn btn-share" style="cursor: pointer;">💬 Share</button>
                        <a href="<?php echo $filePath; ?>" class="btn btn-download" download>Download</a>
                        <button onclick="renameFile('<?php echo htmlspecialchars($file); ?>')" class="btn btn-rename" style="cursor: pointer;">Rename</button>
                        <a href="?delete=<?php echo urlencode($file); ?>" 
                           class="btn btn-delete" 
                           onclick="return confirm('Permanently delete this PDF?')">Delete</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
    function filterPDFs() {
        const input = document.getElementById('searchInput').value.toLowerCase();
        const cards = document.getElementsByClassName('file-card');
        const list = document.getElementById('fileList');
        let visibleCount = 0;

        for (let i = 0; i < cards.length; i++) {
            const fileName = cards[i].querySelector('.file-name').innerText.toLowerCase();
            if (fileName.includes(input)) {
                cards[i].style.display = "flex";
                visibleCount++;
            } else {
                cards[i].style.display = "none";
            }
        }

        // Show "No results" if search finds nothing
        let noResult = document.getElementById('noResult');
        if (visibleCount === 0 && input !== "") {
            if (!noResult) {
                noResult = document.createElement('p');
                noResult.id = 'noResult';
                noResult.style.textAlign = 'center';
                noResult.style.color = '#a0aec0';
                noResult.style.marginTop = '20px';
                noResult.innerText = "No matching files found.";
                list.appendChild(noResult);
            }
        } else if (noResult) {
            noResult.remove();
        }
    }

    function renameFile(oldName) {
        let newName = prompt("Enter new name for the file:", oldName.replace('.pdf', ''));
        if (newName && newName.trim() !== '') {
            window.location.href = `library.php?rename=${encodeURIComponent(oldName)}&new=${encodeURIComponent(newName.trim())}`;
        }
    }

    function shareWhatsApp(relativePath) {
        // Build absolute URL from relative path
        const absoluteUrl = window.location.origin + relativePath.substring(2);
        const msg = `Hello, please print this A3 name slip file for me: ${absoluteUrl}`;
        window.open(`https://wa.me/?text=${encodeURIComponent(msg)}`, '_blank');
    }
</script>

</body>
</html>


