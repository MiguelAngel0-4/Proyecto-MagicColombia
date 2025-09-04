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
  $st = $pdo->prepare('SELECT id, email, password_hash FROM usuarios WHERE email = ? LIMIT 1');
  $st->execute([$email]);
  $row = $st->fetch();

  $exists = (bool)$row;
  $match  = $exists ? password_verify($pass, (string)$row['password_hash']) : false;

  echo json_encode([
    'ok'          => true,
    'found_user'  => $exists,
    'verify_pass' => $match,
  ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);
}
