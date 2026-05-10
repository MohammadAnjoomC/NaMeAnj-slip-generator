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
    <title>Membership Expired | NaMeAnj</title>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Quicksand:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { 
            --red: #ef4444; 
            --blue: #2193b0; 
            --whatsapp: #25D366;
        }

        body { 
            font-family: 'Quicksand', sans-serif; 
            background: #f8fafc; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            margin: 0; 
            text-align: center; 
            user-select: none;
        }

        .expired-card { 
            background: white; 
            padding: 50px 30px; 
            border-radius: 40px; 
            box-shadow: 0 20px 50px rgba(0,0,0,0.05); 
            border-top: 10px solid var(--red); 
            max-width: 420px; 
            width: 90%;
            animation: fadeIn 0.5s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .icon-box {
            background: #fff1f2;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 3rem;
        }

        h1 { 
            font-family: 'Fredoka One', cursive; 
            color: var(--red); 
            margin: 0; 
            font-size: 1.8rem; 
        }

        p { 
            color: #64748b; 
            font-weight: 600; 
            margin: 15px 0 30px; 
            line-height: 1.6; 
            font-size: 0.95rem;
        }

        .btn-stack {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .btn { 
            text-decoration: none; 
            padding: 16px; 
            border-radius: 15px; 
            font-weight: 700; 
            transition: 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-whatsapp { 
            background: var(--whatsapp); 
            color: white; 
            box-shadow: 0 8px 20px rgba(37, 211, 102, 0.2);
        }

        .btn-whatsapp:hover { 
            background: #1eb956;
            transform: translateY(-3px);
        }

        .btn-dashboard { 
            background: #f1f5f9; 
            color: #475569; 
        }

        .btn-dashboard:hover { 
            background: #e2e8f0; 
        }

        .footer-note {
            margin-top: 25px;
            font-size: 0.75rem;
            color: #94a3b8;
            font-weight: 600;
        }
    </style>
</head>
<body ondragstart="return false;" ondrop="return false;">

    <div class="expired-card">
        <div class="icon-box">⏳</div>
        <h1>Access Expired</h1>
        <p>Your membership validity has ended. You can still view your saved library, but the PDF Generator is currently locked.</p>
        
        <div class="btn-stack">
            <a href="https://wa.me/918848643715?text=Hello%20Anju,%20my%20NaMeAnj%20membership%20has%20expired.%20I%20would%20like%20to%20renew%20it." 
               class="btn btn-whatsapp" target="_blank">
                <span>💬</span> Renew via WhatsApp
            </a>
            
            <a href="../app/dashboard.php" class="btn btn-dashboard">
                Return to Dashboard
            </a>
        </div>

        <div class="footer-note">
            NaMeAnj Portal | Technical Support: +91 8848643715
        </div>
    </div>

</body>
</html>


