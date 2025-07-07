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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #800080 0%, #a020a0 50%, #800080 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(128, 0, 128, 0.3);
            padding: 40px;
            width: 100%;
            max-width: 450px;
            position: relative;
            overflow: hidden;
        }

        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #800080, #a020a0, #800080);
        }

        .login-header {
            text-align: center;
            margin-bottom: 35px;
            position: relative;
        }

        .login-header-content {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            margin-bottom: 15px;
        }

        .logo-img {
            height: 50px;
            width: auto;
            transition: transform 0.3s ease;
            filter: drop-shadow(0 4px 8px rgba(128, 0, 128, 0.2));
        }

        .logo-img:hover {
            transform: scale(1.05);
        }

        .login-header h2 {
            color: #800080;
            font-size: 2.2rem;
            font-weight: 600;
            margin: 0;
        }

        .municipality-text {
            text-align: center;
            color: #666;
            font-size: 0.9rem;
            font-weight: 500;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .login-header p {
            color: #666;
            font-size: 1rem;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
            font-size: 0.95rem;
        }

        .input-wrapper {
            position: relative;
        }

        .form-group input[type="text"],
        .form-group input[type="password"] {
            width: 100%;
            padding: 15px 50px 15px 20px;
            border: 2px solid #e1e1e1;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #fafafa;
        }

        .form-group input[type="text"]:focus,
        .form-group input[type="password"]:focus {
            outline: none;
            border-color: #800080;
            background: white;
            box-shadow: 0 0 0 3px rgba(128, 0, 128, 0.1);
            transform: translateY(-2px);
        }

        .input-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #800080;
            font-size: 1.2rem;
        }

        .login-btn {
            width: 100%;
            background: linear-gradient(135deg, #800080, #a020a0);
            color: white;
            padding: 16px;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 20px;
            position: relative;
            overflow: hidden;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(128, 0, 128, 0.4);
        }

        .error {
            background: linear-gradient(135deg, #ff6b6b, #ff5252);
            color: white;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-weight: 500;
            text-align: center;
            animation: shake 0.5s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .links {
            text-align: center;
            margin-top: 25px;
            padding-top: 25px;
            border-top: 1px solid #e1e1e1;
        }

        .links a {
            color: #800080;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-block;
            margin-top: 10px;
        }

        .links a:hover {
            color: #a020a0;
            transform: translateY(-1px);
        }

        .back-home {
            background: #f0e6f6;
            color: #800080;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            margin-top: 15px;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .back-home:hover {
            background: #e0d0e6;
            color: #6a006a;
        }

        .floating-shapes {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
        }

        .shape {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        .shape:nth-child(1) {
            width: 80px;
            height: 80px;
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }

        .shape:nth-child(2) {
            width: 60px;
            height: 60px;
            top: 60%;
            right: 10%;
            animation-delay: 2s;
        }

        .shape:nth-child(3) {
            width: 100px;
            height: 100px;
            bottom: 20%;
            left: 20%;
            animation-delay: 4s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 30px 25px;
                margin: 10px;
                max-width: 400px;
            }
            
            .login-header h2 {
                font-size: 1.8rem;
            }

            .logo-img {
                height: 40px;
            }

            .login-header-content {
                gap: 15px;
            }

            .municipality-text {
                font-size: 0.8rem;
            }
        }

        @media (max-width: 380px) {
            .logo-img {
                height: 35px;
            }
            
            .login-header-content {
                gap: 10px;
            }
            
            .login-header h2 {
                font-size: 1.6rem;
            }
        }

        .login-btn.loading {
            pointer-events: none;
            opacity: 0.8;
        }

        .login-btn.loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid transparent;
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }


    </style>
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
                <img src="logo.png" alt="Calauan Municipality Logo 1" class="logo-img">
                <h2>Welcome Back</h2>
                <img src="logo1.png" alt="Calauan Municipality Logo 2" class="logo-img">
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