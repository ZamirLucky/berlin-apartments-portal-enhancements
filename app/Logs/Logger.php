<?php
// logger.php - A simple standalone logger

/**
 * Logs a message to the given log file.
 * @param string $message  The message to log
 * @param string $logFile  The path to the log file
 * @param string $level    Log level: "INFO", "ERROR", "WARNING"
 */
function mySimpleLog($message, $logFile = __DIR__ . "/../logs/app_log.txt", $level = "INFO") {
    // Format: [YYYY-mm-dd HH:MM:SS] [LEVEL] message
    $date = date("Y-m-d H:i:s");
    $logLine = "[$date] [$level] $message" . PHP_EOL;

    // Append the line
    file_put_contents($logFile, $logLine, FILE_APPEND);
}
