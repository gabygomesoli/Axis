<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

$method = $_SERVER['REQUEST_METHOD'];

function suggest_scores($text){
    // Heurísticas simples para demo (0-200 por competência)
    $lines = preg_split('/\r?\n/', trim($text));
    $chars = mb_strlen($text);
    $words = preg_split('/\s+/', trim($text));
    $uniq = count(array_unique(array_map('mb_strtolower',$words)));
    $punct = preg_match_all('/[\.,;:!?]/u', $text);
    $c1 = min(200, 80 + intdiv($uniq, 5) + ($chars>1000?50:0)); // domínio da norma (vocabulário)
    $c2 = min(200, 80 + intdiv(count($lines), 2) + ($chars>900?30:0)); // compreensão (extensão/coesão básica)
    $c3 = min(200, 80 + $punct*2); // seleção/organização de argumentos (pontuação como proxy)
    $c4 = min(200, 80 + intdiv($chars, 30)); // coesão (tamanho como proxy)
    $c5 = min(200, 80 + (strpos(mb_strtolower($text),'proposta')!==false?40:0) + (strpos(mb_strtolower($text),'interven')!==false?40:0)); // proposta de intervenção
    return [max(40,$c1),max(40,$c2),max(40,$c3),max(40,$c4),max(40,$c5)];
}

if ($method === 'GET') {
    $essay_id = intval($_GET['essay_id'] ?? 0);
    if ($essay_id <= 0){ http_response_code(400); echo json_encode(['error'=>'essay_id inválido']); exit; }
    $s = $pdo->prepare('SELECT * FROM essay_scores WHERE essay_id = ?');
    $s->execute([$essay_id]);
    $row = $s->fetch();
    if (!$row){
        // sugere automaticamente se ainda não existe
        $e = $pdo->prepare('SELECT content FROM essays WHERE id = ?');
        $e->execute([$essay_id]);
        $essay = $e->fetch();
        if (!$essay){ http_response_code(404); echo json_encode(['error'=>'Redação não encontrada']); exit; }
        list($a1,$a2,$a3,$a4,$a5) = suggest_scores($essay['content']);
        echo json_encode(['scores'=>null, 'auto'=>['comp1'=>$a1,'comp2'=>$a2,'comp3'=>$a3,'comp4'=>$a4,'comp5'=>$a5]]);
        exit;
    }
    echo json_encode(['scores'=>$row]);
    exit;
}

if ($method === 'POST') {
    $user = require_auth();
    $data = require_json();
    $essay_id = intval($data['essay_id'] ?? 0);
    if ($essay_id <= 0){ http_response_code(400); echo json_encode(['error'=>'essay_id inválido']); exit; }

    $c1 = intval($data['comp1'] ?? 0);
    $c2 = intval($data['comp2'] ?? 0);
    $c3 = intval($data['comp3'] ?? 0);
    $c4 = intval($data['comp4'] ?? 0);
    $c5 = intval($data['comp5'] ?? 0);

    // busca sugestões auto
    $e = $pdo->prepare('SELECT content FROM essays WHERE id = ?');
    $e->execute([$essay_id]);
    $essay = $e->fetch();
    if (!$essay){ http_response_code(404); echo json_encode(['error'=>'Redação não encontrada']); exit; }
    list($a1,$a2,$a3,$a4,$a5) = suggest_scores($essay['content']);

    $total = ($c1+$c2+$c3+$c4+$c5);
    $stmt = $pdo->prepare('INSERT INTO essay_scores (essay_id, comp1, comp2, comp3, comp4, comp5, auto_comp1, auto_comp2, auto_comp3, auto_comp4, auto_comp5, total)
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                           ON DUPLICATE KEY UPDATE comp1=VALUES(comp1), comp2=VALUES(comp2), comp3=VALUES(comp3), comp4=VALUES(comp4), comp5=VALUES(comp5),
                               auto_comp1=VALUES(auto_comp1), auto_comp2=VALUES(auto_comp2), auto_comp3=VALUES(auto_comp3), auto_comp4=VALUES(auto_comp4), auto_comp5=VALUES(auto_comp5), total=VALUES(total)');
    $stmt->execute([$essay_id, $c1, $c2, $c3, $c4, $c5, $a1, $a2, $a3, $a4, $a5, $total]);

    echo json_encode(['ok'=>true, 'total'=>$total, 'auto'=>['comp1'=>$a1,'comp2'=>$a2,'comp3'=>$a3,'comp4'=>$a4,'comp5'=>$a5]]);
    exit;
}

http_response_code(405);
echo json_encode(['error'=>'Método não suportado']);
