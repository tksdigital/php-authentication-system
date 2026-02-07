<?php
require_once 'config.php';
require_once 'db.php';

$error = '';
$success = '';
$valid_token = false;
$token = isset($_GET['token']) ? trim($_GET['token']) : '';

// Validate token
if (!empty($token)) {
    try {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expires > NOW()");
        $stmt->execute([$token]);
        $user = $stmt->fetch();
        
        if ($user) {
            $valid_token = true;
            $user_id = $user['id'];
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
                $password = $_POST['password'];
                $confirm_password = $_POST['confirm_password'];
                
                if (empty($password) || empty($confirm_password)) {
                    $error = 'Please fill in all fields';
                } elseif ($password !== $confirm_password) {
                    $error = 'Passwords do not match';
                } elseif (strlen($password) < 8) {
                    $error = 'Password must be at least 8 characters long';
                } else {
                    // Update password and clear reset token
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET password_hash = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ?");
                    
                    if ($stmt->execute([$password_hash, $user_id])) {
                        $success = 'Your password has been updated successfully. You can now login with your new password.';
                        $valid_token = false; // Prevent form from being shown again
                    } else {
                        $error = 'An error occurred. Please try again.';
                    }
                }
            }
        } else {
            $error = 'Invalid or expired reset token. Please request a new password reset link.';
        }
    } catch (PDOException $e) {
        $error = 'An error occurred. Please try again later.';
    }
} else {
    $error = 'No reset token provided.';
}
?>
<?php $page_title = 'Reset Password'; include 'includes/header.php'; ?>

<div class="auth-container">
    <h2 class="text-center">Reset Your Password</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
        <div class="text-center mt-3">
            <a href="login.php" class="btn">Go to Login</a>
        </div>
    <?php elseif ($valid_token): ?>
        <p class="text-center">Please enter your new password below.</p>
        
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div class="form-group">
                <label for="password">New Password</label>
                <div style="position: relative;">
                    <input type="password" id="password" name="password" class="form-control" minlength="8" required>
                    <span style="position: absolute; right: 10px; top: 10px; cursor: pointer;" 
                          onclick="togglePassword('password')">
                        👁️
                    </span>
                </div>
                <small class="text-muted">Password must be at least 8 characters long</small>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <div style="position: relative;">
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" minlength="8" required>
                    <span style="position: absolute; right: 10px; top: 10px; cursor: pointer;" 
                          onclick="togglePassword('confirm_password')">
                        👁️
                    </span>
                </div>
            </div>
            
            <div class="form-group">
                <button type="submit" name="update_password" class="btn btn-block">Update Password</button>
            </div>
        </form>
    <?php endif; ?>
    
    <div class="text-center mt-3">
        <p>Remember your password? <a href="login.php">Login here</a></p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
