<?php
declare(strict_types=1);

/**
 * SQLite 기반 런타임 DB.
 * 1단계에서는 업로드 미디어 파일 기록만 담당한다.
 */
function db_connection(): ?PDO
{
    static $pdo = null;
    static $checked = false;
    if ($checked) {
        return $pdo;
    }
    $checked = true;

    if (!extension_loaded('pdo_sqlite')) {
        error_log('PDO SQLite extension is not available. Media DB recording skipped.');
        return null;
    }
    if (!is_dir(STORAGE_DIR)) {
        if (!@mkdir(STORAGE_DIR, 0755, true) && !is_dir(STORAGE_DIR)) {
            error_log('Storage directory creation failed: ' . STORAGE_DIR);
            return null;
        }
    }

    try {
        $pdo = new PDO('sqlite:' . DB_PATH);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec('PRAGMA journal_mode = WAL');
        $pdo->exec('PRAGMA foreign_keys = ON');
        db_migrate($pdo);
        return $pdo;
    } catch (Throwable $e) {
        error_log('SQLite connection failed: ' . $e->getMessage());
        $pdo = null;
        return null;
    }
}

function db_migrate(PDO $pdo): void
{
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS media_files (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            kind TEXT NOT NULL,
            original_name TEXT NOT NULL,
            stored_name TEXT NOT NULL,
            url TEXT NOT NULL,
            path TEXT NOT NULL,
            mime TEXT NOT NULL,
            extension TEXT NOT NULL,
            size_bytes INTEGER NOT NULL,
            width INTEGER,
            height INTEGER,
            created_at TEXT NOT NULL
        )'
    );
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_media_files_kind ON media_files(kind)');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_media_files_created_at ON media_files(created_at)');
}

function media_record_upload(array $media): bool
{
    $pdo = db_connection();
    if (!$pdo) {
        return false;
    }
    try {
        $stmt = $pdo->prepare(
            'INSERT INTO media_files
             (kind, original_name, stored_name, url, path, mime, extension, size_bytes, width, height, created_at)
             VALUES
             (:kind, :original_name, :stored_name, :url, :path, :mime, :extension, :size_bytes, :width, :height, :created_at)'
        );
        $stmt->execute([
            ':kind' => (string)($media['kind'] ?? 'image'),
            ':original_name' => (string)($media['original_name'] ?? ''),
            ':stored_name' => (string)($media['stored_name'] ?? ''),
            ':url' => (string)($media['url'] ?? ''),
            ':path' => (string)($media['path'] ?? ''),
            ':mime' => (string)($media['mime'] ?? ''),
            ':extension' => (string)($media['extension'] ?? ''),
            ':size_bytes' => (int)($media['size_bytes'] ?? 0),
            ':width' => isset($media['width']) ? (int)$media['width'] : null,
            ':height' => isset($media['height']) ? (int)$media['height'] : null,
            ':created_at' => gmdate('c'),
        ]);
        return true;
    } catch (Throwable $e) {
        error_log('Media DB insert failed: ' . $e->getMessage());
        return false;
    }
}

function upload_storage_bytes(array $dirs): int
{
    $total = 0;
    foreach ($dirs as $dir) {
        if (!is_dir($dir)) {
            continue;
        }
        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
        );
        foreach ($it as $file) {
            if ($file->isFile()) {
                $total += (int)$file->getSize();
            }
        }
    }
    return $total;
}

function human_bytes(int $bytes): string
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $value = (float)$bytes;
    $i = 0;
    while ($value >= 1024 && $i < count($units) - 1) {
        $value /= 1024;
        $i++;
    }
    return ($i === 0 ? (string)(int)$value : rtrim(rtrim(number_format($value, 1), '0'), '.')) . ' ' . $units[$i];
}
