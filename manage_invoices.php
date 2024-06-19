<?php
session_start();
require_once 'config.php';

// Kiểm tra quyền truy cập của admin
if ($_SESSION['role'] != 'admin') {
    header('Location: dashboard.php');
    exit();
}

$error = ''; // Biến để lưu thông báo lỗi
$success = ''; // Biến để lưu thông báo thành công

// Xử lý yêu cầu xóa hóa đơn
if (isset($_POST['delete_invoice_id'])) {
    $delete_invoice_id = $_POST['delete_invoice_id'];
    
    // Bắt đầu giao dịch
    $conn->begin_transaction();

    try {
        // Xóa hóa đơn từ bảng invoices
        $delete_sql = "DELETE FROM invoices WHERE id = ?";
        $stmt = $conn->prepare($delete_sql);
        if (!$stmt) {
            throw new Exception("Lỗi khi chuẩn bị câu lệnh xóa invoices: " . $conn->error);
        }
        $stmt->bind_param('i', $delete_invoice_id);
        if (!$stmt->execute()) {
            throw new Exception("Lỗi khi thực hiện câu lệnh xóa invoices: " . $stmt->error);
        }

        // Commit giao dịch nếu mọi thứ thành công
        $conn->commit();
        $success = "Hóa đơn đã được xóa thành công!";
        
    } catch (Exception $e) {
        // Rollback giao dịch nếu có lỗi
        $conn->rollback();
        $error = $e->getMessage();
    }
}

// Lấy giá tiền mỗi đơn vị từ cơ sở dữ liệu
$sql = "SELECT key_value FROM settings WHERE key_name = 'water_price_per_unit'";
$result = $conn->query($sql);
$setting = $result->fetch_assoc();
$water_price_per_unit = $setting ? (int)$setting['key_value'] : 10000; // Giá mặc định nếu không có trong DB

// Pagination and search logic
$invoices_per_page = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $invoices_per_page;

$search_name = isset($_GET['search_name']) ? $conn->real_escape_string($_GET['search_name']) : '';
$search_status = isset($_GET['search_status']) ? $conn->real_escape_string($_GET['search_status']) : '';

// Tạo điều kiện tìm kiếm
$search_conditions = [];
if ($search_name) {
    $search_conditions[] = "users.name LIKE '%$search_name%'";
}
if ($search_status) {
    $search_conditions[] = "invoices.status = '$search_status'";
}
$search_condition = implode(' AND ', $search_conditions);
if ($search_condition) {
    $search_condition = "WHERE $search_condition";
}

// Tổng số hóa đơn phù hợp với điều kiện tìm kiếm
$sql = "SELECT COUNT(*) AS total FROM invoices JOIN users ON invoices.id_user = users.id $search_condition";
$result = $conn->query($sql);
$total_invoices = $result->fetch_assoc()['total'];
$total_pages = ceil($total_invoices / $invoices_per_page);

// Truy vấn để lấy thông tin hóa đơn và thông tin người dùng từ bảng invoices và users
$sql = "
    SELECT invoices.id, invoices.total_amount, invoices.status, users.name, users.email, users.address, MAX(water.date) AS water_date
    FROM invoices 
    JOIN users ON invoices.id_user = users.id
    LEFT JOIN water ON invoices.id_user = water.id_user
    $search_condition
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
        <!-- Hiển thị thông báo thành công và lỗi -->
        <?php if (isset($success)) echo "<p style='color: green;'>$success</p>"; ?>
        <?php if (isset($error)) echo "<p style='color: red;'>$error</p>"; ?>

        <!-- Form tìm kiếm -->
        <form method="get" action="manage_invoices.php">
            <label for="search_name">Tên người dùng:</label>
            <input type="text" name="search_name" value="<?php echo htmlspecialchars($search_name); ?>">
            <label for="search_status">Trạng thái:</label>
            <select name="search_status">
                <option value="">-- Chọn trạng thái --</option>
                <option value="paid" <?php if ($search_status == 'paid') echo 'selected'; ?>>Đã thanh toán</option>
                <option value="unpaid" <?php if ($search_status == 'unpaid') echo 'selected'; ?>>Chưa thanh toán</option>
            </select>
            <input type="submit" value="Tìm kiếm">
        </form>

        <!-- Bảng hiển thị hóa đơn hoặc thông báo nếu không có kết quả -->
        <?php if (count($invoices) > 0): ?>
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

            <!-- Phân trang -->
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>&search_name=<?php echo urlencode($search_name); ?>&search_status=<?php echo urlencode($search_status); ?>">Trang trước</a>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>&search_name=<?php echo urlencode($search_name); ?>&search_status=<?php echo urlencode($search_status); ?>" class="<?php if ($i == $page) echo 'active'; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?>&search_name=<?php echo urlencode($search_name); ?>&search_status=<?php echo urlencode($search_status); ?>">Trang sau</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <p class="error">Không tìm thấy thông tin phù hợp</p>
        <?php endif; ?>
        <p><a href="dashboard.php">Quay lại Dashboard</a></p>
    </div>
</body>
</html>
