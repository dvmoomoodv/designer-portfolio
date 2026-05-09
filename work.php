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
?>
<?php include __DIR__ . '/includes/partials/head.php'; ?>
<body class="page-shell bg-stone-50 text-stone-900 antialiased transition-colors duration-300 dark:bg-stone-950 dark:text-stone-100">
  <?php include __DIR__ . '/includes/partials/header.php'; ?>
  <?php include __DIR__ . '/includes/partials/drawer.php'; ?>

  <main class="mx-auto max-w-7xl px-6 py-16 lg:px-10 lg:py-24">
    <section class="max-w-4xl">
      <p class="text-xs uppercase tracking-[0.22em] text-stone-500 dark:text-stone-400"><?= te($hero['kicker'] ?? []) ?></p>
      <h1 class="muted-heading mt-3 text-4xl font-semibold tracking-tight sm:text-5xl"><?= te($hero['title'] ?? []) ?></h1>
      <p class="muted-copy mt-6 max-w-2xl text-base leading-8 text-stone-600 dark:text-stone-300"><?= te($hero['body'] ?? []) ?></p>
    </section>

    <section class="surface-band mt-14 border-y border-stone-200 py-6 dark:border-stone-800">
      <div class="flex flex-wrap gap-3" aria-label="Work categories">
        <?php foreach ($filters as $i => $f): ?>
          <button type="button" data-filter="<?= e($f['id'] ?? '') ?>" class="filter-pill<?= $i === 0 ? ' is-active' : '' ?>"><?= te($f['label'] ?? []) ?></button>
        <?php endforeach; ?>
      </div>
    </section>

    <section class="mt-12">
      <div class="grid gap-x-8 gap-y-12 sm:grid-cols-2 xl:grid-cols-3">
        <?php foreach ($projects as $p): ?>
          <article data-category="<?= e($p['category_id'] ?? '') ?>" class="work-card">
            <a href="<?= e($p['cover'] ?? '') ?>" data-image-frame class="image-frame surface-card block overflow-hidden border border-stone-200 bg-stone-100 dark:border-stone-800 dark:bg-stone-900">
              <img src="<?= e($p['cover'] ?? '') ?>" alt="<?= te($p['cover_alt'] ?? []) ?>" class="aspect-[4/5] w-full object-cover transition duration-500 hover:scale-[1.02]">
            </a>
            <div class="mt-4 flex items-start justify-between gap-4">
              <div>
                <h2 class="surface-title text-lg font-medium"><a href="./project.php?id=<?= e($p['id'] ?? '') ?>" class="surface-link"><?= te($p['title'] ?? []) ?></a></h2>
                <p class="mt-1 text-sm text-stone-500 dark:text-stone-400"><?= te($p['category_label'] ?? []) ?></p>
              </div>
              <span class="text-sm text-stone-500 dark:text-stone-400"><?= e($p['year'] ?? '') ?></span>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </section>
  </main>

  <?php include __DIR__ . '/includes/partials/footer.php'; ?>
<?php include __DIR__ . '/includes/partials/scripts.php'; ?>
