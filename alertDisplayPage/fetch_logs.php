<?php
// Fetch logs from the SQLite database and return as JSON
header('Content-Type: application/json');

// Define database path with absolute path
$dbPath = __DIR__ . '/logs.db';

// Initialize SQLite database
try {
    // Connect to the database
    $db = new SQLite3($dbPath);
    
    // Create the table if it doesn't exist (regardless of whether the db file exists)
    $db->exec('
        CREATE TABLE IF NOT EXISTS log_entries (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            log_entry TEXT,
            timestamp TEXT,
            is_suspicious INTEGER,
            attack_details TEXT
        )
    ');
    
    // Get stats for header counters
    $stats = [
        'total' => 0,
        'suspicious' => 0
    ];

    $statsQuery = $db->query('
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN is_suspicious = 1 THEN 1 ELSE 0 END) as suspicious
        FROM log_entries
    ');

    if ($statsQuery && $statsRow = $statsQuery->fetchArray(SQLITE3_ASSOC)) {
        $stats['total'] = $statsRow['total'];
        $stats['suspicious'] = $statsRow['suspicious'];
    }

    // Get the most recent 100 logs, ordered by newest first
    $result = $db->query('
        SELECT id, log_entry, timestamp, is_suspicious, attack_details
        FROM log_entries
        ORDER BY id DESC
        LIMIT 100
    ');

    $logs = [];
    if ($result) {
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $logs[] = $row;
        }
    }

    // Close the database
    $db->close();

    // Include stats with the response
    $response = [
        'stats' => $stats,
        'logs' => $logs
    ];

    echo json_encode($response);
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>