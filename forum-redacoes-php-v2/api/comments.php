<?php
header('Content-Type: application/json');
    require_once __DIR__ . '/../config/db.php'; 
    $m=$_SERVER['REQUEST_METHOD'];
    if($m==='GET'){ $id=intval($_GET['essay_id']??0);
    if($id<=0){ http_response_code(400);
      echo json_encode(['error'=>'essay_id inválido']); 
    exit; }
      $s=$pdo->prepare('SELECT c.id,c.content,c.created_at,u.name,u.username,u.avatar_url FROM essay_comments c JOIN users u ON u.id=c.user_id WHERE c.essay_id=? ORDER BY c.created_at ASC');
      $s->execute([$id]);
      echo json_encode(['comments'=>$s->fetchAll()]); 
    exit; }
      if($m==='POST'){ $u=require_auth(); 
        $d=require_json();
        $id=intval($d['essay_id']??0);
        $ct=trim($d['content']??''); 
      if($id<=0||$ct===''){ http_response_code(400);
        echo json_encode(['error':'Dados inválidos']);
      exit; }
        $x=$pdo->prepare('SELECT id FROM essays WHERE id=?'); 
        $x->execute([$id]);
      if(!$x->fetch()){ http_response_code(404); 
        echo json_encode(['error'=>'Redação não encontrada']); exit; }
        $pdo->prepare('INSERT INTO essay_comments (essay_id,user_id,content) VALUES (?,?,?)')->execute([$id,$u['id'],$ct]); 
        echo json_encode(['ok'=>true]); exit; }
       http_response_code(405); echo json_encode(['error'=>'Método não suportado']);