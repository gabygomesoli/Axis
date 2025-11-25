<?php
session_start();

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../autenticar/config.php';

function json_resp(bool $ok, $data, int $status = 200) {
    http_response_code($status);
    if ($ok) {
        echo json_encode(['ok' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(['ok' => false, 'message' => $data], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

if (!isset($_SESSION['usuario'])) {
    json_resp(false, 'Não autenticado.', 401);
}

$userId = (int)$_SESSION['usuario']['id'];
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $categoria    = $_GET['categoria']    ?? '';
        $subcategoria = $_GET['subcategoria'] ?? '';

        if ($categoria === '' || $subcategoria === '') {
            json_resp(false, 'Parâmetros inválidos.', 400);
        }

        $sqlLessons = "
            SELECT lesson_key, completed
            FROM lesson_progress
            WHERE user_id = ? AND categoria = ? AND subcategoria = ?
        ";
        $stmt = $conn->prepare($sqlLessons);
        $stmt->bind_param('iss', $userId, $categoria, $subcategoria);
        $stmt->execute();
        $res = $stmt->get_result();

        $lessons = [];
        while ($row = $res->fetch_assoc()) {
            $lessons[$row['lesson_key']] = (bool)$row['completed'];
        }
        $stmt->close();

        $sqlQuestions = "
            SELECT completed, questions_count
            FROM question_progress
            WHERE user_id = ? AND categoria = ? AND subcategoria = ?
            LIMIT 1
        ";
        $stmt = $conn->prepare($sqlQuestions);
        $stmt->bind_param('iss', $userId, $categoria, $subcategoria);
        $stmt->execute();
        $resQ = $stmt->get_result();
        $questionsRow = $resQ->fetch_assoc();
        $stmt->close();

        $questions = [
            'completed'       => $questionsRow ? (bool)$questionsRow['completed'] : false,
            'questions_count' => $questionsRow ? (int)$questionsRow['questions_count'] : 0
        ];

        json_resp(true, [
            'lessons'   => $lessons,
            'questions' => $questions
        ]);
    }

    if ($method === 'POST') {
        $body = json_decode(file_get_contents('php://input'), true);

        if (!is_array($body)) {
            json_resp(false, 'JSON inválido.', 400);
        }

        $tipo         = $body['tipo']         ?? null;
        $categoria    = $body['categoria']    ?? null;
        $subcategoria = $body['subcategoria'] ?? null;
        $lessonKey    = $body['lesson_key']   ?? null;
        $completed    = isset($body['completed']) ? (int)$body['completed'] : 0;

        if (!$tipo || !$categoria || !$subcategoria) {
            json_resp(false, 'Dados obrigatórios ausentes.', 400);
        }

        $today = date('Y-m-d');
        $completedAt = $completed ? $today : null;

        if ($tipo === 'lesson') {
            if (!$lessonKey) {
                json_resp(false, 'lesson_key é obrigatório para tipo=lesson.', 400);
            }

            $sql = "
                INSERT INTO lesson_progress (user_id, categoria, subcategoria, lesson_key, completed, completed_at)
                VALUES (?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    completed    = VALUES(completed),
                    completed_at = VALUES(completed_at)
            ";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                'isssis',
                $userId,
                $categoria,
                $subcategoria,
                $lessonKey,
                $completed,
                $completedAt
            );
            $stmt->execute();
            $stmt->close();

            json_resp(true, ['message' => 'Progresso de aula atualizado.']);
        }

        if ($tipo === 'questions') {
            $questionsCount = $completed ? 1 : 0;

            $sql = "
                INSERT INTO question_progress (user_id, categoria, subcategoria, completed, questions_count, completed_at)
                VALUES (?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    completed       = VALUES(completed),
                    questions_count = VALUES(questions_count),
                    completed_at    = VALUES(completed_at)
            ";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                'issiis',
                $userId,
                $categoria,
                $subcategoria,
                $completed,
                $questionsCount,
                $completedAt
            );
            $stmt->execute();
            $stmt->close();

            json_resp(true, ['message' => 'Progresso de questões atualizado.']);
        }

        json_resp(false, 'Tipo de progresso inválido.', 400);
    }

    json_resp(false, 'Método não suportado.', 405);

} catch (Throwable $e) {
    json_resp(false, 'Erro no servidor: ' . $e->getMessage(), 500);
}