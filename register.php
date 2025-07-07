<?php
// File: register.php
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($username) || empty($email) || empty($password)) {
        $error = 'All fields are required';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        try {
            // Check if username or email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            
            if ($stmt->rowCount() > 0) {
                $error = 'Username or email already exists';
            } else {
                // Insert new user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                $stmt->execute([$username, $email, $hashed_password]);
                
                $success = 'Registration successful! You can now login.';
            }
        } catch(PDOException $e) {
            $error = 'Registration failed. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Calendar System</title>
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

        .register-container {
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

        .register-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #800080, #a020a0, #800080);
        }

        .register-header {
            text-align: center;
            margin-bottom: 35px;
            position: relative;
        }

        .header-with-logos {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            margin-bottom: 8px;
            flex-wrap: wrap;
        }

        .logo {
            width: 40px;
            height: 40px;
        }

        .logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .register-header h2 {
            color: #800080;
            font-size: 2.2rem;
            font-weight: 600;
            text-shadow: 0 2px 4px rgba(128, 0, 128, 0.1);
            position: relative;
        }

        .register-header p {
            color: #666;
            font-size: 1rem;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
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
        .form-group input[type="email"],
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
        .form-group input[type="email"]:focus,
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

        .register-btn {
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

        .register-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(128, 0, 128, 0.4);
        }

        .register-btn:active {
            transform: translateY(0);
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

        .success {
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-weight: 500;
            text-align: center;
            animation: slideIn 0.5s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        @keyframes slideIn {
            0% { transform: translateY(-20px); opacity: 0; }
            100% { transform: translateY(0); opacity: 1; }
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
        }

        .links a:hover {
            color: #a020a0;
            transform: translateY(-1px);
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
            top: 15%;
            left: 10%;
            animation-delay: 0s;
        }

        .shape:nth-child(2) {
            width: 60px;
            height: 60px;
            top: 70%;
            right: 10%;
            animation-delay: 2s;
        }

        .shape:nth-child(3) {
            width: 100px;
            height: 100px;
            bottom: 15%;
            left: 20%;
            animation-delay: 4s;
        }

        .shape:nth-child(4) {
            width: 40px;
            height: 40px;
            top: 40%;
            right: 20%;
            animation-delay: 1s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        .password-strength {
            margin-top: 8px;
            font-size: 0.85rem;
        }

        .strength-bar {
            height: 4px;
            background: #e1e1e1;
            border-radius: 2px;
            overflow: hidden;
            margin-top: 5px;
        }

        .strength-fill {
            height: 100%;
            transition: all 0.3s ease;
            border-radius: 2px;
        }

        .strength-weak { background: #ff6b6b; width: 33%; }
        .strength-medium { background: #ffa726; width: 66%; }
        .strength-strong { background: #4CAF50; width: 100%; }

        .password-match {
            margin-top: 8px;
            font-size: 0.85rem;
            transition: all 0.3s ease;
        }

        .match-success { color: #4CAF50; }
        .match-error { color: #ff6b6b; }

        @media (max-width: 480px) {
            .register-container {
                padding: 30px 25px;
                margin: 10px;
            }
            
            .register-header h2 {
                font-size: 1.8rem;
            }

            .header-with-logos {
                gap: 15px;
            }

            .logo {
                width: 35px;
                height: 35px;
                font-size: 18px;
            }
        }

        @media (max-width: 360px) {
            .header-with-logos {
                flex-direction: column;
                gap: 10px;
            }
            
            .logo {
                width: 30px;
                height: 30px;
                font-size: 16px;
            }
        }

        /* Loading animation for form submission */
        .register-btn.loading {
            pointer-events: none;
            opacity: 0.8;
        }

        .register-btn.loading::after {
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
        <div class="shape"></div>
    </div>

    <div class="register-container">
        <div class="register-header">
            <div class="header-with-logos">
                <div class="logo logo-left">
                    <img src="logo.png" alt="Logo 1">
                </div>
                <h2>Create Account</h2>
                <div class="logo logo-right">
                    <img src="logo1.png" alt="Logo 2">
                </div>
            </div>
            <p>Join us and start managing your calendar</p>
        </div>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <form method="POST" id="registerForm">
            <div class="form-group">
                <label for="username">Username</label>
                <div class="input-wrapper">
                    <input type="text" name="username" id="username" 
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" 
                           required placeholder="Choose a username">
                    <span class="input-icon">👤</span>
                </div>
            </div>
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <div class="input-wrapper">
                    <input type="email" name="email" id="email" 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                           required placeholder="Enter your email">
                    <span class="input-icon">📧</span>
                </div>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrapper">
                    <input type="password" name="password" id="password" 
                           required placeholder="Create a strong password">
                    <span class="input-icon">🔒</span>
                </div>
                <div class="password-strength" id="passwordStrength" style="display: none;">
                    <div class="strength-bar">
                        <div class="strength-fill" id="strengthFill"></div>
                    </div>
                    <span id="strengthText"></span>
                </div>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <div class="input-wrapper">
                    <input type="password" name="confirm_password" id="confirm_password" 
                           required placeholder="Confirm your password">
                    <span class="input-icon">🔒</span>
                </div>
                <div class="password-match" id="passwordMatch"></div>
            </div>
            
            <button type="submit" class="register-btn" id="registerButton">
                Create Account
            </button>
        </form>
        
        <div class="links">
            <a href="login.php">Already have an account? Sign in here</a>
        </div>
    </div>

    <script>
        // Password strength checker
        const passwordInput = document.getElementById('password');
        const strengthIndicator = document.getElementById('passwordStrength');
        const strengthFill = document.getElementById('strengthFill');
        const strengthText = document.getElementById('strengthText');

        passwordInput.addEventListener('input', function() {
            const password = this.value;
            if (password.length === 0) {
                strengthIndicator.style.display = 'none';
                return;
            }

            strengthIndicator.style.display = 'block';
            const strength = calculatePasswordStrength(password);
            
            strengthFill.className = 'strength-fill';
            if (strength < 3) {
                strengthFill.classList.add('strength-weak');
                strengthText.textContent = 'Weak password';
                strengthText.style.color = '#ff6b6b';
            } else if (strength < 5) {
                strengthFill.classList.add('strength-medium');
                strengthText.textContent = 'Medium strength';
                strengthText.style.color = '#ffa726';
            } else {
                strengthFill.classList.add('strength-strong');
                strengthText.textContent = 'Strong password';
                strengthText.style.color = '#4CAF50';
            }
        });

        // Password confirmation checker
        const confirmPasswordInput = document.getElementById('confirm_password');
        const passwordMatch = document.getElementById('passwordMatch');

        function checkPasswordMatch() {
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            
            if (confirmPassword.length === 0) {
                passwordMatch.textContent = '';
                return;
            }

            if (password === confirmPassword) {
                passwordMatch.textContent = '✓ Passwords match';
                passwordMatch.className = 'password-match match-success';
            } else {
                passwordMatch.textContent = '✗ Passwords do not match';
                passwordMatch.className = 'password-match match-error';
            }
        }

        confirmPasswordInput.addEventListener('input', checkPasswordMatch);
        passwordInput.addEventListener('input', checkPasswordMatch);

        function calculatePasswordStrength(password) {
            let strength = 0;
            if (password.length >= 6) strength++;
            if (password.length >= 10) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            return strength;
        }

        // Add loading animation on form submission
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                passwordMatch.textContent = '✗ Passwords do not match';
                passwordMatch.className = 'password-match match-error';
                return;
            }

            const button = document.getElementById('registerButton');
            button.classList.add('loading');
            button.innerHTML = '';
        });

        // Add focus animations
        const inputs = document.querySelectorAll('input[type="text"], input[type="email"], input[type="password"]');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.parentElement.style.transform = 'translateY(-2px)';
            });
            
            input.addEventListener('blur', function() {
                if (!this.value) {
                    this.parentElement.parentElement.style.transform = 'translateY(0)';
                }
            });
        });
    </script>
</body>
</html>