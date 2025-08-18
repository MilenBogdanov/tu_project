<?php
session_start();
include 'includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$successMsg = isset($_SESSION['successMsg']) ? $_SESSION['successMsg'] : "";
$errorMsg   = isset($_SESSION['errorMsg']) ? $_SESSION['errorMsg'] : "";
unset($_SESSION['successMsg'], $_SESSION['errorMsg']);

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT username, email, keyword, profile_picture FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (empty($user['profile_picture']) || !file_exists("uploads/profile_pics/" . $user['profile_picture'])) {
    $user['profile_picture'] = 'backgr_images/profile.png';
} else {
    $user['profile_picture'] = 'uploads/profile_pics/' . $user['profile_picture'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $keyword = trim($_POST['keyword']);

    $profilePicName = basename($user['profile_picture']);

    if (!empty($_FILES['profile_picture']['name'])) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 2 * 1024 * 1024;

        $fileTmp = $_FILES['profile_picture']['tmp_name'];
        $fileSize = $_FILES['profile_picture']['size'];
        $fileType = mime_content_type($fileTmp);

        if ($fileSize > $maxSize) {
            $_SESSION['errorMsg'] = "Profile picture must be smaller than 2 MB!";
        } elseif (!in_array($fileType, $allowedTypes)) {
            $_SESSION['errorMsg'] = "Only JPEG, PNG, and GIF images are allowed!";
        } else {
            $uploadDir = "uploads/profile_pics/";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $ext = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
            $newFileName = "user_" . $user_id . "." . $ext;
            $uploadPath = $uploadDir . $newFileName;

            if (basename($user['profile_picture']) !== 'profile.png' && file_exists($uploadDir . basename($user['profile_picture']))) {
                unlink($uploadDir . basename($user['profile_picture']));
            }

            if (move_uploaded_file($fileTmp, $uploadPath)) {
                $profilePicName = $newFileName;
            } else {
                $_SESSION['errorMsg'] = "Error uploading profile picture!";
            }
        }
    }

    if (empty($_SESSION['errorMsg'])) {
        if (!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
            $sql = "UPDATE users SET username=?, email=?, keyword=?, password=?, profile_picture=? WHERE user_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssi", $username, $email, $keyword, $password, $profilePicName, $user_id);
        } else {
            $sql = "UPDATE users SET username=?, email=?, keyword=?, profile_picture=? WHERE user_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssi", $username, $email, $keyword, $profilePicName, $user_id);
        }

        if ($stmt->execute()) {
            $_SESSION['username'] = $username;
            $_SESSION['profile_picture'] = $profilePicName;
            $_SESSION['successMsg'] = "Profile updated successfully!";
            header("Location: index.php");
            exit;
        } else {
            $_SESSION['errorMsg'] = "Error updating profile!";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Profile</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #fff8dc, #f5deb3);
    color: #3a3a3a;
    min-height: 100vh;
    margin: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px 10px;
}

.profile-container {
    max-width: 700px;
    width: 100%;
    background: #fff;
    border-radius: 12px;
    padding: 20px 25px;
    box-shadow:
        0 6px 15px rgba(255, 204, 0, 0.25),
        0 3px 10px rgba(0, 0, 0, 0.05);
    transition: box-shadow 0.3s ease;
}

.profile-container:hover {
    box-shadow:
        0 8px 30px rgba(255, 204, 0, 0.35),
        0 5px 15px rgba(0, 0, 0, 0.1);
}

h2 {
    text-align: center;
    font-weight: 700;
    color: #222;
    margin-bottom: 16px;
    letter-spacing: 0.04em;
    font-size: 24px;
}

label {
    font-weight: 600;
    display: block;
    margin-bottom: 5px;
    color: #555;
    font-size: 14px;
}

input {
    width: 100%;
    padding: 8px 12px;
    border: 1.3px solid #ddd;
    border-radius: 8px;
    margin-bottom: 14px;
    font-size: 14px;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
    background-color: #fafafa;
    color: #333;
}

