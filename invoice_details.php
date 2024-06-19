<?php
session_start();
require_once 'config.php';

if ($_SESSION['role'] != 'admin') {
    header('Location: dashboard.php');
    exit();
}

// Lấy ID hóa đơn từ URL
$invoice_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($invoice_id == 0) {
    echo "Không tìm thấy hóa đơn.";
    exit;
}

// Truy vấn cơ sở dữ liệu để lấy thông tin chi tiết hóa đơn
$sql = "SELECT invoices.id, invoices.total_amount, invoices.created_at, users.name, users.email, users.address
        FROM invoices
        JOIN users ON invoices.id_user = users.id
        WHERE invoices.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $invoice_id);
$stmt->execute();
$result = $stmt->get_result();
$invoice = $result->fetch_assoc();

if (!$invoice) {
    echo "Không tìm thấy thông tin hóa đơn.";
    exit;
}

// Khi người dùng click nút "Xuất Email"
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['export'])) {
    // Giả sử gửi email ở đây
    echo "<script>alert('Xuất Hóa Đơn Thành Công');</script>";
}
// Khi người dùng click nút "Xuất Email"
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['export2'])) {
    // Giả sử gửi email ở đây
    echo "<script>alert('Hóa đơn đã được gửi thành công qua email!');</script>";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Chi Tiết Hóa Đơn</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Chi Tiết Hóa Đơn</h2>
        <p>Tên Khách Hàng: <?php echo htmlspecialchars($invoice['name']); ?></p>
        <p>Email: <?php echo htmlspecialchars($invoice['email']); ?></p>
        <p>Địa Chỉ: <?php echo htmlspecialchars($invoice['address']); ?></p>
        <p>Ngày Xuất Hóa Đơn: <?php echo date("d-m-Y", strtotime($invoice['created_at'])); ?></p>
        <p>Số Tiền: <?php echo number_format($invoice['total_amount'], 2); ?> VND</p>
        <form method="post">
            <input type="submit" name="export" value="Xuất Hóa Đơn ">
        </form>
        <form method="post">
            <input type="submit" name="export2" value="Gửi về Email">
        </form>
        <p><a href="manage_invoices.php">Quay lại trang quản lý hóa đơn</a></p>
    </div>
</body>
</html>
