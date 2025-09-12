<?php
require_once '../../config/config.php';

class LoginModel
{
    private $db;

    // Constructor to establish database connection
    public function __construct()
    {
        $this->db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($this->db->connect_error) {
            die('Connection error: ' . $this->db->connect_error);
        }
    }

    // Register a new user with a hashed password
    public function registerUser($email, $password)
    {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $this->db->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
        if (!$stmt) {
            return ['success' => false, 'error' => 'Error preparing the query: ' . $this->db->error];
        }

        $stmt->bind_param("ss", $email, $hashedPassword);
        if ($stmt->execute()) {
            return ['success' => true];
        }

        return ['success' => false, 'error' => 'Error executing the query: ' . $stmt->error];
    }

    // Get user by email
    public function getUserByEmail($email)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        if (!$stmt) {
            echo "Error preparing the query: " . $this->db->error;
            return null;
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        } else {
            echo "Error: User not found.";
            return null;
        }
    }

    // Destructor to close the database connection
    public function __destruct()
    {
        $this->db->close();
    }
}
