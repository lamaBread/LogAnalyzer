<?php
// 종류별 로그파일을 읽어오는 코드를 작성.

if (php_sapi_name() !== 'cli') {
    exit(1);
}

include_once 'functions.php';

$logDir = '/webLogs';
$outputFileAccess = '/var/www/html/combine_access.log';
$outputFileError = '/var/www/html/combine_error.log';

// docker-compose 실행시마다 기존 파일 삭제.
if (file_exists($outputFileAccess)) {
    unlink($outputFileAccess);
}
if (file_exists($outputFileError)) {
    unlink($outputFileError);
}

// 30분마다 로그파일을 읽어와 병합.
while (true) {
    aggregateLogs($logDir, $outputFileAccess, '/access.log*');
    aggregateLogs($logDir, $outputFileError, '/error.log*');
    sleep(1800); // 30 minutes
}