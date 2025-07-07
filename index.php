<?php
require_once 'config.php';

if (isLoggedIn()) {
    redirect('calendar.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Calauan Scheduler</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #800080;
            --primary-dark: #6a006a;
            --bg-light: #f9f9f9;
            --text-color: #333;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-color);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        header {
            background-color: var(--primary-color);
            padding: 1.2rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        .logo-img {
            height: 50px;
            width: auto;
            transition: transform 0.3s ease;
        }

        .logo-img:hover {
            transform: scale(1.05);
        }

        .logo-text {
            font-size: 1.8rem;
            font-weight: 600;
            color: white;
            text-decoration: none;
        }

        nav a {
            color: white;
            text-decoration: none;
            margin-left: 1rem;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            transition: background 0.3s ease;
            font-weight: 500;
        }

        nav a:hover {
            background-color: var(--primary-dark);
        }

        main {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 3rem 2rem;
        }

        .hero-card {
            background: white;
            padding: 3rem;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            text-align: center;
            max-width: 600px;
            width: 100%;
            animation: fadeIn 1s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h1 {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        p {
            font-size: 1.1rem;
            margin-bottom: 2rem;
            color: #555;
        }

        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            margin: 0.5rem;
            font-size: 1rem;
            border: none;
            border-radius: 8px;
            color: white;
            background-color: var(--primary-color);
            cursor: pointer;
            text-decoration: none;
            transition: background 0.3s ease;
        }

        .btn:hover {
            background-color: var(--primary-dark);
        }

        footer {
            background-color: var(--primary-color);
            color: white;
            text-align: center;
            padding: 1rem 2rem;
        }

        @media (max-width: 768px) {
            .header-left {
                gap: 0.5rem;
            }
            
            .logo-container {
                gap: 0.5rem;
            }
            
            .logo-img {
                height: 40px;
            }
            
            .logo-text {
                font-size: 1.5rem;
            }
        }

        @media (max-width: 600px) {
            .hero-card {
                padding: 2rem;
            }

            h1 {
                font-size: 2rem;
            }
            
            .logo-img {
                height: 35px;
            }
            
            .logo-text {
                font-size: 1.3rem;
            }
        }
    </style>
</head>
<body>

<header>
    <div class="header-left">
        <div class="logo-container">
            <img src="logo.png" alt="Calauan Logo 1" class="logo-img">
            
            <img src="logo1.png" alt="Calauan Logo 2" class="logo-img">
            <a href="#" class="logo-text">Calauan Scheduler</a>
        </div>
    </div>
    <nav>
        <a href="login.php">Login</a>
        <a href="register.php" class="btn">Register</a>
    </nav>
</header>

<main>
    <div class="hero-card">
        <h1>Welcome to Calauan's Official Scheduler</h1>
        <p>Efficiently manage appointments and schedules for our municipality. Built with our community in mind.</p>
        <a href="register.php" class="btn">Get Started</a>
        <a href="login.php" class="btn">Login</a>
    </div>
</main>

<footer>
    &copy; <?= date('Y') ?> Calauan Municipality | All rights reserved.
</footer>

</body>
</html>