<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Include the file with regex matching functions
// require_once './../__regex_match_function.php';

$regexPath = __DIR__ . '/../__regex_match_function.php';
require_once $regexPath;

/**
 * Function to log errors to both error_log and to be returned to client
 * This function serves as a centralized logging mechanism
 */
function logError($message, $errorDetails = null) {
    $fullMessage = $message;
    if ($errorDetails) {
        $fullMessage .= ': ' . $errorDetails;
    }
    error_log($fullMessage);
    
    return [
        'success' => false,
        'error' => $message,
        'error_details' => $errorDetails
    ];
}

// Function to parse timestamp from a log entry 
function parseTimestampFromLog($logEntry) {
    // Common timestamp patterns in logs
    /*
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

    */

    return date('Y-m-d H:i:s');
}

// Function to ensure database file has proper permissions
function ensureDatabasePermissions($dbPath) {
    // Create directory if it doesn't exist
    $dbDir = dirname($dbPath);
    if (!file_exists($dbDir)) {
        mkdir($dbDir, 0777, true);
    }
    
    // Make directory writable
    if (!is_writable($dbDir)) {
        chmod($dbDir, 0777);
    }
    
    // If file exists but isn't writable, make it writable
    if (file_exists($dbPath) && !is_writable($dbPath)) {
        chmod($dbPath, 0666);
    }
    
    return is_writable($dbDir) && (!file_exists($dbPath) || is_writable($dbPath));
}

// New function to setup database
function setupDatabase() {
    // Try multiple paths in order of preference
    $possiblePaths = [
        __DIR__ . '/logs.db',                   // Current directory
        sys_get_temp_dir() . '/logs.db',        // System temp directory
        '/tmp/logs.db',                        // Linux temp directory
        '/var/tmp/logs.db'                     // Another Linux temp directory
    ];
    
    $db = null;
    $lastError = '';
    
    foreach ($possiblePaths as $dbPath) {
        try {
            // Try to create directory if it doesn't exist
            $dbDir = dirname($dbPath);
            if (!file_exists($dbDir)) {
                @mkdir($dbDir, 0777, true);
            }
            
            // Try to use this path
            $db = new SQLite3(
                $dbPath,
                SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE
            );
            
            // Test write permissions with a simple query
            if ($db->exec("CREATE TABLE IF NOT EXISTS test_table (id INTEGER)")) {
                // Success! We have a writable database
                error_log("Successfully connected to database at: " . $dbPath);
                break;
            } else {
                // This path didn't work, close and try next one
                $lastError = $db->lastErrorMsg();
                $db->close();
                $db = null;
            }
        } catch (Exception $e) {
            $lastError = $e->getMessage();
            // Continue to next path
        }
    }
    
    // If all paths failed, try in-memory database
    if (!$db) {
        try {
            error_log("All database paths failed. Last error: " . $lastError);
            error_log("Attempting to use in-memory SQLite database as last resort");
            $db = new SQLite3(':memory:');
            error_log("Successfully created in-memory database");
        } catch (Exception $e) {
            error_log("Failed to create in-memory database: " . $e->getMessage());
            return false;
        }
    }
    
    // Create logs table if it doesn't exist.
    $createLogsTableSQL = '
        CREATE TABLE IF NOT EXISTS logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            log_text TEXT NOT NULL,
            timestamp TEXT NOT NULL,
            detected_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            is_suspicious INTEGER DEFAULT 0
        )
    ';
    if (!$db->exec($createLogsTableSQL)) {
        $errorMsg = $db->lastErrorMsg();
        error_log("Failed to create logs table: " . $errorMsg);
        // We continue anyway - the in-memory database should work
    }
    
    // Create attack_detections table to store detected patterns
    $createAttackDetectionsTableSQL = '
        CREATE TABLE IF NOT EXISTS attack_detections (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            log_id INTEGER NOT NULL,
            attack_type TEXT NOT NULL,
            attack_details TEXT,
            pattern TEXT,
            FOREIGN KEY (log_id) REFERENCES logs(id)
        )
    ';
    if (!$db->exec($createAttackDetectionsTableSQL)) {
        $errorMsg = $db->lastErrorMsg();
        error_log("Failed to create attack_detections table: " . $errorMsg);
        // We continue anyway - the in-memory database should work
    }
        
    return $db;
}

