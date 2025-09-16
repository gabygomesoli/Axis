<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../vendor/fpdf.php';

$essay_id = intval($_GET['essay_id'] ?? 0);
if ($essay_id <= 0) {
    http_response_code(400);
    echo 'essay_id inválido';
    exit;
}

$stmt = $pdo->prepare('
    SELECT e.*, u.name, u.username
    FROM essays e
    JOIN users u ON u.id = e.user_id
    WHERE e.id = ?
');
$stmt->execute([$essay_id]);
$e = $stmt->fetch();

if (!$e) {
    http_response_code(404);
    echo 'Redação não encontrada';
    exit;
}

// Renderiza folha de almaço no PDF
$pdf = new FPDF('P', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetFont('Courier', '', 12);

// Dimensões básicas
$left        = 20;
$top         = 20;
$right       = 190;
$bottom      = 277;
$lineHeight  = 8;   // 30 linhas cabem em A4 com margens
$cols        = 66;

// Linhas horizontais azuis
$pdf->SetXY($left, $top);
for ($i = 0; $i < 30; $i++) {
    $y = $top + $i * $lineHeight;

    // stroke color blue
    $pdf->_out(sprintf('0 0 1 RG'));
    $pdf->Line($left, $y, $right, $y);
}

// Linha vermelha (margem esquerda)
$pdf->_out(sprintf('1 0 0 RG'));
$pdf->Line($left + 10, $top - 5, $left + 10, $bottom);

// Volta para preto
$pdf->_out('0 0 0 RG');
$pdf->SetXY($left + 14, $top - 12);
$pdf->Text($left + 14, $top - 12, '');

// Cabeçalho simples (título + autor)
$pdf->Text(
    $left,
    $top - 6,
    utf8_decode($e['title'] . ' — @' . $e['username'])
);

// Escreve o conteúdo limitado a 66 colunas já normalizado
$lines = preg_split('/\r?\n/', $e['content']);
$y     = $top + 6;

foreach ($lines as $i => $ln) {
    if ($i >= 30) break;
    $pdf->Text($left + 14, $y + $i * $lineHeight, utf8_decode($ln));
}

// Saída do PDF
header('Content-Disposition: attachment; filename="redacao_' . $essay_id . '.pdf"');
$pdf->Output('I', 'redacao_' . $essay_id . '.pdf');
