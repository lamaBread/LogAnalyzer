<?php

include_once '__authCode.php';  
include_once '__pre_processing.php';  // Log file reading and grouping by IP.

if (!isset($_POST['question'])) {  // 해당 변수가 없으면 잘못된 접근.
    http_response_code(400);
    die('invalid access');
}

// $_POST['question'] = 'hello?';  // For testing.

// $log_file_path = '../LOG/combine_error.log';  // Log file path
$log_file_path = '../LOG/test_log';  // Log file path. for testing.
$logs = readLogFileToArray($log_file_path);  // Read log file to array.
$logs_string = implode("\n", $logs);  // Convert log array to string.

$question = htmlspecialchars($_POST['question'], ENT_QUOTES, 'UTF-8');  // 사용자 입력 이스케이프.
$prompt = "Here is the log context:\n" . $logs_string . "\n\nQuestion: " . $question;

$response = queryLLM($prompt);

header('Content-Type: application/json');
echo json_encode(md(nl2br($response['response'])));  // HTML로 전환하여 출력.


