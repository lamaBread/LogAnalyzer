<?php

include_once '__authCode.php';  // POST 검증 & 인증코드 검증
include_once '__pre_processing.php';  // readLogFileToArray, groupLogsByIP, groupLogsByStatusCode 함수 포함

try {
    $logArray = readLogFileToArray('../LOG/test_log_access');  // 로그 파일을 배열로 읽어옴
    // print_r($logArray);

    $prompt = "get logs and please make a security report within 300 words. Logs:" . implode("\n", $logArray);
    $output = queryLLM($prompt);

    if (!isset($output)) {
        http_response_code(500);
        $errorData = array('error' => 'An error occurred while processing the request.', 'details' => 'LLM API returned null.');
        echo json_encode($errorData);
        exit();
    }

    $result_html = nl2br($output['response']);  

    $statusValue = 0;  // 0: 보고서 미평가, 1: 안전, 2: 주의, 3: 위험

    /*
    // 개발중...
    $statusEvaluation = queryLLM_evaluateReport($output);
    if (!is_null($statusEvaluation)) {
        $statusValue = $statusEvaluation;
    }
    */
    $data = array('mainText' => $result_html, 'statusValue' => $statusValue);

    http_response_code(200);
    echo json_encode($data);

} catch (Exception $e) {  // 추후 오류보고 비활성화 할 것.
    http_response_code(500);
    $errorData = array('error' => 'An error occurred while processing the request.', 'details' => $e->getMessage());
    echo json_encode($errorData);
}
exit();
