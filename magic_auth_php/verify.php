<?php
declare(strict_types=1);

require_once __DIR__ . '/tools/session.php';
require_once __DIR__ . '/db.php';

try {
    $token = trim((string)($_GET['token'] ?? ''));
    if ($token === '') {
        json_out(400, ['ok' => false, 'error' => 'token_required']);
    }

    $pdo = get_pdo();
    $pdo->beginTransaction();

    // Busca el token
    $st = $pdo->prepare('SELECT ev.id, ev.user_id FROM email_verifications ev WHERE ev.token = ? LIMIT 1');
    $st->execute([$token]);
    $row = $st->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        $pdo->rollBack();
        json_out(404, ['ok' => false, 'error' => 'token_not_found']);
    }

    // Marca verificado y limpia tokens
    $uid = (int)$row['user_id'];
    $pdo->prepare('UPDATE usuarios SET email_verified_at = IFNULL(email_verified_at, NOW()) WHERE id = ?')
        ->execute([$uid]);
    $pdo->prepare('DELETE FROM email_verifications WHERE user_id = ?')->execute([$uid]);

    $pdo->commit();
    json_out(200, ['ok' => true, 'verified_user' => $uid]);
} catch (Throwable $e) {
    if ($e instanceof PDOException && $pdo && $pdo->inTransaction()) { $pdo->rollBack(); }
    json_out(500, ['ok' => false, 'error' => $e->getMessage()]);
}
