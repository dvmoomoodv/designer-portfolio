<?php
require_once __DIR__ . '/includes/bootstrap.php';

$data         = load_data('photograph');
$current_nav  = 'photograph';
$page_title   = te($data['page']['title'] ?? []) ?: 'Photograph';
$page_description = te($data['page']['description'] ?? []) ?: '';
$footer_links = [
    ['href' => './work.php',     'label' => ['ko' => '', 'en' => 'Concept Art']],
    ['href' => './research.php', 'label' => ['ko' => '', 'en' => 'Research']],
    ['href' => './contact.php',  'label' => ['ko' => '', 'en' => 'Contact']],
];

$hero    = $data['hero']    ?? [];
$filters = $data['filters'] ?? [];
$items   = $data['items']   ?? [];
$active_filter = active_filter_from($filters);
$items = filter_by_category($items, $active_filter);
$pager = paginate_array($items, pagination_settings($data, 6));
$items = $pager['items'];
$layout_classes = ['photo-tall', 'photo-large', 'photo-small', 'photo-wide', 'photo-small', 'photo-tall', 'photo-small'];
?>
<?php include __DIR__ . '/includes/partials/head.php'; ?>
<body class="page-shell bg-stone-50 text-stone-900 antialiased transition-colors duration-300 dark:bg-stone-950 dark:text-stone-100">
  <?php include __DIR__ . '/includes/partials/header.php'; ?>
  <?php include __DIR__ . '/includes/partials/drawer.php'; ?>

  <main class="photograph-page mx-auto max-w-7xl px-6 py-16 lg:px-10 lg:py-24">
    <section class="photograph-hero grid gap-8 lg:grid-cols-[0.58fr_1.42fr] lg:items-end">
      <div>
        <p class="text-xs uppercase tracking-[0.22em] text-stone-500 dark:text-stone-400"><?= te($hero['kicker'] ?? []) ?></p>
        <h1 class="muted-heading mt-3 text-4xl font-semibold tracking-tight sm:text-5xl"><?= te($hero['title'] ?? []) ?></h1>
      </div>
      <p class="muted-copy max-w-2xl text-base leading-8 text-stone-600 dark:text-stone-300"><?= te($hero['body'] ?? []) ?></p>
    </section>

    <section class="surface-band mt-14 border-y border-stone-200 px-4 py-7 dark:border-stone-800 sm:px-5">
      <div class="flex flex-wrap gap-4" aria-label="Photograph categories">
        <?php foreach ($filters as $i => $f): ?>
          <?php $fid = (string)($f['id'] ?? ''); ?>
          <a href="<?= e(page_url(['filter' => $fid, 'page' => 1])) ?>" data-filter="<?= e($fid) ?>" class="filter-pill<?= $fid === $active_filter ? ' is-active' : '' ?>"><?= te($f['label'] ?? []) ?></a>
        <?php endforeach; ?>
      </div>
    </section>

    <section class="mt-12">
      <div class="photo-mosaic" aria-label="Photograph gallery">
        <?php foreach ($items as $i => $item): ?>
          <?php $layout = $layout_classes[$i % count($layout_classes)]; ?>
          <article data-category="<?= e($item['category_id'] ?? '') ?>" class="work-card photo-card <?= e($layout) ?>">
            <a href="<?= e($item['image'] ?? '') ?>" data-image-frame data-lightbox class="image-frame photo-frame">
              <img src="<?= e($item['image'] ?? '') ?>" alt="<?= te($item['image_alt'] ?? []) ?>" loading="lazy">
            </a>
            <div class="photo-caption">
              <p><?= te($item['category_label'] ?? []) ?> · <?= e($item['year'] ?? '') ?></p>
              <h2><?= te($item['title'] ?? []) ?></h2>
              <span><?= te($item['location'] ?? []) ?></span>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
      <?php if ($pager['total_pages'] > 1): ?>
        <nav class="archive-pagination" aria-label="Photograph pagination">
          <?php for ($page = 1; $page <= $pager['total_pages']; $page++): ?>
            <a href="<?= e(page_url(['page' => $page])) ?>" class="<?= $page === $pager['page'] ? 'is-active' : '' ?>"><?= e($page) ?></a>
          <?php endfor; ?>
        </nav>
      <?php endif; ?>
    </section>
  </main>

  <?php include __DIR__ . '/includes/partials/footer.php'; ?>
<?php include __DIR__ . '/includes/partials/scripts.php'; ?>
