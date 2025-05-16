<?php
// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set timezone
date_default_timezone_set('UTC');

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

// Function to initialize the database
function initializeDatabase() {
    // Database path
    $dbPath = __DIR__ . '/logs.db';
    
    try {
        // Delete existing database if it exists
        if (file_exists($dbPath)) {
            if (!unlink($dbPath)) {
                return [
                    'success' => false,
                    'error' => 'Failed to delete existing database',
                    'error_details' => 'Could not delete the existing logs.db file'
                ];
            }
        }
        
        // Ensure database directory has proper permissions
        ensureDatabasePermissions($dbPath);
        
        // Connect to the database (this will create a new empty file)
        $db = new SQLite3(
            $dbPath,
            SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE
        );
        
        // Create logs table
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
            $db->close();
            return [
                'success' => false,
                'error' => 'Failed to create logs table',
                'error_details' => $errorMsg
            ];
        }
        
        // Create attack_detections table
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
            $db->close();
            return [
                'success' => false,
                'error' => 'Failed to create attack_detections table',
                'error_details' => $errorMsg
            ];
        }
        
        // Close the database connection
        $db->close();
        
        return [
            'success' => true,
            'message' => 'Database initialized successfully'
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => 'Exception occurred during database initialization',
            'error_details' => $e->getMessage()
        ];
    }
}

// Main execution
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = initializeDatabase();
    
    // Return response as JSON
    header('Content-Type: application/json');
    echo json_encode($result);
} else {
    // Method not allowed
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['error' => 'Only POST requests are allowed']);
}