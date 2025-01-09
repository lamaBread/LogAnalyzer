#!/usr/bin/env php
<?php

$logDir = '/var/log/apache2';
$outputFileAccess = '/var/www/html/combine_access.log';
$outputFileError = '/var/www/html/combine_error.log';

if (php_sapi_name() !== 'cli') {
    //echo "This script must be run from the command line.\n";
    exit(1);
}

//echo "Starting log aggregation...\n";

while (true) {
    aggregateLogs($logDir, $outputFileAccess, '/access.log*');
    aggregateLogs($logDir, $outputFileError, '/error.log*');
    sleep(1800); // 30 minutes
}

function aggregateLogs($logDir, $outputFile, $pattern) {
    if (!is_dir($logDir)) {
        //echo "Log directory not found: $logDir\n";
        return;
    }

    $files = glob($logDir . $pattern);
    $output = "";

    foreach ($files as $file) {
        $output .= "--- " . basename($file) . " ---\n";
        $output .= file_get_contents($file);
        $output .= "\n";
    }

    if (file_put_contents($outputFile, $output) !== false) {
        readLogs_log(true);
    } else {
        readLogs_log(false);
    }
    
}

function readLogs_log($is_success){
    $log = "readLogs.log";
    $log_content = date("Y-m-d H:i:s")." - ";
    if($is_success){
        $log_content .= "Logs aggregated successfully.\n";
    } else {
        $log_content .= "Failed to write to output file.\n";
    }
    file_put_contents($log, $log_content, FILE_APPEND);
}
?>
