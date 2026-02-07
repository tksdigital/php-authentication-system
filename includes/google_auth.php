<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/config.php';

class GoogleAuthHelper {
    private $client;
    private $service;
    private $redirectUrl;
    
    public function __construct() {
        $this->client = new Google_Client();
        $this->client->setClientId(GOOGLE_CLIENT_ID);
        $this->client->setClientSecret(GOOGLE_CLIENT_SECRET);
        $this->client->setRedirectUri(GOOGLE_REDIRECT_URI);
        $this->client->addScope('email');
        $this->client->addScope('profile');
        $this->client->setAccessType('offline');
        $this->client->setPrompt('select_account consent');
        
        $this->redirectUrl = GOOGLE_REDIRECT_URI;
    }
    
    public function getAuthUrl() {
        return $this->client->createAuthUrl();
    }
    
    public function getAccessToken($code) {
        try {
            $token = $this->client->fetchAccessTokenWithAuthCode($code);
            if (isset($token['error'])) {
                throw new Exception($token['error_description'] ?? 'Failed to get access token');
            }
            $this->client->setAccessToken($token);
            return $token;
        } catch (Exception $e) {
            error_log('Google Auth Error: ' . $e->getMessage());
            return false;
        }
    }
    
    public function getUserInfo() {
        try {
            $oauth2 = new Google_Service_Oauth2($this->client);
            $userInfo = $oauth2->userinfo->get();
            
            return [
                'id' => $userInfo->getId(),
                'email' => $userInfo->getEmail(),
                'verified_email' => $userInfo->getVerifiedEmail(),
                'name' => $userInfo->getName(),
                'given_name' => $userInfo->getGivenName(),
                'family_name' => $userInfo->getFamilyName(),
                'picture' => $userInfo->getPicture(),
                'locale' => $userInfo->getLocale()
            ];
        } catch (Exception $e) {
            error_log('Google User Info Error: ' . $e->getMessage());
            return false;
        }
    }
    
    public function validateToken($token) {
        try {
            $payload = $this->client->verifyIdToken($token);
            if ($payload) {
                return $payload;
            }
            return false;
        } catch (Exception $e) {
            error_log('Token Validation Error: ' . $e->getMessage());
            return false;
        }
    }
    
    public function revokeToken() {
        try {
            $this->client->revokeToken();
            return true;
        } catch (Exception $e) {
            error_log('Token Revocation Error: ' . $e->getMessage());
            return false;
        }
    }
}

// Helper function to get Google Auth instance
function getGoogleAuth() {
    static $gauth = null;
    if ($gauth === null) {
        $gauth = new GoogleAuthHelper();
    }
    return $gauth;
}

// Example usage:
// $gauth = getGoogleAuth();
// $authUrl = $gauth->getAuthUrl();
// Redirect to $authUrl for Google login
?>
