<?php
require 'config.php'; // Kết nối tới database

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password']; // Lấy mật khẩu từ form
    $hashed_password = password_hash($password, PASSWORD_BCRYPT); // Mã hóa mật khẩu
    $address = $_POST['address'];

    $sql = "INSERT INTO users (name, email, password, address) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssss', $name, $email, $hashed_password, $address); // Sử dụng mật khẩu đã mã hóa

    if ($stmt->execute()) {
        header('Location: login.php');
        exit();
    } else {
        $error = "Registration failed";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Register</h2>
        <form method="post" action="register.php">
            <label for="name">Name:</label>
            <input type="text" name="name" required>
            <br>
            <label for="email">Email:</label>
            <input type="email" name="email" required>
            <br>
            <label for="password">Password:</label>
            <input type="password" name="password" required>
            <br>
            <label for="address">Address:</label>
            <input type="text" name="address" required>
            <br>
            <input type="submit" value="Register">
        </form>
        <?php if (isset($error)) echo "<p>$error</p>"; ?>
        <p>Đã có tài khoản? <a href="login.php">Đăng nhập tại đây</a></p>
    </div>
</body>
</html>
