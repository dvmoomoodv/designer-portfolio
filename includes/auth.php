<?php
declare(strict_types=1);

/**
 * 어드민 비밀번호 해시 저장 구조 (data/auth.json):
 *   {
 *     "password_hash": "...",
 *     "created_at":    "ISO8601",
 *     "failed":        { "count": 0, "until": 0 }
 *   }
 *
 * 운영자 1명 전제. 첫 사용 시 admin/setup.php 로 비밀번호 설정.
 */

function auth_is_initialized(): bool
{
    return is_file(AUTH_FILE);
}

function auth_load(): array
{
    if (!is_file(AUTH_FILE)) {
        return [];
    }
    $raw = file_get_contents(AUTH_FILE);
    $arr = json_decode($raw === false ? '' : $raw, true);
    return is_array($arr) ? $arr : [];
}

function auth_save(array $auth): void
{
    if (!is_dir(DATA_DIR)) {
        @mkdir(DATA_DIR, 0755, true);
    }
    $tmp = AUTH_FILE . '.tmp.' . bin2hex(random_bytes(4));
    $json = json_encode($auth, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        throw new RuntimeException('auth json encode 실패');
    }
    if (file_put_contents($tmp, $json, LOCK_EX) === false) {
        throw new RuntimeException('auth tmp 쓰기 실패');
    }
    @chmod($tmp, 0600);
    if (!@rename($tmp, AUTH_FILE)) {
        @unlink($tmp);
        throw new RuntimeException('auth 파일 교체 실패');
    }
}

function auth_set_password(string $plain): void
{
    if (strlen($plain) < 8) {
        throw new InvalidArgumentException('비밀번호는 최소 8자 이상.');
    }
    $auth = auth_load();
    $auth['password_hash'] = password_hash($plain, PASSWORD_DEFAULT);
    $auth['created_at']    = $auth['created_at'] ?? date('c');
    $auth['updated_at']    = date('c');
    $auth['failed']        = ['count' => 0, 'until' => 0];
    auth_save($auth);
}

function auth_check_password(string $plain): bool
{
    $auth = auth_load();
    $hash = $auth['password_hash'] ?? '';
    return $hash !== '' && password_verify($plain, $hash);
}

/* 단순 lockout: 5회 연속 실패 시 5분 잠금. */
function auth_throttle_remaining(): int
{
    $auth  = auth_load();
    $until = (int)($auth['failed']['until'] ?? 0);
    return max(0, $until - time());
}

function auth_record_failure(): void
{
    $auth   = auth_load();
    $count  = (int)($auth['failed']['count'] ?? 0) + 1;
    $until  = $count >= 5 ? time() + 300 : 0;
    $auth['failed'] = ['count' => $count, 'until' => $until];
    auth_save($auth);
}

function auth_record_success(): void
{
    $auth = auth_load();
    $auth['failed'] = ['count' => 0, 'until' => 0];
    auth_save($auth);
}

function auth_login(): void
{
    session_regenerate_id(true);
    $_SESSION['admin'] = [
        'logged_in' => true,
        'login_at'  => time(),
    ];
}

function auth_logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

function auth_is_logged_in(): bool
{
    return !empty($_SESSION['admin']['logged_in']);
}

function auth_require(): void
{
    if (!auth_is_logged_in()) {
        $next = $_SERVER['REQUEST_URI'] ?? '/admin/';
        header('Location: ./login.php?next=' . urlencode($next));
        exit;
    }
}
