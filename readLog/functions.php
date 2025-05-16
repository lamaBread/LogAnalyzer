<?php

// $pattern 에 맟는 파일을 읽어서 $outputFile 에 병합하는 함수.
function aggregateLogs($logDir, $outputFile, $logRoot, $pattern) {
    if (!is_dir($logDir)) {
        //echo "Log directory not found: $logDir\n";
        return [];
    }

    $files = glob($logDir . $pattern);
    $output = "";
    $newLogs = []; // 새 로그를 저장할 배열
    
    // 기존 파일 내용 읽기 (없으면 빈 문자열)
    $previousContent = file_exists($outputFile) ? file_get_contents($outputFile) : "";
    
    foreach ($files as $file) {
        $output .= "--- " . basename($file) . " ---\n";
        
        // 파일 내용 가져오기
        $fileContent = "";
        if (substr($file, -3) === '.gz') {
            $fileContent = gzfile_get_contents($file);
        } else {
            $fileContent = file_get_contents($file);
        }
        
        // 로그 라인별로 처리
        $lines = explode("\n", $fileContent);
        foreach ($lines as $line) {
            $line = trim($line);
            // 빈 줄 건너뛰기
            if ($line === '') continue;
            
            // 이 로그 행이 기존 출력 파일에 없으면 새 로그로 간주
            if (strpos($previousContent, $line) === false) {
                // 헤더 라인이 아닌 경우만 새 로그로 추가
                if (strpos($line, '---') !== 0) {
                    $newLogs[] = $line;
                }
            }
        }
        
        $output .= $fileContent;
        $output .= "\n";
    }

    // 파일에 쓰기
    if (file_put_contents($outputFile, $output) !== false) {
        readLogs_log(true, $files, $logRoot);
    } else {
        readLogs_log(false, $files, $logRoot);
    }
    
    // 중복 제거 및 빈 줄 필터링
    $newLogs = array_unique($newLogs);
    $newLogs = array_filter($newLogs, function($line) {
        return !empty(trim($line));
    });
    
    // 식별된 새 로그 반환
    return $newLogs;
}

function gzfile_get_contents($file) {
    $gz = gzopen($file, 'r');
    $content = '';
    while (!gzeof($gz)) {
        $content .= gzread($gz, 4096);
    }
    gzclose($gz);
    return $content;
}

function readLogs_log($is_success, $files, $logRoot){
    $log = $logRoot . "/readLogs.log";
    $log_content = date("Y-m-d H:i:s");
    if($is_success){
        $log_content .= " - Success - ".implode(", ", $files)."\n";
    } else {
        $log_content .= " - Fail - ".implode(", ", $files)."\n";
    }
    file_put_contents($log, $log_content, FILE_APPEND);
}

