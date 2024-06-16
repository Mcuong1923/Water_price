<?php
session_start();
require 'config.php';

// Kiểm tra quyền truy cập của admin
if ($_SESSION['role'] != 'admin') {
    header('Location: dashboard.php');
    exit();
}

// Xử lý yêu cầu xóa hóa đơn
if (isset($_POST['delete_invoice_id'])) {
    $delete_invoice_id = $_POST['delete_invoice_id'];
    
    // Lấy thông tin người dùng và ngày để xóa trong bảng water
    $select_sql = "
        SELECT water.date, invoices.id_user
        FROM invoices 
        JOIN water ON invoices.id_user = water.id_user
        WHERE invoices.id = ? AND water.date = (SELECT MAX(date) FROM water WHERE id_user = invoices.id_user)
    ";
    $stmt = $conn->prepare($select_sql);
    $stmt->bind_param('i', $delete_invoice_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $invoice_data = $result->fetch_assoc();
    
    if ($invoice_data) {
        $water_date = $invoice_data['date'];
        $user_id = $invoice_data['id_user'];

        // Xóa bản ghi liên quan trong bảng water
        $delete_water_sql = "DELETE FROM water WHERE id_user = ? AND date = ?";
        $stmt = $conn->prepare($delete_water_sql);
        $stmt->bind_param('is', $user_id, $water_date);
        if ($stmt->execute()) {
            // Xóa hóa đơn sau khi xóa thông tin nước
            $delete_sql = "DELETE FROM invoices WHERE id = ?";
            $stmt = $conn->prepare($delete_sql);
            $stmt->bind_param('i', $delete_invoice_id);
            if ($stmt->execute()) {
                $success = "Hóa đơn và thông tin nước đã được xóa thành công!";
            } else {
                $error = "Có lỗi xảy ra khi xóa hóa đơn!";
            }
        } else {
            $error = "Có lỗi xảy ra khi xóa thông tin nước!";
        }
    }
}

// Lấy giá tiền mỗi đơn vị từ cơ sở dữ liệu
$sql = "SELECT key_value FROM settings WHERE key_name = 'water_price_per_unit'";
$result = $conn->query($sql);
$setting = $result->fetch_assoc();
$water_price_per_unit = $setting ? (int)$setting['key_value'] : 10000; // Giá mặc định nếu không có trong DB

// Pagination logic
$invoices_per_page = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $invoices_per_page;

// Tổng số hóa đơn
$sql = "SELECT COUNT(*) AS total FROM invoices";
$result = $conn->query($sql);
$total_invoices = $result->fetch_assoc()['total'];
$total_pages = ceil($total_invoices / $invoices_per_page);

// Truy vấn để lấy thông tin hóa đơn và thông tin người dùng từ bảng invoices và users
$sql = "
    SELECT invoices.id, invoices.total_amount, invoices.status, users.name, users.email, users.address, MAX(water.date) AS water_date
    FROM invoices 
    JOIN users ON invoices.id_user = users.id
    LEFT JOIN water ON invoices.id_user = water.id_user
    GROUP BY invoices.id
    ORDER BY MAX(water.date) DESC, invoices.created_at DESC
    LIMIT $invoices_per_page OFFSET $offset
";

$result = $conn->query($sql);
$invoices = $result->fetch_all(MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quản lý hóa đơn</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Quản lý hóa đơn</h2>
        <?php if (isset($success)) echo "<p style='color: green;'>$success</p>"; ?>
        <?php if (isset($error)) echo "<p style='color: red;'>$error</p>"; ?>
        <table>
            <tr>
                <th>Ngày cập nhật số nước</th>
                <th>Tên người dùng</th>
                <th>Email</th>
                <th>Địa chỉ</th>
                <th>Số tiền phải trả</th>
                <th>Trạng thái</th>
                <th>Hành động</th>
            </tr>
            <?php foreach ($invoices as $invoice) : ?>
            <tr>
                <td><?php echo date("d-m-Y", strtotime($invoice['water_date'])); ?></td>
                <td><?php echo htmlspecialchars($invoice['name']); ?></td>
                <td><?php echo htmlspecialchars($invoice['email']); ?></td>
                <td><?php echo htmlspecialchars($invoice['address']); ?></td>
                <td><?php echo number_format($invoice['total_amount'], 2); ?> VND</td>
                <td><?php echo $invoice['status'] == 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán'; ?></td>
                <td>
                    <form method="post" action="manage_invoices.php" onsubmit="return confirm('Bạn có chắc chắn muốn xóa hóa đơn này?');">
                        <input type="hidden" name="delete_invoice_id" value="<?php echo $invoice['id']; ?>">
                        <input type="submit" value="Xóa" class="delete-button">
                    </form>
                    <a href="invoice_details.php?id=<?php echo $invoice['id']; ?>" class="detail-button">Chi tiết</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>">Trang trước</a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?php echo $i; ?>" class="<?php if ($i == $page) echo 'active'; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
            <?php if ($page < $total_pages): ?>
                <a href="?page=<?php echo $page + 1; ?>">Trang sau</a>
            <?php endif; ?>
        </div>
        <p><a href="dashboard.php">Quay lại Dashboard</a></p>
    </div>
</body>
</html>
