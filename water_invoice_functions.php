<?php
require_once 'config.php'; // Kết nối tới database

function checkUserAccess() {
    if ($_SESSION['role'] != 'user') {
        header('Location: dashboard.php');
        exit();
    }
}

function getUnpaidInvoices($conn, $user_id) {
    $sql = "SELECT invoices.id, invoices.total_amount, invoices.created_at, water.date
            FROM invoices
            JOIN users ON invoices.id_user = users.id
            LEFT JOIN water ON invoices.id_user = water.id_user
            WHERE invoices.id_user = ? AND invoices.status = 'unpaid'
            GROUP BY invoices.id
            ORDER BY invoices.created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function payInvoice($conn, $invoice_id) {
    $update_sql = "UPDATE invoices SET status = 'paid' WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param('i', $invoice_id);
    if ($stmt->execute()) {
        return "Hóa đơn đã được thanh toán thành công.";
    } else {
        throw new Exception("Có lỗi xảy ra khi thanh toán hóa đơn!");
    }
}
?>
