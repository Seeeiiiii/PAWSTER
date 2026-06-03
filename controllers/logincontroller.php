<?php
include_once __DIR__ . '/../config/app.php';

class LoginController
{
    public $conn;

    public function __construct()
    {
        $db = new DatabaseConnection();
        $this->conn = $db->conn;
    }

    public function isuserLogin(string $email, string $password): bool
    {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $data = $result->fetch_assoc();

            if (password_verify($password, $data['password'])) {
                $this->userAuthentication($data);
                return true;
            }
        }
        return false;
    }

    private function userAuthentication(array $data): void
    {
        $_SESSION['authenticated'] = true;
        $_SESSION['auth_user'] = [
            'userid'     => $data['userid'], 
            'first_name' => $data['first_name'],
            'last_name'  => $data['last_name'],
            'email'      => $data['email']
        ];

        setcookie('user_login', session_id(), time() + (86400 * 30), '/', '', false, true);
    }
}