<?php
/**
 * 모든 페이지/어드민 진입점에서 가장 먼저 require 되는 부트스트랩.
 * - 경로 상수 정의
 * - 세션 시작 (어드민 로그인/CSRF 용)
 * - 보조 모듈 로드
 */

declare(strict_types=1);

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}
define('DATA_DIR',     APP_ROOT . '/data');
define('UPLOAD_DIR',   APP_ROOT . '/assets/images/uploads');
define('UPLOAD_URL',   './assets/images/uploads');
define('FONT_UPLOAD_DIR', APP_ROOT . '/assets/fonts/uploads');
define('FONT_UPLOAD_URL', './assets/fonts/uploads');
define('STORAGE_DIR',  APP_ROOT . '/storage');
define('DB_PATH',      STORAGE_DIR . '/portfolio.sqlite');
define('AUTH_FILE',    DATA_DIR . '/auth.json');

ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

require_once __DIR__ . '/i18n.php';
require_once __DIR__ . '/data.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/auth.php';
