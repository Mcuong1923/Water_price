<?php
require 'config.php'; // Kết nối tới database

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password']; // Lấy mật khẩu từ form
    $address = $_POST['address'];

    // Kiểm tra độ dài mật khẩu
    if (strlen($password) < 6) {
        $error = "Mật khẩu phải có tối thiểu 6 ký tự.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT); // Mã hóa mật khẩu

        // Kiểm tra xem email đã tồn tại trong cơ sở dữ liệu chưa
        $checkEmailSql = "SELECT * FROM users WHERE email = ?";
        if ($stmt = $conn->prepare($checkEmailSql)) {
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $error = "Email này đã được sử dụng. Vui lòng sử dụng email khác.";
            } else {
                // Email chưa tồn tại, tiến hành đăng ký
                $sql = "INSERT INTO users (name, email, password, address) VALUES (?, ?, ?, ?)";
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->bind_param('ssss', $name, $email, $hashed_password, $address); // Sử dụng mật khẩu đã mã hóa

                    if ($stmt->execute()) {
                        header('Location: login.php');
                        exit();
                    } else {
                        $error = "Đăng ký không thành công. Vui lòng thử lại sau.";
                    }
                } else {
                    // Xử lý lỗi khi chuẩn bị câu lệnh SQL
                    $error = "Lỗi trong việc chuẩn bị câu lệnh đăng ký. Vui lòng thử lại sau.";
                }
            }
        } else {
            // Xử lý lỗi khi chuẩn bị câu lệnh kiểm tra email
            $error = "Lỗi trong việc kiểm tra email. Vui lòng thử lại sau.";
        }
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
        <?php if (isset($error)) echo "<p style='color: red;'>$error</p>"; ?>
        <p>Đã có tài khoản? <a href="login.php">Đăng nhập tại đây</a></p>
    </div>
</body>
</html>
