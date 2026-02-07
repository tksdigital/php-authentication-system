<?php
require_once 'config.php';
require_once 'db.php';

// Redirect to login if not logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';

// Get current user data
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $phone = trim($_POST['phone']);
    $country = trim($_POST['country']);
    $state_city = trim($_POST['state_city']);
    $referral_code = !empty($_POST['referral_code']) ? trim($_POST['referral_code']) : null;
    
    // Validate input
    if (empty($name) || empty($username) || empty($phone) || empty($country) || empty($state_city)) {
        $error = 'All fields are required except referral code';
    } else {
        try {
            // Check if username is available
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
            $stmt->execute([$username, $user_id]);
            
            if ($stmt->rowCount() > 0) {
                $error = 'Username already taken';
            } else {
                // Update user profile
                $stmt = $pdo->prepare("UPDATE users SET name = ?, username = ?, phone = ?, country = ?, state_city = ?, referral_code = ? WHERE id = ?");
                
                if ($stmt->execute([$name, $username, $phone, $country, $state_city, $referral_code, $user_id])) {
                    // Update session name
                    $_SESSION['name'] = $name;
                    
                    // Redirect to dashboard
                    header('Location: dashboard.php?profile_updated=1');
                    exit();
                } else {
                    $error = 'Failed to update profile. Please try again.';
                }
            }
        } catch (PDOException $e) {
            $error = 'An error occurred. Please try again later.';
        }
    }
}
?>
<?php $page_title = 'Complete Your Profile'; include 'includes/header.php'; ?>

<div class="auth-container">
    <h2 class="text-center">Complete Your Profile</h2>
    <p class="text-center">Please complete your profile information to continue.</p>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
        
        <div class="form-group">
            <label for="name">Full Name <span class="text-danger">*</span></label>
            <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="username">Username <span class="text-danger">*</span></label>
            <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="phone">Phone Number <span class="text-danger">*</span></label>
            <input type="tel" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="country">Country <span class="text-danger">*</span></label>
            <select id="country" name="country" class="form-control" required>
                <option value="">Select Country</option>
                <option value="USA" <?php echo (isset($user['country']) && $user['country'] === 'USA') ? 'selected' : ''; ?>>United States</option>
                <option value="UK" <?php echo (isset($user['country']) && $user['country'] === 'UK') ? 'selected' : ''; ?>>United Kingdom</option>
                <option value="Canada" <?php echo (isset($user['country']) && $user['country'] === 'Canada') ? 'selected' : ''; ?>>Canada</option>
                <option value="Australia" <?php echo (isset($user['country']) && $user['country'] === 'Australia') ? 'selected' : ''; ?>>Australia</option>
                <option value="India" <?php echo (isset($user['country']) && $user['country'] === 'India') ? 'selected' : ''; ?>>India</option>
                <!-- Add more countries as needed -->
            </select>
        </div>
        
        <div class="form-group">
            <label for="state_city">State/City <span class="text-danger">*</span></label>
            <input type="text" id="state_city" name="state_city" class="form-control" value="<?php echo htmlspecialchars($user['state_city'] ?? ''); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="referral_code">Referral Code (Optional)</label>
            <input type="text" id="referral_code" name="referral_code" class="form-control" value="<?php echo htmlspecialchars($user['referral_code'] ?? ''); ?>">
        </div>
        
        <div class="form-group">
            <button type="submit" name="update_profile" class="btn btn-block">Save & Continue</button>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
