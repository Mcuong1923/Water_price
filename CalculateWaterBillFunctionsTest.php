<?php
use PHPUnit\Framework\TestCase;

require 'calculate_water_bill_functions.php'; // Bao gồm các hàm xử lý tính toán hóa đơn

class CalculateWaterBillFunctionsTest extends TestCase {
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

    public function testGetWaterPricePerUnit() {
        $stmtMock = $this->createMock(mysqli_stmt::class);
        $resultMock = $this->createMock(mysqli_result::class);

        $resultMock->method('fetch_assoc')->willReturn(['key_value' => '15000']);
        $stmtMock->method('execute')->willReturn(true);
        $stmtMock->method('get_result')->willReturn($resultMock);

        $this->conn->method('query')->willReturn($resultMock);

        $price_per_unit = getWaterPricePerUnit($this->conn);
        $this->assertEquals(15000, $price_per_unit);
    }

    public function testGetUsersAndWaterData() {
        $stmtMock = $this->createMock(mysqli_stmt::class);
        $resultMock = $this->createMock(mysqli_result::class);

        $resultMock->method('fetch_all')->willReturn([
            ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com', 'address' => '123 Main St', 'number_water' => 10, 'date' => '2024-06-19']
        ]);

        $stmtMock->method('execute')->willReturn(true);
        $stmtMock->method('get_result')->willReturn($resultMock);

        $this->conn->method('query')->willReturn($resultMock);

        $users = getUsersAndWaterData($this->conn);
        $this->assertCount(1, $users);
        $this->assertEquals('John Doe', $users[0]['name']);
    }

    public function testCalculateTotalAmount() {
        $total_amount = calculateTotalAmount(10, 15000);
        $this->assertEquals(150000, $total_amount);
    }

    public function testCreateInvoiceAndRemoveWaterDataSuccess() {
        $this->conn->expects($this->once())->method('begin_transaction');
        $this->conn->expects($this->once())->method('commit');

        $stmtMock = $this->createMock(mysqli_stmt::class);
        $stmtMock->method('execute')->willReturn(true);
        $this->conn->method('prepare')->willReturn($stmtMock);

        $user = ['id' => 1, 'number_water' => 10, 'date' => '2024-06-19'];
        createInvoiceAndRemoveWaterData($this->conn, $user, 150000);
    }

    public function testCreateInvoiceAndRemoveWaterDataFailure() {
        $this->conn->expects($this->once())->method('begin_transaction');
        $this->conn->expects($this->once())->method('rollback');

        $stmtMock = $this->createMock(mysqli_stmt::class);
        $stmtMock->method('execute')->willReturn(false);
        $this->conn->method('prepare')->willReturn($stmtMock);

        $user = ['id' => 1, 'number_water' => 10, 'date' => '2024-06-19'];
        $this->expectException(Exception::class);
        createInvoiceAndRemoveWaterData($this->conn, $user, 150000);
    }
}
?>
