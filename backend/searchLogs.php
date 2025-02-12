<?php
// 모든 로그에 대하여 문자열 검색 결과 반환.
include '__functions.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!isset($_POST['question'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Question is required']);
            exit;
        }

        $log_file_path = './LOG/combine_error.log';  // Log file path
        $logs = readLogFileToArray($log_file_path);

        $question = htmlspecialchars($_POST['question'], ENT_QUOTES, 'UTF-8');  // 사용자 입력 이스케이프.
        $results = [];  // 결과를 저장할 배열.

        foreach ($logs as $log) {
            if (strpos($log, $question) !== false) {  // 검색.
                $results[] = $log;
            }
        }

        http_response_code(200);
        echo json_encode($results);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to search logs', 'message' => $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request method']);
}
?>