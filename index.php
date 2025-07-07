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
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <!-- Header Section -->
    <div class="header">
        <div class="header-content">
            <div class="logo">
                <img src="logos/logo.png" alt="Calauan Logo 1">
            </div>
            <div class="title-section">
                <h1>Calauan Scheduler</h1>
                <p class="subtitle">Municipality of Calauan - Official Scheduling System</p>
            </div>
            <div class="logo">
                <img src="logos/logo3.png" alt="Calauan Logo 2">
            </div>
        </div>
    </div>

    <!-- User Info Section -->
    <div class="user-info">
        <div class="welcome-text">
            Welcome to Calauan's Official Scheduling System
        </div>
        <div class="nav-links">
            <a href="login.php" class="nav-link">Login</a>
            <a href="register.php" class="nav-link">Register</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="form-container">
        <div class="hero-card">
            <h1>Efficiently Manage Municipal Appointments</h1>
            <p>Welcome to the official scheduling system for the Municipality of Calauan. Our platform is designed to streamline appointment management and improve service delivery for our community.</p>
            
            <div class="button-group">
                <a href="register.php" class="btn btn-primary">Get Started</a>
                <a href="login.php" class="btn btn-secondary">Login</a>
            </div>
        </div>

        <!-- Features Section -->
        <div style="margin-top: 50px;">
            <h2 class="page-title">Why Choose Calauan Scheduler?</h2>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; margin-bottom: 40px;">
                <div class="event-info">
                    <h3>📅 Easy Scheduling</h3>
                    <p>Book appointments with municipal offices quickly and efficiently. Our intuitive calendar system makes scheduling a breeze.</p>
                </div>
                
                <div class="event-info">
                    <h3>🏛️ Official Platform</h3>
                    <p>This is the official scheduling system for Calauan Municipality, ensuring secure and reliable service for all residents.</p>
                </div>
                
                <div class="event-info">
                    <h3>📱 Mobile Friendly</h3>
                    <p>Access your appointments anywhere, anytime. Our responsive design works perfectly on all devices.</p>
                </div>
            </div>
        </div>

        <!-- Call to Action -->
        <div style="text-align: center; margin-top: 40px; padding: 30px; background: var(--primary-light); border-radius: var(--border-radius-large); border: 2px solid var(--primary-border);">
            <h3 style="color: var(--primary-color); margin-bottom: 20px;">Ready to Get Started?</h3>
            <p style="color: var(--text-muted); margin-bottom: 25px;">Join the Calauan community and start managing your municipal appointments today.</p>
            <div class="button-group">
                <a href="register.php" class="btn btn-primary">Create Account</a>
                <a href="login.php" class="btn btn-secondary">Sign In</a>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<div style="text-align: center; padding: 30px; color: var(--text-muted); background: var(--background-color); margin-top: 40px;">
    <p>&copy; <?= date('Y') ?> Municipality of Calauan | All rights reserved.</p>
    <p style="margin-top: 10px; font-size: 0.9rem;">Serving the Community with Excellence</p>
</div>

</body>
</html>