<?php
include 'includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    
    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $keyword = trim($_POST["keyword"]);
        $role_id = 1;

        
        $sql = "INSERT INTO users (username, password, email, keyword, role_id) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $username, $hashed_password, $email, $keyword, $role_id);

        if ($stmt->execute()) {
            header("Location: login.php?success=1");
            exit;
        } else {
            $error = "Error: " . $conn->error;
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="register-container">
        <h2>Create a New Account</h2>

        <form action="" method="POST" class="register-form">
            <div class="input-group">
                <label for="username">Username:</label>
                <input type="text" name="username" id="username" required>
            </div>

            <div class="input-group">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" required>
            </div>

            <div class="input-group">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required>
            </div>

            <div class="input-group">
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" name="confirm_password" id="confirm_password" required>
            </div>

            <div class="input-group">
                <label for="keyword">Keyword (used for password recovery):</label>
                <input type="text" name="keyword" id="keyword" required>
            </div>

            <button type="submit" class="btn-submit">Register</button>

            <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        </form>

        
        <p><a href="index.php" class="go-back">← Go Back To The Homepage</a></p>
    </div>
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
</style>