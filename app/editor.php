<?php
session_start();
if (!isset($_SESSION['user_logged_in'])) { header("Location: ../auth/login.php"); exit(); }
$template_path = "../assets/templates/" . (isset($_GET['temp']) ? $_GET['temp'] : 'default.jpg');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NaMeAnj | Pro Editor</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Alkatra&family=Bangers&family=Berkshire+Swash&family=Caveat:wght@700&family=Comfortaa:wght@700&family=Cookie&family=Courgette&family=Creepster&family=Damion&family=Dancing+Script:wght@700&family=Diplomata+SC&family=Domine:wght@700&family=Eater&family=Exo+2:wght@900&family=Fascinate&family=Fredoka+One&family=Great+Vibes&family=Grenze+Gotisch:wght@900&family=Handlee&family=Indie+Flower&family=Kaushan+Script&family=Lobster&family=Luckiest+Guy&family=Macondo&family=Monoton&family=Mountains+of+Christmas:wght@700&family=Niconne&family=Nosifer&family=Pacifico&family=Parisienne&family=Permanent+Marker&family=Playball&family=Press+Start+2P&family=Quicksand:wght@700&family=Righteous&family=Russo+One&family=Sacramento&family=Satisfy&family=Shadows+Into+Light&family=Shrikhand&family=Special+Elite&family=Spicy+Rice&family=Titan+One&family=Yellowtail&display=swap" rel="stylesheet">
    
    <style>
        :root { --blue: #2193b0; --rose: #ff1493; --cyan: #6dd5ed; --dark: #1e293b; }
        body { font-family: 'Quicksand', sans-serif; background: #f1f5f9; margin: 0; padding: 10px; display: flex; flex-direction: column; align-items: center; }
        
        .nav-header { width: 100%; max-width: 1100px; display: flex; justify-content: flex-start; padding: 10px 0; }
        .nav-header a { text-decoration: none; color: var(--blue); font-weight: 700; font-size: 0.9rem; display: flex; align-items: center; gap: 5px; }

        .editor-container { 
            display: flex; flex-direction: row; gap: 20px; width: 100%; max-width: 1100px; 
            background: white; padding: 25px; border-radius: 40px; 
            box-shadow: 0 20px 40px rgba(0,0,0,0.05); border-bottom: 12px solid var(--blue);
        }

        .controls { width: 350px; border-right: 1px solid #e2e8f0; padding-right: 20px; }
        .preview-box { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; position: sticky; top: 10px; }
        
        canvas { width: 100%; border: 2px solid #f1f5f9; border-radius: 20px; background: #fff; }
        
        .form-group { margin-bottom: 15px; }
        .section-label { font-weight: 800; font-size: 0.9rem; color: var(--dark); margin-bottom: 8px; display: flex; align-items: center; gap: 8px; }
        
        input[type="text"], select { width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 15px; font-size: 0.95rem; outline: none; }

        .slider-ui { background: #f8fafc; padding: 12px; border-radius: 18px; border: 1px solid #e2e8f0; margin-top: 10px; }
        .slider-row { display: flex; align-items: center; gap: 10px; margin-bottom: 8px; }
        .slider-row span { font-size: 11px; font-weight: 800; width: 75px; color: #64748b; }
        input[type="range"] { flex: 1; accent-color: var(--rose); }
        input[type="color"] { border: none; width: 30px; height: 30px; cursor: pointer; background: none; }

        .btn-gen { 
            background: linear-gradient(135deg, var(--blue), var(--cyan)); color: white; 
            border: none; padding: 18px; width: 100%; border-radius: 20px; 
            font-size: 1.1rem; font-weight: 800; cursor: pointer; margin-top: 15px;
        }

        #loader { 
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
            background: rgba(255,255,255,0.95); z-index: 10000; 
            flex-direction: column; justify-content: center; align-items: center; 
        }
        .spin { width: 50px; height: 50px; border: 6px solid #f3f3f3; border-top: 6px solid var(--rose); border-radius: 50%; animation: rot 0.8s linear infinite; }
        @keyframes rot { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .hidden-ctrl { display: none; }

        @media (max-width: 768px) {
            .editor-container { flex-direction: column; }
            .controls { width: 100%; border-right: none; padding-right: 0; }
        }
    </style>
</head>
<body>

<div id="loader">
    <div class="spin"></div>
    <p id="loader-text" style="margin-top: 20px; font-weight: 800; color: var(--blue);">PROCESSING...</p>
</div>

<div class="nav-header">
    <a href="dashboard.php">⬅️ Back to Gallery</a>
</div>

<div class="editor-container">
    <div class="controls">
        <h3 style="color: var(--rose); font-family: 'Fredoka One', cursive; margin: 0 0 20px 0; font-size: 1.8rem; text-align: center;">NaMeAnj</h3>
        
        <div class="form-group">
            <div class="section-label">📝 Text Information</div>
            <input type="text" id="nameInp" value="Mohammed Anjoom" oninput="draw()" placeholder="Student Name" style="margin-bottom: 8px;">
            <input type="text" id="line1Inp" value="<?php echo htmlspecialchars($_SESSION['default_l1'] ?? 'Class............... Div............... R. No...............'); ?>" oninput="draw()" placeholder="Line 1" style="margin-bottom: 8px; font-size: 0.85rem;">
            <input type="text" id="line2Inp" value="<?php echo htmlspecialchars($_SESSION['default_l2'] ?? 'Subject.............................................................'); ?>" oninput="draw()" placeholder="Line 2" style="margin-bottom: 8px; font-size: 0.85rem;">
            <input type="text" id="line3Inp" value="<?php echo htmlspecialchars($_SESSION['default_l3'] ?? 'School...............................................................'); ?>" oninput="draw()" placeholder="Line 3" style="font-size: 0.85rem;">
        </div>
        
        <div class="form-group">
            <div class="section-label">🖼️ Student Photo</div>
            <input type="file" id="photoInp" accept="image/*" onchange="handleFile(this)" style="font-size: 12px;">
        </div>

        <div class="form-group">
            <div class="section-label">⚙️ Quick Controls</div>
            <select id="manageTarget" onchange="switchMenu()" style="background: #f0f9ff; border-color: var(--blue); font-weight: 700;">
                <option value="name">Adjust Name Style</option>
                <option value="photo">Adjust Photo Style</option>
                <option value="details">Adjust Details Style</option>
            </select>

            <div class="slider-ui">
                <div id="nameControls">
                    <div class="slider-row">
                        <span>🎨 Color</span>
                        <input type="color" id="nameColor" value="#000000" oninput="draw()">
                        <select id="nameFont" onchange="draw()" style="padding:5px; font-size:12px;">
                            <option value="Pacifico">Pacifico</option>
                            <option value="Bangers">Bangers</option>
                            <option value="Dancing Script">Dancing Script</option>
                            <option value="Permanent Marker">Marker</option>
                        </select>
                    </div>
                    <div class="slider-row"><span>📏 Size</span><input type="range" id="nameSize" min="20" max="200" value="90" oninput="draw()"></div>
                    <div class="slider-row"><span>↔️ Pos X</span><input type="range" id="nameX" min="0" max="1300" value="550" oninput="draw()"></div>
                    <div class="slider-row"><span>↕️ Pos Y</span><input type="range" id="nameY" min="0" max="880" value="220" oninput="draw()"></div>
                </div>

                <div id="photoControls" class="hidden-ctrl">
                    <div class="slider-row"><span>📐 Width</span><input type="range" id="photoW" min="100" max="800" value="440" oninput="draw()"></div>
                    <div class="slider-row"><span>↔️ Pos X</span><input type="range" id="photoX" min="-200" max="1100" value="45" oninput="draw()"></div>
                    <div class="slider-row"><span>↕️ Pos Y</span><input type="range" id="photoY" min="-200" max="800" value="115" oninput="draw()"></div>
                </div>

                <div id="detailsControls" class="hidden-ctrl">
                    <div class="slider-row">
                        <span>🎨 Color</span>
                        <input type="color" id="detailsColor" value="#000000" oninput="draw()">
                        <select id="detailsFont" onchange="draw()" style="padding:5px; font-size:12px;">
                            <option value="Arial">Arial</option>
                            <option value="Quicksand">Quicksand</option>
                            <option value="Special Elite">Typewriter</option>
                        </select>
                    </div>
                    <div class="slider-row"><span>📏 Size</span><input type="range" id="detailsSize" min="15" max="80" value="42" oninput="draw()"></div>
                    <div class="slider-row"><span>↕️ Line Gap</span><input type="range" id="detailsSpacing" min="40" max="200" value="100" oninput="draw()"></div>
                    <div class="slider-row"><span>↔️ Pos X</span><input type="range" id="detailsX" min="0" max="1300" value="550" oninput="draw()"></div>
                    <div class="slider-row"><span>↕️ Pos Y</span><input type="range" id="detailsY" min="200" max="850" value="420" oninput="draw()"></div>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="section-label">✨ Remove Background?</div>
            <select id="bgOption">
                <option value="yes">Yes (Pro Mode)</option>
                <option value="no" selected>No</option>
            </select>
        </div>

        <button class="btn-gen" onclick="processAndSave()">🚀 Generate PDF & Save</button>
        <button class="btn-gen" onclick="downloadImage()" style="background: white; color: var(--blue); border: 2px solid var(--blue); margin-top: 10px;">🖼️ Download Image Only</button>
        <button class="btn-gen" onclick="sendToGenerator()" style="background: white; color: var(--rose); border: 2px dashed var(--rose); margin-top: 10px;">🖨️ Send to Sheet Maker (A4/A3)</button>
    </div>

    <div class="preview-box">
        <canvas id="editorCanvas"></canvas>
    </div>
</div>

<script>
    const canvas = document.getElementById('editorCanvas');
    const ctx = canvas.getContext('2d');
    canvas.width = 1300; canvas.height = 880;
    
    let bgImg = new Image();
    let userPhoto = null;
    let rawImageFile = null; // Store original file for API

    const urlParams = new URLSearchParams(window.location.search);
    const temp = urlParams.get('temp');

    if (temp === 'custom') {
        bgImg.src = localStorage.getItem('custom_bg');
    } else {
        bgImg.src = '../assets/templates/' + temp;
    }

    bgImg.onload = draw;

    function downloadImage() {
        const link = document.createElement('a');
        link.download = (document.getElementById('nameInp').value || "Nameslip") + ".png";
        link.href = canvas.toDataURL("image/png");
        link.click();
    }

    function switchMenu() {
        const target = document.getElementById('manageTarget').value;
        ['name', 'photo', 'details'].forEach(id => document.getElementById(id + 'Controls').classList.add('hidden-ctrl'));
        document.getElementById(target + 'Controls').classList.remove('hidden-ctrl');
    }

    function handleFile(input) {
        if (input.files && input.files[0]) {
            rawImageFile = input.files[0];
            const reader = new FileReader();
            reader.onload = (e) => { 
                userPhoto = new Image(); 
                userPhoto.onload = draw; 
                userPhoto.src = e.target.result; 
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function draw() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        // Draw Background (Maintains Aspect Ratio without stretching)
        if (bgImg.width && bgImg.height) {
            let scale = Math.max(canvas.width / bgImg.width, canvas.height / bgImg.height);
            let cx = (canvas.width / 2) - (bgImg.width / 2) * scale;
            let cy = (canvas.height / 2) - (bgImg.height / 2) * scale;
            
            ctx.fillStyle = "#ffffff";
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.drawImage(bgImg, cx, cy, bgImg.width * scale, bgImg.height * scale);
        } else {
            // Fallback
            ctx.drawImage(bgImg, 0, 0, 1300, 880);
        }
        
        if(userPhoto) {
            const px = parseInt(document.getElementById('photoX').value), 
                  py = parseInt(document.getElementById('photoY').value), 
                  pw = parseInt(document.getElementById('photoW').value);
            ctx.drawImage(userPhoto, px, py, pw, pw * (userPhoto.height / userPhoto.width));
        }

        // Draw Name with selected color
        ctx.fillStyle = document.getElementById('nameColor').value;
        ctx.font = `${document.getElementById('nameSize').value}px ${document.getElementById('nameFont').value}`;
        ctx.fillText(document.getElementById('nameInp').value, parseInt(document.getElementById('nameX').value), parseInt(document.getElementById('nameY').value));

        // Draw Details with selected color
        const dx = parseInt(document.getElementById('detailsX').value);
        const dy = parseInt(document.getElementById('detailsY').value);
        const gap = parseInt(document.getElementById('detailsSpacing').value);
        ctx.fillStyle = document.getElementById('detailsColor').value;
        ctx.font = `${document.getElementById('detailsSize').value}px ${document.getElementById('detailsFont').value}`;
        
        ctx.fillText(document.getElementById('line1Inp').value, dx, dy);
        ctx.fillText(document.getElementById('line2Inp').value, dx, dy + gap);
        ctx.fillText(document.getElementById('line3Inp').value, dx, dy + (gap * 2));
    }

    // Function to process image via remove.bg API
    async function removeBackground() {
        if (!rawImageFile) return false;
        
        const apiKey = "YOUR_REMOVE_BG_API_KEY_HERE";
        const formData = new FormData();
        formData.append('image_file', rawImageFile);
        formData.append('size', 'auto');

        try {
            const response = await fetch('https://api.remove.bg/v1.0/removebg', {
                method: 'POST',
                headers: { 'X-Api-Key': apiKey },
                body: formData
            });

            if (!response.ok) {
                throw new Error(`API Error: ${response.status} ${response.statusText}`);
            }

            const blob = await response.blob();
            return new Promise((resolve) => {
                const img = new Image();
                img.onload = () => resolve(img);
                img.src = URL.createObjectURL(blob);
            });
        } catch (error) {
            console.error("Background removal failed:", error);
            alert("Failed to remove background. Proceeding with original image.");
            return false;
        }
    }

    async function processAndSave() {
        const loader = document.getElementById('loader');
        const loaderText = document.getElementById('loader-text');
        const bgOption = document.getElementById('bgOption').value;

        loader.style.display = 'flex';
        
        // Trigger API if option is selected and image exists
        if (bgOption === 'yes' && userPhoto && rawImageFile) {
            loaderText.innerText = "REMOVING BACKGROUND (PRO)...";
            const processedImg = await removeBackground();
            if (processedImg) {
                userPhoto = processedImg; // Update local reference with clear background
                document.getElementById('bgOption').value = 'no'; // Prevent duplicate API calls
                draw(); // Refresh canvas
            }
        }

        loaderText.innerText = "GENERATING PDF...";
        
        setTimeout(async () => {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('l', 'mm', [130, 88]);
            doc.addImage(canvas.toDataURL('image/png'), 'PNG', 0, 0, 130, 88, undefined, 'NONE');
            
            const pdfName = document.getElementById('nameInp').value || "Nameslip";
            const b64 = doc.output('datauristring').split(',')[1];
            
            const fd = new FormData();
            fd.append('pdf', b64);
            fd.append('name', pdfName);

            try {
                const saveRes = await fetch('save_document.php', { method: 'POST', body: fd });
                if ((await saveRes.text()).trim() === "success") {
                    doc.save(`${pdfName}.pdf`);
                    window.location.href = "library.php";
                } else {
                    doc.save(`${pdfName}.pdf`);
                }
            } catch (err) {
                console.error(err);
                doc.save(`${pdfName}.pdf`);
            }
            
            loader.style.display = 'none';
            loaderText.innerText = "PROCESSING..."; // reset text
        }, 1000);
    }

    async function sendToGenerator() {
        const loader = document.getElementById('loader');
        const loaderText = document.getElementById('loader-text');
        const bgOption = document.getElementById('bgOption').value;

        loader.style.display = 'flex';
        
        // Trigger API if option is selected and image exists
        if (bgOption === 'yes' && userPhoto && rawImageFile) {
            loaderText.innerText = "REMOVING BACKGROUND (PRO)...";
            const processedImg = await removeBackground();
            if (processedImg) {
                userPhoto = processedImg; 
                document.getElementById('bgOption').value = 'no'; 
                draw(); 
            }
        }

        loaderText.innerText = "SENDING TO SHEET MAKER...";
        
        setTimeout(() => {
            sessionStorage.setItem('auto_load_image', canvas.toDataURL("image/png"));
            window.location.href = "bulk_generator.php?autoload=true";
        }, 500);
    }
</script>
</body>
</html>


