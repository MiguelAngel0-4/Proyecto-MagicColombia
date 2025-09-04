<?php
declare(strict_types=1);
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json; charset=utf-8');

$in = read_json_body();
if (!$in) $in = $_POST ?? [];

echo json_encode([
  'received'     => $in,
  'content_type' => $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '',
  'session_id'   => session_id(),
  'cookie_path'  => session_get_cookie_params()['path'] ?? null,
], JSON_UNESCAPED_UNICODE);
