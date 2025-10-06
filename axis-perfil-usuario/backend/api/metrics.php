<?php
require_once __DIR__.'/../config.php';
$user_id = intval($_GET['user_id'] ?? 1);

$usePython = isset($_GET['py']) ? boolval($_GET['py']) : false;

if ($usePython) {
  $cmd = 'python3 '.escapeshellarg(__DIR__.'/../python/compute_metrics.py').' '.intval($user_id);
  $out = shell_exec($cmd);
  if ($out) {
    header('Content-Type: application/json; charset=utf-8');
    echo $out;
    exit;
  }
}

// fallback via SQL
$w = db()->prepare('SELECT weekday, lessons, exercises FROM lessons_log WHERE user_id=? ORDER BY weekday');
$w->execute([$user_id]);
$lessons = array_fill(1,7,0);
$ex = array_fill(1,7,0);
while($r=$w->fetch()){
  $lessons[(int)$r['weekday']] = (int)$r['lessons'];
  $ex[(int)$r['weekday']] = (int)$r['exercises'];
}
json_out(['weekday'=>[1,2,3,4,5,6,7],'lessons'=>$lessons,'exercises'=>$ex]);
