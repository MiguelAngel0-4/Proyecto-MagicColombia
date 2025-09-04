<?php
declare(strict_types=1);

require_once __DIR__ . '/tools/session.php';
require_once __DIR__ . '/db.php';

try {
    // Lee JSON o cae a POST/GET
    $raw = file_get_contents('php://input') ?: '';
    $in  = json_decode($raw, true);
    if (!is_array($in)) { $in = array_merge($_POST, $_GET); }

    $email = strtolower(trim((string)($in['email'] ?? '')));
    $pass  = (string)($in['password'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $pass === '') {
        json_out(400, ['ok' => false, 'error' => 'email_or_password_required']);
    }

    $pdo = get_pdo();

    // Trae datos del usuario
    $st = $pdo->prepare(
        'SELECT id, email, is_active, email_verified_at, password_hash, rol
           FROM usuarios
          WHERE email = ?
          LIMIT 1'
    );
    $st->execute([$email]);
    $user = $st->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($pass, (string)$user['password_hash'])) {
        json_out(401, ['ok' => false, 'error' => 'bad_credentials']);
    }
    if ((int)$user['is_active'] !== 1) {
        json_out(403, ['ok' => false, 'error' => 'inactive_user']);
    }

    // Sesión
    $_SESSION['uid']       = (int)$user['id'];
    $_SESSION['rol']       = (string)($user['rol'] ?? 'user');
    $_SESSION['mail']      = (string)$user['email'];
    $_SESSION['logged_at'] = time();

    // Próxima página (ajústala si quieres derivar por rol)
    $next = '/cosmix/html/admin.html';
    if (strtolower($_SESSION['rol']) !== 'admin') {
        // ejemplo: redirigir riders a otra vista si la tienes
        // $next = '/cosmix/html/rider.html';
    }

    json_out(200, [
        'ok'       => true,
        'redirect' => $next,
        'rol'      => $_SESSION['rol'],
    ]);

} catch (Throwable $e) {
    json_out(500, ['ok' => false, 'error' => $e->getMessage()]);
}
