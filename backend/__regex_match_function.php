<?php

/**
 * Reads attack regex patterns from the CSV file
 * @return array Array of regex patterns with attack details
 */
function readAttackRegexPatterns() {
    $csvPath = __DIR__ . '/find_attack_regex_list_v2.csv';
    $patterns = [];
    
    if (file_exists($csvPath)) {
        $file = fopen($csvPath, "r");
        if ($file) {
            // Skip header line
            fgetcsv($file);
            
            $currentAttackType = "";
            
            while (($data = fgetcsv($file)) !== false) {
                if (isset($data[2]) && !empty($data[2])) {
                    // Track the attack type (first column)
                    if (!empty($data[0])) {
                        $currentAttackType = $data[0];
                    }
                    
                    // Remove quotes around the regex if they exist
                    $pattern = $data[2];
                    $pattern = trim($pattern, '"');
                    
                    // Basic pattern validation: check for balanced brackets/parentheses
                    if (substr_count($pattern, '{') != substr_count($pattern, '}') ||
                        substr_count($pattern, '[') != substr_count($pattern, ']') ||
                        substr_count($pattern, '(') != substr_count($pattern, ')')) {
                        error_log("Skipping malformed regex pattern: " . $pattern);
                        continue;
                    }
                    
                    // Store pattern with its attack type and details
                    $patterns[] = [
                        'attackType' => $currentAttackType,
                        'attackDetails' => $data[1] ?? '',
                        'pattern' => $pattern
                    ];
                }
            }
            fclose($file);
        } else {
            error_log("Error opening regex CSV file.");
        }
    } else {
        error_log("Regex CSV file does not exist.");
    }
    
    return $patterns;
}

/**
 * Ensures a regex pattern has proper delimiters and handles special cases
 * @param string $pattern The regex pattern to format
 * @return string|false Properly formatted regex pattern or false if invalid
 */
function formatRegexPattern($pattern) {
    // If pattern is empty, return false
    if (empty($pattern)) {
        return false;
    }
    
    // Additional check for malformed regex patterns
    if (substr_count($pattern, '{') != substr_count($pattern, '}') || 
        substr_count($pattern, '(') != substr_count($pattern, ')') ||
        substr_count($pattern, '[') != substr_count($pattern, ']')) {
        error_log("Malformed regex pattern (unbalanced brackets/parentheses): " . $pattern);
        return false;
    }
    
    // If the pattern already starts and ends with the same delimiter, return as is
    $firstChar = substr($pattern, 0, 1);
    $lastChar = substr($pattern, -1);
    
    // If it's a pattern with modifiers like /pattern/i
    if ($firstChar === '/' && preg_match('#^/(.+)/[imsxUXJ]*$#', $pattern)) {
        return $pattern;
    }
    
    // If pattern already has matching delimiters that are valid
    if ($firstChar === $lastChar && !ctype_alnum($firstChar) && $firstChar !== '\\') {
        // Check if the last character is followed by modifiers
        if (preg_match('#^([/~`!@#$%^&*\-=_+\'";:,.<>\/\[\]\{\}\(\)\|\\\\])(.+)\\1[imsxUXJ]*$#', $pattern)) {
            return $pattern;
        }
    }
    
    // Otherwise, add '/' delimiters and escape any forward slashes in the pattern
    $escapedPattern = str_replace('/', '\\/', $pattern);
    $escapedPattern = preg_replace('/\\$(?!$)/', '\\$', $escapedPattern);
    
    // Remove any existing delimiters if they're not properly matched
    if (in_array($firstChar, ['/', '~', '#', '@', '%', '`', '|']) && 
        $firstChar !== $lastChar) {
        $escapedPattern = substr($escapedPattern, 1);
    }
    
    return '/' . $escapedPattern . '/';
}

/**
 * Determines if a regex pattern is too general and likely to cause false positives
 * @param string $pattern The regex pattern to check
 * @param string $attackType The type of attack this pattern detects
 * @return bool True if the pattern is too general
 */
