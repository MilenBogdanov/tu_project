<?php
session_start();
include 'includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    if (isset($_POST["email"]) && isset($_POST["keyword"]) && !empty($_POST["email"]) && !empty($_POST["keyword"])) {
        $email = trim($_POST["email"]);
        $keyword = trim($_POST["keyword"]);

        
        $sql = "SELECT user_id, email, keyword FROM users WHERE email = ? AND keyword = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $email, $keyword);

        if ($stmt->execute()) {
            $stmt->store_result();

            if ($stmt->num_rows === 1) {
                
                if (isset($_POST['new_password'])) {
                    
                    $new_password = password_hash(trim($_POST['new_password']), PASSWORD_DEFAULT);
                    $update_sql = "UPDATE users SET password = ? WHERE email = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bind_param("ss", $new_password, $email);
                    $update_stmt->execute();

                    $success = "Your password has been successfully updated.";
                    $go_to_login_button = true;
                } else {
                    
                    $modal = true;
                }
            } else {
                $error = "No account found with that email address or invalid keyword.";
            }
        }

        $stmt->close();
    } else {
        $error = "Please provide both email and keyword.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="forgot-password-container">
        <h2>Forgot Your Password?</h2>

        <form action="" method="POST" class="forgot-password-form">
            <div class="input-group">
                <label for="email">Enter your email address:</label>
                <input type="email" name="email" id="email" required>
            </div>

            <div class="input-group">
                <label for="keyword">Enter your keyword:</label>
                <input type="text" name="keyword" id="keyword" required>
            </div>

            <button type="submit" class="btn-submit">Verify</button>

            <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
            <?php if (isset($success)) echo "<p class='success'>$success</p>"; ?>
        </form>

        <?php if (isset($go_to_login_button)) : ?>
            <p>
                <a href="login.php" class="btn-go-to-login">Go to Login</a>
            </p>
        <?php endif; ?>

        
        <p><a href="index.php" class="go-back">← Go Back To The Homepage</a></p>
    </div>

    
    <?php if (isset($modal) && $modal) : ?>
        <div id="password-reset-modal" class="modal">
            <div class="modal-content">
                <span class="close-btn" onclick="closeModal()">&times;</span>
                <h2>Enter your new password</h2>
                <form action="" method="POST">
                    <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
                    <input type="hidden" name="keyword" value="<?= htmlspecialchars($keyword) ?>">
                    <label for="new_password">New Password:</label>
                    <input type="password" name="new_password" id="new_password" required>
                    <button type="submit" class="btn-submit">Reset Password</button>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <script>
        
        function closeModal() {
            document.getElementById("password-reset-modal").style.display = "none";
        }

        
        window.onload = function() {
            if (document.getElementById("password-reset-modal")) {
                document.getElementById("password-reset-modal").style.display = "block";
            }
        }
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

.forgot-password-container {
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

.forgot-password-form {
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

.success {
    color: green;
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

.btn-go-to-login {
    display: inline-block;
    background-color: #007bff;
    color: white;
    font-size: 16px;
    padding: 10px 20px;
    border-radius: 8px;
    text-decoration: none;
    margin-top: 20px;
    transition: background-color 0.3s;
}

.btn-go-to-login:hover {
    background-color: #0056b3;
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


.modal {
    display: none;
    position: fixed;
    z-index: 1;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgb(0,0,0);
    background-color: rgba(0, 0, 0, 0.4);
}

.modal-content {
    background-color: #fff;
    margin: 10% auto;
    padding: 20px;
    border-radius: 8px;
    width: 400px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.close-btn {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.close-btn:hover,
.close-btn:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}

h2 {
    color: #333;
    font-size: 20px;
}

.modal input {
    width: 95%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 16px;
    margin-bottom: 20px;
}

.modal .btn-submit {
    background-color: #f7b500;
    color: white;
    font-size: 16px;
    padding: 12px 20px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: background-color 0.3s;
}

.modal .btn-submit:hover {
    background-color: #c77f00;
}
</style>