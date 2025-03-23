<?php

/**
 * Reads attack regex patterns from the CSV file
 * @return array Array of regex patterns
 */
function readAttackRegexPatterns() {
    $csvPath = __DIR__ . '/find_attack_regex_list.csv';
    $patterns = [];
    
    if (file_exists($csvPath)) {
        $file = fopen($csvPath, "r");
        if ($file) {
            // Skip header line
            fgetcsv($file);
            
            while (($data = fgetcsv($file)) !== false) {
                if (isset($data[2]) && !empty($data[2])) {
                    // Remove quotes around the regex if they exist
                    $pattern = $data[2];
                    $pattern = trim($pattern, '"');
                    $patterns[] = $pattern;
                }
            }
            fclose($file);
        } else {
            echo "Error opening regex CSV file.";
        }
    } else {
        echo "Regex CSV file does not exist.";
    }
    
    return $patterns;
}

/**
 * Checks if a log entry matches any attack pattern
 * @param string $logEntry The log entry to check
 * @param array $patterns Array of regex patterns
 * @return bool True if a pattern matches, false otherwise
 */
function isLogSuspicious($logEntry, $patterns) {
    foreach ($patterns as $pattern) {
        try {
            if (preg_match($pattern, $logEntry)) {
                return true;
            }
        } catch (Exception $e) {
            // Handle potentially invalid regex patterns
            continue;
        }
    }
    return false;
}

/**
 * Analyzes logs by IP to detect potential attacks
 * @param array $ipGroupedLogs Logs grouped by IP address
 * @return array Map of IP addresses to suspicion scores
 */
function analyzeLogsForAttacks($ipGroupedLogs) {
    $patterns = readAttackRegexPatterns();
    $suspicionScores = [];
    
    foreach ($ipGroupedLogs as $ip => $logs) {
        $totalLogs = count($logs);
        if ($totalLogs === 0) {
            $suspicionScores[$ip] = 0;
            continue;
        }
        
        $suspiciousLogs = 0;
        foreach ($logs as $log) {
            if (isLogSuspicious($log, $patterns)) {
                $suspiciousLogs++;
            }
        }
        
        // If all logs are suspicious, score is 1
        // If no logs are suspicious, score is 0
        // Otherwise, it's the ratio of suspicious logs to total logs, rounded to 2 decimal places
        $suspicionScores[$ip] = ($suspiciousLogs === $totalLogs) ? 1 : 
                               (($suspiciousLogs === 0) ? 0 : round($suspiciousLogs / $totalLogs, 2));  // return level of suspicion for each IP address
    }
    
    return $suspicionScores;
}
