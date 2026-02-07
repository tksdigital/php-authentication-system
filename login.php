<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';
require_once 'db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                if (!$user['is_email_verified']) {
                    $error = 'Please verify your email before logging in.';
                } elseif (password_verify($password, $user['password_hash'])) {
                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['name'] = $user['name'];
                    
                    // Check if profile is complete
                    if (empty($user['phone']) || empty($user['country']) || empty($user['state_city'])) {
                        header('Location: complete-profile.php');
                        exit();
                    }
                    
                    header('Location: dashboard.php');
                    exit();
                } else {
                    $error = 'Invalid email or password';
                }
            } else {
                $error = 'Invalid email or password';
            }
        } catch (PDOException $e) {
            $error = 'An error occurred. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login - TKS ULTRA</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
:root {
    --primary-color: #2c3e50;
    --secondary-color: #3498db;
    --accent-color: #e74c3c;
    --success-color: #27ae60;
    --warning-color: #f39c12;
    --light-bg: #f8f9fa;
    --dark-text: #2c3e50;
    --light-text: #7f8c8d;
    --border-color: #e1e8ed;
    --shadow: 0 4px 12px rgba(0,0,0,0.08);
    --gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --glass-bg: rgba(255, 255, 255, 0.15);
    --glass-border: rgba(255, 255, 255, 0.1);
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Inter', 'Poppins', sans-serif;
    -webkit-tap-highlight-color: transparent;
}

body {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: url('https://images.unsplash.com/photo-1506905925346-21bda4d32df4?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80') no-repeat center center fixed;
    background-size: cover;
    padding: 20px;
    touch-action: manipulation;
    -webkit-text-size-adjust: 100%;
    -ms-text-size-adjust: 100%;
}

.main-container {
    display: flex;
    width: 100%;
    max-width: 1100px;
    min-height: 650px;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
}

.character-section {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px;
    background: var(--glass-bg);
    backdrop-filter: blur(15px);
    -webkit-backdrop-filter: blur(15px);
    border-right: 1px solid var(--glass-border);
}

.character-image {
    width: 100%;
    max-width: 400px;
    height: auto;
    border-radius: 0;
    box-shadow: none;
    border: none;
}

.login-section {
    flex: 1;
    background: var(--glass-bg);
    backdrop-filter: blur(15px);
    -webkit-backdrop-filter: blur(15px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px;
    border-left: 1px solid var(--glass-border);
}

.login-container {
    width: 100%;
    max-width: 400px;
}

.auth-header {
    text-align: center;
    margin-bottom: 40px;
}

.logo-container {
    display: flex;
    justify-content: center;
    margin-bottom: 20px;
}

.website-logo {
    object-fit: contain;
    border-radius: 0;
    box-shadow: none;
    border: none;
    background: transparent;
    transition: all 0.3s ease;
}

.auth-title {
    color: white;
    font-size: 32px;
    font-weight: 700;
    margin-bottom: 10px;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.auth-subtitle {
    color: rgba(255, 255, 255, 0.9);
    font-size: 16px;
    font-weight: 400;
}

.form-group {
    margin-bottom: 24px;
    position: relative;
}

.input-container {
    position: relative;
    width: 100%;
}

.form-control {
    width: 100%;
    padding: 14px 50px 14px 16px;
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 12px;
    font-size: 15px;
    transition: all 0.3s ease;
    background: rgba(255, 255, 255, 0.95);
    box-sizing: border-box;
    height: 50px;
    font-family: 'Inter', sans-serif;
    font-size: 16px;
    color: #333;
}

.form-control:focus {
    outline: none;
    border-color: #4B7BFF;
    box-shadow: 0 0 0 3px rgba(75, 123, 255, 0.2);
    background: rgba(255, 255, 255, 0.98);
}

.form-control::placeholder {
    color: #666;
    font-family: 'Inter', sans-serif;
}

.input-icon {
    position: absolute;
    right: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: #666;
    font-size: 18px;
    font-weight: normal;
    pointer-events: none;
}

.password-toggle {
    position: absolute;
    right: 16px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #666;
    cursor: pointer;
    padding: 4px;
    border-radius: 4px;
    transition: color 0.3s ease;
    z-index: 2;
    font-size: 16px;
    font-weight: normal;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.password-toggle:hover {
    color: var(--secondary-color);
}

.btn-primary {
    width: 100%;
    padding: 16px 20px;
    background: white;
    color: #111;
    border: none;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    height: 55px;
    font-family: 'Inter', sans-serif;
    touch-action: manipulation;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.btn-primary:hover {
    background: #F1F1F1;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    cursor: pointer;
}

.btn-primary:active {
    transform: translateY(0);
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

/* Google button styles */
.google-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    padding: 14px 20px;
    background: white;
    color: var(--dark-text);
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-radius: 10px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    margin-top: 20px;
    touch-action: manipulation;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    font-family: 'Inter', sans-serif;
}

.google-btn:hover {
    border-color: var(--secondary-color);
    background: var(--light-bg);
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
}

.google-icon {
    width: 20px;
    height: 20px;
    margin-right: 12px;
    background: url('https://upload.wikimedia.org/wikipedia/commons/thumb/c/c1/Google_%22G%22_logo.svg/32px-Google_%22G%22_logo.svg.png') no-repeat center center;
    background-size: contain;
}

/* Link button styles */
.link-btn {
    background: none;
    border: none;
    color: #EAEAEA;
    text-decoration: none;
    font-weight: 500;
    font-size: 14px;
    transition: color 0.3s ease;
    cursor: pointer;
    font-family: 'Inter', sans-serif;
    padding: 0;
    margin: 0;
}

.link-btn:hover {
    color: #FFFFFF;
    text-decoration: underline;
}

.auth-links {
    text-align: center;
    margin-top: 30px;
}

.text-center {
    text-align: center;
}

.mt-3 {
    margin-top: 20px;
}

.text-muted {
    color: rgba(255, 255, 255, 0.8);
}

.divider {
    text-align: center;
    margin: 30px 0;
    position: relative;
    color: rgba(255, 255, 255, 0.8);
    font-size: 14px;
}

.divider::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 50%;
    margin-right: 15px;
    height: 1px;
    background: rgba(255, 255, 255, 0.3);
}

.divider::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    margin-left: 15px;
    right: 0;
    height: 1px;
    background: rgba(255, 255, 255, 0.3);
}

.alert {
    padding: 14px 16px;
    border-radius: 10px;
    margin-bottom: 24px;
    font-size: 14px;
    font-weight: 500;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.alert-danger {
    color: #c0392b;
    border-color: rgba(231, 76, 60, 0.3);
}

.alert-success {
    color: #27ae60;
    border-color: rgba(39, 174, 96, 0.3);
}

.alert-icon {
    display: inline-block;
    margin-right: 8px;
    font-weight: bold;
    font-size: 16px;
}

/* Loading animation */
.btn-loading {
    pointer-events: none;
    opacity: 0.8;
}

.btn-loading::after {
    content: '';
    position: absolute;
    width: 20px;
    height: 20px;
    top: 50%;
    left: 50%;
    margin-left: -10px;
    margin-top: -10px;
    border: 2px solid #111;
    border-radius: 50%;
    border-top-color: transparent;
    animation: spin 0.8s ease infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* =========================================== */
/* RESPONSIVE DESIGN FOR MOBILE AND TABLET */
/* =========================================== */

/* Tablet Devices (768px and below) */
@media (max-width: 768px) {
    .character-section {
        display: none;
    }
    
    .main-container {
        max-width: 500px;
        min-height: 600px;
        margin: 20px auto;
    }
    
    .login-section {
        padding: 35px 30px;
        width: 100%;
        border-left: none;
    }
    
    .login-container {
        max-width: 100%;
    }
    
    .auth-title {
        font-size: 28px;
    }
    
    .auth-subtitle {
        font-size: 15px;
    }
    
    .form-control {
        height: 48px;
        font-size: 16px;
    }
    
    .btn-primary {
        height: 52px;
        font-size: 16px;
    }
    
    .google-btn {
        padding: 13px 18px;
        font-size: 15px;
    }
}

/* Mobile Devices (480px and below) */
@media (max-width: 480px) {
    body {
        padding: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 100vh;
    }
    
    .main-container {
        max-width: 100%;
        min-height: auto;
        margin: 0 auto;
        border-radius: 16px;
        height: auto;
    }
    
    .login-section {
        padding: 30px 25px;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        border-left: none;
    }
    
    .login-container {
        max-width: 100%;
    }
    
    .auth-header {
        margin-bottom: 35px;
    }
    
    .auth-title {
        font-size: 26px;
        margin-bottom: 8px;
    }
    
    .auth-subtitle {
        font-size: 14px;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-control {
        height: 46px;
        font-size: 16px;
        padding: 12px 45px 12px 14px;
        border-radius: 10px;
    }
    
    .input-icon {
        font-size: 16px;
        right: 14px;
    }
    
    .password-toggle {
        font-size: 15px;
        right: 14px;
    }
    
    .btn-primary {
        height: 50px;
        font-size: 16px;
        border-radius: 10px;
    }
    
    .google-btn {
        padding: 12px 16px;
        font-size: 15px;
        border-radius: 10px;
    }
    
    .divider {
        margin: 25px 0;
        font-size: 13px;
    }
    
    .alert {
        padding: 12px 14px;
        font-size: 13px;
        margin-bottom: 20px;
    }
    
    .auth-links {
        margin-top: 25px;
    }
    
    .link-btn {
        font-size: 13px;
    }
    
    .text-muted {
        font-size: 13px;
    }
}

/* Small Mobile Devices (360px and below) */
@media (max-width: 360px) {
    body {
        padding: 12px;
    }
    
    .main-container {
        border-radius: 14px;
    }
    
    .login-section {
        padding: 25px 20px;
        border-left: none;
    }
    
    .auth-title {
        font-size: 24px;
    }
    
    .auth-subtitle {
        font-size: 13px;
    }
    
    .form-control {
        height: 44px;
        font-size: 16px;
        padding: 11px 40px 11px 12px;
    }
    
    .btn-primary {
        height: 48px;
        font-size: 15px;
    }
    
    .input-icon {
        font-size: 15px;
        right: 12px;
    }
    
    .password-toggle {
        font-size: 14px;
        right: 12px;
    }
}

/* Ensure no vertical scrolling on mobile */
@media (max-height: 700px) and (max-width: 768px) {
    body {
        align-items: flex-start;
        padding-top: 20px;
        padding-bottom: 20px;
    }
    
    .main-container {
        min-height: auto;
        height: auto;
    }
}

/* Landscape mode on mobile */
@media (max-height: 500px) and (max-width: 768px) {
    body {
        padding: 10px;
    }
    
    .main-container {
        min-height: auto;
        max-height: 95vh;
    }
    
    .login-section {
        padding: 20px;
        border-left: none;
    }
    
    .auth-header {
        margin-bottom: 20px;
    }
    
    .form-group {
        margin-bottom: 15px;
    }
}

/* Disable zoom on input focus for iOS */
@media screen and (max-width: 768px) {
    input, select, textarea {
        font-size: 16px !important;
    }
}
</style>
</head>
<body>
<div class="main-container">
    <div class="character-section">
        <img src="https://tksultra.in/LOGIN/assets/images/robot.png" alt="Character" class="character-image">
    </div>
    
    <div class="login-section">
        <div class="login-container">
            <div class="auth-header">
                <div class="logo-container">
                    <!-- Single logo with responsive sizing -->
                    <img src="https://tksultra.in/LOGIN/assets/images/logo.png" alt="TKS ULTRA Logo" class="website-logo" id="responsiveLogo">
                </div>
                <h1 class="auth-title">Welcome Back</h1>
                <p class="auth-subtitle">Sign in to your account to continue</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <span class="alert-icon"><i class="fas fa-exclamation-triangle"></i></span>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['registered'])): ?>
                <div class="alert alert-success">
                    <span class="alert-icon"><i class="fas fa-check-circle"></i></span>
                    Registration successful! Please check your email to verify your account.
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['reset'])): ?>
                <div class="alert alert-success">
                    <span class="alert-icon"><i class="fas fa-check-circle"></i></span>
                    Your password has been reset successfully. You can now login with your new password.
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" id="loginForm">
                <div class="form-group">
                    <div class="input-container">
                        <input type="email" id="email" name="email" class="form-control" required 
                               placeholder="Enter Email" autocomplete="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        <i class="input-icon fas fa-envelope"></i>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="input-container">
                        <input type="password" id="password" name="password" class="form-control" required 
                               placeholder="Password" autocomplete="current-password">
                        <button type="button" class="password-toggle" id="passwordToggle">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="form-group">
                    <button type="submit" name="login" class="btn-primary" id="loginBtn">
                        Sign In
                    </button>
                </div>
                
                <div class="text-center">
                    <button type="button" class="link-btn" id="forgotPasswordBtn">
                        Forgot your password?
                    </button>
                </div>
                
                <div class="divider">OR</div>
                
                <button type="button" class="google-btn" id="googleLoginBtn">
                    <span class="google-icon"></span>
                    Continue with Google
                </button>
                
                <div class="auth-links">
                    <p class="text-muted">Don't have an account? 
                        <button type="button" class="link-btn" id="createAccountBtn">Create Account!</button>
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Logo responsive sizing function
function resizeLogo() {
    const logo = document.getElementById('responsiveLogo');
    const screenWidth = window.innerWidth;
    const originalWidth = 200; // Original logo width
    const originalHeight = 50; // Original logo height
    const aspectRatio = originalHeight / originalWidth;
    
    let newWidth;
    
    if (screenWidth >= 1200) {
        // Large desktop
        newWidth = 200;
    } else if (screenWidth >= 992) {
        // Desktop
        newWidth = 180;
    } else if (screenWidth >= 768) {
        // Tablet landscape
        newWidth = 160;
    } else if (screenWidth >= 576) {
        // Tablet portrait
        newWidth = 140;
    } else if (screenWidth >= 480) {
        // Large mobile
        newWidth = 120;
    } else if (screenWidth >= 360) {
        // Medium mobile
        newWidth = 100;
    } else {
        // Small mobile
        newWidth = 80;
    }
    
    const newHeight = Math.round(newWidth * aspectRatio);
    
    logo.style.width = newWidth + 'px';
    logo.style.height = newHeight + 'px';
}

// Navigation functions
function navigateTo(url) {
    window.location.href = url;
}

function navigateToGoogleAuth() {
    window.location.href = 'google-auth.php?action=login';
}

function navigateToForgotPassword() {
    window.location.href = 'forgot.php';
}

function navigateToSignup() {
    window.location.href = 'signup.php';
}

// Fixed password toggle function
document.getElementById('passwordToggle').addEventListener('click', function() {
    const passwordField = document.getElementById('password');
    const icon = this.querySelector('i');
    
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        passwordField.type = 'password';
        icon.className = 'fas fa-eye';
    }
});

// Button event listeners
document.getElementById('googleLoginBtn').addEventListener('click', navigateToGoogleAuth);
document.getElementById('forgotPasswordBtn').addEventListener('click', navigateToForgotPassword);
document.getElementById('createAccountBtn').addEventListener('click', navigateToSignup);

document.getElementById('loginForm').addEventListener('submit', function() {
    const btn = document.getElementById('loginBtn');
    btn.classList.add('btn-loading');
    btn.disabled = true;
});

// Prevent zoom on input focus on mobile devices
document.addEventListener('DOMContentLoaded', function() {
    // Initialize logo size
    resizeLogo();
    
    // Disable double-tap zoom
    document.addEventListener('touchstart', function(event) {
        if (event.touches.length > 1) {
            event.preventDefault();
        }
    });
    
    let lastTouchEnd = 0;
    document.addEventListener('touchend', function(event) {
        const now = (new Date()).getTime();
        if (now - lastTouchEnd <= 300) {
            event.preventDefault();
        }
        lastTouchEnd = now;
    }, false);
    
    // Prevent zoom on input focus
    const inputs = document.querySelectorAll('input, textarea, select');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            window.scrollTo(0, 0);
            document.body.style.zoom = "100%";
        });
    });
});

// Additional zoom prevention
document.addEventListener('gesturestart', function(e) {
    e.preventDefault();
});

document.addEventListener('gesturechange', function(e) {
    e.preventDefault();
});

document.addEventListener('gestureend', function(e) {
    e.preventDefault();
});

// Auto-focus on email field
document.getElementById('email').focus();

// Resize logo on window resize
window.addEventListener('resize', resizeLogo);
window.addEventListener('orientationchange', resizeLogo);

// Also resize logo when page loads completely
window.addEventListener('load', resizeLogo);
</script>

<?php 
// Include footer if it exists
if (file_exists('includes/footer.php')) {
    include 'includes/footer.php'; 
}
?>
</body>
</html>