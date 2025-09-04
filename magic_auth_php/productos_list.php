<?php
declare(strict_types=1);

require_once __DIR__ . '/tools/session.php'; // no exige login, solo unifica cookie-path
require_once __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');

try {
  $pdo = get_pdo();

  // Parámetros
  $page = max(1, (int)($_GET['page'] ?? 1));
  $per  = max(1, min(100, (int)($_GET['per'] ?? 12)));
  $q    = trim((string)($_GET['q'] ?? ''));
  $offset = ($page - 1) * $per;

  // Base: solo productos activos (si quieres también stock > 0, descomenta la línea)
  $where = 'FROM productos WHERE is_active = 1';
  // $where .= ' AND stock > 0';

  // Búsqueda opcional por nombre o SKU
  $params = [];
  if ($q !== '') {
    $where .= ' AND (nombre LIKE :q OR sku LIKE :q)';
    $params[':q'] = "%{$q}%";
  }

  // total
  $st = $pdo->prepare("SELECT COUNT(*) {$where}");
  foreach ($params as $k => $v) $st->bindValue($k, $v, PDO::PARAM_STR);
  $st->execute();
  $total = (int)$st->fetchColumn();

  // página
  $st = $pdo->prepare("
    SELECT id, sku, nombre, precio, stock, is_active
    {$where}
    ORDER BY id DESC
    LIMIT :per OFFSET :off
  ");
  foreach ($params as $k => $v) $st->bindValue($k, $v, PDO::PARAM_STR);
  $st->bindValue(':per',  $per,   PDO::PARAM_INT);
  $st->bindValue(':off',  $offset, PDO::PARAM_INT);
  $st->execute();
  $items = $st->fetchAll(PDO::FETCH_ASSOC);

  echo json_encode([
    'ok'    => true,
    'items' => $items,
    'page'  => $page,
    'per'   => $per,
    'total' => $total,
  ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
