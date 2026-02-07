<?php
if (!isset($page_title)) {
    $page_title = 'Authentication System';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body {
            background-color: #f5f5f5;
            color: #333;
            line-height: 1.6;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }
        .auth-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 30px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }
        .form-control {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        .btn {
            display: inline-block;
            background: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #45a049;
        }
        .btn-block {
            display: block;
            width: 100%;
        }
        .text-center {
            text-align: center;
        }
        .mt-3 {
            margin-top: 15px;
        }
        .google-btn {
            background: #db4437;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px;
            border-radius: 4px;
            text-decoration: none;
            margin: 10px 0;
            transition: background 0.3s;
        }
        .google-btn:hover {
            background: #c53929;
        }
        .google-icon {
            width: 20px;
            height: 20px;
            margin-right: 10px;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
        .nav {
            background: #333;
            padding: 15px 0;
        }
        .nav a {
            color: white;
            text-decoration: none;
            margin-right: 15px;
        }
        .nav a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <nav class="nav">
        <div class="container">
            <a href="<?php echo SITE_URL; ?>">Home</a>
            <?php if (isLoggedIn()): ?>
                <a href="dashboard.php">Dashboard</a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="signup.php">Sign Up</a>
            <?php endif; ?>
        </div>
    </nav>
    <div class="container">
