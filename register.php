<?php
include 'includes/db_connect.php';

$defaultProfilePic = 'backgr_images/profile.png';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];
    $keyword = trim($_POST["keyword"]);
    $role_id = 1;

    $error = "";
    $profilePicName = '';

    if (!empty($_FILES['profile_picture']['name'])) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 2 * 1024 * 1024;

        $fileTmp = $_FILES['profile_picture']['tmp_name'];
        $fileSize = $_FILES['profile_picture']['size'];
        $fileType = mime_content_type($fileTmp);

        if ($fileSize > $maxSize) {
            $error = "Profile picture must be smaller than 2 MB!";
        } elseif (!in_array($fileType, $allowedTypes)) {
            $error = "Only JPEG, PNG, and GIF images are allowed!";
        } else {
            $uploadDir = "uploads/profile_pics/";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $ext = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
            $newFileName = uniqid('user_') . "." . $ext;
            $uploadPath = $uploadDir . $newFileName;

            if (move_uploaded_file($fileTmp, $uploadPath)) {
                $profilePicName = $newFileName;
            } else {
                $error = "Error uploading profile picture!";
            }
        }
    } else {
        $profilePicName = basename($defaultProfilePic);
    }

    if (empty($error)) {
        if ($password !== $confirm_password) {
            $error = "Passwords do not match.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            
            $sql = "INSERT INTO users (username, password, email, keyword, role_id, profile_picture) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssis", $username, $hashed_password, $email, $keyword, $role_id, $profilePicName);

            if ($stmt->execute()) {
                header("Location: login.php?success=1");
                exit;
            } else {
                $error = "Error: " . $conn->error;
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Register</title>
    <link rel="stylesheet" href="styles.css" />
</head>
<body>
<div class="register-container">
    <h2>Create a New Account</h2>

    <form action="" method="POST" enctype="multipart/form-data" class="register-form">
        <div class="input-group">
            <label for="username">Username:</label>
            <input type="text" name="username" id="username" required />
        </div>

        <div class="input-group">
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" required />
        </div>

        <div class="input-group">
            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required />
        </div>

        <div class="input-group">
            <label for="confirm_password">Confirm Password:</label>
            <input type="password" name="confirm_password" id="confirm_password" required />
        </div>

        <div class="input-group">
            <label for="keyword">Keyword (used for password recovery):</label>
            <input type="text" name="keyword" id="keyword" required />
        </div>

        <div class="input-group profile-pic-input">
    <label>Profile Picture (optional):</label>
    <small>Allowed formats: JPEG, PNG, GIF — Max size: 2 MB</small>

    <div class="file-upload-wrapper">
        <button type="button" id="chooseImageBtn">Choose Image</button>
        <input type="file" name="profile_picture" id="profile_picture" accept="image/jpeg, image/png, image/gif" />
        <img id="previewImg" src="#" alt="Image Preview" style="display:none;" />
    </div>
</div>

        <button type="submit" class="btn-submit">Register</button>

        <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
    </form>

    <p><a href="index.php" class="go-back">← Go Back To The Homepage</a></p>
</div>
    <script>
    const chooseImageBtn = document.getElementById('chooseImageBtn');
    const profileInput = document.getElementById('profile_picture');
    const previewImg = document.getElementById('previewImg');

    chooseImageBtn.addEventListener('click', () => {
        profileInput.click();
    });

    profileInput.addEventListener('change', () => {
        const file = profileInput.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = e => {
                previewImg.src = e.target.result;
                previewImg.style.display = 'inline-block';
            };
            reader.readAsDataURL(file);
        } else {
            previewImg.src = '#';
            previewImg.style.display = 'none';
        }
    });
</script>
</body>
</html>


<style>
    body {
    font-family: Arial, sans-serif;
    background-image: url('backgr_images/background.jpg');
    background-size: cover;
    background-position: center;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    position: relative;
    overflow: hidden;
}

.register-container {
    background-color: rgba(255, 255, 255, 0.9);
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 400px;
    text-align: center;
    position: relative;
}

h2 {
    font-size: 24px;
    color: #333;
    margin-bottom: 20px;
}

.register-form {
    display: flex;
    flex-direction: column;
}

.input-group {
    margin-bottom: 15px;
    text-align: left;
}

.input-group label {
    font-size: 14px;
    color: #333;
    margin-bottom: 5px;
}

.input-group input {
    width: 95%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 16px;
    color: #333;
}

.btn-submit {
    background-color: #f7b500;
    color: white;
    font-size: 16px;
    padding: 12px 20px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: background-color 0.3s;
}

.btn-submit:hover {
    background-color: #c77f00;
}

.error {
    color: red;
    font-size: 14px;
    margin-top: 10px;
}

.go-back {
    text-decoration: none;
    color: #007bff;
    font-size: 16px;
}

.go-back:hover {
    text-decoration: underline;
}

body::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-image: url('backgr_images/background.jpg');
    background-size: cover;
    background-position: center;
    filter: blur(1px);
    z-index: -1;
}

.profile-pic-input .file-upload-wrapper {
    display: flex;
    align-items: center;
    gap: 10px;
}

#profile_picture {
    display: none; /* Hide default file input */
}

#chooseImageBtn {
    background-color: #f7b500;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: bold;
    font-size: 14px;
    transition: background-color 0.3s;
}

#chooseImageBtn:hover {
    background-color: #c77f00;
}

#previewImg {
    max-width: 60px;
    max-height: 60px;
    border-radius: 6px;
    object-fit: cover;
    border: 1px solid #ddd;
    display: none;
}
</style>