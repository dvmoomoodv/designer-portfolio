<?php
/**
 * POST /admin/api/delete-image.php
 *  Body JSON: {"filename":"20250507_123456_ab12.jpg"}
 *   - uploads/ 내 파일만 삭제 가능 (path traversal 방지)
 *  응답: {"ok":true} | {"ok":false,"error":"..."}
 */
require_once __DIR__ . '/../../includes/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

function json_err(string $msg, int $code = 400): never {
    http_response_code($code);
    echo json_encode(['ok' => false, 'error' => $msg]);
    exit;
}

if (!auth_is_initialized() || !auth_is_logged_in()) {
    json_err('Unauthorized', 401);
}

$token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['csrf_token'] ?? null);
if (!csrf_verify($token)) {
    json_err('Invalid CSRF token', 403);
}

$body     = (string)file_get_contents('php://input');
$payload  = json_decode($body, true);
$filename = (string)($payload['filename'] ?? '');

if ($filename === '' || $filename !== basename($filename)) {
    json_err('유효하지 않은 파일명.');
}

$target = UPLOAD_DIR . '/' . $filename;
if (!is_file($target)) {
    json_err('파일이 존재하지 않습니다.');
}

if (!@unlink($target)) {
    json_err('파일 삭제 실패.', 500);
}
echo json_encode(['ok' => true]);
