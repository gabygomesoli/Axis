<?php
require_once __DIR__.'/../config.php';
$user_id = intval($_POST['user_id'] ?? 0);
if (!$user_id || !isset($_FILES['avatar'])) json_out(['error'=>'missing data'], 400);

$uploadDir = __DIR__.'/../uploads/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

$f = $_FILES['avatar'];
$ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
$fname = 'avatar_'.$user_id.'_'.time().'.'.$ext;
$target = $uploadDir.$fname;

if (!in_array($ext, ['jpg','jpeg','png','webp'])) json_out(['error'=>'invalid extension'], 400);
if (!move_uploaded_file($f['tmp_name'], $target)) json_out(['error'=>'move failed'], 500);

$relPath = 'backend/uploads/'.$fname;
$stmt = db()->prepare('UPDATE users SET avatar_path=? WHERE id=?');
$stmt->execute([$relPath, $user_id]);

json_out(['ok'=>true, 'avatar_path'=>$relPath]);
