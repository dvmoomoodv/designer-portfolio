<?php
require_once __DIR__ . '/../includes/bootstrap.php';

if (auth_is_logged_in()) {
    header('Location: ./index.php');
    exit;
}

$next  = (string)($_GET['next'] ?? './index.php');
if (!preg_match('#^/(?!/)|^\./#', $next)) {
    $next = './index.php';
}

$error = null;
$lock  = auth_throttle_remaining();

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if ($lock > 0) {
        $error = "로그인 시도 횟수 초과. {$lock}초 후 다시 시도하세요.";
    } elseif (!csrf_verify($_POST['csrf_token'] ?? null)) {
        $error = '세션이 만료되었습니다. 다시 시도해주세요.';
    } else {
        $username = trim((string)($_POST['username'] ?? ''));
        $pw = (string)($_POST['password'] ?? '');
        if (auth_check_credentials($username, $pw)) {
            auth_record_success();
            auth_login();
            $dest = (string)($_POST['next'] ?? './index.php');
            if (!preg_match('#^/(?!/)|^\./#', $dest)) {
                $dest = './index.php';
            }
            header('Location: ' . $dest);
            exit;
        } else {
            auth_record_failure();
            usleep(400000);
            $error = '아이디 또는 비밀번호가 올바르지 않습니다.';
            $lock  = auth_throttle_remaining();
        }
    }
}

$admin_title       = '로그인';
$admin_show_header = false;
include __DIR__ . '/_layout_head.php';
?>
<div class="mx-auto max-w-md py-12">
  <div class="admin-card p-8">
    <h1 class="text-2xl font-semibold tracking-tight">어드민 로그인</h1>
    <p class="mt-2 text-sm text-stone-500 dark:text-stone-400">콘텐츠 수정을 위해 로그인하세요.</p>

    <?php if ($error): ?>
      <div class="admin-flash admin-flash--error mt-6"><?= e($error) ?></div>
    <?php elseif ($lock > 0): ?>
      <div class="admin-flash admin-flash--info mt-6">현재 잠금 상태입니다. <?= $lock ?>초 후 시도하세요.</div>
    <?php endif; ?>

    <form method="post" class="mt-6 space-y-5" autocomplete="off">
      <?= csrf_field() ?>
      <input type="hidden" name="next" value="<?= e($next) ?>">
      <div>
        <label class="admin-label" for="username">아이디</label>
        <input class="admin-input" type="text" name="username" id="username" required autofocus <?= $lock > 0 ? 'disabled' : '' ?>>
      </div>
      <div>
        <label class="admin-label" for="password">비밀번호</label>
        <input class="admin-input" type="password" name="password" id="password" required <?= $lock > 0 ? 'disabled' : '' ?>>
      </div>
      <button type="submit" class="admin-btn admin-btn--primary w-full" <?= $lock > 0 ? 'disabled' : '' ?>>로그인</button>
    </form>

    <p class="mt-6 text-xs text-stone-400 dark:text-stone-500">
      ↩ <a href="../index.php" class="hover:text-stone-700 dark:hover:text-stone-200">사이트로 돌아가기</a>
    </p>
  </div>
</div>
<?php include __DIR__ . '/_layout_foot.php'; ?>
