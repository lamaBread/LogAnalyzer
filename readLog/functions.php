<?php

// $pattern 에 맟는 파일을 읽어서 $outputFile 에 병합하는 함수.
function aggregateLogs($logDir, $outputFile, $logRoot, $pattern) {
    if (!is_dir($logDir)) {
        //echo "Log directory not found: $logDir\n";
        return;
    }

    $files = glob($logDir . $pattern);
    $output = "";

    foreach ($files as $file) {
        $output .= "--- " . basename($file) . " ---\n";
        if (substr($file, -3) === '.gz') {
            $output .= gzfile_get_contents($file);  // 압축파일은 압축 풀고 병합.
        } else {
            $output .= file_get_contents($file);  //압축파일이 아니면 file_get_contents로 병합.
        }
        $output .= "\n";
    }

    // Write to the output file.
    if (file_put_contents($outputFile, $output) !== false) {
        readLogs_log(true, $files, $logRoot);
    } else {
        readLogs_log(false, $files, $logRoot);
    }
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
    
    // 로그 처리 추적을 위한 변수
    $processedAccessLines = 0;
    $processedErrorLines = 0;
    
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
    
    // 초기 병합 실행
    aggregateLogs($logDir, $outputFileAccess, $logRoot, '/access.log*');
    aggregateLogs($logDir, $outputFileError, $logRoot, '/error.log*');
    
    // 초기 처리된 라인 수 기록
    if (file_exists($outputFileAccess)) {
        $processedAccessLines = count(file($outputFileAccess));
    }
    
    if (file_exists($outputFileError)) {
        $processedErrorLines = count(file($outputFileError));
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
            
            // 병합 전 현재 라인 수 저장
            $previousAccessLines = $processedAccessLines;
            $previousErrorLines = $processedErrorLines;
            
            // 로그 병합
            aggregateLogs($logDir, $outputFileAccess, $logRoot, '/access.log*');
            aggregateLogs($logDir, $outputFileError, $logRoot, '/error.log*');
            
            // 새 로그 분석을 위한 로그 수 계산
            $currentAccessLines = 0;
            $currentErrorLines = 0;
            
            if (file_exists($outputFileAccess)) {
                $currentAccessLines = count(file($outputFileAccess));
            }
            
            if (file_exists($outputFileError)) {
                $currentErrorLines = count(file($outputFileError));
            }
            
            // 새 로그가 있는지 확인
            $newAccessLogs = [];
            $newErrorLogs = [];
            
            if ($currentAccessLines > $previousAccessLines) {
                // 새 접근 로그 추출
                $accessLogContents = file($outputFileAccess);
                $newAccessLogs = array_slice($accessLogContents, $previousAccessLines);
                echo "Found " . count($newAccessLogs) . " new access log entries.\n";
            }
            
            if ($currentErrorLines > $previousErrorLines) {
                // 새 에러 로그 추출
                $errorLogContents = file($outputFileError);
                $newErrorLogs = array_slice($errorLogContents, $previousErrorLines);
                echo "Found " . count($newErrorLogs) . " new error log entries.\n";
            }
            
            // 새 로그가 있으면 평가 API로 전송
            $newLogs = array_merge($newAccessLogs, $newErrorLogs);
            if (!empty($newLogs)) {
                echo "Sending " . count($newLogs) . " new logs for evaluation...\n";
                sendLogsForEvaluation($newLogs);
            }
            
            // 처리된 라인 수 업데이트
            $processedAccessLines = $currentAccessLines;
            $processedErrorLines = $currentErrorLines;
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
    $url = 'http://localhost:8445/APIs/alertDisplayPage/log_evaluate.php'; // 평가 스크립트 URL
    
    // curl 초기화
    $ch = curl_init($url);
    
    // POST 요청 설정
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, ['logs' => $logs]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    // 요청 실행
    $response = curl_exec($ch);
    
    // 오류 확인
    if (curl_errno($ch)) {
        echo "Error sending logs to evaluation API: " . curl_error($ch) . "\n";
        $success = false;
    } else {
        echo "Logs successfully sent to evaluation API.\n";
        $success = true;
    }
    
    // curl 세션 종료
    curl_close($ch);
    
    return $success;
}
?>