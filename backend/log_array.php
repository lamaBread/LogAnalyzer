<?php
// 모든 로그를 배열로 반환.
include '__functions.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $filePath = $_POST['filePath'] ?? '';
        if ($filePath) {
            $logArray = readLogFileToArray($filePath);
            http_response_code(200);
            echo json_encode($logArray);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'File path is required']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to process logs', 'message' => $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request method']);
}
?>
