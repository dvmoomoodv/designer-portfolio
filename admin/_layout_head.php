<?php
/**
 * 어드민 공통 레이아웃 (head + 헤더). 모든 admin 페이지에서 include.
 *   $admin_title       : 페이지 제목
 *   $admin_show_header : 헤더 표시 여부 (login/setup 에선 false)
 */
$admin_title       = $admin_title       ?? 'Admin';
$admin_show_header = $admin_show_header ?? true;
?>
<!DOCTYPE html>
<html lang="ko" class="scroll-smooth">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex,nofollow">
  <title><?= e($admin_title) ?> · rohigraphy admin</title>
  <script>
    (function () {
      var saved = localStorage.getItem('admin-theme');
      var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
      var theme = saved === 'light' || saved === 'dark' ? saved : (prefersDark ? 'dark' : 'light');
      document.documentElement.classList.toggle('dark', theme === 'dark');
      document.documentElement.dataset.theme = theme;
      document.documentElement.style.colorScheme = theme;
    }());
    window.tailwind = window.tailwind || {};
    window.tailwind.config = { darkMode: 'class' };
  </script>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="./assets/admin.css">
</head>
<body class="min-h-screen bg-stone-100 text-stone-900 antialiased dark:bg-stone-950 dark:text-stone-100">
<?php if ($admin_show_header): ?>
  <header class="border-b border-stone-200 bg-white/80 backdrop-blur dark:border-stone-800 dark:bg-stone-900/80">
    <div class="mx-auto flex max-w-6xl items-center justify-between gap-6 px-6 py-4">
      <div class="flex items-center gap-3">
        <a href="./index.php" class="text-sm font-semibold tracking-[0.2em] uppercase text-stone-700 dark:text-stone-200">rohigraphy / admin</a>
        <span class="hidden text-xs text-stone-400 sm:inline">·</span>
        <a href="../index.php" target="_blank" rel="noopener" class="hidden text-xs text-stone-500 hover:text-stone-700 sm:inline dark:text-stone-400 dark:hover:text-stone-200">↗ 사이트 보기</a>
      </div>
      <div class="flex items-center gap-3 text-sm">
        <button type="button" id="adminThemeToggle" class="admin-btn admin-btn--ghost" aria-label="테마 전환">
          <span class="hidden dark:inline">☀</span>
          <span class="inline dark:hidden">☾</span>
        </button>
        <?php if (function_exists('auth_is_logged_in') && auth_is_logged_in()): ?>
          <form method="post" action="./logout.php" class="inline">
            <?= csrf_field() ?>
            <button type="submit" class="admin-btn admin-btn--ghost">로그아웃</button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </header>
<?php endif; ?>
<main class="mx-auto max-w-6xl px-6 py-10">
