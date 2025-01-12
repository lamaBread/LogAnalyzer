<?php

die();

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

function analyze_for_mainPage($logArray) {
    $data_out = array(
        "model" => "llama3.2-vision",
        "prompt" => "get logs, and please make security report. Logs:" . json_encode($logArray),
        "stream" => false,
    );

    $ollama_url = "http://ollama:11434/api/generate";

    $ch = curl_init($ollama_url);
    $jsonData = json_encode($data_out);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($jsonData)
    ]);

    $response = curl_exec($ch);
    if ($response === FALSE) {
        echo "Error analyzing logs: " . curl_error($ch);
        curl_close($ch);
        return null;
    }

    curl_close($ch);
    $result = json_decode($response, true);

    if (isset($result['report'])) {
        return $result['report'];
    } else {
        return "No report generated.";
    }
}

// test code
$logFilePath = '../LOG/combine_error.log';   
$logArray = readLogFileToArray($logFilePath);
echo '<pre>';
print_r($logArray);
echo '</pre>';
echo '<br><br><br>';
$groupedLogs = groupLogsByIP($logArray);
echo '<pre>';
print_r($groupedLogs);
echo '</pre>';
echo '<br><br><br>';
$securityReport = analyze_for_mainPage($logArray);
echo '<pre>';
echo $securityReport;
echo '</pre>';


