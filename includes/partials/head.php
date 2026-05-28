<?php
/**
 * 사용법:
 *   $page_title       = '...';
 *   $page_description = '...';
 *   include __DIR__ . '/.../partials/head.php';
 *
 * 기존 정적 사이트의 head 영역(테마 부트스트랩 + Tailwind 설정 + custom.css)을 그대로 보존한다.
 */
$page_title       = $page_title       ?? te(site_data()['meta']['default_title']       ?? 'Archive Portfolio');
$page_description = $page_description ?? te(site_data()['meta']['default_description'] ?? '');
$lang             = current_lang();
$site_design      = resolved_design([]);
$font_url         = (string)($site_design['font_url'] ?? '');
$font_family      = preg_replace('/[^A-Za-z0-9 _-]+/', '', (string)($site_design['font_family'] ?? '')) ?: 'Inter';
$font_ext         = strtolower(pathinfo($font_url, PATHINFO_EXTENSION));
$font_format      = $font_ext === 'woff2' ? 'woff2' : ($font_ext === 'woff' ? 'woff' : ($font_ext === 'otf' ? 'opentype' : 'truetype'));
?>
<!DOCTYPE html>
<html lang="<?= e($lang) ?>" class="scroll-smooth">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $page_title ?></title>
  <meta name="description" content="<?= $page_description ?>">
  <script>
    (function () {
      var savedTheme = localStorage.getItem('theme');
      var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
      var theme = savedTheme === 'light' || savedTheme === 'dark' ? savedTheme : (prefersDark ? 'dark' : 'light');
      document.documentElement.classList.toggle('dark', theme === 'dark');
      document.documentElement.dataset.theme = theme;
      document.documentElement.style.colorScheme = theme;
    }());
    window.tailwind = window.tailwind || {};
    window.tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: {
            accent: {
              DEFAULT: '#746e67',
              soft: '#8c857d'
            }
          },
          fontFamily: {
            sans: ['Inter', 'ui-sans-serif', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'sans-serif']
          },
          boxShadow: {
            card: '0 18px 40px rgba(17, 24, 39, 0.08)'
          }
        }
      }
    };
  </script>
  <script src="https://cdn.tailwindcss.com?plugins=typography"></script>
  <?php if ($font_url !== ''): ?>
    <style>
      @font-face {
        font-family: '<?= e($font_family) ?>';
        src: url('<?= e($font_url) ?>') format('<?= e($font_format) ?>');
        font-display: swap;
      }
    </style>
  <?php endif; ?>
  <link rel="stylesheet" href="./assets/css/custom.css">
</head>
