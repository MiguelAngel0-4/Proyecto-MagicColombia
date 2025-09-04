<?php
declare(strict_types=1);

require_once __DIR__ . '/../../tools/session.php';
header('Content-Type: application/json; charset=utf-8');

echo json_encode([
  'ok'     => true,
  'uid'    => (int)($_SESSION['uid'] ?? 0),
  'rol'    => (string)($_SESSION['rol'] ?? 'guest'),
  'mail'   => (string)($_SESSION['mail'] ?? ''),
  'logged' => !empty($_SESSION['uid'])
], JSON_UNESCAPED_UNICODE);
