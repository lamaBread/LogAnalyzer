<?php

include_once '__pre_processing.php';  // Log file reading and grouping by IP.

$_POST['question'] = 'hello?';  // For testing.

$log_file_path = '../LOG/test_log';  // Log file path
$logs = readLogFileToArray($log_file_path);  // Read log file to array.
$logs_string = implode("\n", $logs);  // Convert log array to string.

$question = htmlspecialchars($_POST['question'], ENT_QUOTES, 'UTF-8');  // 사용자 입력 이스케이프.
$prompt = "Here is the log context:\n" . $logs_string . "\n\nQuestion: " . $question;

$response = queryLLM($prompt);

header('Content-Type: application/json');
echo json_encode($response);
