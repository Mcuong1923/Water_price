<?php
use PHPUnit\Framework\TestCase;

require_once 'update_water_price_functions.php'; // Bao gồm các hàm xử lý cập nhật giá tiền nước

class UpdateWaterPriceFunctionsTest extends TestCase {
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
        $resultMock = $this->createMock(mysqli_result::class);

        $resultMock->method('fetch_assoc')->willReturn(['key_value' => '15000']);
        $this->conn->method('query')->willReturn($resultMock);

        $price_per_unit = getWaterPricePerUnit($this->conn);
        $this->assertEquals(15000, $price_per_unit);
    }

    public function testGetWaterPricePerUnitDefault() {
        $resultMock = $this->createMock(mysqli_result::class);

        $resultMock->method('fetch_assoc')->willReturn(null);
        $this->conn->method('query')->willReturn($resultMock);

        $price_per_unit = getWaterPricePerUnit($this->conn);
        $this->assertEquals(10000, $price_per_unit); // Giá mặc định
    }

    public function testUpdateWaterPriceSuccess() {
        $stmtMock = $this->createMock(mysqli_stmt::class);
        $stmtMock->method('execute')->willReturn(true);
        $this->conn->method('prepare')->willReturn($stmtMock);

        $new_price = 20000;
        $updated_price = updateWaterPrice($this->conn, $new_price);
        $this->assertEquals($new_price, $updated_price);
    }

    public function testUpdateWaterPriceNegative() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Giá tiền mỗi đơn vị không thể âm. Vui lòng nhập lại!");

        updateWaterPrice($this->conn, -5000);
    }

    public function testUpdateWaterPriceFailure() {
        $stmtMock = $this->createMock(mysqli_stmt::class);
        $stmtMock->method('execute')->willReturn(false);
        $this->conn->method('prepare')->willReturn($stmtMock);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Có lỗi xảy ra khi cập nhật giá tiền mỗi đơn vị!");

        updateWaterPrice($this->conn, 20000);
    }
}
?>
