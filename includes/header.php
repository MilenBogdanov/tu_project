<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rent A Car</title>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    
    <link rel="stylesheet" href="styles.css">
    
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    
    <script src="script.js" defer></script>
</head>
<body>

<header>
    <div class="container">
        <a href="index.php" class="logo">
            <img src="backgr_images/rentacar_logo.png" alt="Rent A Car Logo">
        </a>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="car.php">Cars</a></li>
                <li><a href="about_us.php">About Us</a></li>
                <li><a href="contact.php">Contact</a></li>
                <li><a href="myreservations.php">My Reservations</a></li>
            <?php if (isset($_SESSION['role_name']) && $_SESSION['role_name'] === 'admin'): ?>
                <li><a href="admin_panel.php">Admin Panel</a></li>
            <?php endif; ?>
            </ul>
            <div class="auth-buttons">
                <?php if (isset($_SESSION['user_id'])): ?>
                <span class="welcome-message">
                Welcome, <?= htmlspecialchars($_SESSION['username']) ?>
                </span>
                <a href="logout.php" class="btn login-btn">Logout</a>
            <?php else: ?>
                <a href="login.php" class="btn login-btn">Login</a>
                <a href="register.php" class="btn register-btn">Register</a>
            <?php endif; ?>
            </div>
        </nav>
    </div>
</header>

</body>
</html>