<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

ini_set('display_errors','1'); ini_set('display_startup_errors','1'); error_reporting(E_ALL);

require_once __DIR__ . '/../db.php';

function in_data(): array {
  $raw = file_get_contents('php://input') ?: '';
  $data = json_decode($raw, true);
  if (!is_array($data)) $data = [];
  // Permite también GET/POST normales
  $data = array_merge($_GET, $_POST, $data);
  return $data;
}

try {
  $in = in_data();
  $email = isset($in['email']) ? strtolower(trim((string)$in['email'])) : '';
  $pass  = isset($in['password']) ? (string)$in['password'] : '';

  if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $pass === '') {
    http_response_code(400);
    echo json_encode(['ok'=>false,'error'=>'email_or_password_required']); exit;
  }

  $pdo = get_pdo();

  // ¿Existe el usuario?
  $st = $pdo->prepare('SELECT id FROM usuarios WHERE email = ? LIMIT 1');
  $st->execute([$email]);
  $row = $st->fetch();

  if (!$row) {
    http_response_code(404);
    echo json_encode(['ok'=>false,'error'=>'user_not_found']); exit;
  }

  $hash = password_hash($pass, PASSWORD_DEFAULT);

  // Actualiza hash y, de paso, activa y marca verificado
  $up = $pdo->prepare(
    'UPDATE usuarios 
       SET password_hash = ?, is_active = 1, email_verified_at = NOW()
     WHERE email = ?'
  );
  $up->execute([$hash, $email]);

  echo json_encode(['ok'=>true,'email'=>$email,'hash_len'=>strlen($hash)]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);
}
