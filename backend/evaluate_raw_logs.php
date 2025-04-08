<?php
// Evaluate raw logs for suspicious activities and send email alerts
include '__functions.php';
include '__regex_match_function.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Configuration
$emailConfig = [
    'enabled' => true,
    'to' => '7bde31@gmail.com',
    'from' => 'loganalyzer@loganalyzer.com',
    'subject_prefix' => '[SECURITY ALERT] '
];

// 로그 알림 설정 추가
$httpAlertConfig = [
    'enabled' => true,
    'url' => 'http://localhost:9000',
    'timeout' => 10
];

/**
 * 개별 로그를 평가하는 함수 - isLogSuspiciousFullScan을 활용
 * @param array $logs 평가할 로그 배열
 * @return array 평가 결과 [suspicious_logs, normal_logs, analysis_results]
 */
function evaluateLogsWithoutIpGrouping($logs) {
    // 정규식 패턴 로드
    $patterns = readAttackRegexPatterns();
    
    $normalLogs = [];
    $suspiciousLogs = [];
    $analysisResults = [
        'total' => count($logs),
        'suspicious_count' => 0,
        'detected_attacks' => [],
    ];
    
    $detectedAttackTypes = [];
    
    foreach ($logs as $log) {
        // isLogSuspiciousFullScan 함수를 사용하여 로그 평가
        $matchResults = isLogSuspiciousFullScan($log, $patterns);
        
        if (!empty($matchResults)) {
            // 의심스러운 로그 추가
            $suspiciousLogs[] = [
                'log' => $log,
                'detectedPatterns' => $matchResults
            ];
            
            $analysisResults['suspicious_count']++;
            
            // 발견된 공격 유형 추적
            foreach ($matchResults as $matchResult) {
                $attackId = $matchResult['attackType'] . ' - ' . $matchResult['attackDetails'];
                
                if (!isset($detectedAttackTypes[$attackId])) {
                    $detectedAttackTypes[$attackId] = [
                        'attackType' => $matchResult['attackType'],
                        'attackDetails' => $matchResult['attackDetails'],
                        'count' => 1
                    ];
                } else {
                    $detectedAttackTypes[$attackId]['count']++;
                }
            }
        } else {
            // 정상 로그 추가
            $normalLogs[] = $log;
        }
    }
    
    $analysisResults['detected_attacks'] = array_values($detectedAttackTypes);
    
    return [
        'suspicious_logs' => $suspiciousLogs,
        'normal_logs' => $normalLogs,
        'analysis_results' => $analysisResults
    ];
}

/**
 * HTTP 알림을 보내는 함수 - localhost:9000으로 로그 평가 전송
 * @param array $suspiciousLogs 의심스러운 로그 배열
 * @param array $config HTTP 알림 설정
 * @return bool HTTP 전송 성공 여부
 */
function sendHttpAlert($suspiciousLogs, $config) {
    if (!$config['enabled'] || empty($suspiciousLogs)) {
        return false;
    }
    
    // 의심스러운 로그에서 공격 유형 추출
    $attackTypes = [];
    foreach ($suspiciousLogs as $log) {
        foreach ($log['details'] as $detail) {
            if (!in_array($detail['attackType'], $attackTypes)) {
                $attackTypes[] = $detail['attackType'];
            }
        }
    }
    
    // POST 데이터 구성
    $postData = [
        'timestamp' => date('Y-m-d H:i:s'),
        'suspicious_count' => count($suspiciousLogs),
        'attack_types' => $attackTypes,
        'logs' => $suspiciousLogs
    ];
    
    // cURL 초기화
    $ch = curl_init($config['url']);
    
    // cURL 옵션 설정
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, $config['timeout']);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'X-Source: LogAnalyzer'
    ]);
    
    // 요청 실행
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // 요청 종료
    curl_close($ch);
    
    // 결과 로깅
    error_log("HTTP Alert sent to {$config['url']} - Status: $httpCode, Response: $response");
    
    // 200번대 응답코드면 성공으로 간주
    return $httpCode >= 200 && $httpCode < 300;
}

/**
 * 로그 결과 저장 함수
 * @param array $evaluationResults 로그 평가 결과
 */
