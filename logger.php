<?php
class Logger {
    /**
    * Logs a message to a log file with detailed context in JSON format.
    *
    * @param string $message The primary message to log
    * @param array $context Additional details to log
    * @return void
    */
    public static function log($message, $context = []) {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'requestId' => isset($context['requestId']) ? $context['requestId'] : null,
            'IP' => isset($context['IP']) ? $context['IP'] : null,
            'message' => $message,
            'riskAssessment' => isset($context['Risk Assessment']) ? $context['Risk Assessment'] : null,
        ];
        $logEntryJson = json_encode($logEntry, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        file_put_contents("bl.log", $logEntryJson . "\n", FILE_APPEND | LOCK_EX);
    }
}
?>