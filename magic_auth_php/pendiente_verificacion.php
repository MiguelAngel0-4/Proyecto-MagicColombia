<?php
declare(strict_types=1);

require_once __DIR__ . '/tools/session.php';
require_once __DIR__ . '/db.php';

try {
    if (!isset($_SESSION['uid'])) {
        json_out(401, ['ok' => false, 'error' => 'unauthorized']);
    }
    $pdo = get_pdo();
    $st  = $pdo->prepare('SELECT email, email_verified_at FROM usuarios WHERE id = ? LIMIT 1');
    $st->execute([(int)$_SESSION['uid']]);
    $u = $st->fetch(PDO::FETCH_ASSOC);

    $pending = !$u || $u['email_verified_at'] === null;

    json_out(200, [
        'ok'     => true,
        'pending'=> $pending,
        'email'  => $u ? (string)$u['email'] : (string)($_SESSION['mail'] ?? ''),
        'email_verified_at' => $u['email_verified_at'] ?? null,
    ]);
} catch (Throwable $e) {
    json_out(500, ['ok' => false, 'error' => $e->getMessage()]);
}
