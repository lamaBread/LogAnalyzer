<?php

include_once '__authCode.php';  

include_once '__pre_processing.php';  // Log file reading and grouping by IP.

if (!isset($_POST['question'])) {  // 해당 변수가 없으면 잘못된 접근.
    http_response_code(400);
    die('invalid access');
}

$log_file_path = '../LOG/combine_error.log';  // Log file path
$logs = readLogFileToArray($log_file_path);

$question = htmlspecialchars($_POST['question'], ENT_QUOTES, 'UTF-8');  // 사용자 입력 이스케이프.
// $question = $_GET['question'];  // 시험용 임시 코드.

$results = [];  // 결과를 저장할 배열.

foreach ($logs as $log) {
    if (strpos($log, $question) !== false) {  // 검색.
        $results[] = $log;
    }
}

header('Content-Type: application/json');
echo json_encode($results);