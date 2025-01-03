<?php
include_once '__init.php';

// POST 요청 여부 확인
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(400);
    die('Invalid request');
}

if(isset($_POST['password']) && isset($_POST['path'])){
    if(!authenticate($_POST['password'])){  // 두 변수가 모두 존재할 때만 인증 시작.
        http_response_code(403);
        die('Forbidden');
    }
} else {
    http_response_code(400);
    die('Invalid request');
}
// 인증 성공.

$path = $_POST['path'];
//echo "path: ".$path . "\n";

/*
// 입력받은 경로 내부의 모든 로그 파일을 찾아내어, 하나의 변수에 저장
$logContents = '';
$files = glob($path . 'access.log*');
$files = array_merge($files, glob($path . 'error.log*'));

// 배열의 내용을 전부 출력
echo "files: ";
print_r($files);
echo "\n";

foreach ($files as $file) {
    echo "111";
    //$logContents .= file_get_contents($file) . "\n";
    $logContents .= shell_exec('cat ' . escapeshellarg($file)) . "\n";
    echo escapeshellarg($file) . "\n";
}

// 로그 파일 내용을 출력
echo "log: ".$logContents;
*/
?>
