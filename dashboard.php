<?php
require_once 'config.php';
require_once 'db.php';

// Redirect to login if not logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Get current user data
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// If profile is not complete, redirect to complete profile
if (empty($user['phone']) || empty($user['country']) || empty($user['state_city'])) {
    header('Location: complete-profile.php');
    exit();
}

// Handle profile update success message
$profile_updated = isset($_GET['profile_updated']) ? true : false;
?>
<?php $page_title = 'Dashboard'; include 'includes/header.php'; ?>

<div class="container">
    <div class="dashboard-header">
        <h1>Welcome, <?php echo htmlspecialchars($user['name']); ?>!</h1>
        <p>This is your dashboard. Here you can view and manage your account information.</p>
    </div>
    
    <?php if ($profile_updated): ?>
        <div class="alert alert-success">Your profile has been updated successfully!</div>
    <?php endif; ?>
    
    <div class="profile-card">
        <div class="profile-header">
            <div class="profile-avatar">
                <?php 
                    $initial = strtoupper(substr($user['name'], 0, 1));
                    echo '<div class="avatar-circle">' . $initial . '</div>';
                ?>
            </div>
            <div class="profile-info">
                <h2><?php echo htmlspecialchars($user['name']); ?></h2>
                <p class="text-muted">
                    <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?>
                </p>
                <p class="text-muted">
                    <i class="fas fa-user"></i> 
                    <?php echo !empty($user['google_id']) ? 'Signed in with Google' : 'Signed up with Email'; ?>
                </p>
            </div>
        </div>
        
        <div class="profile-details">
            <div class="detail-row">
                <span class="detail-label">Username:</span>
                <span class="detail-value"><?php echo htmlspecialchars($user['username']); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Phone:</span>
                <span class="detail-value"><?php echo htmlspecialchars($user['phone']); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Location:</span>
                <span class="detail-value">
                    <?php 
                        $location = [];
                        if (!empty($user['city'])) $location[] = htmlspecialchars($user['city']);
                        if (!empty($user['country'])) $location[] = htmlspecialchars($user['country']);
                        echo implode(', ', $location);
                    ?>
                </span>
            </div>
            <?php if (!empty($user['referral_code'])): ?>
                <div class="detail-row">
                    <span class="detail-label">Referral Code:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($user['referral_code']); ?></span>
                </div>
            <?php endif; ?>
            <div class="detail-row">
                <span class="detail-label">Member Since:</span>
                <span class="detail-value">
                    <?php 
                        $date = new DateTime($user['created_at']);
                        echo $date->format('F j, Y');
                    ?>
                </span>
            </div>
        </div>
        
        <div class="profile-actions">
            <a href="edit-profile.php" class="btn btn-outline-primary">
                <i class="fas fa-edit"></i> Edit Profile
            </a>
            <a href="change-password.php" class="btn btn-outline-secondary">
                <i class="fas fa-key"></i> Change Password
            </a>
            <a href="logout.php" class="btn btn-outline-danger" 
               onclick="return confirm('Are you sure you want to logout?')">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>
</div>

<style>
.dashboard-header {
    margin-bottom: 30px;
    text-align: center;
    padding: 20px 0;
    border-bottom: 1px solid #eee;
}

.profile-card {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    max-width: 800px;
    margin: 0 auto;
}

.profile-header {
    display: flex;
    align-items: center;
    padding: 30px;
    background: linear-gradient(135deg, #6e8efb, #a777e3);
    color: white;
}

.profile-avatar {
    margin-right: 20px;
}

.avatar-circle {
    width: 80px;
    height: 80px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 36px;
    font-weight: bold;
    color: white;
}

.profile-info h2 {
    margin: 0 0 5px 0;
    font-size: 24px;
}

.profile-info p {
    margin: 5px 0 0 0;
    opacity: 0.9;
}

.profile-details {
    padding: 30px;
}

.detail-row {
    display: flex;
    padding: 12px 0;
    border-bottom: 1px solid #f0f0f0;
}

.detail-row:last-child {
    border-bottom: none;
}

.detail-label {
    font-weight: 600;
    width: 150px;
    color: #666;
}

.detail-value {
    flex: 1;
    color: #333;
}

.profile-actions {
    padding: 20px 30px;
    background: #f9f9f9;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.btn-outline-primary {
    color: #6e8efb;
    border-color: #6e8efb;
}

.btn-outline-primary:hover {
    background: #6e8efb;
    color: white;
}

.btn-outline-secondary {
    color: #6c757d;
    border-color: #6c757d;
}

.btn-outline-secondary:hover {
    background: #6c757d;
    color: white;
}

.btn-outline-danger {
    color: #dc3545;
    border-color: #dc3545;
}

.btn-outline-danger:hover {
    background: #dc3545;
    color: white;
}

@media (max-width: 768px) {
    .profile-header {
        flex-direction: column;
        text-align: center;
    }
    
    .profile-avatar {
        margin: 0 0 15px 0;
    }
    
    .detail-row {
        flex-direction: column;
    }
    
    .detail-label {
        width: 100%;
        margin-bottom: 5px;
    }
    
    .profile-actions {
        flex-direction: column;
    }
    
    .profile-actions .btn {
        width: 100%;
        margin-bottom: 10px;
    }
}
</style>

<?php include 'includes/footer.php'; ?>
