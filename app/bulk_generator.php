<?php
session_start();
$user_db_path = __DIR__ . '/../database/users.json';

// --- 1. ACCESS SECURITY ---
if (!isset($_SESSION['user_logged_in'])) {
    header("Location: ../auth/login.php");
    exit();
}

$username = $_SESSION['username'];
$is_expired = true;

if (file_exists($user_db_path)) {
    $users = json_decode(file_get_contents($user_db_path), true);
    foreach ($users as $user) {
        if ($user['username'] === $username && isset($user['valid_until'])) {
            $today = new DateTime();
            $expiry = new DateTime($user['valid_until']);
            if ($expiry > $today) { $is_expired = false; }
            break;
        }
    }
}

if ($is_expired) {
    header("Location: ../auth/expired.php"); 
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NaMeAnj | Pro PDF Generator</title>
    <!-- jsPDF for PDF generation -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <!-- PDF.js for PDF extraction -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
    
    <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Quicksand:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --blue: #2193b0; --rose: #ff1493; --cyan: #6dd5ed; --dark: #1e293b; }
        body { 
            font-family: 'Quicksand', sans-serif; background: #f1f5f9; margin: 0; 
            display: flex; justify-content: center; align-items: center; min-height: 100vh;
            user-select: none; padding: 20px 0;
        }
        
        .container { 
            width: 92%; max-width: 420px; background: white; padding: 35px; 
            border-radius: 40px; box-shadow: 0 20px 40px rgba(0,0,0,0.05); 
            text-align: center; border-bottom: 12px solid var(--blue); position: relative;
        }

        .home-btn { position: absolute; top: 25px; left: 30px; text-decoration: none; color: var(--blue); font-weight: 700; font-size: 0.85rem; }
        .logo { font-family: 'Fredoka One', cursive; color: var(--rose); font-size: 2.4rem; margin-top: 10px; }
        .subtitle { color: #64748b; font-size: 0.85rem; margin-bottom: 30px; font-weight: 600; }

        .form-group { text-align: left; margin-bottom: 22px; }
        label { display: block; margin-bottom: 10px; font-weight: 700; color: var(--dark); font-size: 0.95rem; }
        
        input[type="text"], input[type="file"], input[type="number"] { 
            width: 100%; padding: 15px; border: 2px solid #e2e8f0; border-radius: 18px; 
            box-sizing: border-box; outline: none; transition: 0.3s; font-size: 1rem;
        }
        input[type="text"]:focus, input[type="number"]:focus { border-color: var(--blue); background: #f8fafc; }

        .selector-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .card-opt {
            border: 2px solid #f1f5f9; padding: 14px; border-radius: 16px; cursor: pointer;
            display: flex; align-items: center; gap: 10px; transition: 0.2s; background: #fafafa;
        }
        .card-opt input { accent-color: var(--blue); width: 18px; height: 18px; cursor: pointer; }
        .card-opt span { font-size: 0.85rem; font-weight: 700; color: #475569; }
        .card-opt:has(input:checked) { border-color: var(--blue); background: #f0f9ff; }
        .card-opt:has(input:checked) span { color: var(--blue); }

        .img-config-area {
            max-height: 250px; overflow-y: auto; margin-top: 10px; padding-right: 5px;
        }
        .img-config-area::-webkit-scrollbar { width: 6px; }
        .img-config-area::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        
        .img-config-row {
            display: flex; align-items: center; justify-content: space-between; gap: 15px; 
            margin-bottom: 10px; padding: 10px; background: #f8fafc; border-radius: 16px;
            border: 1px solid #e2e8f0;
        }
        .img-preview { width: 60px; height: 60px; object-fit: cover; border-radius: 10px; border: 2px solid white; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .qty-input { width: 80px !important; padding: 10px !important; text-align: center; font-weight: bold; }
        .qty-label { font-size: 0.8rem; color: #64748b; font-weight: 700; margin-bottom: 4px; display: block;}

        .btn-gen { 
            background: linear-gradient(135deg, var(--blue), var(--cyan)); color: white; 
            border: none; padding: 20px; width: 100%; border-radius: 20px; 
            font-size: 1.2rem; font-weight: 800; cursor: pointer; transition: 0.3s; 
            margin-top: 10px; display: flex; align-items: center; justify-content: center; gap: 12px;
            box-shadow: 0 10px 25px rgba(33, 147, 176, 0.2);
        }
        .btn-gen:hover { transform: translateY(-3px); box-shadow: 0 15px 30px rgba(33, 147, 176, 0.3); }

        #loader { 
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
            background: rgba(255,255,255,0.97); z-index: 10000; 
            flex-direction: column; justify-content: center; align-items: center; 
        }
        .spin { width: 50px; height: 50px; border: 6px solid #f3f3f3; border-top: 6px solid var(--rose); border-radius: 50%; animation: rot 0.8s linear infinite; }
        @keyframes rot { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body>

<div id="loader">
    <div class="spin"></div>
    <p id="loader-text" style="margin-top: 20px; font-weight: 800; color: var(--blue);">PROCESSING...</p>
</div>

<div class="container">
    <a href="dashboard.php" class="home-btn">🏠 Home</a>
    <div class="logo">NaMeAnj</div>
    <p class="subtitle">A4 & A3 PDF Extract & Generate</p>
    
    <div class="form-group">
        <label>File Name:</label>
        <input type="text" id="pdfName" placeholder="e.g. Slips_Project" required>
    </div>

    <div class="form-group">
        <label>Paper Size:</label>
        <div class="selector-grid">
            <label class="card-opt">
                <input type="radio" name="pSize" value="a3">
                <span>A3 (30 Slips)</span>
            </label>
            <label class="card-opt">
                <input type="radio" name="pSize" value="a4" checked>
                <span>A4 (9 Slips)</span>
            </label>
        </div>
    </div>

    <div class="form-group">
        <label>Upload Media (PDF or Images):</label>
        <input type="file" id="mediaInp" accept="application/pdf, image/*" multiple onchange="processUploads()">
        <div id="image-config-area" class="img-config-area"></div>
    </div>

    <button class="btn-gen" onclick="startGeneration()">🚀 Generate & Save</button>
</div>

<script>
    // Initialize PDF.js Worker
    const pdfjsLib = window['pdfjs-dist/build/pdf'];
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';

    let extractedImages = []; // Will store base64 strings of uploaded images or PDF pages

    window.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('autoload') === 'true') {
            const imgData = sessionStorage.getItem('auto_load_image');
            if (imgData) {
                extractedImages.push(imgData);
                renderConfigUI();
                sessionStorage.removeItem('auto_load_image');
            }
        }
    });

    async function processUploads() {
        const files = document.getElementById('mediaInp').files;
        const configArea = document.getElementById('image-config-area');
        
        if (files.length === 0) {
            configArea.innerHTML = '';
            extractedImages = [];
            return;
        }

        document.getElementById('loader-text').innerText = "EXTRACTING MEDIA...";
        document.getElementById('loader').style.display = 'flex';
        extractedImages = [];

        try {
            for (let i = 0; i < files.length; i++) {
                const file = files[i];

                if (file.type === 'application/pdf') {
                    // Extract pages from PDF as images
                    const arrayBuffer = await file.arrayBuffer();
                    const pdf = await pdfjsLib.getDocument({data: arrayBuffer}).promise;
                    
                    for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
                        const page = await pdf.getPage(pageNum);
                        
                        // Increased scale to 3.0 for higher base resolution
                        const viewport = page.getViewport({ scale: 3.0 }); 
                        const canvas = document.createElement('canvas');
                        const ctx = canvas.getContext('2d');
                        canvas.width = viewport.width;
                        canvas.height = viewport.height;
                        
                        await page.render({ canvasContext: ctx, viewport: viewport }).promise;
                        
                        // Switched to 'image/png' to ensure completely lossless, uncompressed extraction
                        extractedImages.push(canvas.toDataURL('image/png'));
                    }
                } else if (file.type.startsWith('image/')) {
                    // Process normal image files losslessly
                    extractedImages.push(await toBase64(file));
                }
            }
            
            renderConfigUI();
        } catch (error) {
            alert("Error processing files: " + error.message);
        } finally {
            document.getElementById('loader').style.display = 'none';
        }
    }

    function renderConfigUI() {
        const configArea = document.getElementById('image-config-area');
        configArea.innerHTML = '';
        
        extractedImages.forEach((imgSrc, index) => {
            configArea.innerHTML += `
                <div class="img-config-row">
                    <div style="display:flex; align-items:center; gap: 15px;">
                        <img src="${imgSrc}" class="img-preview" alt="Preview ${index+1}">
                        <span style="font-weight: 700; color: var(--dark); font-size: 0.9rem;">Image ${index + 1}</span>
                    </div>
                    <div>
                        <span class="qty-label">Pieces</span>
                        <input type="number" id="qty_${index}" class="qty-input" value="1" min="0">
                    </div>
                </div>
            `;
        });
    }

    async function startGeneration() {
        const name = document.getElementById('pdfName').value.trim();
        const size = document.querySelector('input[name="pSize"]:checked').value;

        if (!name || extractedImages.length === 0) { 
            alert("Missing file name or media to process!"); 
            return; 
        }

        // Build flat array of images based on requested quantities
        let flatImagesArray = [];
        for (let i = 0; i < extractedImages.length; i++) {
            const qty = parseInt(document.getElementById(`qty_${i}`).value) || 0;
            for (let q = 0; q < qty; q++) {
                flatImagesArray.push(extractedImages[i]);
            }
        }

        if (flatImagesArray.length === 0) {
            alert("Please specify at least 1 piece for one of the images.");
            return;
        }

        document.getElementById('loader-text').innerText = "BUILDING SLIPS...";
        document.getElementById('loader').style.display = 'flex';

        try {
            const { jsPDF } = window.jspdf;
            
            // CONFIG: 0.2cm Border = 2mm margin
            const cfg = (size === 'a4') 
                ? { format: [297, 210], rows: 3, cols: 3, margin: 2 } 
                : { format: [420, 297], rows: 6, cols: 5, margin: 5 }; 

            const doc = new jsPDF({ orientation: 'l', unit: 'mm', format: cfg.format });

            const gap = 0.5; // Small divider gap
            const availW = cfg.format[0] - (cfg.margin * 2);
            const availH = cfg.format[1] - (cfg.margin * 2);
            
            const sW = (availW - (gap * (cfg.cols - 1))) / cfg.cols;
            const sH = (availH - (gap * (cfg.rows - 1))) / cfg.rows;
            const itemsPerPage = cfg.rows * cfg.cols;

            for (let i = 0; i < flatImagesArray.length; i++) {
                // If the current page is full, add a new page
                if (i > 0 && i % itemsPerPage === 0) {
                    doc.addPage(cfg.format, 'l');
                }

                const pageItemIndex = i % itemsPerPage;
                const r = Math.floor(pageItemIndex / cfg.cols);
                const c = pageItemIndex % cfg.cols;

                const x = cfg.margin + (c * (sW + gap));
                const y = cfg.margin + (r * (sH + gap));
                
                // Dynamically check if the image is PNG or JPEG to avoid forced compression errors
                const formatString = flatImagesArray[i].substring(11, flatImagesArray[i].indexOf(';')); 
                const isJpeg = (formatString === 'jpeg' || formatString === 'jpg');
                const imgFormat = isJpeg ? 'JPEG' : 'PNG';

                // Replaced 'FAST' compression with 'NONE' to retain exact image data
                doc.addImage(flatImagesArray[i], imgFormat, x, y, sW, sH, undefined, 'NONE');
                
                // Border outline
                doc.setDrawColor(230);
                doc.setLineWidth(0.05);
                doc.rect(x, y, sW, sH);
            }

            const b64 = doc.output('datauristring').split(',')[1];
            const fd = new FormData();
            fd.append('pdf', b64);
            fd.append('name', name);

            const res = await fetch('save_document.php', { method: 'POST', body: fd });
            if ((await res.text()).trim() === "success") {
                doc.save(`${name}.pdf`);
                window.location.href = "library.php";
            } else {
                alert("Upload failed.");
            }

        } catch (e) {
            alert("Error: " + e.message);
        } finally {
            document.getElementById('loader').style.display = 'none';
        }
    }

    function toBase64(file) {
        return new Promise((res) => {
            const rd = new FileReader();
            rd.onload = () => res(rd.result);
            rd.readAsDataURL(file);
        });
    }
</script>

</body>
</html>


