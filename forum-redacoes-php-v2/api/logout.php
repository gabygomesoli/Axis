<?php 
    header('Content-Type: application/json'); 
    require_once __DIR__.'/../config/db.php'; 
    session_destroy(); echo json_encode(['ok'=>true]);