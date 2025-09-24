<?php
// FILE: controllers/Nuki_State_Controller.php

require_once __DIR__ . '/../models/Nuki_State_Model.php';
require_once __DIR__ . '/Mailer.php';


class Nuki_StateController {
    private $model;
    private $appLogFile;
    private $lockFile;

    public function __construct() {
        $this->model = new SmartLockModel();
        $this->appLogFile = __DIR__ . '/../Logs/app_log.txt';
        //$this->lockFile = __DIR__ . '/../Logs/nuki_cron.lock';
    }

    /**
     * Private method to append one line safely to the app log.
     */
    private function logToAppLog($message): void {
        file_put_contents($this->appLogFile, date('c') . ' - ' . $message . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    /**
     * Fetch smartlocks from all API groups and log state changes.
     * Optionally clears the database before fetching.
     */
    public function fetchAndLogSmartlocks(bool $clearDB = false): void {
        if ($clearDB) {
            $this->model->clearDatabase();
        }

        foreach (API_GROUPS as $group => $token) {
            $this->fetchGroupSmartlocks($group, $token);
        }

        $this->model->autoCloseOngoingLogs();
    }

    /**
     * Internal function to fetch and store smartlock data for a group.
     */
    private function fetchGroupSmartlocks(string $group, string $token): void {
        $ch = curl_init(API_URL_NUKI_DEVICES);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer $token",
                "Accept: application/json"
            ]
        ]);

        $response = curl_exec($ch);
        $http     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err      = curl_error($ch);
        curl_close($ch);

        $response = $response ?? null;   // curl_exec($ch) result
        $http     = $http     ?? null;   // curl_getinfo($ch, CURLINFO_HTTP_CODE)
        $err      = $err      ?? null;   // curl_error($ch)

       /* Network errors */
        if ($response === false || $response === null) {
            //$this->logToAppLog("Nuki API cURL error: " . ($err ?? 'unknown'));
            throw new RuntimeException("Nuki API cURL error: " . ($err ?? 'unknown'));
        }

        $data = json_decode($response, true) ?: [];

        /* if not data an array of items thrown */
        if (!is_array($data)) {
            //$this->logToAppLog("Nuki API unexpected shape (HTTP {$http}): " . json_encode($response));
            throw new RuntimeException(
                "Nuki API unexpected shape (HTTP {$http}). Saved to app/controllers/nuki_unexpected_shape.json"
            );
        }
     
