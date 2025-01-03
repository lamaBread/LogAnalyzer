<?php
include_once '__init.php';

/*
// POST 요청 여부 확인
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(400);
    die('Invalid request');
}
*/

if(isset($_POST['password'])){
    if(!authenticate($_POST['password'])){  // 두 변수가 모두 존재할 때만 인증 시작.
        http_response_code(403);
        die('Forbidden');
    }
} else {
    http_response_code(400);
    die('Invalid request');
}
// 인증 성공.

$path = '/php_data';

// Check if the directory exists and is readable using shell command
$checkDir = shell_exec("ls $path 2>&1");
if (strpos($checkDir, 'No such file or directory') !== false) {
    http_response_code(500);
    die('Directory not accessible');
}

// 입력받은 경로 내부의 모든 로그 파일을 찾아내어, 하나의 변수에 저장
$logContents = '';
$files = shell_exec("ls $path/access.log* 2>/dev/null");
//$files .= shell_exec("ls $path/error.log* 2>/dev/null");

if ($files !== null) {
    $filesArray = explode("\n", trim($files));
} else {
    $filesArray = [];
}

// 배열의 내용을 전부 출력
echo "files: ";
print_r($filesArray);
echo "\n";

foreach ($filesArray as $file) {
    if (!empty($file)) {
        $logContents .= shell_exec("cat $file") . "\n";
        echo escapeshellarg($file) . "\n";
    }
}

// 로그 파일 내용을 출력
echo "log: ".$logContents;

?>
