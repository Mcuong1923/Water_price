<?php
use PHPUnit\Framework\TestCase;

require_once 'update_water_functions.php'; // Bao gồm các hàm xử lý cập nhật số nước

class UpdateWaterFunctionsTest extends TestCase {
    private $conn;

    protected function setUp(): void {
        // Mock đối tượng database connection
        $this->conn = $this->createMock(mysqli::class);
        $_SESSION['role'] = 'admin'; // Thiết lập session cho quyền admin
    }

    protected function tearDown(): void {
        unset($_SESSION['role']);
    }

    public function testCheckAdminAccess() {
        // Test trường hợp không phải admin
        $_SESSION['role'] = 'user';
        $this->expectException(Exception::class);
        checkAdminAccess();
    }

    public function testGetUsers() {
        $resultMock = $this->createMock(mysqli_result::class);

        $resultMock->method('fetch_all')->willReturn([
            ['id' => 1, 'name' => 'John Doe', 'address' => '123 Main St']
        ]);

        $this->conn->method('query')->willReturn($resultMock);

        $users = getUsers($this->conn);
        $this->assertCount(1, $users);
        $this->assertEquals('John Doe', $users[0]['name']);
    }

    public function testUpdateWaterSuccess() {
        $stmtMock = $this->createMock(mysqli_stmt::class);
        $stmtMock->method('execute')->willReturn(true);
        $this->conn->method('prepare')->willReturn($stmtMock);

        $result = updateWater($this->conn, 1, 100, '2024-06-01');
        $this->assertEquals("Cập nhật thành công", $result);
    }

    public function testUpdateWaterNoUserSelected() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Vui lòng chọn người dùng.");

        updateWater($this->conn, '', 100, '2024-06-01');
    }

    public function testUpdateWaterNegativeNumber() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Số nước không thể là số âm.");

        updateWater($this->conn, 1, -100, '2024-06-01');
    }

    public function testUpdateWaterFutureDate() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Ngày không thể là ngày trong tương lai.");

        $future_date = date("Y-m-d", strtotime("+1 day"));
        updateWater($this->conn, 1, 100, $future_date);
    }

    public function testUpdateWaterFailure() {
        $stmtMock = $this->createMock(mysqli_stmt::class);
        $stmtMock->method('execute')->willReturn(false);
        $this->conn->method('prepare')->willReturn($stmtMock);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Cập nhật thất bại");

        updateWater($this->conn, 1, 100, '2024-06-01');
    }
}
?>
