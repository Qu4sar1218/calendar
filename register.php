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
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);

            if ($stmt->rowCount() > 0) {
                $error = 'Username or email already exists';
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                $stmt->execute([$username, $email, $hashed_password]);
                $success = 'Registration successful! You can now login.';
            }
        } catch (PDOException $e) {
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
    <link rel="stylesheet" href="style.css">
</head>
<body class="register-page">
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <div class="register-container">
        <div class="header">
            <div class="header-content">
                <div class="logo">
                    <img src="logos/logo.png" alt="Municipality Logo">
                </div>
                <div class="title-section">
                    <h1>Municipality of Calauan</h1>
                    <div class="subtitle">Calendar Management System</div>
                </div>
                <div class="logo">
                    <img src="logos/logo3.png" alt="Secondary Logo">
                </div>
            </div>
        </div>

        <div class="register-container">
            <h2>Register</h2>
            <p class="register-description">Fill in the details below to create your account</p>

            <?php if ($error): ?>
                <div class="register-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="register-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <form method="POST" id="registerForm">
                <div class="register-form-group">
                    <label for="username">Username *</label>
                    <div class="register-input-wrapper">
                        <input type="text" name="username" id="username"
                            value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                            required placeholder="Choose a username">
                        <span class="register-input-icon">👤</span>
                    </div>
                </div>

                <div class="register-form-group">
                    <label for="email">Email Address *</label>
                    <div class="register-input-wrapper">
                        <input type="email" name="email" id="email"
                            value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                            required placeholder="Enter your email">
                        <span class="register-input-icon">📧</span>
                    </div>
                </div>

                <div class="register-form-group">
                    <label for="password">Password *</label>
                    <div class="register-input-wrapper">
                        <input type="password" name="password" id="password"
                            required placeholder="Create a password">
                        <span class="register-input-icon">🔒</span>
                    </div>
                    <div class="register-password-strength" id="passwordStrength" style="display: none;">
                        <div class="strength-bar">
                            <div class="strength-fill" id="strengthFill"></div>
                        </div>
                        <span id="strengthText"></span>
                    </div>
                </div>

                <div class="register-form-group">
                    <label for="confirm_password">Confirm Password *</label>
                    <div class="register-input-wrapper">
                        <input type="password" name="confirm_password" id="confirm_password"
                            required placeholder="Confirm your password">
                        <span class="register-input-icon">🔒</span>
                    </div>
                    <div class="register-password-match" id="passwordMatch"></div>
                </div>

                <button type="submit" class="register-btn" id="registerButton">
                    Create Account
                </button>
            </form>

            <div class="register-links">
                <a href="login.php">Already have an account? Sign in here</a>
            </div>
        </div>
    </div>

    <script>
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const strengthIndicator = document.getElementById('passwordStrength');
        const strengthFill = document.getElementById('strengthFill');
        const strengthText = document.getElementById('strengthText');
        const passwordMatch = document.getElementById('passwordMatch');

        passwordInput.addEventListener('input', function () {
            const password = this.value;
            if (!password) {
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

        function checkPasswordMatch() {
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            if (!confirmPassword) {
                passwordMatch.textContent = '';
                return;
            }

            if (password === confirmPassword) {
                passwordMatch.textContent = '✓ Passwords match';
                passwordMatch.className = 'register-password-match match-success';
            } else {
                passwordMatch.textContent = '✗ Passwords do not match';
                passwordMatch.className = 'register-password-match match-error';
            }
        }

        passwordInput.addEventListener('input', checkPasswordMatch);
        confirmPasswordInput.addEventListener('input', checkPasswordMatch);

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

        document.getElementById('registerForm').addEventListener('submit', function (e) {
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;

            if (password !== confirmPassword) {
                e.preventDefault();
                passwordMatch.textContent = '✗ Passwords do not match';
                passwordMatch.className = 'register-password-match match-error';
                return;
            }

            const button = document.getElementById('registerButton');
            button.classList.add('loading');
            button.innerHTML = 'Creating...';
        });
    </script>
</body>
</html>
