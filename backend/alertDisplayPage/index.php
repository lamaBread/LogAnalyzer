<?php
// Set timezone
date_default_timezone_set('UTC');

// Connect to the SQLite database
$dbPath = __DIR__ . '/logs.db';
$db = new SQLite3($dbPath);

if (!$db) {
    die("Cannot connect to the database: " . $db->lastErrorMsg());
}

// Initialize default values
$totalCount = 0;
$totalPages = 0;

// Get the page number from query string (for pagination)
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$logsPerPage = 50;
$offset = ($page - 1) * $logsPerPage;

// Check if table exists first
$tableExistsQuery = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='logs'");
$tableExists = $tableExistsQuery && $tableExistsQuery->fetchArray();

if ($tableExists) {
    // Count total logs for pagination
    $totalCountResult = $db->query("SELECT COUNT(*) as count FROM logs");
    
    if ($totalCountResult) {
        $totalCount = $totalCountResult->fetchArray(SQLITE3_ASSOC)['count'];
        $totalPages = ceil($totalCount / $logsPerPage);

        // Query to get logs with pagination, ordered by newest first
        $query = "SELECT id, log_text, timestamp, detected_at, is_suspicious 
                  FROM logs 
                  ORDER BY detected_at DESC, id DESC 
                  LIMIT $logsPerPage OFFSET $offset";

        $result = $db->query($query);
    } else {
        $error = "Error querying the database: " . $db->lastErrorMsg();
        $result = false;
    }
} else {
    // Table doesn't exist yet
    $error = "The logs table does not exist yet. Please add some logs first.";
    $result = false;
}

// Function to get attack detections for a log
function getAttackDetections($db, $logId) {
    // Check if attack_detections table exists
    $tableExistsQuery = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='attack_detections'");
    $tableExists = $tableExistsQuery && $tableExistsQuery->fetchArray();
    
    if (!$tableExists) {
        return [];
    }
    
    $query = "SELECT attack_type, attack_details, pattern 
              FROM attack_detections 
              WHERE log_id = :log_id";
    
    $stmt = $db->prepare($query);
    if (!$stmt) {
        return [];
    }
    
    $stmt->bindValue(':log_id', $logId, SQLITE3_INTEGER);
    $result = $stmt->execute();
    
    if (!$result) {
        return [];
    }
    
    $detections = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $detections[] = $row;
    }
    
    return $detections;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="5">
    <title>Log Analyzer - Logs</title>
    <link rel="stylesheet" href="style.css">
    <script>
        function initializeDatabase() {
            if (confirm('Are you sure you want to initialize the database? This will delete all existing logs.')) {
                // Show loading message
                document.getElementById('initStatus').textContent = 'Initializing database...';
                document.getElementById('initStatus').style.display = 'block';
                
                fetch('initialize_db.php', {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('initStatus').textContent = 'Database initialized successfully! Reloading page...';
                        document.getElementById('initStatus').className = 'success-message';
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else {
                        document.getElementById('initStatus').textContent = 'Error: ' + data.error + (data.error_details ? ' - ' + data.error_details : '');
                        document.getElementById('initStatus').className = 'error-message';
                    }
                })
                .catch(error => {
                    document.getElementById('initStatus').textContent = 'Error: ' + error;
                    document.getElementById('initStatus').className = 'error-message';
                });
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <header>
            <h1>Log Analyzer</h1>
            <p class="subtitle">System Log Analysis and Intrusion Detection</p>
        </header>
        
        <div class="summary">
            <div class="summary-item">
                <span class="label">Total Logs:</span> 
                <span class="value"><?php echo number_format($totalCount); ?></span>
            </div>
            <div class="summary-item">
                <?php
                $suspiciousCount = 0;
                $suspiciousPercentage = 0;
                
                if ($tableExists) {
                    $suspiciousCountResult = $db->query("SELECT COUNT(*) as count FROM logs WHERE is_suspicious = 1");
                    if ($suspiciousCountResult) {
                        $suspiciousCount = $suspiciousCountResult->fetchArray(SQLITE3_ASSOC)['count'];
                        $suspiciousPercentage = ($totalCount > 0) ? round(($suspiciousCount / $totalCount) * 100, 2) : 0;
                    }
                }
                ?>
                <span class="label">Suspicious Logs:</span> 
                <span class="value"><?php echo number_format($suspiciousCount); ?> (<?php echo $suspiciousPercentage; ?>%)</span>
            </div>
            <div class="summary-item">
                <button class="init-db-btn" onclick="initializeDatabase()">Initialize Database</button>
                <div id="initStatus" style="display:none;"></div>
            </div>
        </div>
        
        <div class="log-container">
            <h2>Recent Logs</h2>
            
            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($result && $result->numColumns() > 0): ?>
                <div class="logs">
                    <?php while ($row = $result->fetchArray(SQLITE3_ASSOC)): ?>
                        <div class="log-entry <?php echo $row['is_suspicious'] ? 'suspicious' : 'normal'; ?>">
                            <div class="log-header">
                                <span class="timestamp"><?php echo htmlspecialchars($row['timestamp']); ?></span>
                                <span class="detected-at">Detected: <?php echo htmlspecialchars($row['detected_at']); ?></span>
                                <?php if ($row['is_suspicious']): ?>
                                    <span class="alert-badge">SUSPICIOUS</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="log-content">
                                <pre><?php echo htmlspecialchars($row['log_text']); ?></pre>
                            </div>
                            
                            <?php if ($row['is_suspicious']): ?>
                                <?php $detections = getAttackDetections($db, $row['id']); ?>
                                <?php if (!empty($detections)): ?>
                                    <div class="detections">
                                        <h4>Detected Patterns:</h4>
                                        <ul>
                                            <?php foreach ($detections as $detection): ?>
                                                <li>
                                                    <div class="detection-type"><?php echo htmlspecialchars($detection['attack_type']); ?></div>
                                                    <div class="detection-details"><?php echo htmlspecialchars($detection['attack_details']); ?></div>
                                                    <div class="detection-pattern">
                                                        <span class="pattern-label">Detected Pattern:</span> 
                                                        <code class="highlight-regex"><?php echo htmlspecialchars($detection['pattern']); ?></code>
                                                    </div>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>" class="page-link">&laquo; Previous</a>
                        <?php endif; ?>
                        
                        <?php
                        $startPage = max(1, $page - 2);
                        $endPage = min($totalPages, $page + 2);
                        
                        if ($startPage > 1) {
                            echo '<a href="?page=1" class="page-link">1</a>';
                            if ($startPage > 2) {
                                echo '<span class="page-ellipsis">...</span>';
                            }
                        }
                        
                        for ($i = $startPage; $i <= $endPage; $i++) {
                            $activeClass = ($i == $page) ? 'active' : '';
                            echo '<a href="?page=' . $i . '" class="page-link ' . $activeClass . '">' . $i . '</a>';
                        }
                        
                        if ($endPage < $totalPages) {
                            if ($endPage < $totalPages - 1) {
                                echo '<span class="page-ellipsis">...</span>';
                            }
                            echo '<a href="?page=' . $totalPages . '" class="page-link">' . $totalPages . '</a>';
                        }
                        ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?php echo $page + 1; ?>" class="page-link">Next &raquo;</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="no-logs">No logs found in the database.</div>
            <?php endif; ?>
        </div>
        
        <footer>
            <p>&copy; <?php echo date('Y'); ?> Log Analyzer. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>