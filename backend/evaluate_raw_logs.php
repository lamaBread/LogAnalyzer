<?php
// Evaluate raw logs for suspicious activities and send email alerts
include '__functions.php';
include '__regex_match_function.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");


?>