<?php
// 종류별 로그파일을 읽어오는 코드를 작성.

if (php_sapi_name() !== 'cli') {
    exit(1);
}

include_once 'functions.php';

$logDir = '/webLogs';
$logRoot = '/var/www/html/LOG';
$outputFileAccess = $logRoot . '/combine_access.log';
$outputFileError = $logRoot . '/combine_error.log';

// docker-compose 실행시마다 기존 파일 삭제.
if (file_exists($outputFileAccess)) {
    unlink($outputFileAccess);
}
if (file_exists($outputFileError)) {
    unlink($outputFileError);
}

// 로그 디렉토리가 존재하는지 확인
if (!is_dir($logDir)) {
    echo "Log directory not found: $logDir\n";
    exit(1);
}

// 로그 저장 디렉토리 확인 및 생성
if (!is_dir($logRoot)) {
    if (!mkdir($logRoot, 0755, true)) {
        echo "Failed to create log directory: $logRoot\n";
        exit(1);
    }
}

echo "Starting log file monitoring...\n";

// 파일 변동을 감지하여 로그 병합
monitorLogFiles($logDir, $outputFileAccess, $outputFileError, $logRoot);