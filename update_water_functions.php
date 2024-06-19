<?php
require_once 'config.php'; // Kết nối tới database
require_once 'helpers.php'; 

checkAdminAccess();

function getUsers($conn) {
    $sql = "SELECT id, name, address FROM users WHERE role = 'user'";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

function updateWater($conn, $user_id, $number_water, $date) {
    $current_date = date("Y-m-d");

    if (empty($user_id)) {
        throw new Exception("Vui lòng chọn người dùng.");
    } 
    if ($number_water < 0) {
        throw new Exception("Số nước không thể là số âm.");
    } 
    if ($date > $current_date) {
        throw new Exception("Ngày không thể là ngày trong tương lai.");
    }

    $sql = "INSERT INTO water (number_water, date, id_user) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('isi', $number_water, $date, $user_id);

    if (!$stmt->execute()) {
        throw new Exception("Cập nhật thất bại");
    }

    return "Cập nhật thành công";
}
?>
