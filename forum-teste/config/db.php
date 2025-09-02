<?php
// config/db.php
// Ajuste as variáveis abaixo conforme seu ambiente MySQL
$DB_HOST = getenv('DB_HOST') ?: 'localhost';
$DB_NAME = getenv('DB_NAME') ?: 'forum_db';
$DB_USER = getenv('DB_USER') ?: 'root';
$DB_PASS = getenv('DB_PASS') ?: 'root';

try {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao conectar ao banco: ' . $e->getMessage()]);
    exit;
}

if (session_status() === PHP_SESSION_NONE) {
    // Cookies de sessão mais seguros (ajuste 'secure' para true se usar HTTPS)
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => false
    ]);
    session_start();
}

function require_json() {
    if ($_SERVER['CONTENT_TYPE'] ?? '' !== 'application/json') {
        return json_decode(file_get_contents('php://input'), true) ?: [];
    }
    $raw = file_get_contents('php://input');
    return json_decode($raw, true) ?: [];
}

function require_auth() {
    if (!isset($_SESSION['user'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Não autenticado']);
        exit;
    }
    return $_SESSION['user'];
}

// Helper para sanitizar saída simples
function esc($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}
?>
