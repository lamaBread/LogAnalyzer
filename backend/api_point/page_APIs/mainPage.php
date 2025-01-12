<?php

$authKey = 'A?5Ql1qpU9MQA?r'; // 인증 키 설정 (유출 금지! 이 값은 서버사이드에서만 볼 수 있어야 함. 클라이언트에 노출되면 안됨.)

$output = <<<EOD
    <!-- API 작동 검증 용도의 html 문서입니다. -->
    <div className="mainText">
        <h2>Wellcome to Log Analyzer</h2>
        <p>Log Analyzer is a tool that helps you analyze log files.</p>
        <p>It is a simple tool that can be used to analyze log files.</p>
        <p>Today's Server Status are <strong>Good</strong></p>
    </div>
EOD;

$statusValue = '1';

// POST 요청을 받고 정해진 텍스트를 JSON으로 반환하는 API.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 인증 키 확인
    if (!isset($_SERVER['HTTP_X_AUTH_KEY']) || $_SERVER['HTTP_X_AUTH_KEY'] !== $authKey) {
        http_response_code(403);
        die('Forbidden');
    }

    $data = array('mainText' => $output, 'statusValue' => $statusValue);
    header('Content-Type: application/json');
    http_response_code(200);
    echo json_encode($data);
    exit();
} else {
    http_response_code(400);
    die('Invalid request');
}
