<?php
// 본 파일은 실행시 인자로 받은 비밀번호를 해싱하여, '.pass' 파일에 저장합니다. ('.pass'파일이 존재하지 않을 경우 생성합니다.)

//CLI 에서만 실행될 수 있도록 예외처리. (보안)
if (php_sapi_name() !== 'cli') {
    http_response_code(400);
    die('Invalid request');
}

if ($argc !== 2) {
    echo "Usage: php ./__password_maker.php <password>\n";
    exit(1);
}

$password = $argv[1];
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

echo "Hashed Password: " . $hashedPassword . "\n";

// 해싱된 PW를 파일에 저장.
$file = './.pass';
file_put_contents($file, $hashedPassword . PHP_EOL);

echo "Password saved to " . $file . "\n";