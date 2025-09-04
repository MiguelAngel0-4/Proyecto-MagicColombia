<?php
declare(strict_types=1);

require_once __DIR__ . '/tools/session.php';
require_once __DIR__ . '/db.php';

try {
    $isLogged = isset($_SESSION['uid']);
    json_out(200, [
        'ok'   => true,
        'auth' => $isLogged,
        'uid'  => $isLogged ? (int)$_SESSION['uid'] : null,
        'rol'  => $isLogged ? (string)($_SESSION['rol'] ?? 'user') : null,
    ]);
} catch (Throwable $e) {
    json_out(500, ['ok' => false, 'error' => $e->getMessage()]);
}
