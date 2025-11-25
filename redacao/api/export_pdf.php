<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../vendor/fpdf.php';

$redacao_id = intval($_GET['redacao_id'] ?? 0);
if ($redacao_id <= 0) {
    http_response_code(400);
    echo 'redacao_id inválido';
    exit;
}

$stmt = $pdo->prepare('
    SELECT r.*, u.nome_completo AS name, u.nome_usuario AS username
    FROM redacoes r
    JOIN usuarios u ON u.id = r.user_id
    WHERE r.id = ?
');
$stmt->execute([$redacao_id]);
$e = $stmt->fetch();

if (!$e) {
    http_response_code(404);
    echo 'Redação não encontrada';
    exit;
}

$pdf = new FPDF('P', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetFont('Courier', '', 12);

$left       = 20;
$top        = 20;
$right      = 190;
$bottom     = 277;
$lineHeight = 8;

$pdf->SetXY($left, $top);
for ($i = 0; $i < 30; $i++) {
    $y = $top + $i * $lineHeight;
    $pdf->_out('0 0 1 RG'); // azul
    $pdf->Line($left, $y, $right, $y);
}

$pdf->_out('1 0 0 RG');
$pdf->Line($left + 10, $top - 5, $left + 10, $bottom);

$pdf->_out('0 0 0 RG');

$pdf->Text(
    $left,
    $top - 6,
    utf8_decode($e['titulo'] . ' — @' . $e['username'])
);

$lines = preg_split('/\r?\n/', $e['texto']);
$y     = $top + 6;
foreach ($lines as $i => $ln) {
    if ($i >= 30) break;
    $pdf->Text($left + 14, $y + $i * $lineHeight, utf8_decode($ln));
}

header('Content-Disposition: attachment; filename="redacao_' . $redacao_id . '.pdf"');
$pdf->Output('I', 'redacao_' . $redacao_id . '.pdf');