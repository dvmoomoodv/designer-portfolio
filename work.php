<?php
require_once __DIR__ . '/includes/bootstrap.php';

$data         = load_data('work');
$projects     = load_data('projects')['items'] ?? [];
$current_nav  = 'work';
$page_title   = te($data['page']['title']       ?? 'Work');
$page_description = te($data['page']['description'] ?? '');
$footer_links = [
    ['href' => './about.php',   'label' => ['ko' => '', 'en' => 'About']],
    ['href' => './contact.php', 'label' => ['ko' => '', 'en' => 'Contact']],
    ['href' => './resume.php',  'label' => ['ko' => '', 'en' => 'Resume']],
];

$hero    = $data['hero']    ?? [];
$filters = $data['filters'] ?? [];
$active_filter = active_filter_from($filters);
$projects = filter_by_category($projects, $active_filter);
$pager = paginate_array($projects, pagination_settings($data, 6));
$projects = $pager['items'];
$gallery_classes = ['concept-wide', 'concept-small', 'concept-small', 'concept-wide', 'concept-small', 'concept-small'];
?>
<?php include __DIR__ . '/includes/partials/head.php'; ?>
<body class="page-shell bg-stone-50 text-stone-900 antialiased transition-colors duration-300 dark:bg-stone-950 dark:text-stone-100" style="<?= e(page_design_style($data)) ?>">
  <?php include __DIR__ . '/includes/partials/header.php'; ?>
  <?php include __DIR__ . '/includes/partials/drawer.php'; ?>

  <main class="concept-work-page mx-auto max-w-7xl px-6 py-16 lg:px-10 lg:py-24">
    <section class="concept-hero grid gap-8 lg:grid-cols-[0.7fr_1.3fr] lg:items-end">
      <div>
        <p class="text-xs uppercase tracking-[0.22em] text-stone-500 dark:text-stone-400"><?= te($hero['kicker'] ?? []) ?></p>
        <h1 class="muted-heading mt-3 text-4xl font-semibold tracking-tight sm:text-5xl"><?= te($hero['title'] ?? []) ?></h1>
      </div>
      <p class="muted-copy mt-6 max-w-2xl text-base leading-8 text-stone-600 dark:text-stone-300"><?= te($hero['body'] ?? []) ?></p>
    </section>

    <section class="surface-band mt-14 border-y border-stone-200 px-4 py-7 dark:border-stone-800 sm:px-5">
      <div class="flex flex-wrap gap-4" aria-label="Concept Art categories">
        <?php foreach ($filters as $i => $f): ?>
          <?php $fid = (string)($f['id'] ?? ''); ?>
          <a href="<?= e(page_url(['filter' => $fid, 'page' => 1])) ?>" data-filter="<?= e($fid) ?>" class="filter-pill<?= $fid === $active_filter ? ' is-active' : '' ?>"><?= te($f['label'] ?? []) ?></a>
        <?php endforeach; ?>
      </div>
    </section>

    <section class="mt-12">
      <div class="concept-gallery">
        <?php foreach ($projects as $i => $p): ?>
          <?php $layout = $gallery_classes[$i % count($gallery_classes)]; ?>
          <article data-category="<?= e($p['category_id'] ?? '') ?>" class="work-card concept-gallery-item <?= e($layout) ?>">
            <a href="<?= e($p['cover'] ?? '') ?>" data-image-frame data-lightbox class="image-frame concept-image-link">
              <img src="<?= e($p['cover'] ?? '') ?>" alt="<?= te($p['cover_alt'] ?? []) ?>" loading="lazy">
            </a>
            <div class="concept-caption mt-3 flex items-start justify-between gap-4">
              <div>
                <h2 class="surface-title text-lg font-medium"><a href="./project.php?id=<?= e($p['id'] ?? '') ?>" class="surface-link"><?= te($p['title'] ?? []) ?></a></h2>
                <p class="mt-1 text-sm text-stone-500 dark:text-stone-400"><?= te($p['category_label'] ?? []) ?></p>
              </div>
              <span class="text-sm text-stone-500 dark:text-stone-400"><?= e($p['year'] ?? '') ?></span>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
      <?php if ($pager['total_pages'] > 1): ?>
        <nav class="archive-pagination" aria-label="Concept Art pagination">
          <?php for ($page = 1; $page <= $pager['total_pages']; $page++): ?>
            <a href="<?= e(page_url(['page' => $page])) ?>" class="<?= $page === $pager['page'] ? 'is-active' : '' ?>"><?= e($page) ?></a>
          <?php endfor; ?>
        </nav>
      <?php endif; ?>
    </section>
  </main>

  <?php include __DIR__ . '/includes/partials/footer.php'; ?>
<?php include __DIR__ . '/includes/partials/scripts.php'; ?>
