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

//????????
/**
 * Ensures a regex pattern has proper delimiters
 * @param string $pattern The regex pattern to format
 * @return string Properly formatted regex pattern
 */
function formatRegexPattern($pattern) {
    // Check if the pattern already has delimiters (first and last char are the same non-alphanumeric)
    $firstChar = substr($pattern, 0, 1);
    $lastChar = substr($pattern, -1);
    
    // If already has matching delimiters that are valid, return as is
    if ($firstChar === $lastChar && !ctype_alnum($firstChar) && $firstChar !== '\\') {
        return $pattern;
    }
    
    // Otherwise, add '/' delimiters
    return '/' . str_replace('/', '\\/', $pattern) . '/';
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
            // Format the pattern with proper delimiters
            $formattedPattern = formatRegexPattern($pattern);
            
            if (@preg_match($formattedPattern, $logEntry)) {
                return true;
            }
        } catch (Exception $e) {
            // Log the error but continue checking other patterns
            error_log("Invalid regex pattern: " . $pattern . ". Error: " . $e->getMessage());
            continue;
        }
    }
    return false;
}

/**
 * Analyzes logs by IP to detect potential attacks
 * @param array $ipGroupedLogs Logs grouped by IP address
 * @return array Map of IP addresses to analysis results containing:
 *               - score: Suspicion score (0-1)
 *               - totalLogs: Total number of logs for this IP
 *               - suspiciousCount: Number of logs flagged as suspicious
 *               - suspiciousLogs: Array of suspicious log entries
 */
function analyzeLogsForAttacks($ipGroupedLogs) {
    $patterns = readAttackRegexPatterns();
    $results = [];
    
    foreach ($ipGroupedLogs as $ip => $logs) {
        $totalLogs = count($logs);
        if ($totalLogs === 0) {
            $results[$ip] = [
                'score' => 0,
                'totalLogs' => 0,
                'suspiciousCount' => 0,
                'suspiciousLogs' => []
            ];
            continue;
        }
        
        $suspiciousCount = 0;
        $suspiciousLogs = [];
        
        foreach ($logs as $log) {
            if (isLogSuspicious($log, $patterns)) {
                $suspiciousCount++;
                $suspiciousLogs[] = $log;
            }
        }
        
        // Calculate the suspicion score
        $score = ($suspiciousCount === $totalLogs) ? 1 : 
                (($suspiciousCount === 0) ? 0 : round($suspiciousCount / $totalLogs, 2));
        
        $results[$ip] = [
            'score' => $score,
            'totalLogs' => $totalLogs,
            'suspiciousCount' => $suspiciousCount,
            'suspiciousLogs' => $suspiciousLogs
        ];
    }
    
    return $results;
}
