<?php
session_start();
/**
 * NaMeAnj - Terms & Conditions (Strict A3 Focus)
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms & Conditions | NaMeAnj</title>
    <link rel="icon" type="image/png" href="/images/icon.png">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Quicksand:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --rose: #ffafbd;
            --soft-pink: #ffc3a0;
            --accent: #ff1493;
            --white: #ffffff;
        }

        body {
            margin: 0;
            font-family: 'Quicksand', sans-serif;
            background: linear-gradient(135deg, var(--rose), var(--soft-pink), #eef2f3);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            color: #333;
            line-height: 1.6;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 40px;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(15px);
            border-radius: 40px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.1);
            border: 1px solid rgba(255,255,255,0.4);
        }

        h1 {
            font-family: 'Fredoka One', cursive;
            color: var(--accent);
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 30px;
        }

        .section {
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px dashed rgba(255, 20, 147, 0.2);
        }

        h2 {
            font-size: 1.3rem;
            color: #444;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        p, li {
            font-size: 1rem;
            color: #555;
            font-weight: 500;
        }

        .highlight {
            color: var(--accent);
            font-weight: 700;
        }

        .back-btn {
            display: block;
            width: fit-content;
            margin: 30px auto 0;
            text-decoration: none;
            padding: 12px 30px;
            background: var(--accent);
            color: white;
            border-radius: 50px;
            font-weight: 700;
            transition: 0.3s;
            box-shadow: 0 8px 20px rgba(255, 20, 147, 0.3);
        }

        .back-btn:hover {
            transform: scale(1.05);
        }

        footer {
            text-align: center;
            padding: 20px;
            font-size: 0.8rem;
            color: #666;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>Terms of Service 📜</h1>

        <div class="section">
            <h2>1. Service Description</h2>
            <p>NaMeAnj is a <span class="highlight">digital-only tool</span>. We provide an automated system for users to generate ultra-high-resolution <span class="highlight">A3 PDF files</span> for name slips. We do not provide physical items, stickers, or delivery.</p>
        </div>

        <div class="section">
            <h2>2. Membership Access</h2>
            <ul>
                <li>Access is granted via membership. Accounts remain active until the <span class="highlight">validity period</span> expires.</li>
                <li>Expired accounts can still log in to view their account dashboard but will be restricted from generating new A3 PDFs until a renewal is processed.</li>
            </ul>
        </div>

        <div class="section">
            <h2>3. Usage Restrictions</h2>
            <p>The service is intended for personal and educational use. Users must not attempt to scrape data, bypass the login system, or share membership credentials with unauthorized parties.</p>
        </div>

        <div class="section">
            <h2>4. File Management</h2>
            <p>Your generated PDFs are stored in the cloud for easy access from any device. However, it is recommended to download and back up your final files. NaMeAnj is not liable for data loss due to server maintenance or expired memberships.</p>
        </div>

        <div class="section">
            <h2>5. Printing Responsibility</h2>
            <p>We guarantee high-resolution PDF outputs optimized for <span class="highlight">A3 dimensions</span>. The user is solely responsible for the printing process, including ink, paper choice, and printer settings.</p>
        </div>

        <a href="../index.php" class="back-btn">I Understand, Take Me Home 🚀</a>
    </div>

    <footer>
        &copy; 2026 NaMeAnj Portal | Focus: High-Res A3 Generation
    </footer>

</body>
</html>


