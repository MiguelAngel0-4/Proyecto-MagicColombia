<?php
declare(strict_types=1);

// Siempre antes de cualquier salida
ini_set('session.use_strict_mode', '1');

session_set_cookie_params([
  'path'     => '/cosmix',   // cookie válida en toda tu app
  'httponly' => true,
  'samesite' => 'Lax',
]);

// Si usas un nombre custom, descomenta:
// session_name('CMXSESSID');

if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}
