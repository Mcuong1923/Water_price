<?php
require 'config.php'; // Kết nối tới database
require_once 'helpers.php'; 

checkAdminAccess();

function getWaterPricePerUnit($conn) {
    $sql = "SELECT key_value FROM settings WHERE key_name = 'water_price_per_unit'";
    $result = $conn->query($sql);
    $setting = $result->fetch_assoc();
    return $setting ? (int)$setting['key_value'] : 10000; // Giá mặc định nếu không có trong DB
}

function updateWaterPrice($conn, $new_price) {
    if ($new_price < 0) {
        throw new Exception("Giá tiền mỗi đơn vị không thể âm. Vui lòng nhập lại!");
    }

    $update_sql = "UPDATE settings SET key_value = ? WHERE key_name = 'water_price_per_unit'";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param('s', $new_price);
    if ($stmt->execute()) {
        return $new_price;
    } else {
        throw new Exception("Có lỗi xảy ra khi cập nhật giá tiền mỗi đơn vị!");
    }
}
?>
