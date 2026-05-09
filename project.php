<?php
require_once __DIR__ . '/includes/bootstrap.php';

$all_projects = load_data('projects')['items'] ?? [];
$req_id       = isset($_GET['id']) ? (string)$_GET['id'] : ($all_projects[0]['id'] ?? '');
$project      = null;
$index        = -1;
foreach ($all_projects as $i => $p) {
    if (($p['id'] ?? '') === $req_id) { $project = $p; $index = $i; break; }
}
if (!$project) {
    http_response_code(404);
    $project = $all_projects[0] ?? null;
    $index   = 0;
}

$current_nav  = 'work';
$page_title   = te($project['title'] ?? []) ?: 'Project';
$page_description = te($project['cover_alt'] ?? []) ?: '';
$footer_links = [
    ['href' => './work.php',    'label' => ['ko' => '', 'en' => 'Archive']],
    ['href' => './contact.php', 'label' => ['ko' => '', 'en' => 'Contact']],
];

$detail = $project['detail'] ?? null;
$prev = $index > 0 ? $all_projects[$index - 1] : null;
$next = ($index >= 0 && $index < count($all_projects) - 1) ? $all_projects[$index + 1] : null;
?>
<?php include __DIR__ . '/includes/partials/head.php'; ?>
<body class="page-shell bg-stone-50 text-stone-900 antialiased transition-colors duration-300 dark:bg-stone-950 dark:text-stone-100">
  <?php include __DIR__ . '/includes/partials/header.php'; ?>
  <?php include __DIR__ . '/includes/partials/drawer.php'; ?>

  <main class="mx-auto max-w-7xl px-6 py-16 lg:px-10 lg:py-24">
    <section class="surface-band grid gap-10 px-4 py-6 lg:grid-cols-[0.55fr_1.45fr] lg:items-end lg:px-6">
      <div class="lg:pr-6">
        <p class="text-xs uppercase tracking-[0.22em] text-stone-500 dark:text-stone-400"><?= te($detail['kicker'] ?? []) ?: te($project['category_label'] ?? []) . ' · ' . e($project['year'] ?? '') ?></p>
        <h1 class="muted-heading mt-3 text-4xl font-semibold tracking-tight sm:text-5xl"><?= te($project['title'] ?? []) ?></h1>
        <?php if (!empty($detail['info_dl'])): ?>
          <dl class="mt-8 space-y-3 text-sm text-stone-600 dark:text-stone-300">
            <?php foreach ($detail['info_dl'] as $row): ?>
              <div class="flex items-start justify-between gap-6 border-b border-stone-200 pb-3 dark:border-stone-800">
                <dt><?= te($row['label'] ?? []) ?></dt>
                <dd><?= te($row['value'] ?? []) ?></dd>
              </div>
            <?php endforeach; ?>
          </dl>
        <?php endif; ?>
      </div>
      <a href="<?= e($detail['hero_image'] ?? $project['cover'] ?? '') ?>" data-image-frame class="image-frame surface-card overflow-hidden border border-stone-200 bg-stone-100 dark:border-stone-800 dark:bg-stone-900">
        <img src="<?= e($detail['hero_image'] ?? $project['cover'] ?? '') ?>" alt="<?= te($detail['hero_alt'] ?? $project['cover_alt'] ?? []) ?>" class="aspect-[16/10] w-full object-cover">
      </a>
    </section>

    <?php if (!$detail): ?>
      <section class="mt-20">
        <div class="surface-card border border-stone-200 p-8 dark:border-stone-800">
          <p class="text-sm text-stone-500 dark:text-stone-400">Notice</p>
          <p class="surface-title mt-3 text-xl font-medium">Project detail in preparation.</p>
          <p class="mt-3 leading-7 text-stone-600 dark:text-stone-300">상세 컨텐츠를 어드민에서 곧 추가합니다.</p>
        </div>
      </section>
    <?php else: ?>
      <?php if (!empty($detail['overview_paragraphs'])): ?>
        <section class="mt-20 grid gap-14 lg:grid-cols-[0.5fr_1fr]">
          <div>
            <p class="text-xs uppercase tracking-[0.22em] text-stone-500 dark:text-stone-400"><?= te($detail['overview_kicker'] ?? []) ?></p>
          </div>
          <div class="surface-subtle space-y-8 px-5 py-5">
            <?php foreach ($detail['overview_paragraphs'] as $i => $p): ?>
              <?php if ($i === 0): ?>
                <p class="max-w-3xl text-lg leading-9 text-stone-700 dark:text-stone-200"><?= te($p['text'] ?? []) ?></p>
              <?php else: ?>
                <p class="max-w-3xl leading-8 text-stone-600 dark:text-stone-300"><?= te($p['text'] ?? []) ?></p>
              <?php endif; ?>
            <?php endforeach; ?>
          </div>
        </section>
      <?php endif; ?>

      <?php if (!empty($detail['concept_blocks'])): ?>
        <section class="mt-20 grid gap-14 lg:grid-cols-[0.5fr_1fr]">
          <div>
            <p class="text-xs uppercase tracking-[0.22em] text-stone-500 dark:text-stone-400"><?= te($detail['concept_kicker'] ?? []) ?></p>
          </div>
          <div class="grid gap-10 lg:grid-cols-2">
            <?php foreach ($detail['concept_blocks'] as $cb): ?>
              <div class="surface-card space-y-4 border border-stone-200 p-5 dark:border-stone-800">
                <h2 class="surface-title text-xl font-medium"><?= te($cb['title'] ?? []) ?></h2>
                <p class="leading-8 text-stone-600 dark:text-stone-300"><?= te($cb['body'] ?? []) ?></p>
              </div>
            <?php endforeach; ?>
          </div>
        </section>
      <?php endif; ?>

      <?php if (!empty($detail['gallery_images'])): ?>
        <section class="mt-20">
          <div class="section-divider mb-8 flex items-end justify-between gap-6 border-b border-stone-200 pb-5 dark:border-stone-800">
            <div>
              <p class="text-xs uppercase tracking-[0.22em] text-stone-500 dark:text-stone-400"><?= te($detail['gallery_kicker'] ?? []) ?></p>
              <h2 class="surface-title mt-2 text-2xl font-semibold tracking-tight"><?= te($detail['gallery_title'] ?? []) ?></h2>
            </div>
          </div>
          <div class="grid gap-6 md:grid-cols-2">
            <?php foreach ($detail['gallery_images'] as $i => $g): ?>
              <a href="<?= e($g['src'] ?? '') ?>" data-image-frame class="image-frame surface-card overflow-hidden border border-stone-200 bg-stone-100 dark:border-stone-800 dark:bg-stone-900<?= $i === count($detail['gallery_images']) - 1 && count($detail['gallery_images']) % 2 === 1 ? ' md:col-span-2' : '' ?>">
                <img src="<?= e($g['src'] ?? '') ?>" alt="<?= te($g['alt'] ?? []) ?>" class="<?= $i === count($detail['gallery_images']) - 1 && count($detail['gallery_images']) % 2 === 1 ? 'aspect-[16/8]' : 'aspect-[5/4]' ?> w-full object-cover">
              </a>
            <?php endforeach; ?>
          </div>
        </section>
      <?php endif; ?>

      <?php if (!empty($detail['credits_dl'])): ?>
        <section class="surface-band mt-20 grid gap-14 border-y border-stone-200 py-10 lg:grid-cols-[0.5fr_1fr] dark:border-stone-800">
          <div>
            <p class="text-xs uppercase tracking-[0.22em] text-stone-500 dark:text-stone-400"><?= te($detail['credits_kicker'] ?? []) ?></p>
          </div>
          <dl class="grid gap-4 text-sm text-stone-600 sm:grid-cols-2 dark:text-stone-300">
            <?php foreach ($detail['credits_dl'] as $row): ?>
              <div class="border-b border-stone-200 pb-4 dark:border-stone-800">
                <dt class="text-stone-500 dark:text-stone-400"><?= te($row['label'] ?? []) ?></dt>
                <dd class="mt-2 text-base text-stone-900 dark:text-stone-100"><?= te($row['value'] ?? []) ?></dd>
              </div>
            <?php endforeach; ?>
          </dl>
        </section>
      <?php endif; ?>
    <?php endif; ?>

    <nav class="section-divider mt-14 grid gap-6 border-t border-stone-200 pt-8 sm:grid-cols-2 dark:border-stone-800" aria-label="Project pagination">
      <?php if ($prev): ?>
        <a href="./project.php?id=<?= e($prev['id']) ?>" class="surface-card border border-stone-200 p-5 transition hover:border-stone-400 dark:border-stone-800 dark:hover:border-stone-600">
          <p class="text-sm text-stone-500 dark:text-stone-400">Previous project</p>
          <p class="surface-title mt-2 text-lg font-medium"><?= te($prev['title'] ?? []) ?></p>
        </a>
      <?php else: ?><div></div><?php endif; ?>
      <?php if ($next): ?>
        <a href="./project.php?id=<?= e($next['id']) ?>" class="surface-card border border-stone-200 p-5 text-right transition hover:border-stone-400 dark:border-stone-800 dark:hover:border-stone-600">
          <p class="text-sm text-stone-500 dark:text-stone-400">Next project</p>
          <p class="surface-title mt-2 text-lg font-medium"><?= te($next['title'] ?? []) ?></p>
        </a>
      <?php else: ?><div></div><?php endif; ?>
    </nav>
  </main>

  <?php include __DIR__ . '/includes/partials/footer.php'; ?>
<?php include __DIR__ . '/includes/partials/scripts.php'; ?>
