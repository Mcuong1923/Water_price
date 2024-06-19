<?php
require_once 'config.php'; // Kết nối tới database

function getUserByEmail($conn, $email) {
    $sql = "SELECT * FROM users WHERE email = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        return $user ?: null; // Trả về null nếu không tìm thấy người dùng
    } else {
        throw new Exception("Lỗi khi chuẩn bị câu lệnh SQL: " . $conn->error);
    }
}

function verifyPassword($inputPassword, $storedPassword) {
    return password_verify($inputPassword, $storedPassword);
}

function loginUser($conn, $email, $password) {
    $user = getUserByEmail($conn, $email);
    if ($user) {
        if (verifyPassword($password, $user['password'])) {
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];
            return "Đăng nhập thành công.";
        } else {
            return "Mật khẩu không đúng. Vui lòng thử lại.";
        }
    } else {
        return "Email không tồn tại trong hệ thống.";
    }
}
?>
