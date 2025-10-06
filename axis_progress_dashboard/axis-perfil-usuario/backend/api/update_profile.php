<?php
require_once __DIR__.'/../config.php';
$body = json_decode(file_get_contents('php://input'), true) ?? [];
$user_id = intval($body['user_id'] ?? 0);
if (!$user_id) json_out(['error'=>'user_id required'], 400);

$name = trim($body['name'] ?? '');
$username = trim($body['username'] ?? '');
$cep = trim($body['cep'] ?? '');
$street = trim($body['street'] ?? '');
$number = trim($body['number'] ?? '');
$city = trim($body['city'] ?? '');

db()->beginTransaction();
try {
  if ($name || $username) {
    $stmt = db()->prepare('UPDATE users SET name=?, username=? WHERE id=?');
    $stmt->execute([$name, $username, $user_id]);
  }
  $stmt = db()->prepare('INSERT INTO user_profile (user_id, cep, street, number, city)
                         VALUES (?,?,?,?,?)
                         ON DUPLICATE KEY UPDATE cep=VALUES(cep), street=VALUES(street), number=VALUES(number), city=VALUES(city)');
  $stmt->execute([$user_id,$cep,$street,$number,$city]);
  db()->commit();
  json_out(['ok'=>true]);
} catch(Exception $e){
  db()->rollBack();
  json_out(['error'=>$e->getMessage()], 500);
}
