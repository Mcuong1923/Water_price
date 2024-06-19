<?php
session_start();
require_once 'config.php'; // Kết nối tới database

$error = ''; // Khởi tạo biến lỗi trống

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Kiểm tra email và mật khẩu
    $sql = "SELECT * FROM users WHERE email = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user) {
            // Kiểm tra mật khẩu
            if (password_verify($password, $user['password'])) {
                // Mật khẩu đúng, tạo session và chuyển hướng đến dashboard
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['name'] = $user['name']; // Lưu tên người dùng vào session
                header('Location: dashboard.php');
                exit();
            } else {
                // Mật khẩu không đúng
                $error = "Mật khẩu không đúng. Vui lòng thử lại.";
            }
        } else {
            // Email không tồn tại
            $error = "Email không tồn tại trong hệ thống.";
        }
    } else {
        // Xử lý lỗi khi chuẩn bị câu lệnh SQL
        $error = "Lỗi khi chuẩn bị câu lệnh SQL: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Login</h2>
        <form method="post" action="login.php">
            <label for="email">Email:</label>
            <input type="email" name="email" required>
            <br>
            <label for="password">Password:</label>
            <input type="password" name="password" required>
            <br>
            <input type="submit" value="Login">
        </form>
        <!-- Hiển thị thông báo lỗi nếu có -->
        <?php if ($error): ?>
            <p style='color: red;'><?php echo $error; ?></p>
        <?php endif; ?>
        <p>Chưa có tài khoản? <a href="register.php">Đăng ký tại đây</a></p>
    </div>
</body>
</html>
