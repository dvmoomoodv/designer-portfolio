<?php
/**
 * $current_nav 변수로 활성 nav 표시 가능 (현재 정적 사이트엔 강조 스타일이 따로 없으므로 단순 표시).
 */
$current_nav = $current_nav ?? '';
$site        = site_data();
$nav_items   = $site['nav'] ?? [];
$brand       = $site['brand'] ?? ['ko' => '', 'en' => 'ARCHIVE PORTFOLIO'];
$design      = resolved_design([]);
$brand_script = trim((string)($design['brand_script_text'] ?? 'rohigraphy')) ?: 'rohigraphy';
$show_brand_main = (string)($design['show_brand_main'] ?? '1') !== '0';
$current     = current_lang();
$other_lang  = $current === 'ko' ? 'en' : 'ko';
$active_filter = isset($_GET['filter']) ? (string)$_GET['filter'] : '';
$contact     = load_data('contact');
$contact_links = [];
if (!empty($contact['email']['address'])) {
    $contact_links[] = [
        'href' => 'mailto:' . $contact['email']['address'],
        'label' => $contact['email']['label'] ?? ['ko' => '', 'en' => 'Email'],
    ];
}
foreach (($contact['social']['items'] ?? []) as $link) {
    $contact_links[] = $link;
}
?>
<header class="site-header relative z-30 border-b border-stone-200/80 bg-stone-50/85 backdrop-blur-xl dark:border-stone-800 dark:bg-stone-950/85">
  <div class="site-header-inner mx-auto flex max-w-7xl items-center justify-between px-6 lg:px-10">
    <a href="./index.php" class="header-brand" aria-label="Rohigraphy Archive Portfolio">
      <span class="brand-script"><?= e($brand_script) ?></span>
      <?php if ($show_brand_main): ?>
        <span class="brand-main"><?= te($brand) ?></span>
      <?php endif; ?>
    </a>
    <nav class="hidden items-center gap-2 lg:gap-4 xl:gap-5 md:flex" aria-label="Primary navigation">
      <?php foreach ($nav_items as $item): ?>
        <?php
          $children = is_array($item['children'] ?? null) ? $item['children'] : [];
          $child_active = false;
          foreach ($children as $child) {
              if ($current_nav === ($child['id'] ?? '')) { $child_active = true; break; }
          }
          $is_active = $current_nav === ($item['id'] ?? '') || $child_active;
        ?>
        <?php if ($children): ?>
          <div class="nav-dropdown-wrap">
            <button type="button"
                    data-dropdown-toggle
                    data-nav="<?= e($item['id'] ?? '') ?>"
                    class="nav-link<?= $is_active ? ' is-active' : '' ?>"
                    aria-haspopup="true"
                    aria-expanded="false"><?= te($item['label'] ?? []) ?></button>
            <div class="nav-dropdown" aria-label="<?= te($item['label'] ?? []) ?> links">
              <?php foreach ($children as $child): ?>
                <?php
                  $child_href = (string)($child['href'] ?? '#');
                  $child_current = $current_nav === ($child['id'] ?? '')
                      || ($active_filter !== '' && strpos($child_href, 'filter=' . rawurlencode($active_filter)) !== false);
                ?>
                <a data-nav="<?= e($child['id'] ?? '') ?>" href="<?= e($child_href) ?>"<?= $child_current ? ' aria-current="page"' : '' ?>><?= te($child['label'] ?? []) ?></a>
              <?php endforeach; ?>
            </div>
          </div>
        <?php elseif (($item['id'] ?? '') === 'contact' && $contact_links): ?>
          <div class="nav-dropdown-wrap">
            <a data-nav="<?= e($item['id'] ?? '') ?>"
               href="<?= e($item['href'] ?? '#') ?>"
               class="nav-link<?= $is_active ? ' is-active' : '' ?>"
               aria-haspopup="true"<?= $is_active ? ' aria-current="page"' : '' ?>><?= te($item['label'] ?? []) ?></a>
            <div class="nav-dropdown" aria-label="Contact quick links">
              <a href="./contact.php">Contact Page</a>
              <?php foreach ($contact_links as $link): ?>
                <a href="<?= e($link['href'] ?? '#') ?>"><?= te($link['label'] ?? []) ?></a>
              <?php endforeach; ?>
            </div>
          </div>
        <?php else: ?>
          <a data-nav="<?= e($item['id'] ?? '') ?>"
             href="<?= e($item['href'] ?? '#') ?>"
             class="nav-link<?= $is_active ? ' is-active' : '' ?>"<?= $is_active ? ' aria-current="page"' : '' ?>><?= te($item['label'] ?? []) ?></a>
        <?php endif; ?>
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
