<?php
require_once 'config.php';
require_once 'db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $email = trim($_POST['email']);
    
    if (empty($email)) {
        $error = 'Please enter your email address';
    } else {
        try {
            // Check if email exists
            $stmt = $pdo->prepare("SELECT id, name FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Generate reset token
                $reset_token = bin2hex(random_bytes(32));
                $reset_token_expires = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token expires in 1 hour
                
                // Update user with reset token
                $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE id = ?");
                if ($stmt->execute([$reset_token, $reset_token_expires, $user['id']])) {
                    // Send reset email
                    $reset_link = SITE_URL . "reset-password.php?token=" . $reset_token;
                    $to = $email;
                    $subject = 'Password Reset Request';
                    $message = "
                        <h2>Password Reset Request</h2>
                        <p>Hello " . htmlspecialchars($user['name']) . ",</p>
                        <p>We received a request to reset your password. Click the link below to reset your password:</p>
                        <p><a href='$reset_link'>$reset_link</a></p>
                        <p>This link will expire in 1 hour.</p>
                        <p>If you didn't request this, you can safely ignore this email.</p>
                    ";
                    
                    $headers = "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM_EMAIL . ">\r\n";
                    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
                    
                    // In a real application, use a proper email sending library
                    // mail($to, $subject, $message, $headers);
                    
                    $success = 'Password reset instructions have been sent to your email address.';
                } else {
                    $error = 'An error occurred. Please try again.';
                }
            } else {
                $error = 'If an account with that email exists, a password reset link has been sent.';
                // For security, don't reveal if the email exists or not
                $success = 'If an account with that email exists, a password reset link has been sent.';
            }
        } catch (PDOException $e) {
            $error = 'An error occurred. Please try again later.';
        }
    }
}
?>
<?php $page_title = 'Forgot Password'; include 'includes/header.php'; ?>

<div class="auth-container">
    <h2 class="text-center">Forgot Password</h2>
    <p class="text-center">Enter your email address and we'll send you a link to reset your password.</p>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
        
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" class="form-control" required>
        </div>
        
        <div class="form-group">
            <button type="submit" name="reset_password" class="btn btn-block">Send Reset Link</button>
        </div>
        
        <div class="text-center mt-3">
            <p>Remember your password? <a href="login.php">Login here</a></p>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
