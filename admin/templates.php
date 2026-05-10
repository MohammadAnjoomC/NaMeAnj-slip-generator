<?php
/**
 * NaMeAnj | Admin Template Management (Pro Version)
 * Features: Auto-crop (1300x880), Custom Naming, Delete, Dashboard Link.
 */
session_start();

// --- 1. SECURITY CHECK ---
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$template_dir = __DIR__ . '/../assets/templates/';
if (!file_exists($template_dir)) {
    mkdir($template_dir, 0777, true);
}

// --- 2. DELETE LOGIC ---
if (isset($_GET['delete'])) {
    $file_to_delete = $template_dir . basename($_GET['delete']);
    if (file_exists($file_to_delete)) {
        unlink($file_to_delete);
        header("Location: templates.php?status=deleted");
        exit();
    }
}

$status_msg = "";
if (isset($_GET['status'])) {
    if ($_GET['status'] === 'success') {
        $status_msg = "<div class='alert success'>✅ Templates added to library successfully!</div>";
    } elseif ($_GET['status'] === 'deleted') {
        $status_msg = "<div class='alert success' style='background:#fee2e2; color:#b91c1c; border-color:#fecaca;'>🗑️ Template removed from library!</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NaMeAnj Admin | Pro Manager</title>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Quicksand:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --admin-purple: #6366f1; --rose: #ff1493; --bg-light: #f8fafc; --dark: #1e293b; }
        
        body { font-family: 'Quicksand', sans-serif; background: var(--bg-light); margin: 0; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; }

        /* Top Navigation */
        .header-nav { 
            display: flex; justify-content: space-between; align-items: center; 
            margin-bottom: 25px; background: white; padding: 15px 30px; 
            border-radius: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); 
        }
        .header-nav a { text-decoration: none; color: var(--admin-purple); font-weight: 800; font-size: 0.9rem; display: flex; align-items: center; gap: 8px; }

        .admin-card { 
            background: white; padding: 40px; border-radius: 40px; 
            box-shadow: 0 15px 35px rgba(0,0,0,0.05); text-align: center; 
            border-top: 12px solid var(--admin-purple); margin-bottom: 40px;
        }

        .logo { font-family: 'Fredoka One', cursive; color: var(--rose); font-size: 2.5rem; margin: 0; }

        /* Upload Area */
        .upload-zone { 
            border: 3px dashed #cbd5e1; padding: 50px 20px; border-radius: 30px; 
            margin: 25px 0; background: #f1f5f9; cursor: pointer; transition: 0.3s; 
        }
        .upload-zone:hover { border-color: var(--admin-purple); background: #eef2ff; }
        
        /* Preview Grid */
        #preview-grid { 
            display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); 
            gap: 20px; margin-top: 25px; text-align: left; 
        }
        .preview-item { background: #f8fafc; padding: 12px; border-radius: 20px; border: 1px solid #e2e8f0; }
        .preview-item img { width: 100%; aspect-ratio: 1300/880; object-fit: cover; border-radius: 15px; background: #fff; }
        .preview-item input { 
            width: 100%; padding: 10px; margin-top: 10px; border: 2px solid #e2e8f0; 
            border-radius: 12px; font-size: 0.85rem; font-weight: 700; outline: none; box-sizing: border-box;
        }
        .preview-item input:focus { border-color: var(--admin-purple); }

        .btn-upload { 
            background: var(--admin-purple); color: white; border: none; 
            padding: 18px 40px; border-radius: 20px; font-weight: 800; 
            font-size: 1.1rem; cursor: pointer; width: 100%; transition: 0.3s;
            box-shadow: 0 10px 20px rgba(99, 102, 241, 0.2);
        }
        .btn-upload:hover { transform: translateY(-3px); box-shadow: 0 15px 25px rgba(99, 102, 241, 0.3); }

        /* Alert Styling */
        .alert { padding: 15px; border-radius: 15px; margin-bottom: 25px; font-weight: 700; background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }

        /* Current Library Section */
        .library-title { color: var(--dark); margin: 40px 0 20px 10px; display: flex; align-items: center; gap: 10px; }
        .library-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 25px; }
        .thumb-box { 
            background: white; padding: 15px; border-radius: 30px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.03); position: relative; border: 2px solid transparent; transition: 0.3s;
        }
        .thumb-box:hover { border-color: var(--admin-purple); }
        .thumb-box img { width: 100%; aspect-ratio: 1.4; object-fit: cover; border-radius: 20px; }
        .thumb-box .info { padding: 12px 5px; }
        .thumb-box .name { font-size: 0.9rem; font-weight: 800; color: var(--dark); display: block; margin-bottom: 10px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        
        .btn-delete { 
            background: #fee2e2; color: #b91c1c; border: none; padding: 8px; 
            width: 100%; border-radius: 12px; font-weight: 800; font-size: 0.75rem; 
            cursor: pointer; transition: 0.2s; 
        }
        .btn-delete:hover { background: #fecaca; }

        /* Global Loader */
        #loader { 
            display: none; position: fixed; inset: 0; background: rgba(255,255,255,0.95); 
            z-index: 10000; flex-direction: column; justify-content: center; align-items: center; 
        }
        .spinner { width: 50px; height: 50px; border: 6px solid #f3f3f3; border-top: 6px solid var(--rose); border-radius: 50%; animation: spin 1s linear infinite; }
        @keyframes spin { 100% { transform: rotate(360deg); } }
    </style>
</head>
<body>

<div id="loader">
    <div class="spinner"></div>
    <p id="loader-text" style="margin-top: 20px; font-weight: 800; color: var(--admin-purple);">CROP & PROCESSING...</p>
</div>

<div class="container">
    <div class="header-nav">
        <a href="dashboard.php">🏠 Dashboard</a>
        <a href="dashboard.php" style="color: var(--rose);">🖼️ View Gallery</a>
    </div>

    <div class="admin-card">
        <div class="logo">NaMeAnj Admin</div>
        <p style="color: #64748b; font-weight: 700;">Template Library Management</p>

        <?php echo $status_msg; ?>

        <div class="upload-zone" onclick="document.getElementById('fileInp').click()">
            <p id="zone-text" style="font-weight: 700; color: #475569;">📁 Tap to select images (Auto-crop enabled)</p>
        </div>
        <input type="file" id="fileInp" accept="image/*" multiple style="display:none" onchange="handleSelection()">

        <div id="preview-grid"></div>
        <button id="uploadBtn" class="btn-upload" style="display:none; margin-top: 30px;" onclick="uploadAll()">✨ Upload to Library</button>
    </div>

    <h3 class="library-title">📦 Library Assets</h3>
    <div class="library-grid">
        <?php 
        $existing_files = glob($template_dir . "*.{jpg,jpeg,png,webp}", GLOB_BRACE);
        if (empty($existing_files)): ?>
            <p style="grid-column: 1/-1; text-align: center; color: #94a3b8; font-style: italic;">No templates in library.</p>
        <?php else: 
            foreach($existing_files as $file): 
                $name = basename($file);
        ?>
            <div class="thumb-box">
                <img src="../assets/templates/<?php echo $name; ?>" alt="Template">
                <div class="info">
                    <span class="name"><?php echo $name; ?></span>
                    <button class="btn-delete" onclick="if(confirm('Delete this template forever?')) location.href='?delete=<?php echo urlencode($name); ?>'">🗑️ DELETE</button>
                </div>
            </div>
        <?php endforeach; endif; ?>
    </div>
</div>

<script>
let processedQueue = [];

async function handleSelection() {
    const files = document.getElementById('fileInp').files;
    const grid = document.getElementById('preview-grid');
    const uploadBtn = document.getElementById('uploadBtn');
    
    grid.innerHTML = '';
    processedQueue = [];

    if (files.length > 0) {
        document.getElementById('loader').style.display = 'flex';
        uploadBtn.style.display = 'block';

        for (let file of files) {
            const croppedData = await autoCropImage(file);
            const id = Math.random().toString(36).substr(2, 9);
            
            const div = document.createElement('div');
            div.className = 'preview-item';
            div.innerHTML = `
                <img src="${croppedData}">
                <input type="text" id="name_${id}" value="${file.name.split('.')[0]}" placeholder="Enter design name">
            `;
            grid.appendChild(div);
            processedQueue.push({ id: id, base64: croppedData });
        }
        document.getElementById('loader').style.display = 'none';
    }
}

/**
 * Auto Crops image to 1300x880 (Aspect Ratio of Nameslip)
 */
function autoCropImage(file) {
    return new Promise((resolve) => {
        const reader = new FileReader();
        reader.onload = (e) => {
            const img = new Image();
            img.onload = () => {
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                
                // Target Size
                canvas.width = 1300;
                canvas.height = 880;

                // Center Crop Logic (Object-fit: cover)
                let scale = Math.max(canvas.width / img.width, canvas.height / img.height);
                let x = (canvas.width / 2) - (img.width / 2) * scale;
                let y = (canvas.height / 2) - (img.height / 2) * scale;

                ctx.fillStyle = "white"; // Fallback bg
                ctx.fillRect(0, 0, canvas.width, canvas.height);
                ctx.drawImage(img, x, y, img.width * scale, img.height * scale);
                
                resolve(canvas.toBase64 ? canvas.toBase64() : canvas.toDataURL('image/jpeg', 0.9));
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    });
}

async function uploadAll() {
    const loader = document.getElementById('loader');
    const loaderText = document.getElementById('loader-text');
    
    loader.style.display = 'flex';
    loaderText.innerText = "SAVING TO SERVER...";

    for (let item of processedQueue) {
        const customName = document.getElementById(`name_${item.id}`).value || "Template";
        const formData = new FormData();
        formData.append('image', item.base64);
        formData.append('filename', customName);

        try {
            await fetch('template_action.php', { method: 'POST', body: formData });
        } catch (e) {
            console.error("Upload error for " + customName);
        }
    }

    window.location.href = "templates.php?status=success";
}
</script>

</body>
</html>


