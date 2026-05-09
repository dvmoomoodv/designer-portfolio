<?php
/**
 * 초기 비밀번호 설정.
 *  - 웹: auth 미초기화 상태에서만 폼 노출. 설정 후 자동 로그인 → 대시보드.
 *  - CLI: `php admin/setup.php <password>` 또는 인자 없이 실행 시 stdin 으로 입력 받음.
 *    이미 초기화된 상태에서도 CLI 로는 비밀번호 재설정 가능 (서버 셸 접근 권한 필요).
 */
require_once __DIR__ . '/../includes/bootstrap.php';

if (PHP_SAPI === 'cli') {
    $pw = $argv[1] ?? null;
    if ($pw === null) {
        fwrite(STDOUT, "새 비밀번호 (8자 이상): ");
        $pw = trim((string)fgets(STDIN));
    }
    try {
        auth_set_password($pw);
        fwrite(STDOUT, "✓ 비밀번호 설정 완료: " . AUTH_FILE . "\n");
        exit(0);
    } catch (Throwable $e) {
        fwrite(STDERR, "✗ " . $e->getMessage() . "\n");
        exit(1);
    }
}

if (auth_is_initialized()) {
    header('Location: ./login.php');
    exit;
}

$error = null;
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        $error = '세션이 만료되었습니다. 다시 시도해주세요.';
    } else {
        $pw1 = (string)($_POST['password']         ?? '');
        $pw2 = (string)($_POST['password_confirm'] ?? '');
        if ($pw1 !== $pw2) {
            $error = '비밀번호 확인이 일치하지 않습니다.';
        } else {
            try {
                auth_set_password($pw1);
                auth_login();
                header('Location: ./index.php');
                exit;
            } catch (Throwable $e) {
                $error = $e->getMessage();
            }
        }
    }
}

$admin_title       = '초기 설정';
$admin_show_header = false;
include __DIR__ . '/_layout_head.php';
?>
<div class="mx-auto max-w-md py-12">
  <div class="admin-card p-8">
    <h1 class="text-2xl font-semibold tracking-tight">초기 비밀번호 설정</h1>
    <p class="mt-3 text-sm leading-6 text-stone-600 dark:text-stone-400">
      어드민 비밀번호가 아직 설정되지 않았습니다. 안전한 비밀번호를 입력하세요. 한 번 설정되면 이 페이지는 더 이상 노출되지 않습니다.
    </p>
    <?php if ($error): ?>
      <div class="admin-flash admin-flash--error mt-6"><?= e($error) ?></div>
    <?php endif; ?>
    <form method="post" class="mt-6 space-y-5" autocomplete="off">
      <?= csrf_field() ?>
      <div>
        <label class="admin-label" for="password">비밀번호 (최소 8자)</label>
        <input class="admin-input" type="password" name="password" id="password" required minlength="8" autofocus>
      </div>
      <div>
        <label class="admin-label" for="password_confirm">비밀번호 확인</label>
        <input class="admin-input" type="password" name="password_confirm" id="password_confirm" required minlength="8">
      </div>
      <button type="submit" class="admin-btn admin-btn--primary w-full">설정하고 로그인</button>
    </form>
  </div>
</div>
<?php include __DIR__ . '/_layout_foot.php'; ?>
