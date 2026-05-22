<?php
require_once __DIR__ . '/includes/bootstrap.php';

$data         = load_data('research');
$current_nav  = 'research';
$page_title   = te($data['page']['title'] ?? []) ?: 'Research';
$page_description = te($data['page']['description'] ?? []) ?: '';
$footer_links = [
    ['href' => './work.php',    'label' => ['ko' => '', 'en' => 'Concept Art']],
    ['href' => './resume.php',  'label' => ['ko' => '', 'en' => 'Resume']],
    ['href' => './contact.php', 'label' => ['ko' => '', 'en' => 'Contact']],
];

$hero    = $data['hero']    ?? [];
$filters = $data['filters'] ?? [];
$items   = $data['items']   ?? [];
$active_filter = active_filter_from($filters);
$items = filter_by_category($items, $active_filter);
$pager = paginate_array($items, pagination_settings($data, 5));
$items = $pager['items'];
$sidebar = $data['sidebar'] ?? [];
?>
<?php include __DIR__ . '/includes/partials/head.php'; ?>
<body class="page-shell bg-stone-50 text-stone-900 antialiased transition-colors duration-300 dark:bg-stone-950 dark:text-stone-100">
  <?php include __DIR__ . '/includes/partials/header.php'; ?>
  <?php include __DIR__ . '/includes/partials/drawer.php'; ?>

  <main class="research-page mx-auto max-w-7xl px-6 py-16 lg:px-10 lg:py-24">
    <section class="research-hero grid gap-8 lg:grid-cols-[0.62fr_1.38fr] lg:items-end">
      <div>
        <p class="text-xs uppercase tracking-[0.22em] text-stone-500 dark:text-stone-400"><?= te($hero['kicker'] ?? []) ?></p>
        <h1 class="muted-heading mt-3 text-4xl font-semibold tracking-tight sm:text-5xl"><?= te($hero['title'] ?? []) ?></h1>
      </div>
      <p class="muted-copy max-w-2xl text-base leading-8 text-stone-600 dark:text-stone-300"><?= te($hero['body'] ?? []) ?></p>
    </section>

    <section class="surface-band mt-14 border-y border-stone-200 px-4 py-7 dark:border-stone-800 sm:px-5">
      <div class="flex flex-wrap gap-4" aria-label="Research categories">
        <?php foreach ($filters as $i => $f): ?>
          <?php $fid = (string)($f['id'] ?? ''); ?>
          <a href="<?= e(page_url(['filter' => $fid, 'page' => 1])) ?>" data-filter="<?= e($fid) ?>" class="filter-pill<?= $fid === $active_filter ? ' is-active' : '' ?>"><?= te($f['label'] ?? []) ?></a>
        <?php endforeach; ?>
      </div>
    </section>

    <section class="mt-12 grid gap-10 lg:grid-cols-[1.55fr_0.45fr]">
      <div class="research-feed" aria-label="Research article list">
        <?php foreach ($items as $item): ?>
          <?php $url = trim((string)($item['source_url'] ?? '')); ?>
          <article data-category="<?= e($item['category_id'] ?? '') ?>" class="work-card research-card">
            <a href="<?= e($url !== '' ? $url : '#') ?>" class="research-thumb"<?= str_starts_with($url, 'http') ? ' target="_blank" rel="noopener noreferrer"' : '' ?> aria-label="<?= te($item['title'] ?? []) ?>">
              <img src="<?= e($item['thumbnail'] ?? '') ?>" alt="<?= te($item['thumbnail_alt'] ?? []) ?>" loading="lazy">
            </a>
            <div class="research-card-body">
              <p class="research-category"><?= te($item['category_label'] ?? []) ?></p>
              <h2><a href="<?= e($url !== '' ? $url : '#') ?>"<?= str_starts_with($url, 'http') ? ' target="_blank" rel="noopener noreferrer"' : '' ?>><?= te($item['title'] ?? []) ?></a></h2>
              <p class="research-excerpt"><?= te($item['excerpt'] ?? []) ?></p>
              <p class="research-meta"><?= te($item['meta'] ?? []) ?></p>
            </div>
          </article>
        <?php endforeach; ?>
        <?php if ($pager['total_pages'] > 1): ?>
          <nav class="archive-pagination" aria-label="Research pagination">
            <?php for ($page = 1; $page <= $pager['total_pages']; $page++): ?>
              <a href="<?= e(page_url(['page' => $page])) ?>" class="<?= $page === $pager['page'] ? 'is-active' : '' ?>"><?= e($page) ?></a>
            <?php endfor; ?>
          </nav>
        <?php endif; ?>
      </div>

      <aside class="research-sidebar surface-card border border-stone-200 p-5 dark:border-stone-800">
        <p class="text-xs uppercase tracking-[0.22em] text-stone-500 dark:text-stone-400"><?= te($sidebar['kicker'] ?? []) ?></p>
        <h2 class="surface-title mt-3 text-2xl font-semibold tracking-tight"><?= te($sidebar['title'] ?? []) ?></h2>
        <ol class="mt-6 space-y-3">
          <?php foreach (($sidebar['items'] ?? []) as $i => $row): ?>
            <li><span><?= str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT) ?></span><?= te($row['label'] ?? []) ?></li>
          <?php endforeach; ?>
        </ol>
      </aside>
    </section>
  </main>

  <?php include __DIR__ . '/includes/partials/footer.php'; ?>
<?php include __DIR__ . '/includes/partials/scripts.php'; ?>
