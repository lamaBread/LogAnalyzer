<?php

// 모든 로그를 읽어들여서 배열로 반환하는 함수.
// 하나의 행을 하나의 원소로 삼아서 1차원 배열로 반환.
function readLogFileToArray($filePath) {  // Example usage    $logFilePath = '/path/to/your/logfile.log';   print_r($logArray);
    $lines = [];
    if (file_exists($filePath)) {
        $file = fopen($filePath, "r");
        if ($file) {
            while (($line = fgets($file)) !== false) {
                $trimmedLine = trim($line);
                if ($trimmedLine !== '' && strpos($trimmedLine, '---') !== 0) {  // Skip empty lines and lines that start with '---'
                    $lines[] = $trimmedLine;
                }
            }
            fclose($file);
        } else {
            echo "Error opening the file.";
        }
    } else {
        echo "File does not exist.";
    }
    return $lines;
}

// 로그 배열을 IP 주소별로 그룹화하는 함수.
// IP 주소를 추출하여 그룹화하고, IP 주소가 없는 경우 'unknown'으로 처리.
// Key: IP 주소 / value: 해당 IP 주소를 가지는 로그 배열.
function groupLogsByIP($logArray) {
    $groupedLogs = [];
    foreach ($logArray as $log) {
        // Extract the IP address from the log line
        if (preg_match('/\[client ([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+):[0-9]+\]/', $log, $matches)) {
            $ip = $matches[1];
        } else {
            $ip = 'unknown';
        }
        if (!isset($groupedLogs[$ip])) {
            $groupedLogs[$ip] = [];
        }
        $groupedLogs[$ip][] = $log;
    }
    return $groupedLogs;
}

// 로그 배열을 상태코드별로 그룹화하는 함수.
// 상태코드를 추출하여 그룹화하고, 상태코드가 없는 경우 'unknown'으로 처리.
// Key: 상태코드 / value: 해당 상태코드를 가지는 로그 배열.
function groupLogsByStatusCode($logArray) {
    $groupedLogs = [];
    foreach ($logArray as $log) {
        // Extract the status code from the log line
        if (preg_match('/" [0-9]{3} /', $log, $matches)) {
            $statusCode = trim($matches[0], '" ');
        } else {
            $statusCode = 'unknown';
        }
        if (!isset($groupedLogs[$statusCode])) {
            $groupedLogs[$statusCode] = [];
        }
        $groupedLogs[$statusCode][] = $log;
    }
    return $groupedLogs;
}

// 로그 배열을 100 단위의 상태코드로 그룹화하는 함수.
// 상태코드를 추출하여 100 단위로 그룹화하고, 상태코드가 없는 경우 'unknown'으로 처리.
// Key: 100 단위 상태코드 / value: 해당 상태코드를 가지는 로그 배열.
function groupLogsByStatusCodeRange($logArray) {
    $groupedLogs = [];
    foreach ($logArray as $log) {
        // Extract the status code from the log line
        if (preg_match('/" ([0-9]{3}) /', $log, $matches)) {
            $statusCode = (int)$matches[1];
            $statusCodeRange = floor($statusCode / 100) * 100;
        } else {
            $statusCodeRange = 'unknown';
        }
        if (!isset($groupedLogs[$statusCodeRange])) {
            $groupedLogs[$statusCodeRange] = [];
        }
        $groupedLogs[$statusCodeRange][] = $log;
    }
    return $groupedLogs;
}