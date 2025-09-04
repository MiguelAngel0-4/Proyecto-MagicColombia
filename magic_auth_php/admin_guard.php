<?php
declare(strict_types=1);

require_once __DIR__ . '/tools/session.php';
require_once __DIR__ . '/db.php';

try {
    // Limpia sesión
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time()-42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();

    json_out(200, ['ok' => true]);
} catch (Throwable $e) {
    json_out(500, ['ok' => false, 'error' => $e->getMessage()]);
}
