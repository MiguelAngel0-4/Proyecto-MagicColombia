<?php
declare(strict_types=1);
require_once __DIR__ . '/../db.php';
header('Content-Type: application/json; charset=utf-8');

$in = read_json_body(); if (!$in) $in = $_POST ?? [];
$posted = (string)($in['email'] ?? '');
$email  = strtolower(trim($posted));

$db = get_pdo()->query('SELECT DATABASE() AS db')->fetch()['db'] ?? null;

$sql = 'SELECT id, email,
               HEX(email)      AS hex_db,
               LENGTH(email)   AS len_db
          FROM usuarios
         WHERE email = ?
         LIMIT 1';
$st = get_pdo()->prepare($sql);
$st->execute([$email]);
$row = $st->fetch();

echo json_encode([
  'db'          => $db,
  'posted'      => $posted,
  'posted_norm' => $email,
  'posted_hex'  => bin2hex($posted),
  'found'       => (bool)$row,
  'row'         => $row ?: null,
  'note'        => 'Si found=false pero el usuario existe en phpMyAdmin, es otra BD o hay caracteres invisibles.'
], JSON_UNESCAPED_UNICODE);
