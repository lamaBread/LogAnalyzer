<?php 
// Handle POST requests and store in SQLite database
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Initialize SQLite database if it doesn't exist
    $db = new SQLite3('logs.db');
    
    // Create table if it doesn't exist
    $db->exec('
        CREATE TABLE IF NOT EXISTS log_entries (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            log_entry TEXT,
            timestamp TEXT,
            is_suspicious INTEGER,
            attack_details TEXT
        )
    ');
    
    // Insert the log entry
    $stmt = $db->prepare('
        INSERT INTO log_entries (log_entry, timestamp, is_suspicious, attack_details)
        VALUES (:log_entry, :timestamp, :is_suspicious, :attack_details)
    ');
    
    $stmt->bindValue(':log_entry', $_POST['log_entry'] ?? '', SQLITE3_TEXT);
    $stmt->bindValue(':timestamp', $_POST['timestamp'] ?? date('Y-m-d H:i:s'), SQLITE3_TEXT);
    $stmt->bindValue(':is_suspicious', $_POST['is_suspicious'] ?? 0, SQLITE3_INTEGER);
    $stmt->bindValue(':attack_details', $_POST['attack_details'] ?? '[]', SQLITE3_TEXT);
    
    $stmt->execute();
    $db->close();
    
    // Return success response for API calls
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success']);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alert Display Page</title>
    <link rel="stylesheet" href="./style.css">
    <script>
        // Function to refresh the log display
        function refreshLogs() {
            fetch('http://localhost:8003/fetch_logs.php')
                .then(response => response.json())
                .then(data => {
                    // Update stats
                    document.getElementById('total-count').textContent = data.stats.total;
                    document.getElementById('suspicious-count').textContent = data.stats.suspicious;
                    
                    // Update logs
                    const logContainer = document.getElementById('log-container');
                    logContainer.innerHTML = ''; // Clear current logs
                    
                    data.logs.forEach(log => {
                        const logEntry = document.createElement('div');
                        logEntry.className = log.is_suspicious == 1 ? 'log-entry suspicious' : 'log-entry normal';
                        
                        const timestamp = document.createElement('div');
                        timestamp.className = 'timestamp';
                        timestamp.textContent = log.timestamp;
                        logEntry.appendChild(timestamp);
                        
                        const logText = document.createElement('div');
                        logText.className = 'log-text';
                        logText.textContent = log.log_entry;
                        logEntry.appendChild(logText);
                        
                        if (log.is_suspicious == 1) {
                            const attackDetails = document.createElement('div');
                            attackDetails.className = 'attack-details';
                            
                            try {
                                const details = JSON.parse(log.attack_details);
                                if (details && details.length > 0) {
                                    const detailsList = document.createElement('ul');
                                    details.forEach(detail => {
                                        const item = document.createElement('li');
                                        item.textContent = `${detail.attackType}: ${detail.attackDetails}`;
                                        detailsList.appendChild(item);
                                    });
                                    attackDetails.appendChild(detailsList);
                                }
                            } catch (e) {
                                attackDetails.textContent = 'Invalid attack details format';
                            }
                            
                            logEntry.appendChild(attackDetails);
                        }
                        
                        logContainer.appendChild(logEntry);
                    });
                })
                .catch(error => {
                    console.error('Error fetching logs:', error);
                });
        }
        
        // Refresh logs every 2 seconds
        window.onload = function() {
            refreshLogs(); // Initial load
            setInterval(refreshLogs, 2000); // Refresh every 2 seconds
        };
    </script>
</head>
<body>
    <header>
        <h1>Alert Display Page</h1>
        <div class="summary-stats">
            <div class="stat-box">Total Logs: <span id="total-count">0</span></div>
            <div class="stat-box alert">Suspicious: <span id="suspicious-count">0</span></div>
        </div>
    </header>
    
    <main>
        <div id="log-container" class="log-container">
            <!-- Logs will be loaded here dynamically -->
        </div>
    </main>
    
    <footer>
        <p>Log Analyzer Alert System</p>
    </footer>
</body>
</html>