function isTooGeneralPattern($pattern, $attackType) {
    // 과도한 필터링을 줄이기 위해 명확하게 문제가 되는 패턴만 필터링
    $tooGeneralPatterns = [
        // 특정 문맥 없이 완전히 일반적인 패턴만 필터링
        '/[;\|]/' => true, // 단순 세미콜론이나 파이프 문자만 있는 경우
    ];
    
    // 명령어 삽입 관련 패턴은 컨텍스트 확인 필요 없이 허용
    if (strpos($attackType, '명령어 삽입') !== false) {
        return false;
    }
    
    // SQL 인젝션 패턴도 덜 제한적으로 필터링
    if ($attackType === 'SQL injection') {
        // 매우 일반적인 SQL 패턴만 필터링 (AND/OR만 있는 경우)
        if ($pattern === '/\b(AND|OR)\b/i') {
            return true;
        }
        return false;
    }
    
    return isset($tooGeneralPatterns[$pattern]);
}

/**
 * 로그 문자열에서 URL 부분과 파라미터를 더 정확하게 추출
 * @param string $logEntry 로그 항목
 * @return array URL 정보 (path, query)
 */
function extractUrlFromLog($logEntry) {
    $result = [
        'path' => '',
        'query' => '',
        'fullUrl' => '',
        'method' => '',
        'headers' => '',
        'body' => ''
    ];
    
    // 로그 타입 확인 (access log vs error log)
    $isAccessLog = preg_match('/(GET|POST|PUT|DELETE|PATCH|HEAD|OPTIONS|CONNECT|TRACE)/i', $logEntry);
    $isErrorLog = preg_match('/\[(.*?) (.*?) (.*?)\]/i', $logEntry) && !$isAccessLog;
    
    // Access Log 처리
    if ($isAccessLog) {
        // HTTP 메소드와 URL 추출 (더 다양한 형식 지원)
        if (preg_match('/(GET|POST|PUT|DELETE|PATCH|HEAD|OPTIONS|CONNECT|TRACE)\s+([^\s?]+)(\?[^\s]*)?/i', $logEntry, $urlMatches)) {
            $result['method'] = $urlMatches[1] ?? '';
            $result['path'] = $urlMatches[2] ?? '';
            $result['query'] = $urlMatches[3] ?? '';
            $result['fullUrl'] = ($urlMatches[2] ?? '') . ($urlMatches[3] ?? '');
        }
        // URL 패턴만 추출 (HTTP 메소드가 없는 경우)
        else if (preg_match('/https?:\/\/[^\/]+(\/[^\s\?]*)?(\?[^\s]*)?/i', $logEntry, $urlMatches)) {
            $result['path'] = $urlMatches[1] ?? '';
            $result['query'] = $urlMatches[2] ?? '';
            $result['fullUrl'] = ($urlMatches[1] ?? '') . ($urlMatches[2] ?? '');
        }
        
        // 헤더 정보 추출 (User-Agent, Referer 등)
        if (preg_match('/User-Agent:\s+([^\r\n]+)/i', $logEntry, $uaMatches)) {
            $result['headers'] .= ' ' . ($uaMatches[1] ?? '');
        }
        
        // 요청 바디 추출 시도 (POST 데이터 등)
        if (preg_match('/\r\n\r\n(.*?)(?:\r\n|$)/s', $logEntry, $bodyMatches)) {
            $result['body'] = $bodyMatches[1] ?? '';
        }
    } 
    // Error Log 처리
    else if ($isErrorLog) {
        // 에러 로그에서 API 경로나 파일 경로를 추출 시도
        if (preg_match('/\b(\/[^\s:"\'\[\]]*)\b/', $logEntry, $pathMatches)) {
            $result['path'] = $pathMatches[1] ?? '';
            $result['fullUrl'] = $result['path'];
        }
        
        // script filename이나 requested path 추출 시도
        if (preg_match('/(PHP Warning|PHP Notice|PHP Fatal error).*?in (.*?) on line/i', $logEntry, $errorMatches)) {
            $result['fullUrl'] = $errorMatches[2] ?? '';
            $result['headers'] = $errorMatches[1] ?? ''; // 에러 타입을 헤더로 저장
        }
    }
    
    return $result;
}

