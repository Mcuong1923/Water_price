<?php
use PHPUnit\Framework\TestCase;

require_once 'invoice_details_functions.php'; // Bao gồm các hàm xử lý chi tiết hóa đơn

class InvoiceDetailsFunctionsTest extends TestCase {
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

    public function testGetInvoiceDetails() {
        $stmtMock = $this->createMock(mysqli_stmt::class);
        $resultMock = $this->createMock(mysqli_result::class);

        $resultMock->method('fetch_assoc')->willReturn([
            'id' => 1,
            'total_amount' => 150000,
            'created_at' => '2024-06-19',
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'address' => '123 Main St'
        ]);

        $stmtMock->method('execute')->willReturn(true);
        $stmtMock->method('get_result')->willReturn($resultMock);

        $this->conn->method('prepare')->willReturn($stmtMock);

        $invoice = getInvoiceDetails($this->conn, 1);
        $this->assertNotNull($invoice);
        $this->assertEquals('john@example.com', $invoice['email']);
    }

    public function testHandleExport() {
        $this->expectOutputString("<script>alert('Xuất Hóa Đơn Thành Công');</script>");
        handleExport();
    }

    public function testHandleExportEmail() {
        $this->expectOutputString("<script>alert('Hóa đơn đã được gửi thành công qua email!');</script>");
        handleExportEmail();
    }
}
?>
