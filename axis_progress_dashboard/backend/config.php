<?php
// backend/config.php
$DB_HOST = getenv('DB_HOST') ?: 'localhost';
$DB_USER = getenv('DB_USER') ?: 'root';
$DB_PASS = getenv('DB_PASS') ?: '';
$DB_NAME = getenv('DB_NAME') ?: 'axis_dashboard';

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_errno) {
  http_response_code(500);
  echo json_encode([ 'error' => 'DB connection failed', 'detail' => $mysqli->connect_error ]);
  exit;
}
$mysqli->set_charset('utf8mb4');
?>
