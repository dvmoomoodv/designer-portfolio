<?php
declare(strict_types=1);

/** 고정 어드민 계정. 비밀번호 원문은 저장하지 않고 해시로 비교한다. */
const ADMIN_FIXED_USERNAME = 'admin';
const ADMIN_FIXED_PASSWORD_SHA256 = 'fa1f0cb144bfc60d1c7f1b65de5045af00b0186199f4dbae4c521bb99fe4271e';

function auth_is_initialized(): bool
{
    return true;
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
    throw new LogicException('고정 로그인 방식에서는 비밀번호를 변경할 수 없습니다.');
}

function auth_check_password(string $plain): bool
{
    return hash_equals(ADMIN_FIXED_PASSWORD_SHA256, hash('sha256', $plain));
}

function auth_check_credentials(string $username, string $plain): bool
{
    return hash_equals(ADMIN_FIXED_USERNAME, $username) && auth_check_password($plain);
}

/* 단순 lockout: 5회 연속 실패 시 5분 잠금. */
function auth_throttle_remaining(): int
{
    $until = (int)($_SESSION['admin_failed']['until'] ?? 0);
    return max(0, $until - time());
}

function auth_record_failure(): void
{
    $count  = (int)($_SESSION['admin_failed']['count'] ?? 0) + 1;
    $until  = $count >= 5 ? time() + 300 : 0;
    $_SESSION['admin_failed'] = ['count' => $count, 'until' => $until];
}

function auth_record_success(): void
{
    unset($_SESSION['admin_failed']);
}

function auth_login(): void
{
    session_regenerate_id(true);
    $_SESSION['admin'] = [
        'logged_in' => true,
        'username'  => ADMIN_FIXED_USERNAME,
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
