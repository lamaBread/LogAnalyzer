<?php
// Include functions file
require_once '__functions.php';

// Set header for JSON response
header('Content-Type: application/json');

// Get the log file path from POST data
$logFilePath = $_POST['logFilePath'] ?? '';
if (empty($logFilePath)) {
    echo json_encode(['error' => 'Log file path is required']);
    exit;
}

// Read the log file
$logArray = readLogFileToArray($logFilePath);
if (empty($logArray)) {
    echo json_encode(['error' => 'No logs found or unable to read log file']);
    exit;
}

// Get current time
$currentTime = time();
$twentyFourHoursAgo = $currentTime - (24 * 60 * 60);

// Filter logs within the last 24 hours
$recentLogs = [];
foreach ($logArray as $log) {
    // Extract timestamp from log (format: [Day Month Date HH:MM:SS.microseconds Year])
    if (preg_match('/\[(.*?)\]/', $log, $matches)) {
        $timestampStr = $matches[1];
        // Remove microseconds for proper parsing
        $timestampStr = preg_replace('/(\d{2}:\d{2}:\d{2})\.[\d]+/', '$1', $timestampStr);
        // Parse the timestamp
        $timestamp = strtotime($timestampStr);
        
        if ($timestamp && $timestamp >= $twentyFourHoursAgo) {
            $recentLogs[] = [
                'timestamp' => $timestamp,
                'log' => $log
            ];
        }
    }
}

// Group logs by minute
$logsByMinute = [];
foreach ($recentLogs as $log) {
    $minute = date('Y-m-d H:i', $log['timestamp']);
    if (!isset($logsByMinute[$minute])) {
        $logsByMinute[$minute] = 0;
    }
    $logsByMinute[$minute]++;
}

// Create a complete set of minutes for the last 24 hours
$completeMinutes = [];
for ($i = 0; $i < 24 * 60; $i++) {
    $minuteTime = $currentTime - ($i * 60);
    $minute = date('Y-m-d H:i', $minuteTime);
    $completeMinutes[$minute] = $logsByMinute[$minute] ?? 0;
}

// Sort by time (oldest first)
ksort($completeMinutes);

// Format for JSON response
$result = [
    'timestamps' => array_keys($completeMinutes),
    'counts' => array_values($completeMinutes),
    'total' => count($recentLogs)
];

// Return JSON response
echo json_encode($result);
?>
