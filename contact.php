<?php
require_once __DIR__ . '/includes/bootstrap.php';

$data         = load_data('contact');
$current_nav  = 'contact';
$page_title   = te($data['page']['title']       ?? 'Contact');
$page_description = te($data['page']['description'] ?? '');
$footer_links = [
    ['href' => './work.php',  'label' => ['ko' => '', 'en' => 'Work']],
    ['href' => './about.php', 'label' => ['ko' => '', 'en' => 'About']],
];

$hero    = $data['hero']    ?? [];
$email   = $data['email']   ?? [];
$social  = $data['social']  ?? [];
$inquiry = $data['inquiry'] ?? [];
$brand   = $data['brand']   ?? [];
$worked  = $data['worked_with'] ?? [];
$visual_items = $data['visual_items'] ?? [];
?>
<?php include __DIR__ . '/includes/partials/head.php'; ?>
<body class="page-shell bg-stone-50 text-stone-900 antialiased transition-colors duration-300 dark:bg-stone-950 dark:text-stone-100" style="<?= e(page_design_style($data)) ?>">
  <?php include __DIR__ . '/includes/partials/header.php'; ?>
  <?php include __DIR__ . '/includes/partials/drawer.php'; ?>

  <main class="mx-auto max-w-7xl px-6 py-16 lg:px-10 lg:py-24">
    <section class="grid gap-12 lg:grid-cols-[0.85fr_1.15fr]">
      <div>
        <p class="text-xs uppercase tracking-[0.22em] text-stone-500 dark:text-stone-400"><?= te($hero['kicker'] ?? []) ?></p>
        <h1 class="muted-heading mt-3 text-4xl font-semibold tracking-tight sm:text-5xl"><?= te($hero['title'] ?? []) ?></h1>
        <p class="mt-6 max-w-xl leading-8 text-stone-600 dark:text-stone-300"><?= te($hero['body'] ?? []) ?></p>
      </div>
      <div class="surface-band space-y-6 border-t border-stone-200 px-5 pt-6 dark:border-stone-800 lg:border-t-0 lg:border-l lg:pl-12 lg:pr-6 lg:pt-6">
        <div id="email" class="section-divider border-b border-stone-200 pb-5 dark:border-stone-800">
          <p class="text-sm text-stone-500 dark:text-stone-400"><?= te($email['label'] ?? []) ?></p>
          <a href="mailto:<?= e($email['address'] ?? '') ?>" class="surface-link mt-3 inline-block text-2xl font-medium"><?= e($email['address'] ?? '') ?></a>
        </div>
        <div id="social" class="section-divider border-b border-stone-200 pb-5 dark:border-stone-800">
          <p class="text-sm text-stone-500 dark:text-stone-400"><?= te($social['label'] ?? []) ?></p>
          <div class="mt-3 flex flex-wrap gap-4 text-lg">
            <?php foreach (($social['items'] ?? []) as $s): ?>
              <a href="<?= e($s['href'] ?? '#') ?>" class="surface-link"><?= te($s['label'] ?? []) ?></a>
            <?php endforeach; ?>
          </div>
        </div>
        <div id="inquiry">
          <p class="text-sm text-stone-500 dark:text-stone-400"><?= te($inquiry['label'] ?? []) ?></p>
          <p class="mt-3 max-w-xl leading-8 text-stone-600 dark:text-stone-300"><?= te($inquiry['body'] ?? []) ?></p>
        </div>
      </div>
    </section>

    <section class="surface-band mt-20 grid gap-8 border-y border-stone-200 py-10 md:grid-cols-2 dark:border-stone-800">
      <div id="brand" class="surface-card border border-stone-200 p-5 dark:border-stone-800">
        <p class="text-sm text-stone-500 dark:text-stone-400"><?= te($brand['label'] ?? []) ?></p>
        <p class="mt-3 leading-8 text-stone-600 dark:text-stone-300"><?= te($brand['body'] ?? []) ?></p>
      </div>
      <div id="worked-with" class="surface-card border border-stone-200 p-5 dark:border-stone-800">
        <p class="text-sm text-stone-500 dark:text-stone-400"><?= te($worked['label'] ?? []) ?></p>
        <p class="mt-3 leading-8 text-stone-600 dark:text-stone-300"><?= te($worked['body'] ?? []) ?></p>
      </div>
    </section>

    <?php if (!empty($visual_items['items'])): ?>
      <section class="mt-20">
        <div class="section-divider mb-8 border-b border-stone-200 pb-5 dark:border-stone-800">
          <p class="text-xs uppercase tracking-[0.22em] text-stone-500 dark:text-stone-400"><?= te($visual_items['kicker'] ?? []) ?></p>
          <h2 class="surface-title mt-2 text-2xl font-semibold tracking-tight"><?= te($visual_items['title'] ?? []) ?></h2>
        </div>
        <div class="grid gap-6 md:grid-cols-2">
          <?php foreach (($visual_items['items'] ?? []) as $item): ?>
            <article class="surface-card overflow-hidden border border-stone-200 dark:border-stone-800">
              <a href="<?= e($item['image'] ?? '') ?>" data-image-frame data-lightbox class="image-frame block">
                <img src="<?= e($item['image'] ?? '') ?>" alt="<?= te($item['image_alt'] ?? []) ?>" class="aspect-[4/3] w-full object-cover" loading="lazy">
              </a>
              <div class="p-5">
                <h3 class="surface-title text-lg font-medium"><?= te($item['title'] ?? []) ?></h3>
                <p class="mt-2 leading-7 text-stone-600 dark:text-stone-300"><?= te($item['caption'] ?? []) ?></p>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>
  </main>

  <?php include __DIR__ . '/includes/partials/footer.php'; ?>
<?php include __DIR__ . '/includes/partials/scripts.php'; ?>
