<?php
require_once __DIR__.'/../config.php';
$user_id = intval($_GET['user_id'] ?? 1);

$u = db()->prepare('SELECT id,name,username,email,role,points,avatar_path FROM users WHERE id=?');
$u->execute([$user_id]);
$user = $u->fetch();
if (!$user) json_out(['error'=>'User not found'], 404);

$p = db()->prepare('SELECT cep,street,number,city FROM user_profile WHERE user_id=?');
$p->execute([$user_id]);
$profile = $p->fetch() ?: ['cep'=>'','street'=>'','number'=>'','city'=>''];

json_out(['user'=>$user, 'profile'=>$profile]);
