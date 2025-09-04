<?php
declare(strict_types=1);

require_once __DIR__ . '/tools/session.php';
require_once __DIR__ . '/db.php';

try {
    if (!isset($_SESSION['uid'])) {
        json_out(401, ['ok' => false, 'error' => 'unauthorized']);
    }

    json_out(200, [
        'ok'   => true,
        'uid'  => (int)$_SESSION['uid'],
        'rol'  => (string)($_SESSION['rol'] ?? 'user'),
        'mail' => (string)($_SESSION['mail'] ?? ''),
    ]);
} catch (Throwable $e) {
    json_out(500, ['ok' => false, 'error' => $e->getMessage()]);
}
