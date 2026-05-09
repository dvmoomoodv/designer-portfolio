<?php
declare(strict_types=1);

/**
 * CSRF 토큰을 세션에서 꺼내거나 생성. 폼 hidden 필드에 출력하면 됨.
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * 폼/요청에서 받은 토큰을 검증. timing-safe 비교.
 */
function csrf_verify(?string $token): bool
{
    if (!$token || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * POST 요청에서 csrf_token 필드를 꺼내 검증. 실패 시 403 종료.
 */
function csrf_require_post(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        http_response_code(405);
        exit('Method Not Allowed');
    }
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        exit('Invalid CSRF token');
    }
}

/**
 * 폼에 넣을 hidden 필드 HTML.
 */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES) . '">';
}
