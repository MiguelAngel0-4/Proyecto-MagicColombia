<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/tools/session.php'; // 👈

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['uid']) || ($_SESSION['rol'] ?? '') !== 'rider') {
  http_response_code(401);
  echo json_encode(['ok' => false, 'error' => 'unauthorized']);
  exit;
}
