<?php
declare(strict_types=1);
require_once __DIR__ . '/../db.php';
header('Content-Type: application/json; charset=utf-8');

echo json_encode([
  'sid'     => session_id(),
  'session' => $_SESSION,
  'cookie'  => session_get_cookie_params(),
], JSON_UNESCAPED_UNICODE);
