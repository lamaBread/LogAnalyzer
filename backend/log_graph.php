<?php
// Include functions file
require_once '__functions.php';

// Set header for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Allow cross-origin requests

// Set timezone to ensure consistency
date_default_timezone_set('UTC'); // Change to match your logs' timezone

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
    // Access log format: [DD/Mon/YYYY:HH:MM:SS +ZZZZ]
    if (preg_match('/\[(\d{1,2})\/([A-Za-z]{3})\/(\d{4}):(\d{2}:\d{2}:\d{2}) ([+-]\d{4})\]/', $log, $matches)) {
        $date = $matches[1];
        $month = $matches[2];
        $year = $matches[3];
        $time = $matches[4];
        $timezone = $matches[5];
        
        // Construct timestamp string
        $timestampStr = "$date $month $year $time $timezone";
        // Parse the timestamp
        $timestamp = strtotime($timestampStr);
        
        if ($timestamp && $timestamp >= $twentyFourHoursAgo && $timestamp <= $currentTime) {
            $recentLogs[] = [
                'timestamp' => $timestamp,
                'log' => $log
            ];
        }
    }
}

// Group logs by 10-minute intervals
$logsByInterval = [];
foreach ($recentLogs as $log) {
    // Get the minute and round down to the nearest 10-minute interval
    $minute = (int)date('i', $log['timestamp']);
    $tenMinInterval = floor($minute / 10) * 10;
    // Format to ensure two digits (e.g., '00', '10', '20', etc.)
    $tenMinIntervalFormatted = sprintf('%02d', $tenMinInterval);
    
    // Create interval key in format: YYYY-MM-DD HH:MM where MM is the 10-minute interval
    $intervalKey = date('Y-m-d H:', $log['timestamp']) . $tenMinIntervalFormatted;
    
    if (!isset($logsByInterval[$intervalKey])) {
        $logsByInterval[$intervalKey] = 0;
    }
    $logsByInterval[$intervalKey]++;
}

// Create a complete set of 10-minute intervals for the last 24 hours
$completeIntervals = [];
for ($i = 0; $i < 24 * 6; $i++) { // 6 ten-minute intervals per hour
    $intervalTime = $currentTime - ($i * 10 * 60); // 10 minutes in seconds
    
    // Get the minute and round down to the nearest 10-minute interval
    $minute = (int)date('i', $intervalTime);
    $tenMinInterval = floor($minute / 10) * 10;
    $tenMinIntervalFormatted = sprintf('%02d', $tenMinInterval);
    
    // Create interval key
    $intervalKey = date('Y-m-d H:', $intervalTime) . $tenMinIntervalFormatted;
    
    $completeIntervals[$intervalKey] = isset($logsByInterval[$intervalKey]) ? $logsByInterval[$intervalKey] : 0;
}

// Sort by time (oldest first)
ksort($completeIntervals);

// Format data for the chart
$chartData = [];
foreach ($completeIntervals as $time => $count) {
    $chartData[] = [
        'time' => substr($time, 11, 5), // Extract only HH:MM
        'count' => $count
    ];
}

// Format for JSON response
$result = [
    'data' => $chartData,
    'total' => count($recentLogs)
];

// Return JSON response
echo json_encode($result);
?>