function saveEvaluationResults($evaluationResults) {
    $logDir = __DIR__ . '/../logs';
    
    // 로그 디렉토리 확인 및 생성
    if (!is_dir($logDir)) {
        if (!mkdir($logDir, 0755, true)) {
            error_log("Failed to create log directory: $logDir");
            return false;
        }
    }
    
    $timestamp = date('Y-m-d-H-i-s');
    $logFile = $logDir . "/evaluation-$timestamp.log";
    
    $logContent = "Log Evaluation Results: " . date('Y-m-d H:i:s') . "\n";
    $logContent .= "Total logs: " . $evaluationResults['analysis_results']['total'] . "\n";
    $logContent .= "Suspicious logs: " . $evaluationResults['analysis_results']['suspicious_count'] . "\n\n";
    
    // 의심스러운 공격 유형 기록
    $logContent .= "=== Detected Attack Types ===\n";
    foreach ($evaluationResults['analysis_results']['detected_attacks'] as $attack) {
        $logContent .= "- Type: " . $attack['attackType'] . "\n";
        $logContent .= "  Details: " . $attack['attackDetails'] . "\n";
        $logContent .= "  Count: " . $attack['count'] . "\n\n";
    }
    
    // 의심스러운 로그 세부 정보 기록
    $logContent .= "=== Suspicious Logs ===\n";
    foreach ($evaluationResults['suspicious_logs'] as $index => $logData) {
        $logContent .= "--- Suspicious Log #$index ---\n";
        $logContent .= "Log: " . $logData['log'] . "\n";
        $logContent .= "Attack details:\n";
        
        foreach ($logData['detectedPatterns'] as $pattern) {
            $logContent .= "- Type: " . $pattern['attackType'] . "\n";
            $logContent .= "  Details: " . $pattern['attackDetails'] . "\n";
            $logContent .= "  Pattern: " . $pattern['pattern'] . "\n";
        }
        
        $logContent .= "\n";
    }
    
    return file_put_contents($logFile, $logContent) !== false;
}

// 메인 로직 실행
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // 로그 데이터 받기
        $logs = $_POST['logs'] ?? [];
        
        if (empty($logs)) {
            http_response_code(400);
            echo json_encode(['error' => 'No logs provided']);
            exit;
        }
        
        // 유효한 로그만 필터링
        $filteredLogs = [];
        foreach ($logs as $logEntry) {
            $logEntry = trim($logEntry);
            if (!empty($logEntry) && strpos($logEntry, '---') !== 0) {
                $filteredLogs[] = $logEntry;
            }
        }
        
        // evaluateLogsWithoutIpGrouping 함수로 로그 분석
        $evaluation = evaluateLogsWithoutIpGrouping($filteredLogs);
        
        // 의심스러운 로그가 있으면 알림 전송
        if (!empty($evaluation['suspicious_logs'])) {
            // HTTP 알림 전송
            $alertSent = sendHttpAlert($evaluation['suspicious_logs'], $httpAlertConfig);
            
            // 평가 결과 저장
            saveEvaluationResults($evaluation);
            
            http_response_code(200);
            echo json_encode([
                'status' => 'suspicious_detected',
                'message' => 'Suspicious logs detected and alert sent',
                'suspicious_count' => $evaluation['analysis_results']['suspicious_count'],
                'total_logs' => count($filteredLogs),
                'suspicious_logs' => $evaluation['suspicious_logs'],
                'normal_logs' => $evaluation['normal_logs'],
                'analysis_results' => $evaluation['analysis_results'],
                'alert_sent' => $alertSent
            ]);
        } else {
            http_response_code(200);
            echo json_encode([
                'status' => 'no_suspicious',
                'message' => 'No suspicious logs detected',
                'total_logs' => count($filteredLogs),
                'normal_logs' => $evaluation['normal_logs'],
                'analysis_results' => $evaluation['analysis_results']
            ]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'error' => 'Failed to analyze logs',
            'message' => $e->getMessage()
        ]);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request method']);
}

/**
 * 이메일 알림을 보내는 함수
 * @param array $suspiciousLogs 의심스러운 로그 배열
 * @return bool 이메일 전송 성공 여부
 */
/*
function sendEmailAlert($suspiciousLogs, $config) {
    if (!$config['enabled'] || empty($suspiciousLogs)) {
        return false;
    }
    
    // 이메일 제목
    $attackTypes = [];
    foreach ($suspiciousLogs as $log) {
        foreach ($log['details'] as $detail) {
            if (!in_array($detail['attackType'], $attackTypes)) {
                $attackTypes[] = $detail['attackType'];
            }
        }
    }
    
    $subject = $config['subject_prefix'] . count($suspiciousLogs) . ' suspicious logs detected - ' . implode(', ', array_slice($attackTypes, 0, 3));
    if (count($attackTypes) > 3) {
        $subject .= ' and ' . (count($attackTypes) - 3) . ' more';
    }
    
    // 이메일 본문 구성
    $message = "Security Alert: " . count($suspiciousLogs) . " suspicious log entries detected.\n\n";
    $message .= "Attack types detected: " . implode(', ', $attackTypes) . "\n\n";
    $message .= "Suspicious logs:\n";
    
    foreach ($suspiciousLogs as $index => $log) {
        $message .= "\n--- Log #" . ($index + 1) . " ---\n";
        $message .= "Log: " . $log['log'] . "\n";
        $message .= "Detected attacks:\n";
        
        foreach ($log['details'] as $detail) {
            $message .= "- Type: " . $detail['attackType'] . "\n";
            $message .= "  Details: " . $detail['attackDetails'] . "\n";
            $message .= "  Pattern: " . $detail['pattern'] . "\n";
        }
    }
    
    $message .= "\n\nThis is an automated message from the Log Analyzer System.";
    
    // 이메일 헤더
    $headers = "From: " . $config['from'] . "\r\n";
    $headers .= "Reply-To: " . $config['from'] . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    
    // 이메일 전송
    return mail($config['to'], $subject, $message, $headers);
}
*/
?>

