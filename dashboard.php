<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Dashboard</h2>
        <p>Xin chào, <?php echo $_SESSION['name']; ?>!</p> <!-- Hiển thị tên người dùng -->
        <?php if ($_SESSION['role'] == 'admin') : ?>
            <a href="update_water.php">Cập nhật số nước</a><br>
            <a href="manage_invoices.php">Quản lý hóa đơn</a><br>
            <a href="update_water_price.php">cập nhật tiền nước</a><br>
            <a href="calculate_water_bill.php">Tính toán hóa đơn</a><br>
        <?php elseif ($_SESSION['role'] == 'user') : ?>
        <a href="water_invoice.php">Hóa đơn</a><br>
        <?php endif ?>
        <a href="logout.php">Logout</a>
    </div>
</body>
</html>
