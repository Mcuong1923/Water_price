<?php
use PHPUnit\Framework\TestCase;

require_once 'water_invoice_functions.php'; // Bao gồm các hàm xử lý hóa đơn nước

class WaterInvoiceFunctionsTest extends TestCase {
    private $conn;

    protected function setUp(): void {
        // Mock đối tượng database connection
        $this->conn = $this->createMock(mysqli::class);
        $_SESSION['role'] = 'user'; // Thiết lập session cho quyền user
        $_SESSION['user_id'] = 1; // Thiết lập user_id cho test
    }

    protected function tearDown(): void {
        unset($_SESSION['role']);
        unset($_SESSION['user_id']);
    }

    public function testCheckUserAccess() {
        // Test trường hợp không phải user
        $_SESSION['role'] = 'admin';
        $this->expectException(Exception::class);
        checkUserAccess();
    }

    public function testGetUnpaidInvoices() {
        $resultMock = $this->createMock(mysqli_result::class);

        $resultMock->method('fetch_all')->willReturn([
            ['id' => 1, 'total_amount' => 150000, 'created_at' => '2024-06-01', 'date' => '2024-05-30']
        ]);

        $this->conn->method('prepare')->willReturnSelf();
        $this->conn->method('bind_param')->willReturnSelf();
        $this->conn->method('execute')->willReturn(true);
        $this->conn->method('get_result')->willReturn($resultMock);

        $invoices = getUnpaidInvoices($this->conn, 1);
        $this->assertCount(1, $invoices);
        $this->assertEquals(1, $invoices[0]['id']);
    }

    public function testPayInvoiceSuccess() {
        $stmtMock = $this->createMock(mysqli_stmt::class);
        $stmtMock->method('execute')->willReturn(true);
        $this->conn->method('prepare')->willReturn($stmtMock);

        $result = payInvoice($this->conn, 1);
        $this->assertEquals("Hóa đơn đã được thanh toán thành công.", $result);
    }

    public function testPayInvoiceFailure() {
        $stmtMock = $this->createMock(mysqli_stmt::class);
        $stmtMock->method('execute')->willReturn(false);
        $this->conn->method('prepare')->willReturn($stmtMock);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Có lỗi xảy ra khi thanh toán hóa đơn!");

        payInvoice($this->conn, 1);
    }
}
?>
