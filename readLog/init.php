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

// 추후 로그파일을 읽어오는 모듈을 작성할 것. POST 요청을 받아서, 새로운 로그로 기존 로그를 갱신하는 모듈.

// 30분마다 로그파일을 읽어와 병합.
while (true) {
    aggregateLogs($logDir, $outputFileAccess, $logRoot, '/access.log*');
    aggregateLogs($logDir, $outputFileError, $logRoot, '/error.log*');
    sleep(1800); // 30 minutes
}