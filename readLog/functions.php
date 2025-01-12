<?php

// $pattern 에 맟는 파일을 읽어서 $outputFile 에 병합하는 함수.
function aggregateLogs($logDir, $outputFile, $logRoot, $pattern) {
    if (!is_dir($logDir)) {
        //echo "Log directory not found: $logDir\n";
        return;
    }

    $files = glob($logDir . $pattern);
    $output = "";

    foreach ($files as $file) {
        $output .= "--- " . basename($file) . " ---\n";
        if (substr($file, -3) === '.gz') {
            $output .= gzfile_get_contents($file);  // 압축파일은 압축 풀고 병합.
        } else {
            $output .= file_get_contents($file);  //압축파일이 아니면 file_get_contents로 병합.
        }
        $output .= "\n";
    }

    // Write to the output file.
    if (file_put_contents($outputFile, $output) !== false) {
        readLogs_log(true, $files, $logRoot);
    } else {
        readLogs_log(false, $files, $logRoot);
    }
}

function gzfile_get_contents($file) {
    $gz = gzopen($file, 'r');
    $content = '';
    while (!gzeof($gz)) {
        $content .= gzread($gz, 4096);
    }
    gzclose($gz);
    return $content;
}

function readLogs_log($is_success, $files, $logRoot){
    $log = $logRoot . "/readLogs.log";
    $log_content = date("Y-m-d H:i:s");
    if($is_success){
        $log_content .= " - Success - ".implode(", ", $files)."\n";
    } else {
        $log_content .= " - Fail - ".implode(", ", $files)."\n";
    }
    file_put_contents($log, $log_content, FILE_APPEND);
}
?>