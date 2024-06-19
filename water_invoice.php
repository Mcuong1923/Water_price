<?php
session_start();
require_once 'config.php';

if ($_SESSION['role'] != 'user') {
    header('Location: dashboard.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Truy vấn để lấy thông tin hóa đơn chưa thanh toán của user
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
$invoices = $result->fetch_all(MYSQLI_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['invoice_id'])) {
    $invoice_id = $_POST['invoice_id'];
    $update_sql = "UPDATE invoices SET status = 'paid' WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param('i', $invoice_id);
    if ($stmt->execute()) {
        $success = "Hóa đơn đã được thanh toán thành công.";
    } else {
        $error = "Có lỗi xảy ra khi thanh toán hóa đơn!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Hóa đơn nước</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Hóa đơn nước</h2>
        <?php if (isset($success)) echo "<p style='color: green;'>$success</p>"; ?>
        <?php if (isset($error)) echo "<p style='color: red;'>$error</p>"; ?>
        <form method="post" action="water_invoice.php">
            <label for="invoice_id">Chọn hóa đơn để thanh toán:</label>
            <select name="invoice_id" required>
                <?php foreach ($invoices as $invoice) : ?>
                    <option value="<?php echo $invoice['id']; ?>">
                        Hóa đơn ID: <?php echo $invoice['id']; ?> - Ngày: <?php echo date("d-m-Y", strtotime($invoice['created_at'])); ?> - Số tiền: <?php echo number_format($invoice['total_amount'], 2); ?> VND
                    </option>
                <?php endforeach; ?>
            </select>
            <br>
            <input type="submit" value="Thanh toán">
        </form>
        <p><a href="dashboard.php">Quay lại Dashboard</a></p>
    </div>
</body>
</html>
