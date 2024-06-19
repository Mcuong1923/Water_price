<?php
require_once 'config.php'; // Kết nối tới database
require_once 'helpers.php'; 

checkAdminAccess();

function getInvoiceDetails($conn, $invoice_id) {
    $sql = "SELECT invoices.id, invoices.total_amount, invoices.created_at, users.name, users.email, users.address
            FROM invoices
            JOIN users ON invoices.id_user = users.id
            WHERE invoices.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $invoice_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function handleExport() {
    echo "<script>alert('Xuất Hóa Đơn Thành Công');</script>";
}

function handleExportEmail() {
    echo "<script>alert('Hóa đơn đã được gửi thành công qua email!');</script>";
}
?>
