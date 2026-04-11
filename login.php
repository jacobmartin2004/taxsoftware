<?php
session_start();

// Already logged in? Go to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

require_once 'src/conn.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please enter both username and password.';
    } else {
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $stored = $user['password'];
            $valid = false;

            // Check bcrypt hash first
            if (password_verify($password, $stored)) {
                $valid = true;
            }
            // Fallback: plain-text match (first login after import) → auto-upgrade to bcrypt
            elseif ($stored === $password) {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $upd = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $upd->bind_param("si", $hashed, $user['id']);
                $upd->execute();
                $upd->close();
                $valid = true;
            }

            if ($valid) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                header('Location: index.php');
                exit;
            } else {
                $error = 'Invalid username or password.';
            }
        } else {
            $error = 'Invalid username or password.';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Delvin Diamond Tool Industries</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #0f1923 0%, #1a2942 40%, #2c3e5a 100%);
            position: relative;
            overflow: hidden;
        }

        /* Animated diamond particles */
        body::before, body::after {
            content: '';
            position: absolute;
            border: 2px solid rgba(232, 168, 56, 0.08);
            transform: rotate(45deg);
            animation: float 20s infinite linear;
        }
        body::before {
            width: 300px; height: 300px;
            top: -80px; right: -80px;
            animation-duration: 25s;
        }
        body::after {
            width: 200px; height: 200px;
            bottom: -50px; left: -50px;
            animation-duration: 20s;
            animation-direction: reverse;
        }

        @keyframes float {
            0% { transform: rotate(45deg) translateY(0); }
            50% { transform: rotate(225deg) translateY(-30px); }
            100% { transform: rotate(405deg) translateY(0); }
        }

        @keyframes sparkle {
            0%, 100% { opacity: 0.3; transform: scale(1); }
            50% { opacity: 1; transform: scale(1.2); }
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-container {
            width: 420px;
            animation: slideUp 0.6s ease-out;
            position: relative;
            z-index: 10;
        }

        /* Diamond Logo */
        .logo-section {
            text-align: center;
            margin-bottom: 32px;
        }
        .diamond-logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 16px;
            position: relative;
        }
        .diamond-logo .diamond {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #e8a838 0%, #f0c05e 50%, #d4952e 100%);
            transform: rotate(45deg);
            border-radius: 6px;
            position: absolute;
            top: 50%;
            left: 50%;
            margin-top: -25px;
            margin-left: -25px;
            box-shadow: 0 0 30px rgba(232, 168, 56, 0.4);
        }
        .diamond-logo .diamond::before {
            content: '';
            position: absolute;
            top: 8px; left: 8px; right: 8px; bottom: 8px;
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 3px;
        }
        .diamond-logo .diamond::after {
            content: '\f1c8';
            font-family: 'bootstrap-icons';
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 20px;
            color: rgba(255,255,255,0.9);
        }
        /* Sparkle dots around diamond */
        .sparkle {
            position: absolute;
            width: 6px; height: 6px;
            background: #e8a838;
            border-radius: 50%;
            animation: sparkle 3s infinite;
        }
        .sparkle:nth-child(2) { top: 5px; left: 50%; margin-left: -3px; animation-delay: 0s; }
        .sparkle:nth-child(3) { top: 50%; right: 0; margin-top: -3px; animation-delay: 0.8s; }
        .sparkle:nth-child(4) { bottom: 5px; left: 50%; margin-left: -3px; animation-delay: 1.6s; }
        .sparkle:nth-child(5) { top: 50%; left: 0; margin-top: -3px; animation-delay: 2.4s; }

        .logo-section h1 {
            font-size: 22px;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .logo-section p {
            font-size: 13px;
            color: rgba(255,255,255,0.5);
            margin-top: 4px;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        /* Card */
        .login-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.3);
        }

        .login-card h2 {
            color: #ffffff;
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 6px;
        }
        .login-card .subtitle {
            color: rgba(255,255,255,0.45);
            font-size: 13px;
            margin-bottom: 28px;
        }

        /* Input Groups */
        .input-group {
            position: relative;
            margin-bottom: 20px;
        }
        .input-group label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: rgba(255,255,255,0.6);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }
        .input-wrapper {
            position: relative;
        }
        .input-wrapper i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 18px;
            color: rgba(255,255,255,0.3);
            transition: color 0.3s;
        }
        .input-wrapper input {
            width: 100%;
            padding: 14px 16px 14px 48px;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            font-size: 15px;
            color: #ffffff;
            outline: none;
            transition: all 0.3s;
        }
        .input-wrapper input::placeholder {
            color: rgba(255,255,255,0.25);
        }
        .input-wrapper input:focus {
            border-color: #e8a838;
            background: rgba(232, 168, 56, 0.06);
            box-shadow: 0 0 0 3px rgba(232, 168, 56, 0.1);
        }
        .input-wrapper input:focus + i,
        .input-wrapper input:focus ~ i {
            color: #e8a838;
        }

        /* Toggle password */
        .toggle-password {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: rgba(255,255,255,0.3);
            cursor: pointer;
            font-size: 18px;
            padding: 0;
            transition: color 0.3s;
        }
        .toggle-password:hover {
            color: rgba(255,255,255,0.6);
        }

        /* Error Alert */
        .error-alert {
            background: rgba(239, 68, 68, 0.12);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 10px;
            padding: 12px 16px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            color: #fca5a5;
            animation: slideUp 0.3s ease-out;
        }
        .error-alert i {
            font-size: 18px;
            color: #ef4444;
        }

        /* Submit Button */
        .btn-login {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #e8a838 0%, #d4952e 100%);
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 700;
            color: #1a2942;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 8px;
        }
        .btn-login:hover {
            background: linear-gradient(135deg, #f0c05e 0%, #e8a838 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(232, 168, 56, 0.3);
        }
        .btn-login:active {
            transform: translateY(0);
        }

        /* Footer */
        .login-footer {
            text-align: center;
            margin-top: 24px;
            color: rgba(255,255,255,0.25);
            font-size: 12px;
        }
        .login-footer i { color: #e8a838; }

        /* Floating diamond decorations */
        .bg-diamond {
            position: fixed;
            border: 1px solid rgba(232, 168, 56, 0.06);
            transform: rotate(45deg);
            border-radius: 4px;
            pointer-events: none;
        }
        .bg-diamond:nth-child(1) { width: 120px; height: 120px; top: 10%; left: 5%; animation: float 18s infinite; }
        .bg-diamond:nth-child(2) { width: 80px; height: 80px; top: 60%; right: 10%; animation: float 22s infinite reverse; }
        .bg-diamond:nth-child(3) { width: 60px; height: 60px; bottom: 15%; left: 15%; animation: float 15s infinite; animation-delay: 3s; }
        .bg-diamond:nth-child(4) { width: 150px; height: 150px; top: 20%; right: 20%; animation: float 28s infinite reverse; animation-delay: 5s; }

        /* Responsive */
        @media (max-width: 480px) {
            .login-container { width: 92%; margin: 20px; }
            .login-card { padding: 28px 24px; }
            .logo-section h1 { font-size: 18px; }
        }
    </style>
</head>
<body>
    <!-- Background diamonds -->
    <div class="bg-diamond"></div>
    <div class="bg-diamond"></div>
    <div class="bg-diamond"></div>
    <div class="bg-diamond"></div>

    <div class="login-container">
        <!-- Logo -->
        <div class="logo-section">
            <div class="diamond-logo">
                <div class="diamond"></div>
                <span class="sparkle"></span>
                <span class="sparkle"></span>
                <span class="sparkle"></span>
                <span class="sparkle"></span>
            </div>
            <h1>Delvin Diamond</h1>
            <p>Tool Industries</p>
        </div>

        <!-- Login Card -->
        <div class="login-card">
            <h2>Welcome Back</h2>
            <p class="subtitle">Sign in to your account</p>

            <?php if ($error): ?>
                <div class="error-alert">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php" autocomplete="off">
                <div class="input-group">
                    <label>Username</label>
                    <div class="input-wrapper">
                        <input type="text" name="username" placeholder="Enter your username"
                               value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                               required autofocus>
                        <i class="bi bi-person-fill"></i>
                    </div>
                </div>

                <div class="input-group">
                    <label>Password</label>
                    <div class="input-wrapper">
                        <input type="password" name="password" id="password"
                               placeholder="Enter your password" required>
                        <i class="bi bi-lock-fill"></i>
                        <button type="button" class="toggle-password" onclick="togglePassword()">
                            <i class="bi bi-eye-fill" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    <i class="bi bi-box-arrow-in-right"></i>
                    Sign In
                </button>
            </form>
        </div>

        <div class="login-footer">
            <i class="bi bi-gem"></i> Delvin Diamond Tool Industries &copy; <?php echo date('Y'); ?>
        </div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('toggleIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye-fill');
                icon.classList.add('bi-eye-slash-fill');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash-fill');
                icon.classList.add('bi-eye-fill');
            }
        }
    </script>
</body>
</html>
