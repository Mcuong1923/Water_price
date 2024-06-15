<?php
session_start();
require 'config.php';

// Kiểm tra quyền truy cập của admin
if ($_SESSION['role'] != 'admin') {
    header('Location: dashboard.php');
    exit();
}

// Lấy giá tiền mỗi đơn vị từ cơ sở dữ liệu
$sql = "SELECT key_value FROM settings WHERE key_name = 'water_price_per_unit'";
$result = $conn->query($sql);
$setting = $result->fetch_assoc();
$water_price_per_unit = $setting ? (int)$setting['key_value'] : 10000; // Giá mặc định nếu không có trong DB

// Nếu admin cập nhật giá tiền mỗi đơn vị
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['new_water_price_per_unit'])) {
    $new_price = $_POST['new_water_price_per_unit'];
    // Cập nhật giá tiền mỗi đơn vị trong cơ sở dữ liệu
    $update_sql = "UPDATE settings SET key_value = ? WHERE key_name = 'water_price_per_unit'";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param('s', $new_price);
    if ($stmt->execute()) {
        $water_price_per_unit = $new_price;
        $success = "Giá tiền mỗi đơn vị đã được cập nhật thành công!";
    } else {
        $error = "Có lỗi xảy ra khi cập nhật giá tiền mỗi đơn vị!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cập nhật giá tiền nước</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Cập nhật giá tiền nước</h2>
        <form method="post" action="update_water_price.php">
            <label for="new_water_price_per_unit">Giá tiền mỗi đơn vị:</label>
            <input type="number" name="new_water_price_per_unit" value="<?php echo $water_price_per_unit; ?>" required>
            <br>
            <input type="submit" value="Xác nhận và lưu">
        </form>
        <?php if (isset($success)) echo "<p style='color: green;'>$success</p>"; ?>
        <?php if (isset($error)) echo "<p style='color: red;'>$error</p>"; ?>
        <p><a href="dashboard.php">Quay lại Dashboard</a></p>
    </div>
</body>
</html>
