<?php
declare(strict_types=1);

require_once __DIR__ . '/tools/session.php';
require_once __DIR__ . '/db.php';

try {
    if (!isset($_SESSION['uid'])) {
        json_out(401, ['ok' => false, 'error' => 'unauthorized']);
    }

    $uid = (int)$_SESSION['uid'];
    $pdo = get_pdo();

    // Evita duplicados: borra tokens previos
    $pdo->prepare('DELETE FROM email_verifications WHERE user_id = ?')->execute([$uid]);

    // Crea token
    $token = bin2hex(random_bytes(16));
    $pdo->prepare('INSERT INTO email_verifications (user_id, token, created_at) VALUES (?, ?, NOW())')
        ->execute([$uid, $token]);

    // En local, devolvemos el enlace (simula envío email)
    $link = sprintf('http://localhost/cosmix/magic_auth_php/verify.php?token=%s', $token);

    json_out(200, [
        'ok'   => true,
        'sent' => true,
        'link' => $link,   // en producción se manda por email
    ]);
} catch (Throwable $e) {
    json_out(500, ['ok' => false, 'error' => $e->getMessage()]);
}
