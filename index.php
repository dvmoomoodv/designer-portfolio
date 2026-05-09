<?php
require_once __DIR__ . '/includes/bootstrap.php';

$data         = load_data('index');
$current_nav  = 'index';
$page_title   = te($data['page']['title']       ?? site_data()['meta']['default_title']       ?? 'Archive Portfolio');
$page_description = te($data['page']['description'] ?? site_data()['meta']['default_description'] ?? '');
$footer_links = [
    ['href' => './about.php',   'label' => ['ko' => '', 'en' => 'Notes']],
    ['href' => './contact.php', 'label' => ['ko' => '', 'en' => 'Contact']],
    ['href' => './resume.php',  'label' => ['ko' => '', 'en' => 'Resume']],
];

$hero          = $data['hero']          ?? [];
$selected      = $data['selected_work'] ?? [];
$categories    = $data['categories']    ?? [];
$notes         = $data['notes']         ?? [];

$selected_projects = [];
foreach (($selected['project_ids'] ?? []) as $pid) {
    $p = find_project((string)$pid);
    if ($p) { $selected_projects[] = $p; }
}
?>
<?php include __DIR__ . '/includes/partials/head.php'; ?>
<body class="page-shell bg-stone-50 text-stone-900 antialiased transition-colors duration-300 dark:bg-stone-950 dark:text-stone-100">
  <div class="relative min-h-screen overflow-x-clip">
    <div class="pointer-events-none absolute inset-x-0 top-0 h-80 bg-[radial-gradient(circle_at_top,rgba(120,113,108,0.10),transparent_60%)] dark:bg-[radial-gradient(circle_at_top,rgba(231,229,228,0.08),transparent_60%)]"></div>

    <?php include __DIR__ . '/includes/partials/header.php'; ?>
    <?php include __DIR__ . '/includes/partials/drawer.php'; ?>

    <main>
      <section class="mx-auto max-w-7xl px-6 pb-20 pt-16 lg:px-10 lg:pb-28 lg:pt-24">
        <div class="grid gap-12 lg:grid-cols-[1.1fr_0.9fr] lg:items-end">
          <div class="max-w-3xl">
            <p class="mb-4 text-xs uppercase tracking-[0.24em] text-stone-500 dark:text-stone-400"><?= te($hero['kicker'] ?? []) ?></p>
            <h1 class="muted-heading max-w-4xl text-4xl font-semibold tracking-tight text-stone-900 sm:text-5xl lg:text-6xl dark:text-stone-100"><?= te($hero['title'] ?? []) ?></h1>
            <p class="muted-copy mt-6 max-w-2xl text-base leading-8 text-stone-600 dark:text-stone-300"><?= te($hero['body'] ?? []) ?></p>
            <?php if (!empty($hero['meta_items'])): ?>
              <div class="mt-10 flex flex-wrap items-center gap-4 text-sm text-stone-500 dark:text-stone-400">
                <?php $first = true; foreach ($hero['meta_items'] as $mi): ?>
                  <?php if (!$first): ?>
                    <span class="hidden h-1 w-1 rounded-full bg-stone-300 sm:block dark:bg-stone-700"></span>
                  <?php endif; $first = false; ?>
                  <span><?= te($mi['text'] ?? []) ?></span>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
          <div class="lg:pl-12">
            <a href="<?= e($hero['image'] ?? '#') ?>" data-image-frame class="image-frame surface-card overflow-hidden border border-stone-200 bg-stone-100 dark:border-stone-800 dark:bg-stone-900">
              <img src="<?= e($hero['image'] ?? '') ?>" alt="<?= te($hero['image_alt'] ?? []) ?>" class="h-full w-full object-cover transition duration-700 hover:scale-[1.02]">
            </a>
          </div>
        </div>
      </section>

      <section class="surface-subtle mx-auto max-w-7xl px-6 py-20 lg:px-10 lg:py-28">
        <div class="section-divider mb-10 flex items-end justify-between gap-6 border-b border-stone-200 pb-5 dark:border-stone-800">
          <div>
            <p class="text-xs uppercase tracking-[0.22em] text-stone-500 dark:text-stone-400"><?= te($selected['kicker'] ?? []) ?></p>
            <h2 class="surface-title mt-2 text-2xl font-semibold tracking-tight"><?= te($selected['title'] ?? []) ?></h2>
          </div>
          <a href="./work.php" class="meta-link text-sm"><?= te($selected['view_all_label'] ?? []) ?></a>
        </div>
        <div class="grid gap-8 md:grid-cols-2 xl:grid-cols-3">
          <?php foreach ($selected_projects as $p): ?>
            <article class="group space-y-4">
              <a href="<?= e($p['cover'] ?? '') ?>" data-image-frame class="image-frame surface-card block overflow-hidden border border-stone-200 bg-stone-100 dark:border-stone-800 dark:bg-stone-900">
                <img src="<?= e($p['cover'] ?? '') ?>" alt="<?= te($p['cover_alt'] ?? []) ?>" class="aspect-[4/5] w-full object-cover transition duration-500 group-hover:scale-[1.02] group-hover:opacity-95">
              </a>
              <div class="flex items-start justify-between gap-4">
                <div>
                  <h3 class="surface-title text-lg font-medium"><a href="./project.php?id=<?= e($p['id'] ?? '') ?>" class="surface-link"><?= te($p['title'] ?? []) ?></a></h3>
                  <p class="mt-1 text-sm text-stone-500 dark:text-stone-400"><?= te($p['category_label'] ?? []) ?></p>
                </div>
                <span class="text-sm text-stone-500 dark:text-stone-400"><?= e($p['year'] ?? '') ?></span>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </section>

      <section class="mx-auto max-w-7xl px-6 py-20 lg:px-10 lg:py-28">
        <div class="surface-band grid gap-10 border-y border-stone-200 py-12 lg:grid-cols-[0.7fr_1.3fr] dark:border-stone-800">
          <div>
            <p class="text-xs uppercase tracking-[0.22em] text-stone-500 dark:text-stone-400"><?= te($categories['kicker'] ?? []) ?></p>
            <h2 class="surface-title mt-2 text-2xl font-semibold tracking-tight"><?= te($categories['title'] ?? []) ?></h2>
          </div>
          <div class="grid gap-4 sm:grid-cols-2">
            <?php foreach (($categories['items'] ?? []) as $c): ?>
              <a href="./work.php" class="category-tile">
                <span><?= te($c['label'] ?? []) ?></span>
                <span class="meta"><?= te($c['meta'] ?? []) ?></span>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
      </section>

      <section class="mx-auto max-w-7xl px-6 py-20 lg:px-10 lg:py-28">
        <div class="section-divider mb-10 flex items-end justify-between gap-6 border-b border-stone-200 pb-5 dark:border-stone-800">
          <div>
            <p class="text-xs uppercase tracking-[0.22em] text-stone-500 dark:text-stone-400"><?= te($notes['kicker'] ?? []) ?></p>
            <h2 class="surface-title mt-2 text-2xl font-semibold tracking-tight"><?= te($notes['title'] ?? []) ?></h2>
          </div>
          <a href="./about.php" class="meta-link text-sm"><?= te($notes['view_all_label'] ?? []) ?></a>
        </div>
        <div class="grid gap-6 lg:grid-cols-3">
          <?php foreach (($notes['items'] ?? []) as $n): ?>
            <article class="surface-card border border-stone-200 p-6 transition hover:border-stone-400 dark:border-stone-800 dark:hover:border-stone-600">
              <p class="text-sm text-stone-500 dark:text-stone-400"><?= te($n['tag'] ?? []) ?></p>
              <h3 class="surface-title mt-6 text-xl font-medium"><?= te($n['title'] ?? []) ?></h3>
              <p class="mt-4 leading-7 text-stone-600 dark:text-stone-300"><?= te($n['body'] ?? []) ?></p>
            </article>
          <?php endforeach; ?>
        </div>
      </section>
    </main>

    <?php include __DIR__ . '/includes/partials/footer.php'; ?>
  </div>

<?php include __DIR__ . '/includes/partials/scripts.php'; ?>
