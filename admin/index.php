<?php
require_once __DIR__ . '/../includes/bootstrap.php';

auth_require();

$pages = [
    ['key' => 'site',     'label' => '사이트 공통 / 메뉴', 'desc' => '브랜드, 상단 메뉴, 드롭다운, 푸터, 기본 메타 정보'],
    ['key' => 'index',    'label' => 'Home',           'desc' => '홈 히어로, Selected work, 카테고리, Notes'],
    ['key' => 'about',    'label' => 'About Me',       'desc' => 'Story, Edu, CV 섹션 내용'],
    ['key' => 'work',     'label' => 'Concept Art',    'desc' => 'Concept Art 페이지 헤더와 필터 메뉴'],
    ['key' => 'research', 'label' => 'Research',       'desc' => '리서치 페이지 헤더, 필터, 기사형 목록'],
    ['key' => 'photograph', 'label' => 'Photography',  'desc' => '사진 페이지 헤더, 필터, 모자이크 갤러리'],
    ['key' => 'doodle',   'label' => 'Doodle & Scribble', 'desc' => '드로잉 습작, 낙서, 아이디어 노트 그리드'],
    ['key' => 'projects', 'label' => '프로젝트 목록',   'desc' => '카드 + 상세 (Project Detail)'],
    ['key' => 'resume',   'label' => 'Resume',         'desc' => '경험, 역량, 연락 카드'],
    ['key' => 'contact',  'label' => 'Contact',        'desc' => '이메일, 소셜, 문의, Brand, worked with 섹션'],
];

$admin_title = '대시보드';
include __DIR__ . '/_layout_head.php';
?>
<section class="mb-10">
  <p class="text-xs uppercase tracking-[0.22em] text-stone-500 dark:text-stone-400">Dashboard</p>
  <h1 class="mt-2 text-3xl font-semibold tracking-tight">콘텐츠 관리</h1>
  <p class="mt-3 max-w-2xl text-sm leading-7 text-stone-600 dark:text-stone-400">
    아래 항목에서 사이트의 모든 주요 내용을 수정할 수 있습니다. 텍스트, 메뉴, 필터, 카드, 상세 내용, 이미지 경로를 편집하고 이미지 슬롯은 바로 업로드해 교체할 수 있습니다.
  </p>
</section>

<section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
  <?php foreach ($pages as $p): ?>
    <a href="./edit.php?page=<?= e($p['key']) ?>" class="admin-card group block p-5 transition hover:border-stone-400 dark:hover:border-stone-600">
      <p class="text-xs uppercase tracking-[0.18em] text-stone-500 dark:text-stone-400"><?= e($p['key']) ?>.json</p>
      <h2 class="mt-2 text-lg font-medium"><?= e($p['label']) ?></h2>
      <p class="mt-2 text-sm leading-6 text-stone-600 dark:text-stone-400"><?= e($p['desc']) ?></p>
      <span class="mt-4 inline-flex text-sm text-stone-700 group-hover:underline dark:text-stone-200">편집 →</span>
    </a>
  <?php endforeach; ?>
</section>

<section class="mt-12">
  <div class="admin-card p-5 text-sm text-stone-600 dark:text-stone-400">
    <p class="font-medium text-stone-800 dark:text-stone-200">팁</p>
    <ul class="mt-2 list-disc space-y-1 pl-5">
      <li>이미지는 업로드 즉시 <code class="rounded bg-stone-100 px-1 py-0.5 text-xs dark:bg-stone-800">assets/images/uploads/</code> 에 저장되고 해당 슬롯에 자동 연결됩니다.</li>
      <li>저장 버튼을 눌러야 변경사항이 반영됩니다. 편집 도중 새로고침하면 변경사항이 사라집니다.</li>
      <li>한국어 입력란을 비워두면 사이트에서는 영문이 그대로 표시됩니다.</li>
      <li>목록형 항목은 추가, 삭제, 위/아래 순서 변경이 가능합니다.</li>
    </ul>
  </div>
</section>
<?php include __DIR__ . '/_layout_foot.php'; ?>
