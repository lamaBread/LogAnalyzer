<?php
// 필요한 다양한 함수를 현재 파일에 추가할 것.

function authenticate($inputPassword) {  // 비밀번호 인증 함수. 비밀번호가 맞으면 true, 틀리면 false 반환.
    $configFile = '.pass'; 
    if (!file_exists($configFile)) {
        return false;
    }

    $hashedPassword = trim(file($configFile)[0]);
    return password_verify($inputPassword, $hashedPassword);
}