// Main processing function
function processLogs($logs) {
    try {
        // Get attack patterns for evaluation
        try {
            $patterns = readAttackRegexPatterns();
            if (empty($patterns)) {
                error_log("Warning: No attack patterns loaded. Continuing with empty pattern set.");
                $patterns = [];
            }
        } catch (Exception $e) {
            error_log("Error loading attack patterns: " . $e->getMessage());
            $patterns = [];
        }
        
        // Setup database
        $db = setupDatabase();
        if (!$db) {
            // Try to use an in-memory database as a fallback
            try {
                error_log("Attempting to use in-memory SQLite database as fallback");
                $db = new SQLite3(':memory:');
                
                // Create our tables in memory
                $db->exec('
                    CREATE TABLE logs (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        log_text TEXT NOT NULL,
                        timestamp TEXT NOT NULL,
                        detected_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                        is_suspicious INTEGER DEFAULT 0
                    )
                ');
                
                $db->exec('
                    CREATE TABLE attack_detections (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        log_id INTEGER NOT NULL,
                        attack_type TEXT NOT NULL,
                        attack_details TEXT,
                        pattern TEXT,
                        FOREIGN KEY (log_id) REFERENCES logs(id)
                    )
                ');
                
                error_log("Successfully created in-memory database as fallback");
            } catch (Exception $e) {
                error_log("Failed to create in-memory fallback database: " . $e->getMessage());
                return [
                    'success' => false,
                    'error' => 'Failed to connect to database',
                    'error_details' => 'Database connection error: ' . $e->getMessage()
                ];
            }
        }
        
        // Start a transaction for better performance
        if (!$db->exec('BEGIN TRANSACTION')) {
            return [
                'success' => false,
                'error' => 'Failed to start database transaction',
                'error_details' => $db->lastErrorMsg()
            ];
        }
        
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
            
            // Evaluate log for suspicious patterns with error handling
            try {
                $detectedPatterns = isLogSuspiciousFullScan($logEntry, $patterns);
            } catch (Exception $e) {
                error_log("Error analyzing log entry: " . $e->getMessage());
                $detectedPatterns = [];
            }
            
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
            // Bind parameters
            $stmt->bindValue(':log', $logEntry, SQLITE3_TEXT);
            $stmt->bindValue(':timestamp', $timestamp, SQLITE3_TEXT);
            $stmt->bindValue(':is_suspicious', $isSuspicious, SQLITE3_INTEGER);
            
            // Execute statement
            $result = $stmt->execute();
            
            if ($result) {
                $logId = $db->lastInsertRowID();
                $totalProcessed++;
                if ($isSuspicious) {
                    $suspiciousCount++;
                }
                
                // Store detected patterns if any
                if (!empty($detectedPatterns)) {
                    foreach ($detectedPatterns as $patternInfo) {
                        $attackStmt = $db->prepare('
                            INSERT INTO attack_detections (log_id, attack_type, attack_details, pattern)
                            VALUES (:log_id, :attack_type, :attack_details, :pattern)
                        ');
                        $attackStmt->bindValue(':log_id', $logId, SQLITE3_INTEGER);
                        $attackStmt->bindValue(':attack_type', $patternInfo['attack_type'], SQLITE3_TEXT);
                        $attackStmt->bindValue(':attack_details', $patternInfo['description'] ?? '', SQLITE3_TEXT);
                        $attackStmt->bindValue(':pattern', $patternInfo['pattern'], SQLITE3_TEXT);
                        $attackStmt->execute();
                    }
                }
                // $processedLogs[] = $logData; // logData was not populated, consider if this response needs actual data
            } else {
                // Log error or handle failure
                error_log("Failed to insert log: " . $db->lastErrorMsg());
            }
        }
        
        // Commit the transaction
        if (!$db->exec('COMMIT')) {
            $db->exec('ROLLBACK');
            $db->close();
            return [
                'success' => false,
                'error' => 'Failed to commit database transaction',
                'error_details' => $db->lastErrorMsg()
            ];
        }
        
        $db->close();
        
        return [
            'success' => true,
            'total_processed' => $totalProcessed,
            'suspicious_count' => $suspiciousCount,
            'processed_logs' => $processedLogs
        ];
    } catch (Exception $e) {
        // Catch any other exceptions
        if (isset($db) && $db) {
            $db->exec('ROLLBACK');
            $db->close();
        }
        
        return [
            'success' => false,
            'error' => 'Exception occurred during log processing',
            'error_details' => $e->getMessage()
        ];
    }
}

// Main execution
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check for JSON content type
    $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
    
    // Handle JSON input
    if (strpos($contentType, 'application/json') !== false) {
        $jsonInput = file_get_contents('php://input');
        $data = json_decode($jsonInput, true);
        
        if (isset($data['logs']) && is_array($data['logs'])) {
            $result = processLogs($data['logs']);
            
            // Return response as JSON
            header('Content-Type: application/json');
            echo json_encode($result);
            exit;
        }
    }
    
    // Handle regular POST data
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
