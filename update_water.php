<?php
session_start();
require_once 'config.php';

// Kiểm tra quyền admin
if ($_SESSION['role'] != 'admin') {
    header('Location: dashboard.php');
    exit();
}

$error = ''; // Biến để lưu thông báo lỗi
$success = ''; // Biến để lưu thông báo thành công

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_POST['user_id'];
    $number_water = $_POST['number_water'];
    $date = $_POST['date'];

    // Lấy ngày hiện tại
    $current_date = date("Y-m-d");

    // Kiểm tra nếu người dùng chưa được chọn
    if (empty($user_id)) {
        $error = "Vui lòng chọn người dùng.";
    } 
    // Kiểm tra nếu số nước âm
    elseif ($number_water < 0) {
        $error = "Số nước không thể là số âm.";
    }
    // Kiểm tra nếu ngày là ngày trong tương lai
    elseif ($date > $current_date) {
        $error = "Ngày không thể là ngày trong tương lai.";
    } 
    else {
        $sql = "INSERT INTO water (number_water, date, id_user) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('isi', $number_water, $date, $user_id);

        if ($stmt->execute()) {
            $success = "Cập nhật thành công";
        } else {
            $error = "Cập nhật thất bại";
        }
    }
}

// Lấy danh sách người dùng
$sql = "SELECT id, name, address FROM users WHERE role = 'user'";
$result = $conn->query($sql);
$users = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cập nhật số nước</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Cập nhật số nước</h2>
        <form method="post" action="update_water.php">
            <label for="user_id">User:</label>
            <select name="user_id" required>
                <option value="">-- Chọn người dùng --</option> <!-- Thêm tùy chọn mặc định -->
                <?php foreach ($users as $user) : ?>
                    <option value="<?php echo $user['id']; ?>">
                        <?php echo $user['id'] . " - " . $user['name'] . " - " . $user['address']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <br>
            <label for="number_water">Số nước:</label>
            <input type="number" name="number_water" required>
            <br>
            <label for="date">Date:</label>
            <input type="date" name="date" required>
            <br>
            <input type="submit" value="Update">
        </form>
        <!-- Hiển thị thông báo -->
        <?php if ($error): ?>
            <p style='color: red;'><?php echo $error; ?></p>
        <?php endif; ?>
        <?php if ($success): ?>
            <p style='color: green;'><?php echo $success; ?></p>
        <?php endif; ?>
        <p><a href="dashboard.php">Quay lại Dashboard</a></p>
    </div>
</body>
</html>
