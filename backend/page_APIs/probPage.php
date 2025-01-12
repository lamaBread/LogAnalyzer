<?php

include_once '__authCode.php';  // POST 검증 & 인증코드 검증

$output = <<<EOD
    <!-- API 작동 검증 용도의 html 문서입니다. -->
    <div className="mainText">
        <h2>probable attack page</h2>
    </div>
EOD;

$statusValue = '1';

$data = array('mainText' => $output, 'statusValue' => $statusValue);
header('Content-Type: application/json');
http_response_code(200);
echo json_encode($data);
exit();
