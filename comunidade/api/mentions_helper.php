<?php
function extract_mentions($text) {
    preg_match_all('/@([A-Za-z0-9_.]+)/u', $text, $matches);
    $usernames = array_unique($matches[1] ?? []);
    return $usernames;
}

function create_notifications_for_mentions($pdo, $actor_user_id, $content, $post_id, $comment_id = null) {
    $mentions = extract_mentions($content);
    if (empty($mentions)) return;

    $in  = str_repeat('?,', count($mentions) - 1) . '?';
    $stmt = $pdo->prepare("SELECT id, nome_usuario FROM usuarios WHERE nome_usuario IN ($in)");
    $stmt->execute($mentions);
    $rows = $stmt->fetchAll();

    foreach ($rows as $row) {
        if (intval($row['id']) === intval($actor_user_id)) continue;

        $type = $comment_id ? 'mention_comment' : 'mention_post';
        $ins = $pdo->prepare('
            INSERT INTO notifications (user_id, actor_user_id, type, source_post_id, source_comment_id, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ');
        $ins->execute([$row['id'], $actor_user_id, $type, $post_id, $comment_id]);
    }
}
