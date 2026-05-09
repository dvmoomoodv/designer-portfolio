<?php
declare(strict_types=1);

const SUPPORTED_LANGS = ['en', 'ko'];
const LANG_COOKIE     = 'rohi_lang';
const DEFAULT_LANG    = 'en';

/**
 * 현재 언어를 결정하고 쿠키에 저장한다.
 * 우선순위: ?lang=xx (쿼리) → 쿠키 → DEFAULT_LANG.
 */
function current_lang(): string
{
    static $lang = null;
    if ($lang !== null) {
        return $lang;
    }

    $candidate = null;
    if (isset($_GET['lang']) && in_array($_GET['lang'], SUPPORTED_LANGS, true)) {
        $candidate = $_GET['lang'];
        setcookie(LANG_COOKIE, $candidate, [
            'expires'  => time() + 60 * 60 * 24 * 365,
            'path'     => '/',
            'httponly' => false,
            'samesite' => 'Lax',
        ]);
    } elseif (isset($_COOKIE[LANG_COOKIE]) && in_array($_COOKIE[LANG_COOKIE], SUPPORTED_LANGS, true)) {
        $candidate = $_COOKIE[LANG_COOKIE];
    }

    $lang = $candidate ?: DEFAULT_LANG;
    return $lang;
}

/**
 * i18n 필드(`['ko' => ..., 'en' => ...]`) 에서 현재 언어 값을 꺼낸다.
 * 비어 있으면 영문으로 폴백, 영문도 비어 있으면 빈 문자열.
 * 일반 문자열을 받으면 그대로 반환 (방어적).
 */
function t($field, ?string $lang = null): string
{
    if (is_string($field)) {
        return $field;
    }
    if (!is_array($field)) {
        return '';
    }
    $lang = $lang ?? current_lang();
    $val  = isset($field[$lang]) && $field[$lang] !== '' ? $field[$lang] : null;
    if ($val === null) {
        $val = $field['en'] ?? '';
    }
    return is_string($val) ? $val : '';
}

/**
 * HTML 이스케이프 + i18n 추출.
 */
function te($field, ?string $lang = null): string
{
    return htmlspecialchars(t($field, $lang), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * 일반 문자열 HTML 이스케이프.
 */
function e($s): string
{
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * lang 토글용 URL: 현재 URL 에 ?lang=xx 추가/대체.
 */
function lang_toggle_url(string $target): string
{
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $parts = explode('?', $uri, 2);
    $path  = $parts[0];
    parse_str($parts[1] ?? '', $q);
    $q['lang'] = $target;
    return $path . '?' . http_build_query($q);
}
