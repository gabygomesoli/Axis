<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
require_once __DIR__ . '/../config.php';

function latest_row($mysqli, $table) {
  $sql = "SELECT * FROM $table ORDER BY created_at DESC, id DESC LIMIT 7";
  $res = $mysqli->query($sql);
  $rows = [];
  if ($res) {
    while ($r = $res->fetch_assoc()) { $rows[] = $r; }
  }
  return $rows;
}

$lessons = latest_row($mysqli, 'lessons_week');
$posts = latest_row($mysqli, 'posts_week');

$qres = $mysqli->query("SELECT * FROM questions_week ORDER BY created_at DESC, id DESC LIMIT 1");
$questions = $qres ? $qres->fetch_assoc() : ['this_week'=>0,'last_week'=>0,'created_at'=>date('Y-m-d')];

echo json_encode([
  'lessons_week' => $lessons,
  'posts_week' => $posts,
  'questions_week' => $questions
], JSON_UNESCAPED_UNICODE);

$qres = $mysqli->query("SELECT * FROM questions_week ORDER BY created_at DESC, id DESC LIMIT 1");
$questions = null;
if ($qres) { $questions = $qres->fetch_assoc(); }
if (!$questions) {
  $questions = ['this_week'=>0,'last_week'=>0,'created_at'=>date('Y-m-d')];
}

?>
