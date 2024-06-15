<?php
session_start();
require 'config.php';

if ($_SESSION['role'] != 'admin') {
    header('Location: dashboard.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_POST['user_id'];
    $number_water = $_POST['number_water'];
    $date = $_POST['date'];

    $sql = "INSERT INTO water (number_water, date, id_user) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('isi', $number_water, $date, $user_id);

    if ($stmt->execute()) {
        $success = "Cập nhật thành công";
    } else {
        $error = "Cập nhật thất bại";
    }
}

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
            <select name="user_id">
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
        <?php if (isset($success)) echo "<p>$success</p>"; ?>
        <?php if (isset($error)) echo "<p>$error</p>"; ?>
        <p><a href="dashboard.php">Quay lại Dashboard</a></p>
    </div>
</body>
</html>

