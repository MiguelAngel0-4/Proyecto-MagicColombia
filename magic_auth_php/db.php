<?php
declare(strict_types=1);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

/**
 * Conexión mysqli (para consultas simples)
 */
function get_db(): mysqli {
    // AJUSTA ESTAS CREDENCIALES A TU ENTORNO
    $host = 'localhost';
    $user = 'root';
    $pass = '';
    $name = 'cosmix';

    $conn = new mysqli($host, $user, $pass, $name);
    $conn->set_charset('utf8mb4');
    return $conn;
}

/**
 * Conexión PDO (si la prefieres para otras partes)
 */
function get_pdo(): PDO {
    // AJUSTA ESTAS CREDENCIALES A TU ENTORNO
    $host = '127.0.0.1';
    $db   = 'cosmix';
    $user = 'root';
    $pass = '';
    $opt = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $dsn = "mysql:host={$host};dbname={$db};charset=utf8mb4";
    return new PDO($dsn, $user, $pass, $opt);
}

/**
 * Helper para responder JSON uniforme
 */
function json_out(int $status, array $payload): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
    exit;
}
