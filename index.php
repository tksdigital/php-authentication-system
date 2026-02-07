<?php
require_once 'config.php';
require_once 'db.php';

// Check if user is already logged in
$isLoggedIn = false;
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id'])) {
    $isLoggedIn = true;
    $userName = $_SESSION['name'] ?? 'User';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Our Authentication System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #1cc88a;
            --dark-color: #5a5c69;
        }
        
        body {
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f8f9fc;
            color: #5a5c69;
            line-height: 1.6;
        }
        
        .hero-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, #224abe 100%);
            color: white;
            padding: 6rem 0;
            margin-bottom: 3rem;
            border-radius: 0 0 30px 30px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        
        .feature-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }
        
        .card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 2rem 0 rgba(58, 59, 69, 0.2);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 0.5rem 1.5rem;
            font-weight: 600;
        }
        
        .btn-outline-light {
            padding: 0.5rem 1.5rem;
            font-weight: 600;
        }
        
        .navbar {
            background-color: #fff !important;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        
        .navbar-brand {
            font-weight: 800;
            color: var(--primary-color) !important;
            font-size: 1.5rem;
        }
        
        .feature-box {
            padding: 2rem;
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .test-section {
            background-color: #f8f9fc;
            padding: 3rem 0;
            margin-top: 3rem;
            border-top: 1px solid #e3e6f0;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white">
        <div class="container">
            <a class="navbar-brand" href="index.php">AuthSystem</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="test-connection.php">Tests</a>
                    </li>
                    <?php if ($isLoggedIn): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-primary ms-2" href="signup.php">Sign Up</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="hero-section">
        <div class="container text-center">
            <h1 class="display-4 fw-bold mb-4">Secure Authentication System</h1>
            <p class="lead mb-5">A complete authentication solution with email verification, password reset, and social login.</p>
            
            <?php if ($isLoggedIn): ?>
                <div class="d-flex justify-content-center gap-3">
                    <a href="dashboard.php" class="btn btn-light btn-lg">Go to Dashboard</a>
                    <a href="test-connection.php" class="btn btn-outline-light btn-lg">Run Tests</a>
                </div>
            <?php else: ?>
                <div class="d-flex justify-content-center gap-3">
                    <a href="signup.php" class="btn btn-light btn-lg">Get Started</a>
                    <a href="login.php" class="btn btn-outline-light btn-lg">Login</a>
                </div>
            <?php endif; ?>
        </div>
    </header>

    <!-- Features Section -->
    <section id="features" class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Features</h2>
                <p class="lead text-muted">Everything you need for secure user authentication</p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body text-center p-4">
                            <div class="feature-icon">
                                <i class="fas fa-user-shield"></i>
                            </div>
                            <h4 class="card-title">Secure Authentication</h4>
                            <p class="card-text">Industry-standard password hashing and secure session management to protect user accounts.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body text-center p-4">
                            <div class="feature-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <h4 class="card-title">Email Verification</h4>
                            <p class="card-text">Verify user email addresses to ensure valid accounts and reduce spam.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body text-center p-4">
                            <div class="feature-icon">
                                <i class="fas fa-key"></i>
                            </div>
                            <h4 class="card-title">Password Reset</h4>
                            <p class="card-text">Secure password reset flow with expiring tokens sent via email.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body text-center p-4">
                            <div class="feature-icon">
                                <i class="fab fa-google"></i>
                            </div>
                            <h4 class="card-title">Google Login</h4>
                            <p class="card-text">Allow users to sign in with their Google account for a seamless experience.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body text-center p-4">
                            <div class="feature-icon">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <h4 class="card-title">Security</h4>
                            <p class="card-text">Protection against common vulnerabilities like CSRF, XSS, and SQL injection.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body text-center p-4">
                            <div class="feature-icon">
                                <i class="fas fa-mobile-alt"></i>
                            </div>
                            <h4 class="card-title">Responsive Design</h4>
                            <p class="card-text">Works perfectly on all devices, from mobile phones to desktop computers.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Test Section -->
    <section class="test-section">
        <div class="container text-center">
            <h2 class="fw-bold mb-4">Test the System</h2>
            <p class="lead mb-4">Run tests to ensure everything is working correctly</p>
            <div class="d-flex justify-content-center gap-3">
                <a href="test-connection.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-plug me-2"></i> Test Connection
                </a>
                <a href="test-auth.php" class="btn btn-outline-primary btn-lg">
                    <i class="fas fa-user-shield me-2"></i> Test Authentication
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-white py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0 text-muted">&copy; <?php echo date('Y'); ?> AuthSystem. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
