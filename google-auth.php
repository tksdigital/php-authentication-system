<?php
require_once 'config.php';
require_once 'db.php';

// Google API Client Library (you'll need to download this)
// Download from: https://github.com/googleapis/google-api-php-client
// Place the 'vendor' folder in the same directory as this file
require_once 'vendor/autoload.php';

$client = new Google_Client();
$client->setClientId(GOOGLE_CLIENT_ID);
$client->setClientSecret(GOOGLE_CLIENT_SECRET);
$client->setRedirectUri(GOOGLE_REDIRECT_URI);
$client->addScope('email');
$client->addScope('profile');

if (isset($_GET['code'])) {
    // Handle the OAuth 2.0 server response
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    
    if (!isset($token['error'])) {
        $client->setAccessToken($token['access_token']);
        
        // Get user profile data from google
        $google_oauth = new Google_Service_Oauth2($client);
        $google_account_info = $google_oauth->userinfo->get();
        
        $email = $google_account_info->email;
        $name = $google_account_info->name;
        $google_id = $google_account_info->id;
        
        try {
            // Check if user already exists with this email
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Case 1: Email exists but not verified
                if (!$user['is_email_verified']) {
                    $stmt = $pdo->prepare("UPDATE users SET is_email_verified = 1, google_id = ? WHERE id = ?");
                    $stmt->execute([$google_id, $user['id']]);
                    $user_id = $user['id'];
                }
                // Case 2: Email exists and verified
                else {
                    // Update google_id if not set
                    if (empty($user['google_id'])) {
                        $stmt = $pdo->prepare("UPDATE users SET google_id = ? WHERE id = ?");
                        $stmt->execute([$google_id, $user['id']]);
                    }
                    $user_id = $user['id'];
                }
                
                // Set session variables
                $_SESSION['user_id'] = $user_id;
                $_SESSION['email'] = $email;
                $_SESSION['name'] = $name;
                
                // Check if profile is complete
                if (empty($user['phone']) || empty($user['country']) || empty($user['state_city'])) {
                    header('Location: complete-profile.php');
                    exit();
                }
                
                header('Location: dashboard.php');
                exit();
            } 
            // Case 3: Email does not exist
            else {
                // Create new user
                $stmt = $pdo->prepare("INSERT INTO users (name, email, google_id, is_email_verified) VALUES (?, ?, ?, 1)");
                if ($stmt->execute([$name, $email, $google_id])) {
                    $user_id = $pdo->lastInsertId();
                    
                    // Set session variables
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['email'] = $email;
                    $_SESSION['name'] = $name;
                    
                    // Redirect to complete profile
                    header('Location: complete-profile.php');
                    exit();
                } else {
                    throw new Exception('Failed to create user');
                }
            }
        } catch (PDOException $e) {
            // Log the error and redirect to login with error
            error_log('Google Auth Error: ' . $e->getMessage());
            header('Location: login.php?error=google_auth_failed');
            exit();
        }
    } else {
        // Handle error
        header('Location: login.php?error=google_auth_failed');
        exit();
    }
} else {
    // Generate and redirect to Google's OAuth consent page
    $auth_url = $client->createAuthUrl();
    header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
    exit();
}
