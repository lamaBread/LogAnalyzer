<?php
// Include the file with regex matching functions
require_once '../__regex_match_function.php';

// Function to parse timestamp from a log entry
function parseTimestampFromLog($logEntry) {
    // Common timestamp patterns in logs
    $patterns = [
        // Apache/Nginx access log format: [day/month/year:hour:minute:second zone]
        '/\[(\d{2}\/\w{3}\/\d{4}:\d{2}:\d{2}:\d{2} [\+\-]\d{4})\]/',
        // Standard syslog format: Month Day HH:MM:SS
        '/(\w{3}\s+\d{1,2}\s+\d{2}:\d{2}:\d{2})/',
        // ISO 8601 format: YYYY-MM-DD HH:MM:SS
        '/(\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})/',
        // Error log format with timestamp in brackets
        '/\[([^\]]+)\]/'
    ];

    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $logEntry, $matches)) {
            return $matches[1];
        }
    }

    // Return current timestamp if no timestamp found in log
    return date('Y-m-d H:i:s');
}

// Function to setup the SQLite database
function setupDatabase() {
    // $dbFile = __DIR__ . '/logs.db';
    $dbFile = './logs.db'; // Adjust the path as needed
    if (!file_exists($dbFile)) {
        touch($dbFile);
    }
    $db = new SQLite3($dbFile);
    
    // Create logs table if it doesn't exist
    $db->exec('
        CREATE TABLE IF NOT EXISTS logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            log_text TEXT NOT NULL,
            timestamp TEXT NOT NULL,
            detected_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            is_suspicious INTEGER DEFAULT 0
        )
    ');
    
    // Create attack_detections table to store detected patterns
    $db->exec('
        CREATE TABLE IF NOT EXISTS attack_detections (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            log_id INTEGER NOT NULL,
            attack_type TEXT NOT NULL,
            attack_details TEXT,
            pattern TEXT,
            FOREIGN KEY (log_id) REFERENCES logs(id)
        )
    ');
    
    return $db;
}

// Main processing function
function processLogs($logs) {
    // Get attack patterns for evaluation
    $patterns = readAttackRegexPatterns();
    
    // Setup database
    $db = setupDatabase();
    
    // Start a transaction for better performance
    $db->exec('BEGIN TRANSACTION');
    
    $processedLogs = [];
    $totalProcessed = 0;
    $suspiciousCount = 0;
    
    foreach ($logs as $logEntry) {
        // Skip empty logs
        if (empty(trim($logEntry))) {
            continue;
        }
        
        // Parse timestamp from log
        $timestamp = parseTimestampFromLog($logEntry);
        
        // Evaluate log for suspicious patterns
        $detectedPatterns = isLogSuspiciousFullScan($logEntry, $patterns);
        
        // Mark as suspicious if patterns detected
        $isSuspicious = !empty($detectedPatterns) ? 1 : 0;
        
        // Prepare log data
        $logData = [
            'log' => $logEntry,
            'timestamp' => $timestamp,
            'detectedPatterns' => $detectedPatterns
        ];
        
        // Store in database
        $stmt = $db->prepare('
            INSERT INTO logs (log_text, timestamp, is_suspicious)
            VALUES (:log, :timestamp, :is_suspicious)
        ');
        
        $stmt->bindValue(':log', $logEntry, SQLITE3_TEXT);
        $stmt->bindValue(':timestamp', $timestamp, SQLITE3_TEXT);
        $stmt->bindValue(':is_suspicious', $isSuspicious, SQLITE3_INTEGER);
        $stmt->execute();
        
        $logId = $db->lastInsertRowID();
        
        // Store detected patterns if any
        if ($isSuspicious) {
            $suspiciousCount++;
            
            foreach ($detectedPatterns as $pattern) {
                $patternStmt = $db->prepare('
                    INSERT INTO attack_detections (log_id, attack_type, attack_details, pattern)
                    VALUES (:log_id, :attack_type, :attack_details, :pattern)
                ');
                
                $patternStmt->bindValue(':log_id', $logId, SQLITE3_INTEGER);
                $patternStmt->bindValue(':attack_type', $pattern['attackType'], SQLITE3_TEXT);
                $patternStmt->bindValue(':attack_details', $pattern['attackDetails'], SQLITE3_TEXT);
                $patternStmt->bindValue(':pattern', $pattern['pattern'], SQLITE3_TEXT);
                $patternStmt->execute();
            }
        }
        
        $processedLogs[] = $logData;
        $totalProcessed++;
    }
    
    // Commit the transaction
    $db->exec('COMMIT');
    $db->close();
    
    return [
        'success' => true,
        'total_processed' => $totalProcessed,
        'suspicious_count' => $suspiciousCount,
        'processed_logs' => $processedLogs
    ];
}

// Main execution
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if logs are present in the request
    if (isset($_POST['logs']) && is_array($_POST['logs'])) {
        $result = processLogs($_POST['logs']);
        
        // Return response as JSON
        header('Content-Type: application/json');
        echo json_encode($result);
    } else {
        // Invalid request
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['error' => 'No logs provided or invalid format']);
    }
} else {
    // Method not allowed
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['error' => 'Only POST requests are allowed']);
}
