<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../db.php';
if (!isset($_SESSION['usuario']) || ($_SESSION['usuario']['rol'] ?? '') !== 'domiciliario') {
  header('Location: ' . AUTH_BASE . '/');
  exit;
}
