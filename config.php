<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'tkslogin');
define('DB_PASS', 'root');
define('DB_NAME', 'root');

// Google OAuth Configuration
define('GOOGLE_CLIENT_ID', 'add your Google client ID');
define('GOOGLE_CLIENT_SECRET', 'add client secret');
define('GOOGLE_REDIRECT_URI', 'URL of google-auth.php');

// SMTP Configuration
define('SMTP_HOST', 'smtp.tksultra.in');
define('SMTP_USERNAME', 'notification@tksultra.in');
define('SMTP_PASSWORD', 'add Yor SMTP Password');
define('SMTP_PORT', 465);
define('SMTP_FROM_EMAIL', 'notification@tksultra.in');
define('SMTP_FROM_NAME', 'TKS AUTH');

// Site URL
define('SITE_URL', 'add server URL');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Helper function to redirect
function redirect($path) {
    header("Location: " . SITE_URL . $path);
    exit();
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

// Generate CSRF token
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Generate random string
function generateRandomString($length = 32) {
    return bin2hex(random_bytes($length));
}
