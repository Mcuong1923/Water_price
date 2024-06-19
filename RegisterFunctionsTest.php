<?php
use PHPUnit\Framework\TestCase;

require_once 'register_functions.php'; // Bao gồm các hàm xử lý đăng ký

class RegisterFunctionsTest extends TestCase {
    private $conn;

    protected function setUp(): void {
        // Mock đối tượng database connection
        $this->conn = $this->createMock(mysqli::class);
    }

    public function testValidatePasswordTooShort() {
        $result = validatePassword('123');
        $this->assertEquals("Mật khẩu phải có tối thiểu 6 ký tự.", $result);
    }

    public function testValidatePasswordValid() {
        $result = validatePassword('password123');
        $this->assertNull($result);
    }

    public function testCheckEmailExistsTrue() {
        $stmtMock = $this->createMock(mysqli_stmt::class);
        $resultMock = $this->createMock(mysqli_result::class);

        $resultMock->method('fetch_assoc')->willReturn(['email' => 'john@example.com']);

        $stmtMock->method('execute')->willReturn(true);
        $stmtMock->method('get_result')->willReturn($resultMock);

        $this->conn->method('prepare')->willReturn($stmtMock);

        $result = checkEmailExists($this->conn, 'john@example.com');
        $this->assertTrue($result);
    }

    public function testCheckEmailExistsFalse() {
        $stmtMock = $this->createMock(mysqli_stmt::class);
        $resultMock = $this->createMock(mysqli_result::class);

        $resultMock->method('fetch_assoc')->willReturn(false);

        $stmtMock->method('execute')->willReturn(true);
        $stmtMock->method('get_result')->willReturn($resultMock);

        $this->conn->method('prepare')->willReturn($stmtMock);

        $result = checkEmailExists($this->conn, 'john@example.com');
        $this->assertFalse($result);
    }

    public function testRegisterUserSuccess() {
        $stmtMock = $this->createMock(mysqli_stmt::class);
        $stmtMock->method('execute')->willReturn(true);
        $this->conn->method('prepare')->willReturn($stmtMock);

        $result = registerUser($this->conn, 'John Doe', 'john@example.com', 'password123', '123 Main St');
        $this->assertEquals("Đăng ký thành công.", $result);
    }

    public function testRegisterUserFailure() {
        $stmtMock = $this->createMock(mysqli_stmt::class);
        $stmtMock->method('execute')->willReturn(false);
        $this->conn->method('prepare')->willReturn($stmtMock);

        $result = registerUser($this->conn, 'John Doe', 'john@example.com', 'password123', '123 Main St');
        $this->assertEquals("Đăng ký không thành công. Vui lòng thử lại sau.", $result);
    }
}
?>