        foreach ($data as $lock) {
            // Check for required keys before using the values
            if (!isset($lock['smartlockId'], $lock['name'], $lock['serverState'])) {
                if (is_array($lock)) {
                    //$this->logToAppLog('Missing keys in smartlock data: ' . json_encode($lock));
                    echo '<script>console.log("Array\n", ' . json_encode($lock) . ');</script>';
                } else {
                    // !not an array, stringify safely
                    echo '<script>console.log("!Array\n", ' . json_encode([
                        'php_debug_lock' => $lock,
                        'type' => gettype($lock)
                    ]) . ');</script>';
                    //$this->logToAppLog('Missing keys in smartlock data: ' . json_encode($lock));
                }
                continue;
            }

            $id    = $lock['smartlockId'];
            $name  = $lock['name'];
            $state = $lock['serverState']; // 0 = Online, 1 = Offline
            // $is_offline_now = ($state == 4);

            $this->model->insertSmartlock($id, $name, $group);

            $last = $this->model->getLastStateLog($id);
            if (!$last || $last['state'] != $state) {
                if ($last) {
                    $this->model->updateStateLogEndTime($last['id']);
                }
                $this->model->insertStateLog($id, $state);
            }
        }
    }

    /**
     * Returns the full or filtered smartlock history.
     *
     * @param bool $offlineOnlyMoreThanOneDay
     * @return array
     */
    public function getSmartlockHistory(bool $offlineOnlyMoreThanOneDay = false): array {
        return $this->model->getSmartlockHistory($offlineOnlyMoreThanOneDay);
    }

    public function CronPoll(): void {

        header('Content-Type: application/json');

        if (!isset($_GET['token']) || $_GET['token'] !== CRON_TOKEN) {
            http_response_code(403);
            echo json_encode(["error" => "Forbidden: Invalid token."]);
            exit;
        }

        // Single-run lock (prevent overlap)
        $lockPath = sys_get_temp_dir() . '/nuki.cron.lock';
        $lockFp = @fopen($lockPath, 'c');
        if (!$lockFp) {
            echo json_encode(['error' => 'lock_open_failed', 'path' => $lockPath]);
            return;
        }
        if (!flock($lockFp, LOCK_EX | LOCK_NB)) {
            echo json_encode(['skipped' => 'already_running']);
            fclose($lockFp);
            return;
        }

        $startTime = microtime(true);
        $sent = 0;
        $errors = 0;
        $rowsToNotify = [];


        try {
            $this->fetchAndLogSmartlocks(false);

            // Query current open offline rows that reached 10m and were not emailed yet
            $pdo = $this->getPdo(); 
            $sql = "
                SELECT sl.id AS state_log_id,
                    sl.smartlock_id,
                    sl.start_time AS offline_started_at,
                    s.name AS lock_name
                FROM smartlock_state_logs sl
                JOIN smartlocks s ON s.id = sl.smartlock_id
                WHERE sl.end_time IS NULL
                AND sl.state = 4
                AND sl.alert_sent_at IS NULL
                AND TIMESTAMPDIFF(MINUTE, sl.start_time, NOW()) >= :min
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([':min' => OFFLINE_THRESHOLD_MINUTES]);
            $rowsToNotify = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->logToAppLog('Rows to notify: ' . print_r($rowsToNotify, true));

            if (!empty($rowsToNotify)) {
                $mailer = new Mailer();

                foreach ($rowsToNotify as $row) {
                    // Prepare email payload
                    $lockName = $row['lock_name'] ?? 'Unknown Lock';
                    $offlineSince = $row['offline_started_at'] ?? 'Unknown Time';
                    $durationSec  = max(0, strtotime('now') - strtotime($offlineSince));
                    $durationHuman = $this->formatDuration($durationSec);
                    $subject = NUKI_OFFLINE_SUBJECT;
                    $body = str_replace(
                        ['{LockName}', '{FirstOfflineTime}', '{OfflineDuration}'],
                        [$lockName, $offlineSince, $durationHuman],
                        NUKI_OFFLINE_BODY
                    );
                    
                    // Send email
                    try {
                        $mailer->sendEmail(OPS_EMAIL_TO, $subject, $body);
                        $sent++;
                        // Mark this row as emailed
                        $updateSql = "UPDATE smartlock_state_logs SET alert_sent_at = NOW() WHERE id = :id";
                        $updateStmt = $pdo->prepare($updateSql);
                        $updateStmt->execute([':id' => $row['state_log_id']]);
                    } catch (Exception $e) {
                        $errors++;      
                        $this->logToAppLog("Email send error for lock {$lockName}: " . $e->getMessage());
                        echo json_encode(['error' => 'email_send_failed', 'lock' => $lockName, 'message' => $e->getMessage()]);
                    }
                }
            }
            $tookMs = (int) round((microtime(true) - $started) * 1000);
            echo json_encode([
                'at' => date('c'),
                'open_emails_sent' => $sent,
                'open_email_errors' => $errors,
                'took_ms' => $tookMs
            ]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => 'exception', 'message' => $e->getMessage()]);
            //$this->logToAppLog("CronPoll exception: " . $e->getMessage());
        } finally {
            flock($lockFp, LOCK_UN);
            fclose($lockFp);
        }

        $duration = microtime(true) - $startTime;
        echo json_encode([
            'status' => 'success',
            'duration_seconds' => $duration
        ]);
    }

    private function formatDuration(int $sec): string {
        $m = intdiv($sec, 60);
        $s = $sec % 60;
        if ($m < 60) return sprintf('%dm %02ds', $m, $s);
        $h = intdiv($m, 60);
        $m = $m % 60;
        return sprintf('%dh %02dm', $h, $m);
    }

    private function getPdo(): PDO {
        static $pdo = null;
        if ($pdo === null) {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        }
        return $pdo;
    }
}
