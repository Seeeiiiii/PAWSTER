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
            'email'      => $data['email'],
            'role'       => $data['role']
        ];

        // Cache seller status in session at login time so navbar doesn't need to re-query every page load.
        // A seller is someone with a 'verified' row in tblsellerstatus.
        $is_seller = false;
        $role = $data['role'] ?? 'user';

        if ($role !== 'admin') {
            $stmt = $this->conn->prepare("
                SELECT 1 FROM tblsellerstatus
                WHERE userid = ? AND status = 'verified'
                LIMIT 1
            ");
            $stmt->bind_param('i', $data['userid']);
            $stmt->execute();
            $stmt->store_result();
            $is_seller = $stmt->num_rows > 0;
            $stmt->close();
        }

        $_SESSION['is_seller'] = $is_seller;

        // Default navbar mode: sellers start in 'user' mode; they can toggle to 'seller' mode.
        $_SESSION['navbar_mode'] = 'user';

        setcookie('user_login', session_id(), time() + (86400 * 30), '/', '', false, true);
    }
}