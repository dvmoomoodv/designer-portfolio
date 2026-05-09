<?php
/**
 * POST /admin/api/upload.php
 *  헤더: X-CSRF-Token 또는 POST csrf_token
 *  파일: $_FILES['file']
 *  응답: {"ok":true,"url":"./assets/images/uploads/...","filename":"..."}
 *       {"ok":false,"error":"..."}
 */
require_once __DIR__ . '/../../includes/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

function json_err(string $msg, int $code = 400): never {
    http_response_code($code);
    echo json_encode(['ok' => false, 'error' => $msg]);
    exit;
}

/* ── 인증 ── */
if (!auth_is_initialized() || !auth_is_logged_in()) {
    json_err('Unauthorized', 401);
}

/* ── CSRF (헤더 or POST 필드) ── */
$token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['csrf_token'] ?? null);
if (!csrf_verify($token)) {
    json_err('Invalid CSRF token', 403);
}

/* ── 파일 유효성 ── */
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    $err = $_FILES['file']['error'] ?? UPLOAD_ERR_NO_FILE;
    json_err('Upload error: ' . $err);
}

$file     = $_FILES['file'];
$maxBytes = 10 * 1024 * 1024; // 10 MB
if ($file['size'] > $maxBytes) {
    json_err('파일이 너무 큽니다 (최대 10 MB).');
}

/* ── MIME 검증 (서버 측 독립 체크) ── */
$finfo    = finfo_open(FILEINFO_MIME_TYPE);
$mime     = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

const ALLOWED_MIMES = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/gif'  => 'gif',
    'image/webp' => 'webp',
    'image/svg+xml' => 'svg',
];
if (!isset(ALLOWED_MIMES[$mime])) {
    json_err("허용되지 않는 파일 형식: {$mime}");
}

/* ── 저장 ── */
if (!is_dir(UPLOAD_DIR)) {
    if (!@mkdir(UPLOAD_DIR, 0755, true) && !is_dir(UPLOAD_DIR)) {
        json_err('업로드 디렉터리 생성 실패.', 500);
    }
}

$ext      = ALLOWED_MIMES[$mime];
$newName  = date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
$dest     = UPLOAD_DIR . '/' . $newName;

if (!move_uploaded_file($file['tmp_name'], $dest)) {
    json_err('파일 저장 실패.', 500);
}
@chmod($dest, 0644);

$url = UPLOAD_URL . '/' . $newName;
echo json_encode(['ok' => true, 'url' => $url, 'filename' => $newName]);
