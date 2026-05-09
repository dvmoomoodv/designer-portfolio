<?php
/**
 * $current_nav 변수로 활성 nav 표시 가능 (현재 정적 사이트엔 강조 스타일이 따로 없으므로 단순 표시).
 */
$current_nav = $current_nav ?? '';
$site        = site_data();
$nav_items   = $site['nav'] ?? [];
$brand       = $site['brand'] ?? ['ko' => '', 'en' => 'ARCHIVE PORTFOLIO'];
$current     = current_lang();
$other_lang  = $current === 'ko' ? 'en' : 'ko';
?>
<header class="site-header relative z-30 border-b border-stone-200/80 bg-stone-50/85 backdrop-blur-xl dark:border-stone-800 dark:bg-stone-950/85">
  <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-5 lg:px-10">
    <a href="./index.php" class="header-brand text-sm font-medium tracking-[0.22em]"><?= te($brand) ?></a>
    <nav class="hidden items-center gap-8 md:flex" aria-label="Primary navigation">
      <?php foreach ($nav_items as $item): ?>
        <a data-nav="<?= e($item['id'] ?? '') ?>"
           href="<?= e($item['href'] ?? '#') ?>"
           class="nav-link<?= ($current_nav === ($item['id'] ?? '')) ? ' is-active' : '' ?>"><?= te($item['label'] ?? []) ?></a>
      <?php endforeach; ?>
    </nav>
    <div class="flex items-center gap-2">
      <a href="<?= e(lang_toggle_url($other_lang)) ?>"
         class="header-icon-button inline-flex h-10 min-w-10 items-center justify-center border px-2 text-xs font-medium tracking-wider focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent"
         aria-label="Toggle language">
        <?= strtoupper(e($other_lang)) ?>
      </a>
      <button type="button" data-theme-toggle class="header-icon-button inline-flex h-10 w-10 items-center justify-center border focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent" aria-label="Toggle color theme">
        <span data-theme-icon aria-hidden="true">◐</span>
      </button>
      <button type="button" data-menu-open class="header-icon-button inline-flex h-10 w-10 items-center justify-center border focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent md:hidden" aria-label="Open menu">
        <span aria-hidden="true">≡</span>
      </button>
    </div>
  </div>
</header>
