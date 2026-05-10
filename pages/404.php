<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found | NaMeAnj</title>
    <meta http-equiv="refresh" content="7;url=index.php">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Quicksand:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --rose: #ffafbd;
            --soft-pink: #ffc3a0;
            --accent: #ff1493;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Quicksand', sans-serif;
            background: linear-gradient(135deg, var(--rose), var(--soft-pink), #eef2f3);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            color: #444;
            overflow: hidden;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .error-container {
            background: rgba(255, 255, 255, 0.4);
            backdrop-filter: blur(15px);
            padding: 50px;
            border-radius: 40px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.05);
            border: 1px solid rgba(255,255,255,0.3);
            max-width: 90%;
        }

        h1 {
            font-family: 'Fredoka One', cursive;
            font-size: 6rem;
            margin: 0;
            color: var(--accent);
            text-shadow: 2px 2px white;
        }

        h2 {
            font-size: 2rem;
            margin-bottom: 10px;
        }

        p {
            font-size: 1.1rem;
            color: #666;
        }

        .timer {
            font-weight: 700;
            color: var(--accent);
            font-size: 1.3rem;
        }

        .home-btn {
            display: inline-block;
            margin-top: 25px;
            padding: 12px 30px;
            background: var(--accent);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 700;
            transition: 0.3s;
            box-shadow: 0 8px 20px rgba(255, 20, 147, 0.3);
        }

        .home-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 25px rgba(255, 20, 147, 0.4);
        }
    </style>
</head>
<body>

    <div class="error-container">
        <h1>404</h1>
        <h2>Oops! Page Lost.</h2>
        <p>It seems this page went on a vacation. 🎨</p>
        <p>Redirecting you back to home in <span id="countdown" class="timer">7</span> seconds...</p>
        
        <a href="../index.php" class="home-btn">Go Home Now 🚀</a>
    </div>

    <script>
        // Countdown Logic
        let timeLeft = 7;
        const countdownElement = document.getElementById('countdown');

        const timer = setInterval(() => {
            timeLeft--;
            countdownElement.textContent = timeLeft;
            if (timeLeft <= 0) {
                clearInterval(timer);
                window.location.href = "index.php";
            }
        }, 1000);
    </script>

</body>
</html>


