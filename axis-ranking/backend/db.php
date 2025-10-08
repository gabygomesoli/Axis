<?php
function get_pdo() {
  $cfg = require __DIR__ . '/config.php';
  $dsn = "mysql:host={$cfg['db_host']};dbname={$cfg['db_name']};charset={$cfg['db_charset']}";
  $opt = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
  ];
  return new PDO($dsn, $cfg['db_user'], $cfg['db_pass'], $opt);
}
