<?php
require_once 'config.php'; // Kết nối tới database
require_once 'helpers.php'; 

checkAdminAccess();
function getWaterPricePerUnit($conn) {
    $sql = "SELECT key_value FROM settings WHERE key_name = 'water_price_per_unit'";
    $result = $conn->query($sql);
    $setting = $result->fetch_assoc();
    return $setting ? (int)$setting['key_value'] : 10000; // Giá mặc định nếu không có trong DB
}

function getUsersAndWaterData($conn) {
    $sql = "
        SELECT users.id, users.name, users.email, users.address, water.number_water, water.date
        FROM users 
        LEFT JOIN water ON users.id = water.id_user 
        WHERE users.role = 'user'
        ORDER BY water.date DESC
    ";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

function calculateTotalAmount($number_water, $price_per_unit) {
    return $number_water * $price_per_unit;
}

function createInvoiceAndRemoveWaterData($conn, $user, $total_amount) {
    $conn->begin_transaction();

    try {
        // Thêm hóa đơn vào cơ sở dữ liệu
        $insert_sql = "INSERT INTO invoices (id_user, total_amount, status, created_at) VALUES (?, ?, 'unpaid', NOW())";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param('id', $user['id'], $total_amount);
        if (!$stmt->execute()) {
            throw new Exception("Có lỗi xảy ra khi tạo hóa đơn!");
        }

        // Xóa thông tin số nước của người dùng sau khi tạo hóa đơn
        if ($user['number_water'] > 0 && $user['date']) {
            $delete_sql = "DELETE FROM water WHERE id_user = ? AND date = ?";
            $stmt = $conn->prepare($delete_sql);
            $stmt->bind_param('is', $user['id'], $user['date']);
            if (!$stmt->execute()) {
                throw new Exception("Có lỗi xảy ra khi xóa thông tin nước của người dùng!");
            }
        }

        // Commit giao dịch nếu mọi thứ thành công
        $conn->commit();
    } catch (Exception $e) {
        // Rollback giao dịch nếu có lỗi
        $conn->rollback();
        throw $e;
    }
}
?>
