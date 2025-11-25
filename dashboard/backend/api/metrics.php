<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['usuario']['id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Não autenticado.']);
    exit;
}

require_once __DIR__ . '/../config.php';

$userId = (int) $_SESSION['usuario']['id'];

date_default_timezone_set('America/Sao_Paulo');

$today    = new DateTimeImmutable('today');
$w        = (int)$today->format('N'); // 1 (seg) a 7 (dom)
$monday   = $today->modify('-' . ($w - 1) . ' days')->format('Y-m-d');
$sunday   = $today->modify('+' . (7 - $w) . ' days')->format('Y-m-d');
$lastMon  = date('Y-m-d', strtotime($monday . ' -7 days'));
$lastSun  = date('Y-m-d', strtotime($sunday . ' -7 days'));

$lessons = [];
$stmt = $mysqli->prepare("
    SELECT WEEKDAY(completed_at) AS wd, COUNT(*) AS total
    FROM lesson_progress
    WHERE user_id = ?
      AND completed = 1
      AND completed_at BETWEEN ? AND ?
    GROUP BY WEEKDAY(completed_at)
    ORDER BY WEEKDAY(completed_at)
");
$stmt->bind_param('iss', $userId, $monday, $sunday);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $lessons[] = [
        'weekday'    => (int)$row['wd'] + 1,
        'lessons'    => (int)$row['total'],
        'created_at' => $monday
    ];
}
$stmt->close();

$posts = [];
$stmt = $mysqli->prepare("
    SELECT WEEKDAY(created_at) AS wd, COUNT(*) AS total
    FROM posts
    WHERE user_id = ?
      AND created_at BETWEEN ? AND ?
    GROUP BY WEEKDAY(created_at)
    ORDER BY WEEKDAY(created_at)
");
$stmt->bind_param('iss', $userId, $monday, $sunday);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $posts[] = [
        'weekday'    => (int)$row['wd'] + 1,
        'posts'      => (int)$row['total'],
        'created_at' => $monday
    ];
}
$stmt->close();

$stmt = $mysqli->prepare("
    SELECT
      SUM(CASE WHEN completed = 1 AND completed_at BETWEEN ? AND ? THEN 1 ELSE 0 END) AS this_week,
      SUM(CASE WHEN completed = 1 AND completed_at BETWEEN ? AND ? THEN 1 ELSE 0 END) AS last_week
    FROM question_progress
    WHERE user_id = ?
");
$stmt->bind_param('ssssi', $monday, $sunday, $lastMon, $lastSun, $userId);
$stmt->execute();
$res = $stmt->get_result();
$questions = $res->fetch_assoc() ?: ['this_week' => 0, 'last_week' => 0];
$stmt->close();

echo json_encode([
    'lessons_week'   => $lessons,
    'posts_week'     => $posts,
    'questions_week' => [
        'this_week' => (int)$questions['this_week'],
        'last_week' => (int)$questions['last_week'],
        'created_at'=> $monday
    ]
], JSON_UNESCAPED_UNICODE);