/**
 * Checks if a log entry matches any attack pattern
 * @param string $logEntry The log entry to check
 * @param array $patterns Array of regex patterns with attack details
 * @return array Array with all matched patterns or empty array if no matches
 */
function isLogSuspicious($logEntry, $patterns) {
    $matches = [];
    
    // Skip empty log entries
    if (empty(trim($logEntry))) {
        return $matches;
    }
    
    // 로그에서 URL과 파라미터 정보 추출
    $urlInfo = extractUrlFromLog($logEntry);
    $urlPath = $urlInfo['path'];
    $queryString = $urlInfo['query'];
    $fullUrl = $urlInfo['fullUrl'];
    $headers = $urlInfo['headers'];
    $body = $urlInfo['body'];
    
    // URL 디코딩하여 인코딩된 공격 패턴도 탐지
    $decodedQuery = urldecode($queryString);
    $decodedPath = urldecode($urlPath);
    $decodedBody = urldecode($body);
    
    // 일관된 로깅을 위한 패턴 캐시
    static $failedPatterns = [];
    
    foreach ($patterns as $patternData) {
        $attackType = $patternData['attackType'];
        $pattern = $patternData['pattern'];
        
        // 너무 일반적인 패턴 필터링 (제한적으로 적용)
        if (isTooGeneralPattern($pattern, $attackType)) {
            continue;
        }
        
        try {
            // 정규식 패턴 포맷팅
            $formattedPattern = formatRegexPattern($pattern);
            
            // 유효하지 않은 패턴 건너뛰기
            if ($formattedPattern === false) {
                if (!isset($failedPatterns[$pattern])) {
                    error_log("Invalid regex pattern: " . $pattern);
                    $failedPatterns[$pattern] = true;
                }
                continue;
            }
            
            // 공격 유형에 따른 다양한 탐지 전략 적용
            $isMatch = false;
            
            // SQL 인젝션 패턴 검사
            if (strpos($attackType, 'SQL injection') !== false) {
                // 쿼리 파라미터, PATH, POST 바디 모두 검사
                $isMatch = (!empty($queryString) && @preg_match($formattedPattern, $queryString)) ||
                           (!empty($decodedQuery) && @preg_match($formattedPattern, $decodedQuery)) ||
                           (!empty($body) && @preg_match($formattedPattern, $body)) ||
                           (!empty($decodedBody) && @preg_match($formattedPattern, $decodedBody));
                
                // 전체 로그에서도 강한 SQL 인젝션 패턴(UNION SELECT 등) 확인
                if (!$isMatch && (strpos($pattern, 'UNION') !== false || strpos($pattern, 'SELECT') !== false)) {
                    $isMatch = @preg_match($formattedPattern, $logEntry);
                }
            } 
            // XSS 패턴 검사
            else if (strpos($attackType, 'XSS') !== false) {
                // 쿼리 파라미터와 POST 바디 모두 검사
                $isMatch = (!empty($queryString) && @preg_match($formattedPattern, $queryString)) ||
                           (!empty($decodedQuery) && @preg_match($formattedPattern, $decodedQuery)) ||
                           (!empty($body) && @preg_match($formattedPattern, $body)) ||
                           (!empty($decodedBody) && @preg_match($formattedPattern, $decodedBody));
                
                // 특정 XSS 패턴은 전체 로그에서도 검사
                if (!$isMatch && (strpos($pattern, '<script') !== false || strpos($pattern, 'javascript:') !== false)) {
                    $isMatch = @preg_match($formattedPattern, $logEntry);
                }
            }
            // 디렉토리 탐색 패턴 검사
            else if (strpos($attackType, '디렉토리 탐색') !== false) {
                // URL 경로와 쿼리 파라미터 모두 검사
                $isMatch = (!empty($urlPath) && @preg_match($formattedPattern, $urlPath)) ||
                           (!empty($decodedPath) && @preg_match($formattedPattern, $decodedPath)) ||
                           (!empty($queryString) && @preg_match($formattedPattern, $queryString)) ||
                           (!empty($decodedQuery) && @preg_match($formattedPattern, $decodedQuery));
            }
            // 명령어 삽입 패턴 검사
            else if (strpos($attackType, '명령어 삽입') !== false) {
                // 명령어 삽입은 쿼리와 바디 모두 검사 (더 민감하게)
                $isMatch = (!empty($queryString) && @preg_match($formattedPattern, $queryString)) ||
                           (!empty($decodedQuery) && @preg_match($formattedPattern, $decodedQuery)) ||
                           (!empty($body) && @preg_match($formattedPattern, $body)) ||
                           (!empty($decodedBody) && @preg_match($formattedPattern, $decodedBody)) ||
                           (!empty($fullUrl) && @preg_match($formattedPattern, $fullUrl));
                
                // 특정 명령어 패턴은 전체 로그에서도 검사
                if (!$isMatch && (strpos($pattern, 'exec') !== false || strpos($pattern, 'shell') !== false || 
                                  strpos($pattern, 'cmd') !== false || strpos($pattern, 'powershell') !== false)) {
                    $isMatch = @preg_match($formattedPattern, $logEntry);
                }
            }
            // 파일 포함 취약점 검사
            else if (strpos($attackType, '파일 포함') !== false) {
                // 파일 포함은 URL과 쿼리 모두 검사
                $isMatch = (!empty($fullUrl) && @preg_match($formattedPattern, $fullUrl)) ||
                           (!empty(urldecode($fullUrl)) && @preg_match($formattedPattern, urldecode($fullUrl))) ||
                           (!empty($body) && @preg_match($formattedPattern, $body));
            }
            // 웹 취약점 스캐너 시그니처 검사
            else if (strpos($attackType, '웹 취약점 스캐너') !== false) {
                // User-Agent와 같은 헤더 정보와 URL 모두 검사
                $isMatch = (!empty($headers) && @preg_match($formattedPattern, $headers)) ||
                           (!empty($fullUrl) && @preg_match($formattedPattern, $fullUrl)) ||
                           @preg_match($formattedPattern, $logEntry);
            }
            // 기타 공격 유형
            else {
                // 다른 모든 공격 유형은 전체 로그에서 검사
                $isMatch = @preg_match($formattedPattern, $logEntry);
            }
            
            // 오류 처리
            if ($isMatch === false) {
                if (!isset($failedPatterns[$pattern])) {
                    error_log("Error with pattern: " . $pattern);
                    $failedPatterns[$pattern] = true;
                }
                continue;
            }
            
            // 매치된 패턴 기록
            if ($isMatch) {
                $matches[] = [
                    'attackType' => $attackType,
                    'attackDetails' => $patternData['attackDetails'],
                    'pattern' => $pattern
                ];
            }
        } catch (Exception $e) {
            // 에러 로깅하고 다음 패턴으로 넘어감
            if (!isset($failedPatterns[$pattern])) {
                error_log("Exception with regex pattern: " . $pattern . ". Error: " . $e->getMessage());
                $failedPatterns[$pattern] = true;
            }
            continue;
        }
    }
    
    return $matches;
}

