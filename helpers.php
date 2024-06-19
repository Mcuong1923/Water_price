<?php

// Kiểm tra xem hàm checkAdminAccess đã tồn tại chưa
if (!function_exists('checkAdminAccess')) {
    function checkAdminAccess() {
        // Kiểm tra xem session có role admin không
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header('Location: dashboard.php');
            exit();
        }
    }
}

// Bạn có thể thêm nhiều hàm khác vào đây
// Ví dụ: hàm kiểm tra quyền truy cập của người dùng
if (!function_exists('checkUserAccess')) {
    function checkUserAccess() {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
            header('Location: login.php');
            exit();
        }
    }
}

?>
