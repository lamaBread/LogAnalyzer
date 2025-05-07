<?php
// Evaluate raw logs for suspicious activities and send email alerts
include '__functions.php';
include '__regex_match_function.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Process POST data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the log entries from the POST data
    $logs = isset($_POST['logs']) ? $_POST['logs'] : [];
    
    if (empty($logs)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'No logs provided']);
        exit();
    }
    
    // Get attack regex patterns
    $patterns = readAttackRegexPatterns();
    
    // Evaluate each log using the regex functions
    $results = [];
    
    foreach ($logs as $log) {
        // Skip empty lines or headers
        if (empty(trim($log)) || strpos($log, '---') === 0) {
            continue;
        }
        
        // Process with isLogSuspiciousFullScan from __regex_match_function.php
        $attackDetails = isLogSuspiciousFullScan($log, $patterns);
        $isSuspicious = !empty($attackDetails);
        
        // Record the evaluation result
        $results[] = [
            'log' => $log,
            'is_suspicious' => $isSuspicious,
            'attack_details' => $attackDetails
        ];
    }
    
    // Send response
    echo json_encode([
        'status' => 'success',
        'evaluated' => count($results),
        'results' => $results
    ]);
    
    // Optional: Log the evaluation results to a file
    $log_file = __DIR__ . '/LOG/evaluation_results.log';
    file_put_contents(
        $log_file, 
        date('Y-m-d H:i:s') . ' - Evaluated ' . count($results) . " logs\n", 
        FILE_APPEND
    );
    
    exit();
}

// Handle invalid request method
http_response_code(405);
echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
exit();
?>