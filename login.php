<?php
session_start();
include 'includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    $sql = "SELECT users.user_id, users.password, users.email, users.role_id, roles.role_name, users.profile_picture 
            FROM users 
            JOIN roles ON users.role_id = roles.role_id 
            WHERE users.username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);

    if ($stmt->execute()) {
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($user_id, $hashed_password, $email, $role_id, $role_name, $profile_picture);
            $stmt->fetch();

            if (password_verify($password, $hashed_password)) {
                
                $_SESSION["user_id"] = $user_id;
                $_SESSION["username"] = $username;
                $_SESSION["user_email"] = $email;  
                $_SESSION["role_id"] = $role_id;
                $_SESSION["role_name"] = $role_name;
                $_SESSION["profile_picture"] = $profile_picture;

                header("Location: index.php");
                exit;
            } else {
                $error = "Invalid password.";
            }
        } else {
            $error = "User not found.";
        }
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Login</title>
    <link rel="stylesheet" href="styles.css" />
</head>
<body>
    <div class="login-container">
        <h2>Login to Your Account</h2>

        <form action="" method="POST" class="login-form">
            <div class="input-group">
                <label for="username">Username:</label>
                <input type="text" name="username" id="username" required />
            </div>

            <div class="input-group">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required />
            </div>

            <button type="submit" class="btn-submit">Login</button>

            <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>

            <div class="forgot-password">
                <a href="forgot_password.php">Forgot Password?</a>
            </div>
        </form>

        <p><a href="index.php" class="go-back">← Go Back To The Homepage</a></p>
    </div>

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

        .login-container {
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

        .login-form {
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

        .forgot-password {
            margin-top: 15px;
        }

        .forgot-password a {
            color: #007bff;
            text-decoration: none;
            font-size: 14px;
        }

        .forgot-password a:hover {
            text-decoration: underline;
        }

        .go-back {
            text-decoration: none;
            color: #007bff;
            font-size: 16px;
            margin-top: 15px;
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
    </style>
</body>
</html>