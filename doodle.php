<?php
require_once __DIR__ . '/includes/bootstrap.php';

$data         = load_data('doodle');
$current_nav  = 'doodle';
$page_title   = te($data['page']['title'] ?? []) ?: 'Doodle & Scribble';
$page_description = te($data['page']['description'] ?? []) ?: '';
$footer_links = [
    ['href' => './work.php',       'label' => ['ko' => '', 'en' => 'Concept Art']],
    ['href' => './photograph.php', 'label' => ['ko' => '', 'en' => 'Photograph']],
    ['href' => './contact.php',    'label' => ['ko' => '', 'en' => 'Contact']],
];

$hero       = $data['hero']       ?? [];
$filters    = $data['filters']    ?? [];
$items      = $data['items']      ?? [];
$pagination = $data['pagination'] ?? [];
?>
<?php include __DIR__ . '/includes/partials/head.php'; ?>
<body class="page-shell bg-stone-50 text-stone-900 antialiased transition-colors duration-300 dark:bg-stone-950 dark:text-stone-100">
  <?php include __DIR__ . '/includes/partials/header.php'; ?>
  <?php include __DIR__ . '/includes/partials/drawer.php'; ?>

  <main class="doodle-page mx-auto max-w-7xl px-6 py-16 lg:px-10 lg:py-24">
    <section class="doodle-hero grid gap-8 lg:grid-cols-[0.6fr_1.4fr] lg:items-end">
      <div>
        <p class="text-xs uppercase tracking-[0.22em] text-stone-500 dark:text-stone-400"><?= te($hero['kicker'] ?? []) ?></p>
        <h1 class="muted-heading mt-3 text-4xl font-semibold tracking-tight sm:text-5xl"><?= te($hero['title'] ?? []) ?></h1>
      </div>
      <p class="muted-copy max-w-2xl text-base leading-8 text-stone-600 dark:text-stone-300"><?= te($hero['body'] ?? []) ?></p>
    </section>

    <section class="surface-band mt-14 border-y border-stone-200 px-4 py-7 dark:border-stone-800 sm:px-5">
      <div class="flex flex-wrap gap-4" aria-label="Doodle and Scribble categories">
        <?php foreach ($filters as $i => $f): ?>
          <button type="button" data-filter="<?= e($f['id'] ?? '') ?>" class="filter-pill<?= $i === 0 ? ' is-active' : '' ?>"><?= te($f['label'] ?? []) ?></button>
        <?php endforeach; ?>
      </div>
    </section>

    <section class="mt-12">
      <div class="doodle-grid" aria-label="Doodle and Scribble gallery">
        <?php foreach ($items as $item): ?>
          <article data-category="<?= e($item['category_id'] ?? '') ?>" class="work-card doodle-card">
            <a href="<?= e($item['image'] ?? '') ?>" data-image-frame data-lightbox class="image-frame doodle-thumb">
              <img src="<?= e($item['image'] ?? '') ?>" alt="<?= te($item['image_alt'] ?? []) ?>" loading="lazy">
            </a>
            <div class="doodle-card-copy">
              <p><?= te($item['category_label'] ?? []) ?> · <?= e($item['year'] ?? '') ?></p>
              <h2><?= te($item['title'] ?? []) ?></h2>
            </div>
          </article>
        <?php endforeach; ?>
      </div>

      <?php if ($pagination): ?>
        <nav class="doodle-pagination" aria-label="Doodle pagination preview">
          <?php foreach ($pagination as $i => $page): ?>
            <span class="<?= $i === 0 ? 'is-active' : '' ?>"><?= e($page) ?></span>
          <?php endforeach; ?>
        </nav>
      <?php endif; ?>
    </section>
  </main>

  <?php include __DIR__ . '/includes/partials/footer.php'; ?>
<?php include __DIR__ . '/includes/partials/scripts.php'; ?>
