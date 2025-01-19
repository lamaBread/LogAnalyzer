<?php
// 임시 인증 키. 추후 다른 방법을 고민해 보아야 함.
$authKey = 'A?5Ql1qpU9MQA?r'; // 인증 키 설정 (유출 금지! 이 값은 서버사이드에서만 볼 수 있어야 함. 클라이언트에 노출되면 안됨.)

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    die();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(400);
    die('Invalid request');
}

if (!isset($_SERVER['HTTP_X_AUTH_KEY']) || $_SERVER['HTTP_X_AUTH_KEY'] !== $authKey) {// 인증 키 확인
    http_response_code(403);
    die('Forbidden');
}