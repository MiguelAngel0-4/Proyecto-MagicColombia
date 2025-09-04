<?php
declare(strict_types=1);

require_once __DIR__ . '/../../tools/session.php';
require_once __DIR__ . '/admin_guard.php';

header('Content-Type: application/json; charset=utf-8');
echo json_encode(['ok'=>true, 'msg'=>'admin api up']);
