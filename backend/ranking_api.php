<?php
session_start();
require_once __DIR__ . '/../autenticar/config.php';

header('Content-Type: application/json; charset=utf-8');

function json_response(bool $ok, $payload, int $status = 200): void {
    http_response_code($status);
    if ($ok) {
        echo json_encode(['ok' => true, 'data' => $payload], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(['ok' => false, 'error' => $payload], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

if (!isset($_SESSION['usuario'])) {
    json_response(false, 'Não autenticado.', 401);
}

$userId = (int)$_SESSION['usuario']['id'];
$action = $_GET['action'] ?? 'all';

if ($action !== 'all') {
    json_response(false, 'Ação inválida.', 400);
}

try {
    $tz = new DateTimeZone('America/Sao_Paulo');

    $today = new DateTime('today', $tz);
    $weekStart = clone $today;
    $weekStart->modify('monday this week')->setTime(0, 0, 0);

    $weekEnd = clone $weekStart;
    $weekEnd->modify('sunday this week')->setTime(23, 59, 59);

    $prevWeekStart = clone $weekStart;
    $prevWeekStart->modify('-7 days');

    $prevWeekEnd = clone $weekEnd;
    $prevWeekEnd->modify('-7 days');

    $ws  = $weekStart->format('Y-m-d H:i:s');
    $we  = $weekEnd->format('Y-m-d H:i:s');
    $pws = $prevWeekStart->format('Y-m-d H:i:s');
    $pwe = $prevWeekEnd->format('Y-m-d H:i:s');

    $stmt = $conn->prepare("
        SELECT id, nome_usuario, nome_completo, foto_perfil
        FROM usuarios
        WHERE id = ?
    ");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $resUser = $stmt->get_result();
    $user = $resUser->fetch_assoc();
    $stmt->close();

    if (!$user) {
        json_response(false, 'Usuário não encontrado.', 404);
    }

    $sqlCurrent = "
        SELECT
            u.id,
            u.nome_usuario,
            COALESCE(lp.lessons_count, 0)   AS lessons_week,
            COALESCE(qp.questions_count, 0) AS questions_week,
            COALESCE(po.posts_count, 0)     AS posts_week,
            COALESCE(li.likes_count, 0)     AS likes_week
        FROM usuarios u
        LEFT JOIN (
            SELECT user_id, COUNT(*) AS lessons_count
            FROM lesson_progress
            WHERE completed = 1
              AND completed_at BETWEEN ? AND ?
            GROUP BY user_id
        ) lp ON lp.user_id = u.id
        LEFT JOIN (
            -- AQUI É A CORREÇÃO: CONTAR LINHAS, NÃO SOMAR questions_count=0
            SELECT user_id, COUNT(*) AS questions_count
            FROM question_progress
            WHERE completed = 1
              AND completed_at BETWEEN ? AND ?
            GROUP BY user_id
        ) qp ON qp.user_id = u.id
        LEFT JOIN (
            SELECT user_id, COUNT(*) AS posts_count
            FROM posts
            WHERE created_at BETWEEN ? AND ?
            GROUP BY user_id
        ) po ON po.user_id = u.id
        LEFT JOIN (
            SELECT p.user_id AS user_id, COUNT(l.id) AS likes_count
            FROM likes l
            INNER JOIN posts p ON p.id = l.post_id
            WHERE l.created_at BETWEEN ? AND ?
            GROUP BY p.user_id
        ) li ON li.user_id = u.id
    ";

    $stmt = $conn->prepare($sqlCurrent);
    $stmt->bind_param('ssssssss', $ws, $we, $ws, $we, $ws, $we, $ws, $we);
    $stmt->execute();
    $resultCurrent = $stmt->get_result();

    $currentRows = [];
    while ($row = $resultCurrent->fetch_assoc()) {
        $currentRows[$row['id']] = $row;
    }
    $stmt->close();

    $sqlPrev = "
        SELECT
            u.id,
            COALESCE(lp.lessons_count, 0)   AS lessons_week,
            COALESCE(qp.questions_count, 0) AS questions_week,
            COALESCE(po.posts_count, 0)     AS posts_week,
            COALESCE(li.likes_count, 0)     AS likes_week
        FROM usuarios u
        LEFT JOIN (
            SELECT user_id, COUNT(*) AS lessons_count
            FROM lesson_progress
            WHERE completed = 1
              AND completed_at BETWEEN ? AND ?
            GROUP BY user_id
        ) lp ON lp.user_id = u.id
        LEFT JOIN (
            -- MESMA CORREÇÃO PARA A SEMANA ANTERIOR
            SELECT user_id, COUNT(*) AS questions_count
            FROM question_progress
            WHERE completed = 1
              AND completed_at BETWEEN ? AND ?
            GROUP BY user_id
        ) qp ON qp.user_id = u.id
        LEFT JOIN (
            SELECT user_id, COUNT(*) AS posts_count
            FROM posts
            WHERE created_at BETWEEN ? AND ?
            GROUP BY user_id
        ) po ON po.user_id = u.id
        LEFT JOIN (
            SELECT p.user_id AS user_id, COUNT(l.id) AS likes_count
            FROM likes l
            INNER JOIN posts p ON p.id = l.post_id
            WHERE l.created_at BETWEEN ? AND ?
            GROUP BY p.user_id
        ) li ON li.user_id = u.id
    ";

    $stmt = $conn->prepare($sqlPrev);
    $stmt->bind_param('ssssssss', $pws, $pwe, $pws, $pwe, $pws, $pwe, $pws, $pwe);
    $stmt->execute();
    $resultPrev = $stmt->get_result();

    $prevScores = [];
    while ($row = $resultPrev->fetch_assoc()) {
        $scorePrev = $row['lessons_week'] * 25
                   + $row['questions_week'] * 10
                   + $row['posts_week'] * 5
                   + $row['likes_week'] * 1;
        $prevScores[$row['id']] = $scorePrev;
    }
    $stmt->close();

    $leaderboard = [];
    foreach ($currentRows as $id => $row) {
        $score = $row['lessons_week'] * 25
               + $row['questions_week'] * 10
               + $row['posts_week'] * 5
               + $row['likes_week'] * 1;

        $prevScore = $prevScores[$id] ?? 0;
        $trend = $score - $prevScore;

        $leaderboard[] = [
            'id'             => (int)$id,
            'name'           => $row['nome_usuario'],
            'score'          => (int)$score,
            'trend'          => (int)$trend,
            'lessons_week'   => (int)$row['lessons_week'],
            'questions_week' => (int)$row['questions_week'],
            'posts_week'     => (int)$row['posts_week'],
            'likes_week'     => (int)$row['likes_week']
        ];
    }

    usort($leaderboard, function ($a, $b) {
        if ($a['score'] === $b['score']) {
            return strcmp($a['name'], $b['name']);
        }
        return $b['score'] <=> $a['score'];
    });

    $myStats = [
        'lessons'   => 0,
        'questions' => 0,
        'posts'     => 0,
        'likes'     => 0
    ];

    foreach ($leaderboard as $item) {
        if ($item['id'] === $userId) {
            $myStats['lessons']   = $item['lessons_week'];
            $myStats['questions'] = $item['questions_week'];
            $myStats['posts']     = $item['posts_week'];
            $myStats['likes']     = $item['likes_week'];
            break;
        }
    }

    $goalLessons   = 4;
    $goalQuestions = 4;
    $goalPosts     = 10;

    $goals = [
        [
            'key'           => 'lessons',
            'title'         => 'Aulas assistidas nesta semana',
            'current_value' => $myStats['lessons'],
            'target_value'  => $goalLessons,
            'color'         => '#FFE32D'
        ],
        [
            'key'           => 'questions',
            'title'         => 'Questões respondidas nesta semana',
            'current_value' => $myStats['questions'],
            'target_value'  => $goalQuestions,
            'color'         => '#FF79B0'
        ],
        [
            'key'           => 'posts',
            'title'         => 'Posts na comunidade nesta semana',
            'current_value' => $myStats['posts'],
            'target_value'  => $goalPosts,
            'color'         => '#4CC2FF'
        ]
    ];

    $badges = [];

    $addBadge = function (string $key, string $title, string $icon, bool $earned) use (&$badges) {
        $badges[] = [
            'key'       => $key,
            'title'     => $title,
            'icon'      => $icon,
            'earned_at' => $earned ? date('Y-m-d') : null
        ];
    };

    // Aulas
    $addBadge('lesson_1', 'Assistiu 1 aula nesta semana', '📘', $myStats['lessons'] >= 1);
    $addBadge('lesson_4', 'Completou 4 aulas nesta semana', '🎓', $myStats['lessons'] >= 4);

    // Questões
    $addBadge('question_1', 'Respondeu 1 questão nesta semana', '❓', $myStats['questions'] >= 1);
    $addBadge('question_4', 'Respondeu 4 questões nesta semana', '🧠', $myStats['questions'] >= 4);

    // Posts
    $addBadge('post_1', 'Fez 1 post na comunidade nesta semana', '💬', $myStats['posts'] >= 1);
    $addBadge('post_10', 'Fez 10 posts na comunidade nesta semana', '🔥', $myStats['posts'] >= 10);

    // Engajamento (likes)
    $addBadge('likes_5', 'Recebeu 5 curtidas nesta semana', '👍', $myStats['likes'] >= 5);
    $addBadge('likes_20', 'Recebeu 20 curtidas nesta semana', '🌟', $myStats['likes'] >= 20);

    $payload = [
        'user' => [
            'id'           => (int)$user['id'],
            'nome_usuario' => $user['nome_usuario'],
            'nome_completo'=> $user['nome_completo'],
            'foto_perfil'  => $user['foto_perfil']
        ],
        'leaderboard' => $leaderboard,
        'goals'       => $goals,
        'badges'      => $badges,
        'week'        => [
            'start' => $weekStart->format('Y-m-d'),
            'end'   => $weekEnd->format('Y-m-d')
        ]
    ];

    json_response(true, $payload);
} catch (Throwable $e) {
    json_response(false, 'Erro no servidor: ' . $e->getMessage(), 500);
}
