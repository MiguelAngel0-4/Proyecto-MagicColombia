<?php
// /cosmix/magic_auth_php/admin/productos_api.php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
  // Ajusta las credenciales si hace falta
  $pdo = new PDO('mysql:host=127.0.0.1;dbname=cosmix;charset=utf8mb4','root','',[
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'db','msg'=>$e->getMessage()]);
  exit;
}

try {
  if ($action === 'list') {
    $page = max(1, (int)($_GET['page'] ?? $_POST['page'] ?? 1));
    $per  = min(200, max(1, (int)($_GET['per']  ?? $_POST['per']  ?? 50)));
    $off  = ($page - 1) * $per;

    $total = (int)$pdo->query("SELECT COUNT(*) FROM productos")->fetchColumn();

    $sql = "SELECT id, sku, nombre, precio, stock, is_active, img_url
            FROM productos ORDER BY id DESC LIMIT :off,:per";
    $st = $pdo->prepare($sql);
    $st->bindValue(':off', $off, PDO::PARAM_INT);
    $st->bindValue(':per', $per, PDO::PARAM_INT);
    $st->execute();
    $items = $st->fetchAll();

    echo json_encode(['ok'=>true,'items'=>$items,'page'=>$page,'per'=>$per,'total'=>$total], JSON_UNESCAPED_UNICODE);
    exit;
  }

  if ($action === 'save') {
    // si viene id > 0 => UPDATE, si no => INSERT
    $id        = (int)($_POST['id'] ?? 0);
    $sku       = trim((string)($_POST['sku'] ?? ''));
    $nombre    = trim((string)($_POST['nombre'] ?? ''));
    $categoria = trim((string)($_POST['categoria'] ?? ''));
    $tipo      = trim((string)($_POST['tipo_carta'] ?? ''));
    $edicion   = trim((string)($_POST['edicion'] ?? ''));
    $precio    = (float)($_POST['precio'] ?? 0);
    $stock     = (int)($_POST['stock'] ?? 0);
    $img_url   = trim((string)($_POST['img_url'] ?? ''));
    $is_active = (int)(($_POST['is_active'] ?? '1') === '1');

    if ($sku === '' || $nombre === '') {
      http_response_code(400);
      echo json_encode(['ok'=>false,'error'=>'bad_request','msg'=>'Faltan datos obligatorios']);
      exit;
    }

    if ($id > 0) {
      // UPDATE
      if ($img_url === '') $img_url = "/cosmix/images/productos/img.$id.jpg";
      $sql = "UPDATE productos
                SET sku=:sku, nombre=:nombre, precio=:precio, stock=:stock,
                    is_active=:act, img_url=:img
              WHERE id=:id";
      $st = $pdo->prepare($sql);
      $st->execute([
        ':sku'=>$sku, ':nombre'=>$nombre, ':precio'=>$precio, ':stock'=>$stock,
        ':act'=>$is_active, ':img'=>$img_url, ':id'=>$id
      ]);
      echo json_encode(['ok'=>true,'id'=>$id]); exit;
    } else {
      // INSERT
      $st = $pdo->prepare("INSERT INTO productos (sku,nombre,precio,stock,is_active,img_url)
                           VALUES (:sku,:nombre,:precio,:stock,:act,'')");
      $st->execute([
        ':sku'=>$sku, ':nombre'=>$nombre, ':precio'=>$precio, ':stock'=>$stock, ':act'=>$is_active
      ]);
      $newId = (int)$pdo->lastInsertId();
      if ($img_url === '') $img_url = "/cosmix/images/productos/img.$newId.jpg";
      $st2 = $pdo->prepare("UPDATE productos SET img_url=:img WHERE id=:id");
      $st2->execute([':img'=>$img_url,':id'=>$newId]);

      echo json_encode(['ok'=>true,'id'=>$newId]); exit;
    }
  }

  if ($action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) { echo json_encode(['ok'=>false,'error'=>'bad_id']); exit; }
    // Borrado lógico: inactiva
    $st = $pdo->prepare("UPDATE productos SET is_active=0 WHERE id=:id");
    $st->execute([':id'=>$id]);
    echo json_encode(['ok'=>true]); exit;
  }

  // Acción desconocida
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>'unknown_action']);
  exit;

} catch (PDOException $e) {
  // clave duplicada (SKU único)
  if ((string)$e->getCode() === '23000') {
    http_response_code(409);
    echo json_encode(['ok'=>false,'error'=>'duplicate','msg'=>'SKU ya existe']); exit;
  }
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'sql','msg'=>$e->getMessage()]); exit;
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'server','msg'=>$e->getMessage()]); exit;
}
function normalize_img_url($v) {
  $v = trim((string)$v);
  if ($v === '') return null;

  // Backslashes -> slashes
  $v = str_replace('\\', '/', $v);

  // Quita prefijo local (por si pegan rutas de Windows)
  $v = preg_replace('~^[A-Za-z]:/xampp/htdocs~i', '', $v);

  // Comprime // a /
  $v = preg_replace('~/+~', '/', $v);

  // Asegura 1 slash inicial
  if ($v[0] !== '/') $v = '/'.$v;

  // Armoniza img_12 → img.12 (opcional, por si se teclea con guion bajo)
  $v = preg_replace('~/img_([0-9]+)\.jpg$~i', '/img.$1.jpg', $v);

  return $v;
}

// …dentro de tu handler "save":
$img_url = normalize_img_url($_POST['img_url'] ?? '');