// 파일 변경 감지를 위한 함수
function monitorLogFiles($logDir, $outputFileAccess, $outputFileError, $logRoot) {
    $accessPattern = $logDir . '/access.log*';
    $errorPattern = $logDir . '/error.log*';
    
    $lastAccessFiles = [];
    $lastErrorFiles = [];
    $lastAccessModTime = [];
    $lastErrorModTime = [];
    
    // 초기 파일 상태 기록
    $accessFiles = glob($accessPattern);
    $errorFiles = glob($errorPattern);
    
    foreach ($accessFiles as $file) {
        $lastAccessFiles[] = $file;
        $lastAccessModTime[$file] = filemtime($file);
    }
    
    foreach ($errorFiles as $file) {
        $lastErrorFiles[] = $file;
        $lastErrorModTime[$file] = filemtime($file);
    }

    // 초기 병합 실행 및 새 로그 수집
    $initialAccessLogs = aggregateLogs($logDir, $outputFileAccess, $logRoot, '/access.log*');
    $initialErrorLogs = aggregateLogs($logDir, $outputFileError, $logRoot, '/error.log*');
    
    // 초기 로그 전송
    $initialLogs = array_merge($initialAccessLogs, $initialErrorLogs);
    if (!empty($initialLogs)) {
        echo "Sending " . count($initialLogs) . " initial logs for evaluation...\n";
        $result = sendLogsForEvaluation($initialLogs);
        echo "Result: " . ($result ? "Success" : "Failed") . "\n";
    }
    
    echo "Initial log files processed. Monitoring for changes...\n";
    
    // 변경 감지 루프
    while (true) {
        $changed = false;
        
        // 액세스 로그 파일 변경 확인
        $currentAccessFiles = glob($accessPattern);
        foreach ($currentAccessFiles as $file) {
            // 새 파일 확인
            if (!in_array($file, $lastAccessFiles)) {
                $changed = true;
                $lastAccessFiles[] = $file;
                $lastAccessModTime[$file] = filemtime($file);
            } 
            // 기존 파일 수정 확인
            elseif (filemtime($file) > $lastAccessModTime[$file]) {
                $changed = true;
                $lastAccessModTime[$file] = filemtime($file);
            }
        }
        
        // 에러 로그 파일 변경 확인
        $currentErrorFiles = glob($errorPattern);
        foreach ($currentErrorFiles as $file) {
            // 새 파일 확인
            if (!in_array($file, $lastErrorFiles)) {
                $changed = true;
                $lastErrorFiles[] = $file;
                $lastErrorModTime[$file] = filemtime($file);
            } 
            // 기존 파일 수정 확인
            elseif (filemtime($file) > $lastErrorModTime[$file]) {
                $changed = true;
                $lastErrorModTime[$file] = filemtime($file);
            }
        }
        
        // 변경사항이 있으면 로그 병합 실행 및 새 로그 분석
        if ($changed) {
            echo "Log file changes detected. Updating combined logs...\n";
            
            // 로그 병합 및 새 로그 직접 식별
            $newAccessLogs = aggregateLogs($logDir, $outputFileAccess, $logRoot, '/access.log*');
            $newErrorLogs = aggregateLogs($logDir, $outputFileError, $logRoot, '/error.log*');
            
            // 새 로그 합치기
            $newLogs = array_merge($newAccessLogs, $newErrorLogs);
            
            // 새 로그가 있으면 평가 API로 전송
            if (!empty($newLogs)) {
                echo "Found " . count($newLogs) . " new log entries for evaluation.\n";
                echo "Sending sample: " . print_r(array_slice($newLogs, 0, 3), true) . "...\n";
                $result = sendLogsForEvaluation($newLogs);
                echo "Result: " . ($result ? "Success" : "Failed") . "\n";
            } else {
                echo "No new logs to evaluate.\n";
            }
        }
        
        // 짧은 간격으로 확인 (CPU 과부하 방지)
        sleep(10);
    }
}

/**
 * 새로운 로그를 평가 스크립트로 전송하는 함수
 * @param array $logs 평가할 로그 배열
 */
function sendLogsForEvaluation($logs) {
    // php-apache 컨테이너 입장에서는, 마운트 포인트가 /var/www/html 이다.

    $url = 'http://php-apache/alertDisplayPage/log_evaluate.php';
    
    // curl 초기화
    $ch = curl_init($url);

    // POST 요청 설정
    curl_setopt($ch, CURLOPT_POST, 1);
    // 배열을 JSON으로 변환하여 전송
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['logs' => $logs]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    // 요청 실행
    $response = curl_exec($ch);
    
    // 오류 확인
    if (curl_errno($ch)) {
        echo "Error sending logs to evaluation API: " . curl_error($ch) . "\n";
        $success = false;
    } else {
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $responseData = json_decode($response, true);
        
        if ($httpCode == 200) {
            if (isset($responseData['success']) && $responseData['success'] === true) {
                echo "Logs successfully sent to evaluation API.\n";
                echo "Processed: " . $responseData['total_processed'] . " logs, ";
                echo "Suspicious: " . $responseData['suspicious_count'] . " logs.\n";
                $success = true;
            } else {
                echo "API returned an error:\n";
                if (isset($responseData['error'])) {
                    echo "Error: " . $responseData['error'] . "\n";
                }
                if (isset($responseData['error_details'])) {
                    echo "Details: " . $responseData['error_details'] . "\n";
                }
                $success = false;
            }
        } else {
            echo "API returned HTTP code: " . $httpCode . "\n";
            if (isset($responseData['error'])) {
                echo "Error message: " . $responseData['error'] . "\n";
            } else {
                echo "Raw response: " . $response . "\n";
            }
            $success = false;
        }
    }
    
    // curl 세션 종료
    curl_close($ch);
    
    return $success;
}
?>