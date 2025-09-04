<?php
declare(strict_types=1);
session_start();
require_once __DIR__.'/db.php';

try { $pdo = get_pdo(); } 
catch (Throwable $e) { json_response(500, ['ok'=>false,'error'=>'DB no disponible']); }

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
  json_response(405, ['ok'=>false,'error'=>'Método inválido']);
}

$body = read_json_body();
$nombre = trim((string)($body['name'] ?? ($_POST['nombre'] ?? $_POST['name'] ?? '')));
$email  = trim(strtolower($body['email'] ?? ($_POST['email'] ?? '')));
$pass   = (string)($body['password'] ?? ($_POST['password'] ?? ''));
$confirm= (string)($body['confirmPassword'] ?? ($_POST['confirmPassword'] ?? ''));

if ($email === '' || $pass === '') { json_response(422, ['ok'=>false,'error'=>'Email y contraseña son obligatorios']); }
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { json_response(422, ['ok'=>false,'error'=>'Email inválido']); }
if ($pass !== $confirm && $confirm !== '') { json_response(422, ['ok'=>false,'error'=>'Las contraseñas no coinciden']); }

$hash = password_hash($pass, PASSWORD_DEFAULT);

try {
  $stmt = $pdo->prepare('INSERT INTO usuarios (nombre,email,password_hash,rol,is_active) VALUES (:n,:e,:p,'user',1)');
  $stmt->execute([':n'=>$nombre ?: 'Usuario', ':e'=>$email, ':p'=>$hash]);
  json_response(200, ['ok'=>true]);
} catch (PDOException $ex) {
  if (($ex->errorInfo[1] ?? 0) == 1062) json_response(409, ['ok'=>false,'error'=>'El email ya está registrado']);
  json_response(500, ['ok'=>false,'error'=>'No se pudo registrar']);
}
