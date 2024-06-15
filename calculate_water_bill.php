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

// Truy vấn để lấy thông tin người dùng (chỉ những user có role là 'user') và số nước gần nhất của họ
$sql = "
    SELECT users.id, users.name, users.email, users.address, water.number_water, water.date
    FROM users 
    LEFT JOIN water ON users.id = water.id_user 
    WHERE users.role = 'user'
    ORDER BY water.date DESC
";
$result = $conn->query($sql);
$users = $result->fetch_all(MYSQLI_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];
    // Tính toán tổng số tiền
    foreach ($users as $user) {
        if ($user['id'] == $user_id) {
            $number_water = $user['number_water'] ? $user['number_water'] : 0;
            $total_amount = $number_water * $water_price_per_unit;

            // Thêm hóa đơn vào cơ sở dữ liệu
            $insert_sql = "INSERT INTO invoices (id_user, total_amount, status, created_at) VALUES (?, ?, 'unpaid', NOW())";
            $stmt = $conn->prepare($insert_sql);
            $stmt->bind_param('id', $user_id, $total_amount);
            if ($stmt->execute()) {
                // Lưu thông báo thành công và tổng số tiền vào session
                $_SESSION['success'] = "Hóa đơn đã được tạo thành công!";
                $_SESSION['total_amount'] = $total_amount;
                header("Location: calculate_water_bill.php");
                exit();
            } else {
                $error = "Có lỗi xảy ra khi tạo hóa đơn!";
            }
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tính toán số tiền nước</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Tính toán số tiền nước</h2>
        <!-- Hiển thị thông báo thành công và tổng số tiền nếu có -->
        <?php if (isset($_SESSION['success'])): ?>
            <p style="color: green;"><?php echo $_SESSION['success']; ?></p>
            <p>Tổng số tiền cần thanh toán: <?php echo number_format($_SESSION['total_amount'], 2); ?> VND</p>
            <?php
            // Xóa thông báo và tổng số tiền khỏi session sau khi hiển thị
            unset($_SESSION['success']);
            unset($_SESSION['total_amount']);
            ?>
        <?php endif; ?>
        
        <!-- Form chọn người dùng và tạo hóa đơn -->
        <form method="post" action="calculate_water_bill.php">
            <label for="user_id">Chọn người dùng:</label>
            <select name="user_id" required>
                <?php foreach ($users as $user) : ?>
                    <option value="<?php echo $user['id']; ?>">
                        <?php 
                        echo $user['id'] . " - " . htmlspecialchars($user['name']) . " - " . htmlspecialchars($user['email']) . " - " . htmlspecialchars($user['address']);
                        if ($user['date']) {
                            echo " - " . date("d-m-Y", strtotime($user['date'])); // Hiển thị ngày ở định dạng dd-mm-yyyy
                        } else {
                            echo " - Không có ngày";
                        }
                        ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <br>
            <input type="submit" value="Tạo hóa đơn">
        </form>
        
        <!-- Hiển thị thông báo lỗi nếu có -->
        <?php if (isset($error)) echo "<p style='color: red;'>$error</p>"; ?>
        
        <p><a href="dashboard.php">Quay lại Dashboard</a></p>
    </div>
</body>
</html>
