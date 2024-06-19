<?php
require_once 'config.php'; // Kết nối tới database

function validatePassword($password) {
    if (strlen($password) < 6) {
        return "Mật khẩu phải có tối thiểu 6 ký tự.";
    }
    return null;
}

function checkEmailExists($conn, $email) {
    $checkEmailSql = "SELECT * FROM users WHERE email = ?";
    if ($stmt = $conn->prepare($checkEmailSql)) {
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc() ? true : false;
    } else {
        throw new Exception("Lỗi trong việc kiểm tra email. Vui lòng thử lại sau.");
    }
}


function registerUser($conn, $name, $email, $password, $address) {
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    $sql = "INSERT INTO users (name, email, password, address) VALUES (?, ?, ?, ?)";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('ssss', $name, $email, $hashed_password, $address);
        if ($stmt->execute()) {
            return "Đăng ký thành công.";
        } else {
            return "Đăng ký không thành công. Vui lòng thử lại sau.";
        }
    } else {
        throw new Exception("Lỗi trong việc chuẩn bị câu lệnh đăng ký. Vui lòng thử lại sau.");
    }
}
?>
