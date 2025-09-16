<?php
    require_once __DIR__ . '/../config/db.php';
    header('Content-Type: application/json');

    $user = require_auth();
    if (!isset($_FILES['scan']) || !isset($_POST['essay_id'])){
        http_response_code(400); echo json_encode(['error'=>'Arquivo e essay_id são obrigatórios']); exit;
    }

    $essay_id = intval($_POST['essay_id']);
    $chk = $pdo->prepare('SELECT id FROM essays WHERE id = ? AND user_id = ?');
    $chk->execute([$essay_id, $user['id']]);
    if (!$chk->fetch()){ http_response_code(403); echo json_encode(['error'=>'Você só pode enviar imagem para a sua própria redação']); exit; }

    $f = $_FILES['scan'];
    if ($f['error'] !== UPLOAD_ERR_OK){ http_response_code(400); echo json_encode(['error'=>'Falha no upload']); exit; }

    $allowed = ['image/jpeg'=>'jpg','image/png'=>'png'];
    $mime = mime_content_type($f['tmp_name']);
    if (!isset($allowed[$mime])){ http_response_code(400); echo json_encode(['error'=>'Formato não suportado. Envie JPG ou PNG.']); exit; }

    $ext = $allowed[$mime];
    $dir = __DIR__ . '/../public/uploads/';
    if (!is_dir($dir)) mkdir($dir, 0775, true);
    $name = 'scan_' . $essay_id . '_' . time() . '.' . $ext;
    $path = $dir . $name;

    if (!move_uploaded_file($f['tmp_name'], $path)){ http_response_code(500); echo json_encode(['error'=>'Não foi possível salvar o arquivo']); exit; }

    $url = '/uploads/' . $name; // path relativo a /public
    $upd = $pdo->prepare('UPDATE essays SET scan_path = ? WHERE id = ?');
    $upd->execute([$url, $essay_id]);

    echo json_encode(['ok'=>true, 'scan_path'=>$url]);
