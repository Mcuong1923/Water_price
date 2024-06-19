<?php
require_once 'config.php'; // Kết nối tới database
require_once 'helpers.php'; 

checkAdminAccess();
function deleteInvoice($conn, $invoice_id) {
    $conn->begin_transaction();

    try {
        $delete_sql = "DELETE FROM invoices WHERE id = ?";
        $stmt = $conn->prepare($delete_sql);
        if (!$stmt) {
            throw new Exception("Lỗi khi chuẩn bị câu lệnh xóa invoices: " . $conn->error);
        }
        $stmt->bind_param('i', $invoice_id);
        if (!$stmt->execute()) {
            throw new Exception("Lỗi khi thực hiện câu lệnh xóa invoices: " . $stmt->error);
        }
        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
}

function getWaterPricePerUnit($conn) {
    $sql = "SELECT key_value FROM settings WHERE key_name = 'water_price_per_unit'";
    $result = $conn->query($sql);
    $setting = $result->fetch_assoc();
    return $setting ? (int)$setting['key_value'] : 10000; // Giá mặc định nếu không có trong DB
}

function searchInvoices($conn, $search_name, $search_status, $invoices_per_page, $offset) {
    $search_conditions = [];
    if ($search_name) {
        $search_conditions[] = "users.name LIKE '%$search_name%'";
    }
    if ($search_status) {
        $search_conditions[] = "invoices.status = '$search_status'";
    }
    $search_condition = implode(' AND ', $search_conditions);
    if ($search_condition) {
        $search_condition = "WHERE $search_condition";
    }

    $sql = "
        SELECT invoices.id, invoices.total_amount, invoices.status, users.name, users.email, users.address, MAX(water.date) AS water_date
        FROM invoices 
        JOIN users ON invoices.id_user = users.id
        LEFT JOIN water ON invoices.id_user = water.id_user
        $search_condition
        GROUP BY invoices.id
        ORDER BY MAX(water.date) DESC, invoices.created_at DESC
        LIMIT $invoices_per_page OFFSET $offset
    ";

    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

function countInvoices($conn, $search_name, $search_status) {
    $search_conditions = [];
    if ($search_name) {
        $search_conditions[] = "users.name LIKE '%$search_name%'";
    }
    if ($search_status) {
        $search_conditions[] = "invoices.status = '$search_status'";
    }
    $search_condition = implode(' AND ', $search_conditions);
    if ($search_condition) {
        $search_condition = "WHERE $search_condition";
    }

    $sql = "SELECT COUNT(*) AS total FROM invoices JOIN users ON invoices.id_user = users.id $search_condition";
    $result = $conn->query($sql);
    return $result->fetch_assoc()['total'];
}
?>
