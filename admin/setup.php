<?php
require_once __DIR__ . '/../includes/bootstrap.php';

if (PHP_SAPI === 'cli') {
    fwrite(STDOUT, "고정 로그인 방식입니다. setup은 사용하지 않습니다.\n");
    exit(0);
}

header('Location: ./login.php');
exit;
