<?php
// Evaluate raw logs for suspicious activities and send email alerts
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

/**
 * 개별 로그를 평가하는 함수
 * @param string $logEntry 평가할 로그 항목
 * @return array 평가 결과 [isSuspicious(bool), details(array)]
 */
function evaluateSingleLog($logEntry) {
    // 정규식 패턴 로드
    $patterns = readAttackRegexPatterns();
    
    // 로그 평가
    $matchResults = isLogSuspiciousFullScan($logEntry, $patterns);
    
    // 결과 반환
    return [
        'isSuspicious' => !empty($matchResults),
        'details' => $matchResults
    ];
}

/**
 * 이메일 알림을 보내는 함수
 * @param array $suspiciousLogs 의심스러운 로그 배열
 * @return bool 이메일 전송 성공 여부
 */
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
    $logContent .= "Total logs: " . count($evaluationResults) . "\n";
    
    $suspiciousCount = 0;
    foreach ($evaluationResults as $result) {
        if ($result['isSuspicious']) {
            $suspiciousCount++;
        }
    }
    
    $logContent .= "Suspicious logs: $suspiciousCount\n\n";
    
    foreach ($evaluationResults as $index => $result) {
        if ($result['isSuspicious']) {
            $logContent .= "=== Suspicious Log #$index ===\n";
            $logContent .= "Log: " . $result['log'] . "\n";
            $logContent .= "Attack details:\n";
            
            foreach ($result['details'] as $detail) {
                $logContent .= "- Type: " . $detail['attackType'] . "\n";
                $logContent .= "  Details: " . $detail['attackDetails'] . "\n";
                $logContent .= "  Pattern: " . $detail['pattern'] . "\n";
            }
            
            $logContent .= "\n";
        }
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
        
        // 각 로그 평가
        $evaluationResults = [];
        $suspiciousLogs = [];
        
        foreach ($logs as $logEntry) {
            $logEntry = trim($logEntry);
            if (empty($logEntry) || strpos($logEntry, '---') === 0) {
                continue; // 빈 로그나 파일 구분자 스킵
            }
            
            $result = evaluateSingleLog($logEntry);
            
            // 평가 결과 저장
            $evaluationResult = [
                'log' => $logEntry,
                'isSuspicious' => $result['isSuspicious'],
                'details' => $result['details']
            ];
            
            $evaluationResults[] = $evaluationResult;
            
            // 의심스러운 로그 수집
            if ($result['isSuspicious']) {
                $suspiciousLogs[] = [
                    'log' => $logEntry,
                    'details' => $result['details']
                ];
            }
        }
        
        // 의심스러운 로그가 있으면 이메일 알림
        if (!empty($suspiciousLogs)) {
            $emailSent = sendEmailAlert($suspiciousLogs, $emailConfig);
            
            // 평가 결과 저장
            saveEvaluationResults($evaluationResults);
            
            http_response_code(200);
            echo json_encode([
                'status' => 'suspicious_detected',
                'message' => 'Suspicious logs detected and alert sent',
                'suspicious_count' => count($suspiciousLogs),
                'total_logs' => count($logs),
                'email_sent' => $emailSent
            ]);
        } else {
            http_response_code(200);
            echo json_encode([
                'status' => 'no_suspicious',
                'message' => 'No suspicious logs detected',
                'total_logs' => count($logs)
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
?>
