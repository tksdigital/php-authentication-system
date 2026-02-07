<?php
require_once 'config.php';
require_once 'db.php';
require_once 'includes/email.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signup'])) {
    // Get form data
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $country = trim($_POST['country']);
    $state_city = trim($_POST['state_city']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $referral_code = !empty($_POST['referral_code']) ? trim($_POST['referral_code']) : null;
    
    // Validate input
    if (empty($name) || empty($username) || empty($email) || empty($phone) || empty($country) || empty($state_city) || empty($password) || empty($confirm_password)) {
        $error = 'All fields are required except referral code';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long';
    } else {
        try {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
                $error = 'Email already exists';
            } else {
                // Check if username already exists
                $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
                $stmt->execute([$username]);
                
                if ($stmt->rowCount() > 0) {
                    $error = 'Username already taken';
                } else {
                    // Generate verification token
                    $verification_token = bin2hex(random_bytes(32));
                    $verification_link = SITE_URL . "verify-email.php?token=" . $verification_token;
                    
                    // Hash password
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Insert user into database
                    $stmt = $pdo->prepare("INSERT INTO users (name, username, email, phone, country, state_city, password_hash, referral_code, verification_token) 
                                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    
                    if ($stmt->execute([$name, $username, $email, $phone, $country, $state_city, $password_hash, $referral_code, $verification_token])) {
                        // Send verification email using EmailHelper
                        $emailer = getEmailer();
                        
                        if ($emailer->sendVerificationEmail($email, $name, $verification_link)) {
                            $success = 'Registration successful! Please check your email to verify your account.';
                            // Clear form
                            $name = $email = $username = $phone = $country = $state_city = '';
                        } else {
                            $error = 'Failed to send verification email. Please try again later.';
                            error_log('Email sending failed: ' . $emailer->getError());
                        }
                    } else {
                        $error = 'Registration failed. Please try again.';
                    }
                }
            }
        } catch (PDOException $e) {
            $error = 'An error occurred. Please try again later.';
            error_log('Database error: ' . $e->getMessage());
        } catch (Exception $e) {
            $error = 'An error occurred while sending the verification email.';
            error_log('Email error: ' . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Sign Up - TKS ULTRA</title>
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
    padding: 15px;
    touch-action: manipulation;
    -webkit-text-size-adjust: 100%;
    -ms-text-size-adjust: 100%;
}

.main-container {
    display: flex;
    width: 100%;
    max-width: 1200px;
    min-height: auto;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
}

.character-section {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 30px;
    background: var(--glass-bg);
    backdrop-filter: blur(15px);
    -webkit-backdrop-filter: blur(15px);
    border-right: 1px solid var(--glass-border);
}

.character-image {
    width: 100%;
    max-width: 350px;
    height: auto;
    border-radius: 0;
    box-shadow: none;
    border: none;
}

.signup-section {
    flex: 1;
    background: var(--glass-bg);
    backdrop-filter: blur(15px);
    -webkit-backdrop-filter: blur(15px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 30px;
    border-left: 1px solid var(--glass-border);
}

.signup-container {
    width: 100%;
    max-width: 450px;
}

.auth-header {
    text-align: center;
    margin-bottom: 30px;
}

.logo-container {
    display: flex;
    justify-content: center;
    margin-bottom: 15px;
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
    font-size: 28px;
    font-weight: 700;
    margin-bottom: 8px;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.auth-subtitle {
    color: rgba(255, 255, 255, 0.9);
    font-size: 14px;
    font-weight: 400;
}

/* Single Row Form Layout */
.form-single-row {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.form-group {
    position: relative;
    width: 100%;
}

.input-container {
    position: relative;
    width: 100%;
}

.form-control {
    width: 100%;
    padding: 14px 45px 14px 14px;
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 10px;
    font-size: 14px;
    transition: all 0.3s ease;
    background: rgba(255, 255, 255, 0.95);
    box-sizing: border-box;
    height: 50px;
    font-family: 'Inter', sans-serif;
    color: #333;
}

.form-control:focus {
    outline: none;
    border-color: #4B7BFF;
    box-shadow: 0 0 0 3px rgba(75, 123, 255, 0.2);
    background: rgba(255, 255, 255, 0.98);
    transform: translateY(-1px);
}

.form-control::placeholder {
    color: #666;
    font-family: 'Inter', sans-serif;
    font-size: 14px;
}

.input-icon {
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #666;
    font-size: 14px;
    font-weight: normal;
    pointer-events: none;
    width: 18px;
    height: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.password-toggle {
    position: absolute;
    right: 14px;
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
    font-size: 14px;
    font-weight: normal;
    width: 22px;
    height: 22px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.password-toggle:hover {
    color: var(--secondary-color);
}

.btn-primary {
    width: 100%;
    padding: 14px 20px;
    background: white;
    color: #111;
    border: none;
    border-radius: 10px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    height: 50px;
    font-family: 'Inter', sans-serif;
    touch-action: manipulation;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    margin-top: 10px;
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
    padding: 12px 18px;
    background: white;
    color: var(--dark-text);
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    margin-top: 15px;
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
    width: 18px;
    height: 18px;
    margin-right: 10px;
    background: url('https://upload.wikimedia.org/wikipedia/commons/thumb/c/c1/Google_%22G%22_logo.svg/32px-Google_%22G%22_logo.svg.png') no-repeat center center;
    background-size: contain;
}

/* Link button styles */
.link-btn {
    background: none;
    border: none;
    color: rgba(255, 255, 255, 0.9);
    text-decoration: none;
    font-weight: 500;
    font-size: 13px;
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
    margin-top: 20px;
}

.text-center {
    text-align: center;
}

.mt-3 {
    margin-top: 15px;
}

.text-muted {
    color: rgba(255, 255, 255, 0.8);
    font-size: 13px;
}

.divider {
    text-align: center;
    margin: 20px 0;
    position: relative;
    color: rgba(255, 255, 255, 0.8);
    font-size: 13px;
}

.divider::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 50%;
    margin-right: 12px;
    height: 1px;
    background: rgba(255, 255, 255, 0.3);
}

.divider::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    margin-left: 12px;
    right: 0;
    height: 1px;
    background: rgba(255, 255, 255, 0.3);
}

.alert {
    padding: 12px 14px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-size: 13px;
    font-weight: 500;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    gap: 8px;
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
    display: flex;
    align-items: center;
    justify-content: center;
    width: 16px;
    height: 16px;
    flex-shrink: 0;
}

/* Loading animation */
.btn-loading {
    pointer-events: none;
    opacity: 0.8;
}

.btn-loading::after {
    content: '';
    position: absolute;
    width: 18px;
    height: 18px;
    top: 50%;
    left: 50%;
    margin-left: -9px;
    margin-top: -9px;
    border: 2px solid #111;
    border-radius: 50%;
    border-top-color: transparent;
    animation: spin 0.8s ease infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Password strength indicator */
.password-strength {
    margin-top: 6px;
    height: 3px;
    border-radius: 2px;
    background: rgba(255, 255, 255, 0.3);
    overflow: hidden;
}

.strength-bar {
    height: 100%;
    width: 0%;
    transition: all 0.3s ease;
    border-radius: 2px;
}

.strength-weak {
    background: #e74c3c;
    width: 33%;
}

.strength-medium {
    background: #f39c12;
    width: 66%;
}

.strength-strong {
    background: #27ae60;
    width: 100%;
}

.strength-text {
    font-size: 11px;
    margin-top: 4px;
    color: rgba(255, 255, 255, 0.8);
}

/* Validation messages */
.validation-message {
    font-size: 11px;
    margin-top: 4px;
    display: flex;
    align-items: center;
    gap: 5px;
}

.validation-error {
    color: #e74c3c;
}

.validation-success {
    color: #27ae60;
}

.form-text {
    display: block;
    margin-top: 4px;
    font-size: 11px;
    color: rgba(255, 255, 255, 0.7);
}

/* Icon fixes */
.fas, .far, .fab {
    display: inline-block;
    text-rendering: auto;
    -webkit-font-smoothing: antialiased;
}

/* =========================================== */
/* MOBILE-FIRST RESPONSIVE DESIGN */
/* =========================================== */

/* Default mobile styles (applies to all screens) */
.character-section {
    display: none;
}

.main-container {
    max-width: 100%;
    min-height: auto;
    margin: 0 auto;
    border-radius: 16px;
}

.signup-section {
    padding: 25px 20px;
    width: 100%;
    border-left: none;
}

.signup-container {
    max-width: 100%;
}

/* Tablet Devices (768px and above) */
@media (min-width: 768px) {
    body {
        padding: 20px;
    }
    
    .main-container {
        max-width: 700px;
        min-height: 600px;
    }
    
    .signup-section {
        padding: 35px 30px;
    }
    
    .auth-title {
        font-size: 32px;
    }
    
    .auth-subtitle {
        font-size: 16px;
    }
    
    .form-control {
        height: 52px;
        font-size: 15px;
        padding: 16px 50px 16px 16px;
    }
    
    .btn-primary {
        height: 52px;
        font-size: 16px;
    }
    
    .google-btn {
        padding: 14px 20px;
        font-size: 15px;
    }
    
    .form-single-row {
        gap: 18px;
    }
}

/* Desktop Devices (1024px and above) */
@media (min-width: 1024px) {
    .main-container {
        max-width: 1200px;
        flex-direction: row;
    }
    
    .character-section {
        display: flex;
        padding: 40px;
    }
    
    .signup-section {
        padding: 40px;
        border-left: 1px solid var(--glass-border);
    }
    
    .signup-container {
        max-width: 450px;
    }
    
    .auth-title {
        font-size: 32px;
    }
    
    .form-control {
        height: 55px;
    }
    
    .btn-primary {
        height: 55px;
    }
}

/* Small Mobile Devices (360px and below) */
@media (max-width: 360px) {
    body {
        padding: 10px;
    }
    
    .main-container {
        border-radius: 14px;
    }
    
    .signup-section {
        padding: 20px 15px;
    }
    
    .auth-title {
        font-size: 24px;
    }
    
    .auth-subtitle {
        font-size: 13px;
    }
    
    .form-single-row {
        gap: 12px;
    }
    
    .form-control {
        height: 46px;
        font-size: 14px;
        padding: 12px 40px 12px 12px;
    }
    
    .btn-primary {
        height: 46px;
        font-size: 14px;
        padding: 12px 16px;
    }
    
    .input-icon {
        font-size: 13px;
        right: 12px;
        width: 16px;
        height: 16px;
    }
    
    .password-toggle {
        font-size: 13px;
        right: 12px;
        width: 20px;
        height: 20px;
    }
    
    .alert {
        padding: 10px 12px;
        font-size: 12px;
    }
}

/* Extra small devices (320px and below) */
@media (max-width: 320px) {
    .signup-section {
        padding: 15px 12px;
    }
    
    .auth-title {
        font-size: 22px;
    }
    
    .form-control {
        height: 44px;
        padding: 10px 35px 10px 10px;
    }
    
    .btn-primary {
        height: 44px;
        font-size: 14px;
    }
    
    .form-single-row {
        gap: 10px;
    }
}

/* Landscape mode on mobile */
@media (max-height: 500px) and (max-width: 768px) {
    body {
        padding: 10px;
        align-items: flex-start;
    }
    
    .main-container {
        min-height: auto;
        margin: 10px auto;
    }
    
    .signup-section {
        padding: 15px;
    }
    
    .auth-header {
        margin-bottom: 20px;
    }
    
    .form-single-row {
        gap: 10px;
    }
    
    .form-control {
        height: 44px;
    }
    
    .btn-primary {
        height: 44px;
    }
}

/* Disable zoom on input focus for iOS */
@media screen and (max-width: 768px) {
    input, select, textarea {
        font-size: 16px !important;
    }
}

/* Ensure no horizontal scrolling */
html, body {
    overflow-x: hidden;
    width: 100%;
}
</style>
</head>
<body>
<div class="main-container">
    <div class="character-section">
        <img src="https://tksultra.in/LOGIN/assets/images/robot.png" alt="Character" class="character-image">
    </div>
    
    <div class="signup-section">
        <div class="signup-container">
            <div class="auth-header">
                <div class="logo-container">
                    <img src="https://tksultra.in/LOGIN/assets/images/logo.png" alt="TKS ULTRA Logo" class="website-logo" id="responsiveLogo">
                </div>
                <h1 class="auth-title">Create Account</h1>
                <p class="auth-subtitle">Join our community and get started today</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <span class="alert-icon"><i class="fas fa-exclamation-triangle"></i></span>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <span class="alert-icon"><i class="fas fa-check-circle"></i></span>
                    <span><?php echo htmlspecialchars($success); ?></span>
                </div>
            <?php endif; ?>
            
             <!-- Google Signup -->
                <button type="button" class="google-btn" id="googleSignupBtn">
                    <span class="google-icon"></span>
                    Continue with Google
                </button>
                <!-- Divider -->
                <div class="divider">OR</div>
                
            <form method="POST" action="" id="signupForm">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="form-single-row">
                    <!-- Full Name -->
                    <div class="form-group">
                        <div class="input-container">
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" 
                                   required placeholder="Full Name">
                            <i class="input-icon fas fa-user"></i>
                        </div>
                    </div>
                    
                    <!-- Username -->
                    <div class="form-group">
                        <div class="input-container">
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>" 
                                   required placeholder="Username">
                            <i class="input-icon fas fa-at"></i>
                        </div>
                    </div>
                    
                    <!-- Email -->
                    <div class="form-group">
                        <div class="input-container">
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" 
                                   required placeholder="Email Address">
                            <i class="input-icon fas fa-envelope"></i>
                        </div>
                    </div>
                    
                    <!-- Phone -->
                    <div class="form-group">
                        <div class="input-container">
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?php echo isset($phone) ? htmlspecialchars($phone) : ''; ?>" 
                                   required placeholder="Phone Number">
                            <i class="input-icon fas fa-phone"></i>
                        </div>
                    </div>
                    
                    <!-- Country -->
                    <div class="form-group">
                        <div class="input-container">
                            <input type="text" class="form-control" id="country" name="country" 
                                   value="<?php echo isset($country) ? htmlspecialchars($country) : ''; ?>" 
                                   required placeholder="Country">
                            <i class="input-icon fas fa-globe"></i>
                        </div>
                    </div>
                    
                    <!-- State/City -->
                    <div class="form-group">
                        <div class="input-container">
                            <input type="text" class="form-control" id="state_city" name="state_city" 
                                   value="<?php echo isset($state_city) ? htmlspecialchars($state_city) : ''; ?>" 
                                   required placeholder="State/City">
                            <i class="input-icon fas fa-map-marker-alt"></i>
                        </div>
                    </div>
                    
                    <!-- Password -->
                    <div class="form-group">
                        <div class="input-container">
                            <input type="password" class="form-control" id="password" name="password" 
                                   required placeholder="Password">
                            <button type="button" class="password-toggle" id="passwordToggle">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="password-strength">
                            <div class="strength-bar" id="passwordStrength"></div>
                        </div>
                        <div class="strength-text" id="passwordStrengthText">Password strength</div>
                        <small class="form-text">Must be at least 8 characters long</small>
                    </div>
                    
                    <!-- Confirm Password -->
                    <div class="form-group">
                        <div class="input-container">
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                   required placeholder="Confirm Password">
                            <button type="button" class="password-toggle" id="confirmPasswordToggle">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="validation-message" id="passwordMatchMessage"></div>
                    </div>
                    
                    <!-- Referral Code>
                    <div class="form-group">
                        <div class="input-container">
                            <input type="text" class="form-control" id="referral_code" name="referral_code" 
                                   value="<?php echo isset($referral_code) ? htmlspecialchars($referral_code) : ''; ?>" 
                                   placeholder="Referral Code (Optional)">
                            <i class="input-icon fas fa-share-alt"></i>
                        </div>
                    </div-->
                </div>
                
                <!-- Submit Button -->
                <div class="form-group">
                    <button type="submit" name="signup" class="btn-primary" id="signupBtn">
                        <i class="fas fa-user-plus" style="margin-right: 6px;"></i>
                        Create Account
                    </button>
                </div>
                
                <!-- Login Link -->
                <div class="auth-links">
                    <p class="text-muted">Already have an account? 
                        <button type="button" class="link-btn" id="loginBtn">
                            <i class="fas fa-sign-in-alt" style="margin-right: 4px;"></i>
                            Sign in here
                        </button>
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
    const originalWidth = 200;
    const originalHeight = 50;
    const aspectRatio = originalHeight / originalWidth;
    
    let newWidth;
    
    if (screenWidth >= 1024) {
        newWidth = 180;
    } else if (screenWidth >= 768) {
        newWidth = 160;
    } else if (screenWidth >= 480) {
        newWidth = 140;
    } else if (screenWidth >= 360) {
        newWidth = 120;
    } else {
        newWidth = 100;
    }
    
    const newHeight = Math.round(newWidth * aspectRatio);
    
    logo.style.width = newWidth + 'px';
    logo.style.height = newHeight + 'px';
}

// Navigation functions
function navigateToLogin() {
    window.location.href = 'login.php';
}

function navigateToGoogleAuth() {
    window.location.href = 'google-auth.php?action=signup';
}

// Password strength function
function checkPasswordStrength(password) {
    let strength = 0;
    const strengthBar = document.getElementById('passwordStrength');
    const strengthText = document.getElementById('passwordStrengthText');
    
    if (password.length >= 8) strength++;
    if (password.match(/[a-z]+/)) strength++;
    if (password.match(/[A-Z]+/)) strength++;
    if (password.match(/[0-9]+/)) strength++;
    if (password.match(/[!@#$%^&*(),.?":{}|<>]+/)) strength++;
    
    strengthBar.className = 'strength-bar';
    strengthText.className = 'strength-text';
    
    if (password.length === 0) {
        strengthBar.style.width = '0%';
        strengthText.textContent = 'Password strength';
        strengthText.style.color = 'rgba(255, 255, 255, 0.8)';
        return;
    }
    
    switch(strength) {
        case 0:
        case 1:
            strengthBar.className = 'strength-bar strength-weak';
            strengthText.textContent = 'Weak password';
            strengthText.style.color = '#e74c3c';
            break;
        case 2:
        case 3:
            strengthBar.className = 'strength-bar strength-medium';
            strengthText.textContent = 'Medium strength';
            strengthText.style.color = '#f39c12';
            break;
        case 4:
        case 5:
            strengthBar.className = 'strength-bar strength-strong';
            strengthText.textContent = 'Strong password';
            strengthText.style.color = '#27ae60';
            break;
    }
}

// Password match function
function checkPasswordMatch() {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const message = document.getElementById('passwordMatchMessage');
    
    if (confirmPassword.length === 0) {
        message.textContent = '';
        message.className = 'validation-message';
        return;
    }
    
    if (password === confirmPassword) {
        message.innerHTML = `
            <i class="fas fa-check" style="color: #27ae60;"></i>
            <span>Passwords match</span>
        `;
        message.className = 'validation-message validation-success';
    } else {
        message.innerHTML = `
            <i class="fas fa-times" style="color: #e74c3c;"></i>
            <span>Passwords do not match</span>
        `;
        message.className = 'validation-message validation-error';
    }
}

// Password toggle function
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const toggleBtn = field.parentNode.querySelector('.password-toggle');
    const icon = toggleBtn.querySelector('i');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        field.type = 'password';
        icon.className = 'fas fa-eye';
    }
}

// Event listeners
document.getElementById('password').addEventListener('input', function() {
    checkPasswordStrength(this.value);
    checkPasswordMatch();
});

document.getElementById('confirm_password').addEventListener('input', checkPasswordMatch);

document.getElementById('passwordToggle').addEventListener('click', function() {
    togglePassword('password');
});

document.getElementById('confirmPasswordToggle').addEventListener('click', function() {
    togglePassword('confirm_password');
});

document.getElementById('loginBtn').addEventListener('click', navigateToLogin);
document.getElementById('googleSignupBtn').addEventListener('click', navigateToGoogleAuth);

document.getElementById('signupForm').addEventListener('submit', function() {
    const btn = document.getElementById('signupBtn');
    btn.classList.add('btn-loading');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin" style="margin-right: 6px;"></i> Creating Account...';
});

// Mobile optimization
document.addEventListener('DOMContentLoaded', function() {
    resizeLogo();
    
    // Prevent zoom
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
    
    // Auto-focus on first field
    document.getElementById('name').focus();
});

// Resize handlers
window.addEventListener('resize', resizeLogo);
window.addEventListener('orientationchange', resizeLogo);
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