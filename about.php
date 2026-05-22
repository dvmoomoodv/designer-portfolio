<?php
require_once __DIR__ . '/includes/bootstrap.php';

$data         = load_data('about');
$current_nav  = 'about';
$page_title   = te($data['page']['title']       ?? 'About');
$page_description = te($data['page']['description'] ?? '');
$footer_links = [
    ['href' => './work.php',    'label' => ['ko' => '', 'en' => 'Concept Art']],
    ['href' => './contact.php', 'label' => ['ko' => '', 'en' => 'Contact']],
];

$hero        = $data['hero']        ?? [];
$side_nav    = $data['side_nav']    ?? [];
$disciplines = $data['disciplines'] ?? [];
$experience  = $data['experience']  ?? [];
?>
<?php include __DIR__ . '/includes/partials/head.php'; ?>
<body class="page-shell bg-stone-50 text-stone-900 antialiased transition-colors duration-300 dark:bg-stone-950 dark:text-stone-100">
  <?php include __DIR__ . '/includes/partials/header.php'; ?>
  <?php include __DIR__ . '/includes/partials/drawer.php'; ?>

  <main class="mx-auto max-w-7xl px-6 py-16 lg:px-10 lg:py-24">
    <section id="story" class="grid gap-14 lg:grid-cols-[0.7fr_1.3fr]">
      <div>
        <p class="text-xs uppercase tracking-[0.22em] text-stone-500 dark:text-stone-400"><?= te($hero['kicker'] ?? []) ?></p>
        <h1 class="muted-heading mt-3 text-4xl font-semibold tracking-tight sm:text-5xl"><?= te($hero['title'] ?? []) ?></h1>
        <?php if (!empty($side_nav['items'])): ?>
          <div class="about-mini-nav mt-8">
            <p><?= te($side_nav['title'] ?? []) ?></p>
            <ul>
              <?php foreach ($side_nav['items'] as $item): ?>
                <li><a href="#<?= e($item['id'] ?? '') ?>" class="surface-link"><?= te($item['label'] ?? []) ?></a></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>
      </div>
      <div class="space-y-8">
        <?php foreach (($hero['paragraphs'] ?? []) as $i => $p): ?>
          <?php if ($i === 0): ?>
            <p class="max-w-3xl text-lg leading-9 text-stone-700 dark:text-stone-200"><?= te($p['text'] ?? []) ?></p>
          <?php else: ?>
            <p class="max-w-3xl leading-8 text-stone-600 dark:text-stone-300"><?= te($p['text'] ?? []) ?></p>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    </section>

    <section id="education" class="surface-band mt-20 grid gap-10 border-y border-stone-200 py-10 lg:grid-cols-3 dark:border-stone-800">
      <div>
        <p class="text-xs uppercase tracking-[0.22em] text-stone-500 dark:text-stone-400"><?= te($disciplines['kicker'] ?? []) ?></p>
      </div>
      <div class="lg:col-span-2 grid gap-4 sm:grid-cols-2">
        <?php foreach (($disciplines['items'] ?? []) as $d): ?>
          <div class="surface-card border border-stone-200 p-5 dark:border-stone-800">
            <h2 class="surface-title text-lg font-medium"><?= te($d['title'] ?? []) ?></h2>
            <p class="mt-3 leading-7 text-stone-600 dark:text-stone-300"><?= te($d['body'] ?? []) ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    </section>

    <section id="cv" class="mt-20 grid gap-14 lg:grid-cols-[0.5fr_1fr]">
      <div>
        <p class="text-xs uppercase tracking-[0.22em] text-stone-500 dark:text-stone-400"><?= te($experience['kicker'] ?? []) ?></p>
      </div>
      <div class="space-y-5">
        <?php foreach (($experience['items'] ?? []) as $x): ?>
          <div class="section-divider border-b border-stone-200 pb-5 dark:border-stone-800">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-baseline sm:justify-between">
              <h2 class="surface-title text-xl font-medium"><?= te($x['title'] ?? []) ?></h2>
              <span class="text-sm text-stone-500 dark:text-stone-400"><?= te($x['period'] ?? []) ?></span>
            </div>
            <p class="mt-3 leading-8 text-stone-600 dark:text-stone-300"><?= te($x['body'] ?? []) ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    </section>

  </main>

  <?php include __DIR__ . '/includes/partials/footer.php'; ?>
<?php include __DIR__ . '/includes/partials/scripts.php'; ?>
