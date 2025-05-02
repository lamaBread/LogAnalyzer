<?php
// API to return suspicion scores for each IP address
include '__functions.php';
include '__regex_match_function.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $filePath = $_POST['filePath'] ?? '';
        if ($filePath) {
            // Read log file to array
            $logArray = readLogFileToArray($filePath);
            
            // Group logs by IP address
            $ipGroupedLogs = groupLogsByIP($logArray);
            
            // Analyze logs and get suspicion scores
            $suspicionScores = analyzeLogsForAttacks($ipGroupedLogs);
            
            http_response_code(200);
            echo json_encode($suspicionScores);  // Return suspicion scores for each IP address
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'File path is required']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to analyze logs', 'message' => $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request method']);
}
?>
