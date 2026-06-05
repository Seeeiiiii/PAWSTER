<?php
include_once __DIR__ . '/../config/app.php';

class RegisterController
{
    public $conn;

    public function __construct()
    {
        $db = new DatabaseConnection();
        $this->conn = $db->conn;
    }

    public function registration(
        string $first_name,
        string $last_name,
        string $contact_number,
        string $email,
        string $password
    ): bool {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->conn->prepare(
            "INSERT INTO users (first_name, last_name, contact_number, email, password)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("sssss", $first_name, $last_name, $contact_number, $email, $hashed_password);
        return $stmt->execute();
    }

    public function isUserExists(string $email): bool
    {
        $stmt = $this->conn->prepare("SELECT email FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }
}