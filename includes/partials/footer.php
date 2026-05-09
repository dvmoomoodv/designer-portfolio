<?php
/**
 * $footer_links: [['href' => '...', 'label' => ['ko'=>'','en'=>'']], ...]
 * 페이지마다 다른 푸터 링크 셋을 받기 위함. 미지정시 기본 셋.
 */
$footer_links = $footer_links ?? [
    ['href' => './about.php',   'label' => ['ko' => '',     'en' => 'Notes']],
    ['href' => './contact.php', 'label' => ['ko' => '',     'en' => 'Contact']],
    ['href' => './resume.php',  'label' => ['ko' => '',     'en' => 'Resume']],
];
$copyright = site_data()['footer']['copyright'] ?? ['ko' => '', 'en' => 'Archive Portfolio © 2026'];
?>
<footer class="surface-band border-t border-stone-200/80 py-8 dark:border-stone-800">
  <div class="mx-auto flex max-w-7xl flex-col gap-3 px-6 text-sm text-stone-500 sm:flex-row sm:items-center sm:justify-between lg:px-10 dark:text-stone-400">
    <p><?= te($copyright) ?></p>
    <div class="flex flex-wrap gap-4">
      <?php foreach ($footer_links as $link): ?>
        <a href="<?= e($link['href']) ?>" class="meta-link"><?= te($link['label']) ?></a>
      <?php endforeach; ?>
    </div>
  </div>
</footer>
