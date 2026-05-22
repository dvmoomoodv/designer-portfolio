<?php
$current_nav = $current_nav ?? '';
$nav_items   = site_data()['nav'] ?? [];
$active_filter = isset($_GET['filter']) ? (string)$_GET['filter'] : '';
?>
<div data-menu-overlay class="fixed inset-0 z-40 hidden bg-stone-950/40 backdrop-blur-sm"></div>
<aside data-menu-drawer class="site-drawer fixed right-0 top-0 z-50 flex h-full w-full max-w-xs translate-x-full flex-col border-l border-stone-200 bg-stone-50 p-6 transition-transform duration-300 dark:border-stone-800 dark:bg-stone-950">
  <div class="mb-10 flex items-center justify-between">
    <span class="text-xs uppercase tracking-[0.22em] text-stone-500 dark:text-stone-400">Navigation</span>
    <button type="button" data-menu-close class="header-icon-button inline-flex h-10 w-10 items-center justify-center border" aria-label="Close menu">×</button>
  </div>
  <nav class="flex flex-col gap-5 text-lg" aria-label="Mobile navigation">
    <?php foreach ($nav_items as $item): ?>
      <?php
        $children = is_array($item['children'] ?? null) ? $item['children'] : [];
        $child_active = false;
        foreach ($children as $child) {
            if ($current_nav === ($child['id'] ?? '')) { $child_active = true; break; }
        }
      ?>
      <a data-nav="<?= e($item['id'] ?? '') ?>"
         href="<?= e($item['href'] ?? '#') ?>"
         class="drawer-link<?= ($current_nav === ($item['id'] ?? '') || $child_active) ? ' is-active' : '' ?>"><?= te($item['label'] ?? []) ?></a>
      <?php if ($children): ?>
        <div class="drawer-sub-links">
          <?php foreach ($children as $child): ?>
            <?php
              $child_href = (string)($child['href'] ?? '#');
              $child_current = $current_nav === ($child['id'] ?? '')
                  || ($active_filter !== '' && str_contains($child_href, 'filter=' . rawurlencode($active_filter)));
            ?>
            <a data-nav="<?= e($child['id'] ?? '') ?>" href="<?= e($child_href) ?>" class="drawer-sub-link<?= $child_current ? ' is-active' : '' ?>"><?= te($child['label'] ?? []) ?></a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    <?php endforeach; ?>
  </nav>
</aside>
