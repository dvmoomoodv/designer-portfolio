<?php
declare(strict_types=1);

const ALLOWED_IMAGE_MIME = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
    'image/gif'  => 'gif',
    'image/svg+xml' => 'svg',
];
const MAX_UPLOAD_BYTES = 8 * 1024 * 1024; // 8 MiB

/**
 * $_FILES 항목을 받아 검증 후 uploads/ 디렉터리에 저장한다.
 * 성공 시 ['ok' => true, 'path' => './assets/images/uploads/xxxx.ext']
 * 실패 시 ['ok' => false, 'error' => '메시지']
 */
function handle_image_upload(array $file): array
{
    if (!isset($file['error']) || is_array($file['error'])) {
        return ['ok' => false, 'error' => '잘못된 업로드 요청'];
    }
    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            return ['ok' => false, 'error' => '파일이 선택되지 않았습니다'];
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return ['ok' => false, 'error' => '파일이 너무 큽니다'];
        default:
            return ['ok' => false, 'error' => '업로드 오류 코드 ' . $file['error']];
    }

    if (($file['size'] ?? 0) > MAX_UPLOAD_BYTES) {
        return ['ok' => false, 'error' => '파일이 8MB 를 초과합니다'];
    }

    $tmp = $file['tmp_name'] ?? '';
    if (!is_uploaded_file($tmp)) {
        return ['ok' => false, 'error' => '업로드 파일이 유효하지 않습니다'];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($tmp) ?: '';
    if (!isset(ALLOWED_IMAGE_MIME[$mime])) {
        return ['ok' => false, 'error' => '허용되지 않은 형식 (' . $mime . ')'];
    }
    $ext = ALLOWED_IMAGE_MIME[$mime];

    if (!is_dir(UPLOAD_DIR)) {
        if (!@mkdir(UPLOAD_DIR, 0755, true) && !is_dir(UPLOAD_DIR)) {
            return ['ok' => false, 'error' => '업로드 디렉터리 생성 실패'];
        }
    }

    $original = $file['name'] ?? 'upload';
    $base     = preg_replace('/[^A-Za-z0-9_.-]+/', '-', pathinfo($original, PATHINFO_FILENAME));
    $base     = trim((string)$base, '-') ?: 'image';
    $base     = substr($base, 0, 40);
    $name     = sprintf('%s-%s-%s.%s', $base, date('YmdHis'), bin2hex(random_bytes(3)), $ext);
    $dest     = UPLOAD_DIR . '/' . $name;

    if (!@move_uploaded_file($tmp, $dest)) {
        return ['ok' => false, 'error' => '저장 실패'];
    }
    @chmod($dest, 0644);

    return ['ok' => true, 'path' => UPLOAD_URL . '/' . $name];
}