input:focus {
    border-color: #ffcc00;
    outline: none;
    box-shadow: 0 0 6px rgba(255, 204, 0, 0.85);
    background-color: #fff;
}

button {
    width: 100%;
    padding: 12px;
    background: #ffcc00;
    color: #222;
    font-weight: 700;
    font-size: 16px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.3s ease, transform 0.2s ease;
    box-shadow: 0 3px 7px rgba(255, 204, 0, 0.5);
}

button:hover {
    background: #e6b800;
    transform: scale(1.04);
    box-shadow: 0 5px 10px rgba(230, 184, 0, 0.75);
}

.profile-pic {
    display: block;
    margin: 0 auto 16px auto;
    width: 110px;
    height: 110px;
    object-fit: cover;
    border-radius: 50%;
    border: 3px solid #ffcc00;
    box-shadow: 0 0 10px rgba(255, 204, 0, 0.4);
}

.message {
    text-align: center;
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 18px;
    font-weight: 600;
    font-size: 14px;
    letter-spacing: 0.02em;
}

.success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

small {
    font-size: 11px;
    color: #666;
    display: block;
    margin-bottom: 5px;
}

.file-upload-wrapper {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 14px;
}

#profile_picture {
    display: none;
}

#chooseImageBtn {
    background-color: #ffcc00;
    color: #222;
    border: none;
    padding: 6px 12px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    font-size: 12px;
    transition: background-color 0.3s;
}

#chooseImageBtn:hover {
    background-color: #e6b800;
}

#previewImg {
    width: 70px;
    height: 70px;
    object-fit: cover;
    border-radius: 50%;
    border: 2px solid #ffcc00;
    box-shadow: 0 0 6px rgba(255, 204, 0, 0.4);
}

.cancel-link {
    display: block;
    text-align: center;
    margin: 12px auto 0 auto;
    font-weight: 600;
    color: #ffcc00;
    text-decoration: none;
    font-size: 14px;
    border: 2px solid #ffcc00;
    border-radius: 8px;
    padding: 8px 0;
    max-width: 160px;
    transition: background-color 0.3s ease, color 0.3s ease;
}

.cancel-link:hover {
    background-color: #ffcc00;
    color: #222;
    box-shadow: 0 0 10px rgba(255, 204, 0, 0.6);
}

</style>
</head>
<body>

<div class="profile-container">
    <h2>Edit Profile</h2>

    <?php if ($successMsg): ?><div class="message success"><?= $successMsg ?></div><?php endif; ?>
    <?php if ($errorMsg): ?><div class="message error"><?= $errorMsg ?></div><?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <img src="<?= htmlspecialchars($user['profile_picture']) ?>" class="profile-pic" alt="Profile Picture">
        <label>Change Profile Picture</label>
<small>Allowed formats: JPEG, PNG, GIF — Max size: 2 MB</small>
<div class="file-upload-wrapper">
    <button type="button" id="chooseImageBtn">Choose Image</button>
    <input type="file" name="profile_picture" id="profile_picture" accept="image/jpeg, image/png, image/gif" />
    <img id="previewImg" src="<?= htmlspecialchars($user['profile_picture']) ?>" alt="Image Preview" />
</div>

        <label>Username</label>
        <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>

        <label>Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

        <label>Keyword</label>
        <input type="text" name="keyword" value="<?= htmlspecialchars($user['keyword']) ?>">

        <label>New Password (leave blank to keep current)</label>
        <input type="password" name="password">

        <button type="submit">Save Changes</button>
        <a href="index.php" class="cancel-link">Cancel / Go Back</a>
    </form>
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
            };
            reader.readAsDataURL(file);
        } else {
            previewImg.src = "<?= htmlspecialchars($user['profile_picture']) ?>";
        }
    });
</script>
</body>
</html>