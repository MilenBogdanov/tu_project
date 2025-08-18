<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Rent A Car</title>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" />
    <link rel="stylesheet" href="styles.css" />
    
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="script.js" defer></script>

    <style>
        .profile-pic-wrapper {
            position: relative;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            overflow: hidden;
            cursor: pointer;
            display: inline-block;
        }

        .profile-pic-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: opacity 0.3s ease;
        }

        .edit-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            font-size: 18px;
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
        }

        .profile-pic-wrapper:hover img {
            opacity: 0.6;
        }

        .profile-pic-wrapper:hover .edit-overlay {
            opacity: 1;
        }
    </style>
</head>
<body>

<header>
    <div class="container">
        <a href="index.php" class="logo">
            <img src="backgr_images/rentacar_logo.png" alt="Rent A Car Logo" />
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

                    <?php
                    $defaultProfilePic = 'backgr_images/profile.png';

                    if (!empty($_SESSION['profile_picture']) 
                        && $_SESSION['profile_picture'] !== 'default.png' 
                        && file_exists('uploads/profile_pics/' . $_SESSION['profile_picture'])) {
                        $profilePic = 'uploads/profile_pics/' . htmlspecialchars($_SESSION['profile_picture']) . '?v=' . time();
                    } else {
                        $profilePic = $defaultProfilePic;
                    }
                    ?>

                    <a href="profile.php" class="profile-btn" title="Edit Profile">
                        <div class="profile-pic-wrapper">
                            <img src="<?= $profilePic ?>" alt="Profile" />
                            <div class="edit-overlay">
                                <span>✏️</span>
                            </div>
                        </div>
                    </a>
                    <span class="welcome-message">
                        <?= htmlspecialchars($_SESSION['username']) ?>
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
