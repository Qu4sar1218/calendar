<?php
// File: login.php
require_once 'config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('calendar.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Username and password are required';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                redirect('calendar.php');
            } else {
                $error = 'Invalid username or password';
            }
        } catch(PDOException $e) {
            $error = 'Login failed. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Login - Calauan Scheduler</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <div class="login-container">
        <div class="municipality-text">
            Calauan Municipality
        </div>
        
        <div class="login-header">
            <div class="login-header-content">
                <img src="logos/logo.png" alt="Calauan Municipality Logo 1" class="logo-img">
                <h2>Welcome Back</h2>
                <img src="logos/logo3.png" alt="Calauan Municipality Logo 2" class="logo-img">
            </div>
            <p>Sign in to your scheduler account</p>
        </div>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" id="loginForm">
            <div class="form-group">
                <label for="username">Username</label>
                <div class="input-wrapper">
                    <input type="text" name="username" id="username" 
                        value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" 
                        required placeholder="Enter your username">
                    <span class="input-icon">👤</span>
                </div>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrapper">
                    <input type="password" name="password" id="password" 
                        required placeholder="Enter your password">
                    <span class="input-icon">🔒</span>
                </div>
            </div>
            
            <button type="submit" class="login-btn" id="loginButton">Sign In</button>
        </form>
        
        <div class="links">
            <a href="register.php">Don't have an account? Create one here</a><br>
            <a href="index.php" class="back-home">← Back to Home</a>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', function() {
            const button = document.getElementById('loginButton');
            button.classList.add('loading');
            button.innerHTML = '';
        });
    </script>
</body>
</html>