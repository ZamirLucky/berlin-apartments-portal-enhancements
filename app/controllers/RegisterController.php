<?php
/**
 * RegisterController.php
 *
 * Handles the logic for registering a new user into the database.
 */

require_once '../models/UserModel.php'; // If you need a separate model. Otherwise, keep your DB logic here.

class RegisterController
{
    private $db;

    /**
     * Constructor
     *
     * Initializes the database connection and optional session.
     *
     * @throws Exception if the database connection fails.
     */
    public function __construct()
    {
        // Connect to the database
        $this->db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($this->db->connect_error) {
            throw new Exception('Connection error: ' . $this->db->connect_error);
        }

        // Start session if not already started (optional)
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * register
     *
     * Registers a new user after basic validation and password hashing.
     *
     * @param array $data Expected keys: 'email', 'name', 'password', 'confirmPassword'
     * @return bool True on success
     * @throws Exception on any validation or database error
     */
    public function register(array $data): bool
    {
        // 1. Check if passwords match
        if ($data['password'] !== $data['confirmPassword']) {
            throw new Exception("Passwords do not match.");
        }

        // 2. Check if the email already exists (optional, but recommended)
        $checkStmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        if (!$checkStmt) {
            throw new Exception("Preparation error (check email): " . $this->db->error);
        }
        $checkStmt->bind_param('s', $data['email']);
        $checkStmt->execute();
        $checkStmt->bind_result($count);
        $checkStmt->fetch();
        $checkStmt->close();

        if ($count > 0) {
            throw new Exception("This email is already in use.");
        }

        // 3. Hash the password for security
        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);

        // 4. Insert the new user into the database
        $stmt = $this->db->prepare("INSERT INTO users (email, name, password) VALUES (?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Preparation error (insert user): " . $this->db->error);
        }

        $stmt->bind_param('sss', $data['email'], $data['name'], $hashedPassword);
        if (!$stmt->execute()) {
            throw new Exception("Error during registration: " . $stmt->error);
        }
        $stmt->close();

        return true;
    }

    /**
     * Destructor
     *
     * Closes the database connection when this controller is destroyed.
     */
    public function __destruct()
    {
        if ($this->db) {
            $this->db->close();
        }
    }
}
