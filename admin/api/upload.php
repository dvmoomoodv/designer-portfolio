<?php
/**
 * POST /admin/api/upload.php
 *  헤더: X-CSRF-Token 또는 POST csrf_token
 *  파일: $_FILES['file']
 *  kind=image|font
 *  응답: {"ok":true,"url":"./assets/images/uploads/...","filename":"..."}
 *       {"ok":false,"error":"..."}
 */
require_once __DIR__ . '/../../includes/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

function json_err(string $msg, int $code = 400): void {
    http_response_code($code);
    echo json_encode(['ok' => false, 'error' => $msg]);
    exit;
}

function upload_limit_message(string $kind, int $maxBytes): string {
    if ($kind === 'image') {
        return '사진 용량이 너무 큽니다. 파일 1개 최대 용량은 ' . human_bytes($maxBytes) . '입니다.';
    }
    if ($kind === 'font') {
        return '폰트 파일 용량이 너무 큽니다. 파일 1개 최대 용량은 ' . human_bytes($maxBytes) . '입니다.';
    }
    return '파일 용량이 너무 큽니다. 파일 1개 최대 용량은 ' . human_bytes($maxBytes) . '입니다.';
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
    $messages = [
        UPLOAD_ERR_INI_SIZE => '서버 업로드 제한보다 파일이 큽니다. php.ini upload_max_filesize를 확인하세요.',
        UPLOAD_ERR_FORM_SIZE => '폼 업로드 제한보다 파일이 큽니다.',
        UPLOAD_ERR_PARTIAL => '파일이 일부만 업로드되었습니다. 다시 시도해주세요.',
        UPLOAD_ERR_NO_FILE => '업로드할 파일이 없습니다.',
        UPLOAD_ERR_NO_TMP_DIR => '서버 임시 업로드 폴더가 없습니다.',
        UPLOAD_ERR_CANT_WRITE => '서버가 업로드 파일을 저장할 수 없습니다. 폴더 권한을 확인하세요.',
        UPLOAD_ERR_EXTENSION => '서버 확장 기능이 업로드를 중단했습니다.',
    ];
    json_err($messages[$err] ?? ('Upload error: ' . $err));
}

$file     = $_FILES['file'];
$kind     = (string)($_POST['kind'] ?? 'image');
if (!in_array($kind, ['image', 'font'], true)) {
    json_err('허용되지 않는 업로드 종류입니다.');
}
$maxBytes = $kind === 'font' ? 25 * 1024 * 1024 : 25 * 1024 * 1024; // 25 MB
if ($file['size'] > $maxBytes) {
    json_err(upload_limit_message($kind, $maxBytes));
}

$totalLimitBytes = 100 * 1024 * 1024 * 1024; // 100 GB
$currentBytes = upload_storage_bytes([UPLOAD_DIR, FONT_UPLOAD_DIR]);
if ($currentBytes >= $totalLimitBytes) {
    json_err('전체 업로드 사용량이 100GB 이상입니다. 더 이상 업로드할 수 없습니다. 기존 파일을 정리한 뒤 다시 시도해주세요.', 507);
}
if ($currentBytes + (int)$file['size'] > $totalLimitBytes) {
    json_err('이 파일을 업로드하면 전체 업로드 사용량이 100GB를 초과합니다. 더 이상 업로드할 수 없습니다.', 507);
}

/* ── MIME 검증 (서버 측 독립 체크) ── */
$finfo    = finfo_open(FILEINFO_MIME_TYPE);
$mime     = $finfo ? finfo_file($finfo, $file['tmp_name']) : false;
if ($finfo) {
    finfo_close($finfo);
}
$mime = is_string($mime) && $mime !== '' ? $mime : 'application/octet-stream';

$allowed = [
    'image' => [
        'image/jpeg' => 'jpg',
        'image/pjpeg' => 'jpg',
        'image/jfif' => 'jfif',
        'image/png'  => 'png',
        'image/x-png' => 'png',
        'image/apng' => 'apng',
        'image/gif'  => 'gif',
        'image/webp' => 'webp',
        'image/avif' => 'avif',
        'image/bmp' => 'bmp',
        'image/x-ms-bmp' => 'bmp',
        'image/tiff' => 'tiff',
        'image/heic' => 'heic',
        'image/heif' => 'heif',
        'image/vnd.microsoft.icon' => 'ico',
        'image/x-icon' => 'ico',
        'image/svg+xml' => 'svg',
    ],
    'font' => [
        'font/woff2' => 'woff2',
        'font/woff' => 'woff',
        'application/font-woff' => 'woff',
        'application/x-font-woff' => 'woff',
        'application/octet-stream' => 'woff2',
        'font/ttf' => 'ttf',
        'application/x-font-ttf' => 'ttf',
        'font/otf' => 'otf',
        'application/x-font-otf' => 'otf',
    ],
];
$imageExts = ['jpg', 'jpeg', 'jfif', 'png', 'apng', 'gif', 'webp', 'svg', 'avif', 'bmp', 'tif', 'tiff', 'heic', 'heif', 'ico'];
$originalExt = strtolower(pathinfo((string)($file['name'] ?? ''), PATHINFO_EXTENSION));
if ($kind === 'font' && in_array($originalExt, ['woff2', 'woff', 'ttf', 'otf'], true)) {
    $ext = $originalExt;
} elseif ($kind === 'image' && in_array($originalExt, $imageExts, true)) {
    $ext = $originalExt === 'jpeg' ? 'jpg' : $originalExt;
} elseif (isset($allowed[$kind][$mime])) {
    $ext = $allowed[$kind][$mime];
} else {
    json_err("허용되지 않는 파일 형식: {$mime} / .{$originalExt}");
}

/* ── 저장 ── */
$targetDir = $kind === 'font' ? FONT_UPLOAD_DIR : UPLOAD_DIR;
$targetUrl = $kind === 'font' ? FONT_UPLOAD_URL : UPLOAD_URL;
if (!is_dir($targetDir)) {
    if (!@mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
        json_err('업로드 디렉터리 생성 실패.', 500);
    }
}
if (!is_writable($targetDir)) {
    json_err('업로드 폴더에 쓰기 권한이 없습니다: ' . basename($targetDir), 500);
}

$newName  = date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
$dest     = $targetDir . '/' . $newName;

if (!move_uploaded_file($file['tmp_name'], $dest)) {
    json_err('파일 저장 실패.', 500);
}
@chmod($dest, 0644);

$url = $targetUrl . '/' . $newName;
$dimensions = [null, null];
if ($kind === 'image' && $ext !== 'svg') {
    $info = @getimagesize($dest);
    if (is_array($info)) {
        $dimensions = [(int)($info[0] ?? 0), (int)($info[1] ?? 0)];
    }
}
$dbRecorded = media_record_upload([
    'kind' => $kind,
    'original_name' => (string)($file['name'] ?? ''),
    'stored_name' => $newName,
    'url' => $url,
    'path' => $dest,
    'mime' => (string)$mime,
    'extension' => $ext,
    'size_bytes' => (int)$file['size'],
    'width' => $dimensions[0],
    'height' => $dimensions[1],
]);

echo json_encode([
    'ok' => true,
    'url' => $url,
    'filename' => $newName,
    'size_bytes' => (int)$file['size'],
    'storage_used_bytes' => $currentBytes + (int)$file['size'],
    'storage_limit_bytes' => $totalLimitBytes,
    'db_recorded' => $dbRecorded,
]);
