<?php
declare(strict_types=1);
session_start();
if (!defined('COSMIX_BASE')) { define('COSMIX_BASE', '/cosmix'); }
if (!defined('AUTH_BASE'))   { define('AUTH_BASE', COSMIX_BASE . '/php'); }
?>
<?php require_once __DIR__ . '/../rider_guard.php'; ?>
<?php
$path = realpath(__DIR__ . '/../../html/domiciliario.html');
if ($path && is_readable($path)) { readfile($path); } else { echo "<p>Sube tu archivo en /cosmix/html/domiciliario.html</p>"; }
