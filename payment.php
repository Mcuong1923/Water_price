<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$total_amount = $_GET['total'];
$user_id = $_SESSION['user_id'];

$sql = "SELECT name, email, address FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['pay'])) {
    $sql = "INSERT INTO invoices (id_user, total_amount) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('id', $user_id, $total_amount);

    if ($stmt->execute()) {
        $success = "Thanh toán thành công";
    } else {
        $error = "Thanh toán thất bại";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Thanh toán</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Thanh toán</h2>
        <p>Tên: <?php echo $user['name']; ?></p>
        <p>Email: <?php echo $user['email']; ?></p>
        <p>Địa chỉ: <?php echo $user['address']; ?></p>
        <p>Tổng số tiền cần trả: <?php echo $total_amount; ?></p>
        <form method="post" action="payment.php">
            <input type="hidden" name="pay" value="true">
            <input type="submit" value="Thanh toán">
        </form>
        <?php if (isset($success)) echo "<p>$success</p>"; ?>
        <?php if (isset($error)) echo "<p>$error</p>"; ?>
        <p><a href="dashboard.php">Quay lại Dashboard</a></p>
    </div>
</body>
</html>
