<?php
return [
  'db_host' => getenv('DB_HOST') ?: '127.0.0.1',
  'db_name' => getenv('DB_NAME') ?: 'axis_ranking',
  'db_user' => getenv('DB_USER') ?: 'root',
  'db_pass' => getenv('DB_PASS') ?: '', // sempre lembrar de mudar essa senha pq o mysql da escola tem a senha root
  'db_charset' => 'utf8mb4'
];
