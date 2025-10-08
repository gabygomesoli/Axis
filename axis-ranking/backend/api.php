<?php
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/db.php';
$pdo = get_pdo();

$action = $_GET['action'] ?? 'all';
$userId = intval($_GET['user_id'] ?? 4); // usuário "Você" por padrão

function json_ok($data){ echo json_encode(['ok'=>true, 'data'=>$data], JSON_UNESCAPED_UNICODE); exit; }
function json_err($msg){ http_response_code(400); echo json_encode(['ok'=>false, 'error'=>$msg], JSON_UNESCAPED_UNICODE); exit; }

if ($action === 'leaderboard' || $action === 'all') {
  $stmt = $pdo->query("SELECT id, name, username, score, trend FROM users ORDER BY score DESC, id ASC LIMIT 50");
  $leaderboard = $stmt->fetchAll();
}

if ($action === 'goals' || $action === 'all') {
  $sql = "SELECT g.id, g.title, g.target_value, gp.current_value, g.color
          FROM goals g
          JOIN goal_progress gp ON gp.goal_id = g.id AND gp.user_id = :uid
          ORDER BY g.id";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([':uid' => $userId]);
  $goals = $stmt->fetchAll();
}

if ($action === 'badges' || $action === 'all') {
  $sql = "SELECT b.id, b.title, b.icon, ub.earned_at
          FROM badges b
          LEFT JOIN user_badges ub ON ub.badge_id = b.id AND ub.user_id = :uid
          ORDER BY b.position";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([':uid' => $userId]);
  $badges = $stmt->fetchAll();
}

if ($action === 'user' || $action === 'all') {
  $stmt = $pdo->prepare("SELECT id, name, username FROM users WHERE id = :uid");
  $stmt->execute([':uid' => $userId]);
  $user = $stmt->fetch();
}

if ($action === 'leaderboard') json_ok(['leaderboard' => $leaderboard]);
if ($action === 'goals')       json_ok(['goals' => $goals]);
if ($action === 'badges')      json_ok(['badges' => $badges]);
if ($action === 'user')        json_ok(['user' => $user]);

json_ok([
  'user'        => $user,
  'leaderboard' => $leaderboard,
  'goals'       => $goals,
  'badges'      => $badges
]);
