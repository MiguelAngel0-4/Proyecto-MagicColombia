<?php
declare(strict_types=1);

require_once __DIR__ . '/../tools/session.php';
require_once __DIR__ . '/admin_guard.php';   // exige rol admin
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json; charset=utf-8');

function read_json_body(): array {
  $raw = file_get_contents('php://input');
  if ($raw !== false && $raw !== '') {
    $j = json_decode($raw, true);
    if (is_array($j)) return $j;
  }
  return $_POST + $_GET;
}

try {
  $in = read_json_body();

  $email = strtolower(trim((string)($in['email'] ?? '')));
  // acepta "password" o "pass"
  $pass  = (string)($in['password'] ?? $in['pass'] ?? '');

  if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $pass === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'email_or_password_required']);
    exit;
  }

  if (strlen($pass) < 8) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'password_too_short']);
    exit;
  }

  $pdo = get_pdo();

  // ¿ya existe?
  $st = $pdo->prepare('SELECT id FROM usuarios WHERE email = ? LIMIT 1');
  $st->execute([$email]);
  $exists = (bool)$st->fetch();

  if ($exists) {
    http_response_code(409);
    echo json_encode(['ok' => false, 'error' => 'email_already_registered']);
    exit;
  }

  $hash = password_hash($pass, PASSWORD_BCRYPT);

  // Insert: activo y verificado (al ser creado por admin)
  $st = $pdo->prepare(
    'INSERT INTO usuarios (email, is_active, email_verified_at, password_hash)
     VALUES (?, 1, NOW(), ?)'
  );
  $st->execute([$email, $hash]);

  $id = (int)$pdo->lastInsertId();

  echo json_encode([
    'ok'    => true,
    'id'    => $id,
    'email' => $email,
  ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
