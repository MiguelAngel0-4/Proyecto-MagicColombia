<?php
declare(strict_types=1);

require_once __DIR__ . '/../db.php'; // db.php está 1 nivel arriba

$in = read_json_body();
if (!$in) { $in = $_POST + $_GET; }

$email_raw = (string)($in['email'] ?? '');
$email     = strtolower(trim($email_raw));

$pdo = get_pdo();
$st  = $pdo->prepare(
  'SELECT id, email, rol, is_active, email_verified_at, password_hash
     FROM usuarios
    WHERE email = ?
    LIMIT 1'
);
$st->execute([$email]);
$row = $st->fetch();

echo json_encode([
  'ok'              => true,
  'posted'          => $email_raw,
  'posted_norm'     => $email,
  'posted_hex'      => bin2hex($email),
  'found'           => (bool)$row,
  'row'             => $row,
  'email_in_db_hex' => $row ? bin2hex((string)$row['email']) : null,
  'has_hash'        => $row ? (int)(($row['password_hash'] ?? '') !== '') : 0,
  'hash_prefix'     => $row ? substr((string)$row['password_hash'], 0, 10) : null,
], JSON_UNESCAPED_UNICODE);
