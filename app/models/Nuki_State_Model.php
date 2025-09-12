<?php
require_once __DIR__ . '/../../config/config.php';

class SmartLockModel {
    private mysqli $conn;

    public function __construct() {
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($this->conn->connect_error) {
            die('DB connection failed: ' . $this->conn->connect_error);
        }
    }

    // 🔁 Clear DB (only if needed)
    public function clearDatabase(): void {
        $this->conn->query('SET FOREIGN_KEY_CHECKS=0');
        $this->conn->query('TRUNCATE TABLE smartlock_state_logs');
        $this->conn->query('TRUNCATE TABLE smartlocks');
        $this->conn->query('SET FOREIGN_KEY_CHECKS=1');
    }

    public function insertSmartlock(int $id, string $name, string $category = 'General'): void {
        $sql = '
            INSERT INTO smartlocks (id, name, category)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE
              name = VALUES(name),
              category = VALUES(category)';
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('iss', $id, $name, $category);
        $stmt->execute();
    }

    public function getLastStateLog(int $id): ?array {
        $stmt = $this->conn->prepare(
            'SELECT * FROM smartlock_state_logs
             WHERE smartlock_id = ?
             ORDER BY start_time DESC
             LIMIT 1'
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() ?: null;
    }

    public function insertStateLog(int $id, int $state): void {
        $stmt = $this->conn->prepare(
            'INSERT INTO smartlock_state_logs (smartlock_id, state, start_time)
             VALUES (?, ?, NOW())'
        );
        $stmt->bind_param('ii', $id, $state);
        $stmt->execute();
    }

    public function updateStateLogEndTime(int $logId): void {
        $stmt = $this->conn->prepare(
            'UPDATE smartlock_state_logs
               SET end_time = NOW(),
                   duration = TIMESTAMPDIFF(SECOND, start_time, NOW())
             WHERE id = ?'
        );
        $stmt->bind_param('i', $logId);
        $stmt->execute();
    }

    public function autoCloseOngoingLogs(): void {
        $this->conn->query(
            'UPDATE smartlock_state_logs
               SET end_time = NOW(),
                   duration = TIMESTAMPDIFF(SECOND,start_time,NOW())
             WHERE end_time IS NULL
               AND TIMESTAMPDIFF(HOUR,start_time,NOW()) > 24'
        );
    }

    // ✅ NEW: Unified log fetcher for charts and tables
    public function getSmartlockHistory(): array {
        $res = $this->conn->query(
            'SELECT l.*, s.name, s.category
             FROM smartlock_state_logs l
             JOIN smartlocks s ON s.id = l.smartlock_id
             ORDER BY l.start_time DESC'
        );
        return $res->fetch_all(MYSQLI_ASSOC);
    }

    public function __destruct() {
        $this->conn->close();
    }
}
