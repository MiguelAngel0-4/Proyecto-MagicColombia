<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors','1');
ini_set('display_startup_errors','1');
error_reporting(E_ALL);

require_once __DIR__ . '/db.php';

try {
    $conn = get_db();
    $conn->query('SELECT 1'); // ¿hay conexión real?

    // ¿existe la tabla usuarios?
    $ok = $conn->query('SELECT 1 FROM usuarios LIMIT 1');
    echo json_encode(['ok' => true, 'db' => $ok !== false], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
