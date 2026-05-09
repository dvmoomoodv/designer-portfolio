<?php
require_once __DIR__ . '/../includes/bootstrap.php';

csrf_require_post();
auth_logout();
header('Location: ./login.php');
exit;