/**
 * 최적화 없이 모든 패턴을 로그에 직접 적용하는 함수
 * @param string $logEntry 검사할 로그 항목
 * @param array $patterns 정규식 패턴 배열
 * @return array 일치하는 모든 패턴 정보 배열
 */
function isLogSuspiciousFullScan($logEntry, $patterns) {
    $matches = [];
    
    // 빈 로그 항목 건너뛰기
    if (empty(trim($logEntry))) {
        return $matches;
    }
    
    // 로그 타입 확인 (access log vs error log)
    $isAccessLog = preg_match('/(GET|POST|PUT|DELETE|PATCH|HEAD|OPTIONS|CONNECT|TRACE)/i', $logEntry);
    $isErrorLog = preg_match('/\[(.*?) (.*?) (.*?)\]/i', $logEntry) && !$isAccessLog;
    
    // 일관된 로깅을 위한 패턴 캐시
    static $failedPatterns = [];
    
    foreach ($patterns as $patternData) {
        $attackType = $patternData['attackType'];
        $pattern = $patternData['pattern'];
        
        // 너무 일반적인 패턴 필터링 (일부 에러 로그의 경우에는 제외)
        if (!$isErrorLog && isTooGeneralPattern($pattern, $attackType)) {
            continue;
        }
        
        try {
            // 정규식 패턴 포맷팅
            $formattedPattern = formatRegexPattern($pattern);
            
            // 유효하지 않은 패턴 건너뛰기
            if ($formattedPattern === false) {
                if (!isset($failedPatterns[$pattern])) {
                    error_log("Invalid regex pattern: " . $pattern);
                    $failedPatterns[$pattern] = true;
                }
                continue;
            }
            
            // 패턴 매칭 시도
            try {
                $isMatch = @preg_match($formattedPattern, $logEntry);
            } catch (Throwable $e) {
                if (!isset($failedPatterns[$pattern])) {
                    error_log("Exception during preg_match with pattern: " . $pattern . " - " . $e->getMessage());
                    $failedPatterns[$pattern] = true;
                }
                continue;
            }
            
            // 오류 처리
            if ($isMatch === false) {
                if (!isset($failedPatterns[$pattern])) {
                    $errorMessage = preg_last_error_msg();
                    error_log("Error with pattern: " . $pattern . " - " . $errorMessage);
                    $failedPatterns[$pattern] = true;
                }
                continue;
            }
            
            // 일치하는 패턴 기록
            if ($isMatch) {
                $matches[] = [
                    'attackType' => $attackType,
                    'attackDetails' => $patternData['attackDetails'],
                    'pattern' => $pattern
                ];
            }
        } catch (Exception $e) {
            // 에러 로깅하고 다음 패턴으로 넘어감
            if (!isset($failedPatterns[$pattern])) {
                error_log("Exception with regex pattern: " . $pattern . ". Error: " . $e->getMessage());
                $failedPatterns[$pattern] = true;
            }
            continue;
        }
    }
    
    return $matches;
}

