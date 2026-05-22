<?php
require_once __DIR__ . '/../includes/bootstrap.php';
include   __DIR__ . '/_form_renderer.php';

if (!auth_is_initialized()) { header('Location: ./setup.php'); exit; }
auth_require();

const EDITABLE_PAGES = ['site', 'index', 'about', 'work', 'research', 'photograph', 'doodle', 'projects', 'resume', 'contact'];
$page = in_array($_GET['page'] ?? '', EDITABLE_PAGES, true) ? (string)$_GET['page'] : '';
if (!$page) { header('Location: ./index.php'); exit; }

/* ── 저장 (POST) ── */
$flash = null;
$data  = load_data($page);

/**
 * POST 배열을 정규화:
 *  - 숫자 인덱스로만 된 배열 → array_values() 로 재인덱스 (삭제/정렬 후 빈 공간 제거)
 *  - 나머지는 재귀 처리
 */
function normalize_post(mixed $v): mixed {
    if (!is_array($v)) {
        return is_string($v) ? $v : '';
    }
    $keys = array_keys($v);
    $isSeq = $keys === array_keys(array_fill(0, count($keys), null));
    if ($isSeq) {
        return array_values(array_map('normalize_post', $v));
    }
    $out = [];
    foreach ($v as $k => $sub) {
        $out[$k] = normalize_post($sub);
    }
    return $out;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    csrf_require_post();
    $raw  = $_POST['d'] ?? [];
    $norm = normalize_post($raw);
    save_data($page, $norm);
    $data  = $norm;
    $flash = 'success';
}

/* ── 뷰 ── */
$page_labels = [
    'site'     => '사이트 공통 (site.json)',
    'index'    => '메인 (index.json)',
    'about'    => 'About (about.json)',
    'work'     => 'Work (work.json)',
    'research' => 'Research (research.json)',
    'photograph' => 'Photograph (photograph.json)',
    'doodle'   => 'Doodle & Scribble (doodle.json)',
    'projects' => '프로젝트 목록 (projects.json)',
    'resume'   => 'Resume (resume.json)',
    'contact'  => 'Contact (contact.json)',
];

$preview_map = [
    'site'       => 'index.php',
    'index'      => 'index.php',
    'about'      => 'about.php',
    'work'       => 'work.php',
    'research'   => 'research.php',
    'photograph' => 'photograph.php',
    'doodle'     => 'doodle.php',
    'projects'   => 'work.php',
    'resume'     => 'resume.php',
    'contact'    => 'contact.php',
];

$admin_title = $page_labels[$page] ?? $page;
include __DIR__ . '/_layout_head.php';
?>

<div class="mb-8 flex flex-wrap items-center justify-between gap-4">
  <div>
    <a href="./index.php" class="text-sm text-stone-500 hover:text-stone-700 dark:text-stone-400 dark:hover:text-stone-200">← 대시보드</a>
    <h1 class="mt-2 text-3xl font-semibold tracking-tight"><?= e($page_labels[$page] ?? $page) ?></h1>
    <p class="mt-1 text-sm text-stone-500 dark:text-stone-400">텍스트, 메뉴, 목록, 이미지 경로를 수정할 수 있습니다. 이미지 업로드 후에도 하단 <strong>저장</strong> 버튼을 눌러야 반영됩니다.</p>
  </div>
  <div class="flex flex-wrap gap-3">
    <button type="button" class="admin-btn admin-btn--ghost text-sm" data-expand-all>전체 펼치기</button>
    <button type="button" class="admin-btn admin-btn--ghost text-sm" data-collapse-all>전체 접기</button>
    <a href="../<?= e($preview_map[$page] ?? 'index.php') ?>" target="_blank"
       class="admin-btn admin-btn--ghost text-sm">↗ 미리보기</a>
  </div>
</div>

<?php if ($flash === 'success'): ?>
  <div class="admin-flash admin-flash--success mb-6">✓ 저장되었습니다.</div>
<?php endif; ?>

<form method="post" id="editForm" autocomplete="off">
  <?= csrf_field() ?>
  <div class="space-y-4">
    <?php foreach ($data as $section_key => $section_val): ?>
      <?php $open = in_array($section_key, ['page', 'hero', 'pagination'], true); ?>
      <details class="admin-card"<?= $open ? ' open' : '' ?>>
        <summary class="flex cursor-pointer select-none items-center justify-between gap-4 p-5 list-none">
          <span class="font-semibold tracking-wide"><?= e(fr_label($section_key)) ?></span>
          <span class="text-xs text-stone-400 transition-transform" data-chevron>▾</span>
        </summary>
        <div class="space-y-5 border-t border-stone-200 p-5 dark:border-stone-800">
          <?php render_node('d[' . $section_key . ']', $section_val, $section_key); ?>
        </div>
      </details>
    <?php endforeach; ?>
  </div>

  <!-- 스티키 저장 바 -->
  <div class="sticky bottom-4 mt-6 flex items-center justify-between rounded-xl border border-stone-200 bg-white/90 p-3 shadow-lg backdrop-blur dark:border-stone-700 dark:bg-stone-900/90">
    <span class="text-sm text-stone-500 dark:text-stone-400" id="saveStatus">변경사항을 저장하려면 저장 버튼을 클릭하세요.</span>
    <button type="submit" class="admin-btn admin-btn--primary">저장</button>
  </div>
</form>

<section class="admin-card mt-8 p-4" data-preview-panel>
  <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
    <div>
      <h2 class="text-lg font-semibold">미리보기</h2>
      <p class="text-sm text-stone-500 dark:text-stone-400">저장 후 새로고침하면 실제 반영 화면을 확인할 수 있습니다. Home 색상은 입력 중에도 아래 화면에 바로 반영됩니다.</p>
    </div>
    <button type="button" class="admin-btn admin-btn--ghost text-sm" data-preview-refresh>미리보기 새로고침</button>
  </div>
  <iframe src="../<?= e($preview_map[$page] ?? 'index.php') ?>" data-admin-preview class="h-[520px] w-full rounded-lg border border-stone-200 bg-white dark:border-stone-800"></iframe>
</section>

<?php include __DIR__ . '/_layout_foot.php'; ?>
