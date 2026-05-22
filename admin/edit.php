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

$admin_title = $page_labels[$page] ?? $page;
include __DIR__ . '/_layout_head.php';
?>

<div class="mb-8 flex flex-wrap items-center justify-between gap-4">
  <div>
    <a href="./index.php" class="text-sm text-stone-500 hover:text-stone-700 dark:text-stone-400 dark:hover:text-stone-200">← 대시보드</a>
    <h1 class="mt-2 text-3xl font-semibold tracking-tight"><?= e($page_labels[$page] ?? $page) ?></h1>
    <p class="mt-1 text-sm text-stone-500 dark:text-stone-400">필드 편집 후 하단 <strong>저장</strong> 버튼 클릭. 이미지 업로드는 Phase 5에서 추가됩니다.</p>
  </div>
  <div class="flex gap-3">
    <a href="../<?= e($page === 'index' ? 'index.php' : ($page === 'projects' ? 'work.php' : $page . '.php')) ?>" target="_blank"
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
      <details class="admin-card" open>
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

<?php include __DIR__ . '/_layout_foot.php'; ?>