/**
 * Analyzes logs by IP to detect potential attacks
 * @param array $ipGroupedLogs Logs grouped by IP address
 * @return array Map of IP addresses to analysis results containing:
 *               - score: Suspicion score (0-1)
 *               - totalLogs: Total number of logs for this IP
 *               - suspiciousCount: Number of logs flagged as suspicious
 *               - suspiciousLogs: Array of suspicious log entries with attack details
 *               - detectedAttacks: Array of attack types and details detected
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
                'suspiciousLogs' => [],
                'detectedAttacks' => []
            ];
            continue;
        }
        
        $suspiciousCount = 0;
        $suspiciousLogs = [];
        $detectedAttacks = [];
        
        foreach ($logs as $log) {
            // 모든 정규식을 개별 로그에 대해 수행
            $matchResults = isLogSuspiciousFullScan($log, $patterns);
            
            if (!empty($matchResults)) {
                $suspiciousCount++;
                
                // Store the log entry with its detected attack patterns
                $suspiciousLogs[] = [
                    'log' => $log,
                    'detectedPatterns' => $matchResults
                ];
                
                // Track all detected attack types for this log
                foreach ($matchResults as $matchResult) {
                    // Create a unique identifier for this attack type and details
                    $attackId = $matchResult['attackType'] . ' - ' . $matchResult['attackDetails'];
                    
                    // Store information about this attack
                    if (!isset($detectedAttacks[$attackId])) {
                        $detectedAttacks[$attackId] = [
                            'attackType' => $matchResult['attackType'],
                            'attackDetails' => $matchResult['attackDetails'],
                            'count' => 1
                        ];
                    } else {
                        $detectedAttacks[$attackId]['count']++;
                    }
                }
            }
        }
        
        // Calculate the suspicion score
        $score = ($totalLogs > 0) ? round($suspiciousCount / $totalLogs, 2) : 0;
        
        $results[$ip] = [
            'score' => $score,
            'totalLogs' => $totalLogs,
            'suspiciousCount' => $suspiciousCount,
            'suspiciousLogs' => $suspiciousLogs,
            'detectedAttacks' => array_values($detectedAttacks)
        ];
    }
    
    return $results;
}