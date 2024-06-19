<?php
use PHPUnit\Framework\TestCase;

require_once 'manage_invoices_functions.php'; // Bao gồm các hàm xử lý quản lý hóa đơn

class ManageInvoicesFunctionsTest extends TestCase {
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

    public function testDeleteInvoiceSuccess() {
        $this->conn->expects($this->once())->method('begin_transaction');
        $this->conn->expects($this->once())->method('commit');

        $stmtMock = $this->createMock(mysqli_stmt::class);
        $stmtMock->method('execute')->willReturn(true);
        $this->conn->method('prepare')->willReturn($stmtMock);

        $result = deleteInvoice($this->conn, 1);
        $this->assertTrue($result);
    }

    public function testDeleteInvoiceFailure() {
        $this->conn->expects($this->once())->method('begin_transaction');
        $this->conn->expects($this->once())->method('rollback');

        $stmtMock = $this->createMock(mysqli_stmt::class);
        $stmtMock->method('execute')->willReturn(false);
        $this->conn->method('prepare')->willReturn($stmtMock);

        $this->expectException(Exception::class);
        deleteInvoice($this->conn, 1);
    }

    public function testGetWaterPricePerUnit() {
        $resultMock = $this->createMock(mysqli_result::class);

        $resultMock->method('fetch_assoc')->willReturn(['key_value' => '15000']);

        $this->conn->method('query')->willReturn($resultMock);

        $price_per_unit = getWaterPricePerUnit($this->conn);
        $this->assertEquals(15000, $price_per_unit);
    }

    public function testSearchInvoices() {
        $resultMock = $this->createMock(mysqli_result::class);

        $resultMock->method('fetch_all')->willReturn([
            ['id' => 1, 'total_amount' => 150000, 'status' => 'unpaid', 'name' => 'John Doe', 'email' => 'john@example.com', 'address' => '123 Main St', 'water_date' => '2024-06-19']
        ]);

        $this->conn->method('query')->willReturn($resultMock);

        $invoices = searchInvoices($this->conn, 'John', 'unpaid', 5, 0);
        $this->assertCount(1, $invoices);
        $this->assertEquals('John Doe', $invoices[0]['name']);
    }

    public function testCountInvoices() {
        $resultMock = $this->createMock(mysqli_result::class);

        $resultMock->method('fetch_assoc')->willReturn(['total' => 10]);

        $this->conn->method('query')->willReturn($resultMock);

        $total = countInvoices($this->conn, 'John', 'unpaid');
        $this->assertEquals(10, $total);
    }
}
?>

