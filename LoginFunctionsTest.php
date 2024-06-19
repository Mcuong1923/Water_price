<?php
use PHPUnit\Framework\TestCase;

require_once 'login_functions.php'; // Bao gồm các hàm xử lý đăng nhập

class LoginFunctionsTest extends TestCase {
    private $conn;

    protected function setUp(): void {
        // Mock đối tượng database connection
        $this->conn = $this->createMock(mysqli::class);
    }

    public function testGetUserByEmailExists() {
        $stmtMock = $this->createMock(mysqli_stmt::class);
        $resultMock = $this->createMock(mysqli_result::class);

        $resultMock->method('fetch_assoc')->willReturn([
            'id' => 1,
            'email' => 'john@example.com',
            'password' => password_hash('password123', PASSWORD_BCRYPT),
            'role' => 'user',
            'name' => 'John Doe'
        ]);

        $stmtMock->method('execute')->willReturn(true);
        $stmtMock->method('get_result')->willReturn($resultMock);

        $this->conn->method('prepare')->willReturn($stmtMock);

        $user = getUserByEmail($this->conn, 'john@example.com');
        $this->assertNotNull($user);
        $this->assertEquals('john@example.com', $user['email']);
    }

    public function testGetUserByEmailNotExists() {
        $stmtMock = $this->createMock(mysqli_stmt::class);
        $resultMock = $this->createMock(mysqli_result::class);

        $resultMock->method('fetch_assoc')->willReturn(false);

        $stmtMock->method('execute')->willReturn(true);
        $stmtMock->method('get_result')->willReturn($resultMock);

        $this->conn->method('prepare')->willReturn($stmtMock);

        $user = getUserByEmail($this->conn, 'nonexistent@example.com');
        $this->assertNull($user);
    }

    public function testVerifyPasswordSuccess() {
        $hashedPassword = password_hash('password123', PASSWORD_BCRYPT);
        $this->assertTrue(verifyPassword('password123', $hashedPassword));
    }

    public function testVerifyPasswordFailure() {
        $hashedPassword = password_hash('password123', PASSWORD_BCRYPT);
        $this->assertFalse(verifyPassword('wrongpassword', $hashedPassword));
    }

    public function testLoginUserSuccess() {
        $stmtMock = $this->createMock(mysqli_stmt::class);
        $resultMock = $this->createMock(mysqli_result::class);

        $resultMock->method('fetch_assoc')->willReturn([
            'id' => 1,
            'email' => 'john@example.com',
            'password' => password_hash('password123', PASSWORD_BCRYPT),
            'role' => 'user',
            'name' => 'John Doe'
        ]);

        $stmtMock->method('execute')->willReturn(true);
        $stmtMock->method('get_result')->willReturn($resultMock);

        $this->conn->method('prepare')->willReturn($stmtMock);

        $result = loginUser($this->conn, 'john@example.com', 'password123');
        $this->assertEquals("Đăng nhập thành công.", $result);
        $this->assertArrayHasKey('user_id', $_SESSION);
        $this->assertEquals(1, $_SESSION['user_id']);
    }

    public function testLoginUserWrongPassword() {
        $stmtMock = $this->createMock(mysqli_stmt::class);
        $resultMock = $this->createMock(mysqli_result::class);

        $resultMock->method('fetch_assoc')->willReturn([
            'id' => 1,
            'email' => 'john@example.com',
            'password' => password_hash('password123', PASSWORD_BCRYPT),
            'role' => 'user',
            'name' => 'John Doe'
        ]);

        $stmtMock->method('execute')->willReturn(true);
        $stmtMock->method('get_result')->willReturn($resultMock);

        $this->conn->method('prepare')->willReturn($stmtMock);

        $result = loginUser($this->conn, 'john@example.com', 'wrongpassword');
        $this->assertEquals("Mật khẩu không đúng. Vui lòng thử lại.", $result);
    }

    public function testLoginUserEmailNotExist() {
        $stmtMock = $this->createMock(mysqli_stmt::class);
        $resultMock = $this->createMock(mysqli_result::class);

        $resultMock->method('fetch_assoc')->willReturn(false);

        $stmtMock->method('execute')->willReturn(true);
        $stmtMock->method('get_result')->willReturn($resultMock);

        $this->conn->method('prepare')->willReturn($stmtMock);

        $result = loginUser($this->conn, 'nonexistent@example.com', 'password123');
        $this->assertEquals("Email không tồn tại trong hệ thống.", $result);
    }
}
?>
