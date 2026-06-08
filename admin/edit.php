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
$flash_message = '';
$data  = load_data($page);

/**
 * POST 배열을 정규화:
 *  - 숫자 인덱스로만 된 배열 → array_values() 로 재인덱스 (삭제/정렬 후 빈 공간 제거)
 *  - 나머지는 재귀 처리
 */
function normalize_post($v) {
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

function sanitize_design_payload(array $payload, array $existing): array {
    if (!isset($payload['design']) || !is_array($payload['design'])) {
        return $payload;
    }
    $defaults = function_exists('design_defaults') ? design_defaults() : [
        'background_color' => '#f8f6f3',
        'text_color' => '#1c1917',
        'muted_text_color' => '#57534e',
        'accent_color' => '#047857',
        'card_background_color' => '#ffffff',
    ];
    foreach ($defaults as $key => $fallback) {
        if (strpos($key, 'color') !== false) {
            $value = strtolower(trim((string)($payload['design'][$key] ?? '')));
            if (!preg_match('/^#[0-9a-f]{6}$/', $value)) {
                $value = (string)($existing['design'][$key] ?? $fallback);
            }
            if (!preg_match('/^#[0-9a-fA-F]{6}$/', $value)) {
                $value = $fallback;
            }
            $payload['design'][$key] = strtolower($value);
            continue;
        }
        $payload['design'][$key] = trim((string)($payload['design'][$key] ?? ($existing['design'][$key] ?? $fallback)));
    }
    if (isset($payload['design']['apply_to_all_pages'])) {
        $payload['design']['apply_to_all_pages'] = (string)$payload['design']['apply_to_all_pages'] === '1' ? '1' : '0';
    }
    return $payload;
}

function sync_llms_files(array $payload): void {
    $text = (string)($payload['meta']['llms_txt'] ?? '');
    if ($text === '') {
        return;
    }
    $text = str_replace(["\r\n", "\r"], "\n", $text);
    if (substr($text, -1) !== "\n") {
        $text .= "\n";
    }
    foreach ([APP_ROOT . '/llm.txt', APP_ROOT . '/llms.txt'] as $path) {
        if (@file_put_contents($path, $text, LOCK_EX) === false) {
            throw new RuntimeException('LLMs 텍스트 파일 저장 실패: ' . basename($path));
        }
        @chmod($path, 0644);
    }
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    try {
        csrf_require_post();
        $raw  = $_POST['d'] ?? [];
        $norm = normalize_post($raw);
        $norm = sanitize_design_payload($norm, $data);
        save_data($page, $norm);
        if ($page === 'site') {
            sync_llms_files($norm);
        }
        $data  = $norm;
        $flash = 'success';
        $flash_message = '✓ 저장되었습니다.';
    } catch (Throwable $e) {
        error_log('Admin save failed [' . $page . ']: ' . $e->getMessage());
        $flash = 'error';
        $flash_message = '저장 실패: ' . $e->getMessage();
    }
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

$common_guide = [
    '라이트 모드 색상은 Background/Text/Muted/Accent/Card/Header 항목에 저장됩니다.',
    '다크 모드 색상은 Dark Background/Dark Text/Dark Muted/Dark Accent/Dark Card/Dark Header 항목에 저장됩니다.',
    '상단 테마 버튼을 누르면 개별 페이지 색상이 지정되어 있어도 현재 설정된 라이트/다크 모드 색상 세트가 즉시 적용됩니다.',
    '색상은 #ffffff 형식의 6자리 HEX 값만 저장됩니다. 잘못된 값은 기존값 또는 기본값으로 자동 보정됩니다.',
    '이미지는 업로드 후에도 반드시 저장 버튼을 눌러야 실제 데이터에 반영됩니다.',
    '목록 항목은 추가, 삭제, 위/아래 이동 후 저장하면 순서가 그대로 사이트에 반영됩니다.',
    '한국어 입력칸이 비어 있으면 사이트에서는 영문 값이 대신 표시됩니다.',
];

$page_guides = [
    'site' => [
        'title' => '사이트 공통 / 메뉴 저장 기준',
        'items' => [
            'Brand는 상단 로고 텍스트에 반영됩니다.',
            'Nav는 상단 큰 메뉴와 드롭다운 소항목에 반영됩니다. href 값은 ./page.php 또는 ./page.php?filter=id 형식으로 입력하세요.',
            'Design의 Apply To All Pages를 전체 적용으로 바꾸면 모든 페이지가 site의 색상 기준을 따릅니다.',
            'Apply To All Pages가 전체 적용이면 라이트/다크 모드 색상 모두 site의 Design 기준으로 통일됩니다.',
            'Font Family와 Font Url을 설정하면 전체 사이트 폰트가 바뀝니다. Font Url은 폰트 업로드 버튼으로 넣을 수 있습니다.',
            'Header Background/Text Color는 웹사이트 상단 메뉴 바 색상에 반영됩니다.',
        ],
    ],
    'index' => [
        'title' => 'Home 저장 기준',
        'items' => [
            'Design은 Home 색상입니다. 단, site에서 전체 적용이 켜져 있으면 site 색상이 우선합니다.',
            'Hero image는 메인 첫 화면 우측 이미지에 반영됩니다.',
            'Selected Work의 project_ids는 projects.json에 있는 프로젝트 id와 같아야 표시됩니다.',
            'Categories의 href는 클릭 시 이동할 페이지/필터 주소입니다.',
            'Layout에서 히어로 이미지 위치, 카드 컬럼 수, 섹션 간격을 조절할 수 있습니다.',
        ],
    ],
    'about' => [
        'title' => 'About Me 저장 기준',
        'items' => [
            'Side Nav의 id는 about.php 안의 섹션 앵커와 연결됩니다. 예: story, education, cv',
            'Disciplines는 Edu 영역 카드로 표시됩니다.',
            'Experience는 CV 영역의 경력/설명 목록으로 표시됩니다.',
        ],
    ],
    'work' => [
        'title' => 'Concept Art 저장 기준',
        'items' => [
            'Filters의 id는 프로젝트 category_id와 같아야 필터가 정상 작동합니다.',
            'Pagination의 Per Page는 한 페이지에 보일 프로젝트 수입니다.',
            'Items는 Concept Art 화면에 표시되는 이미지 카드입니다. 각 item image에서 사진 업로드가 가능합니다.',
            'Project 상세 페이지까지 연결하려면 item의 link를 ./project.php?id=프로젝트ID 형식으로 입력하세요.',
        ],
    ],
    'research' => [
        'title' => 'Research 저장 기준',
        'items' => [
            'Filters의 id와 각 item의 category_id가 같아야 필터가 정상 작동합니다.',
            'source_url이 http로 시작하면 새 창으로 열립니다. 비워두면 링크는 # 처리됩니다.',
            'Pagination의 Per Page는 한 페이지에 보일 글 개수입니다.',
        ],
    ],
    'photograph' => [
        'title' => 'Photography 저장 기준',
        'items' => [
            'Filters의 id와 각 사진 item의 category_id가 같아야 필터가 정상 작동합니다.',
            'image는 썸네일과 클릭 확대 이미지에 같이 사용됩니다.',
            'Pagination의 Per Page는 한 페이지에 보일 사진 개수입니다.',
        ],
    ],
    'doodle' => [
        'title' => 'Doodle & Scribble 저장 기준',
        'items' => [
            'category_id는 doodle 또는 scribble처럼 Filters의 id와 맞춰주세요.',
            'Pagination의 Per Page는 한 페이지에 보일 드로잉/낙서 개수입니다.',
            'image_alt는 이미지 설명이며 접근성과 검색에 사용됩니다.',
        ],
    ],
    'projects' => [
        'title' => 'Projects 저장 기준',
        'items' => [
            'id는 다른 곳에서 참조되므로 가능한 한 변경하지 않는 것이 안전합니다.',
            'category_id는 Concept Art 필터 id와 같아야 필터링됩니다.',
            'cover는 목록 카드 이미지이고, detail.gallery_images는 프로젝트 상세 갤러리 이미지입니다.',
        ],
    ],
    'resume' => [
        'title' => 'Resume 저장 기준',
        'items' => [
            'Experience는 경력 목록, Capabilities는 역량 카드로 표시됩니다.',
            'Contact Card의 address는 하단 연락 카드에 표시됩니다.',
            'Resume은 현재 상단 메뉴에는 없지만 미리보기와 직접 URL에서 확인 가능합니다.',
        ],
    ],
    'contact' => [
        'title' => 'Contact 저장 기준',
        'items' => [
            'Email address는 mailto 링크로 사용됩니다.',
            'Social items의 href는 외부 링크 주소입니다. https:// 포함을 권장합니다.',
            'Brand와 Worked With는 상단 Contact 드롭다운의 소항목 앵커와 연결됩니다.',
            'Visual Items는 Contact 하단 이미지 카드 영역입니다. 각 item image에서 사진 업로드가 가능합니다.',
        ],
    ],
];

$guide = $page_guides[$page] ?? [
    'title' => '저장 기준',
    'items' => ['필드 수정 후 저장하면 해당 JSON 데이터와 연결된 페이지에 반영됩니다.'],
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

<details class="admin-card mb-6">
  <summary class="flex cursor-pointer select-none items-center justify-between gap-4 p-5 list-none">
    <span class="flex items-center gap-3 font-semibold tracking-wide">
      <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-stone-900 text-sm text-white dark:bg-stone-100 dark:text-stone-900">?</span>
      <?= e($guide['title']) ?>
    </span>
    <span class="text-xs text-stone-400 transition-transform" data-chevron>▾</span>
  </summary>
  <div class="grid gap-5 border-t border-stone-200 p-5 text-sm leading-7 text-stone-600 md:grid-cols-2 dark:border-stone-800 dark:text-stone-300">
    <div>
      <p class="mb-2 font-semibold text-stone-800 dark:text-stone-100">이 페이지 기준</p>
      <ul class="list-disc space-y-1 pl-5">
        <?php foreach (($guide['items'] ?? []) as $item): ?>
          <li><?= e($item) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <div>
      <p class="mb-2 font-semibold text-stone-800 dark:text-stone-100">공통 저장 기준</p>
      <ul class="list-disc space-y-1 pl-5">
        <?php foreach ($common_guide as $item): ?>
          <li><?= e($item) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</details>

<?php if ($flash === 'success'): ?>
  <div class="admin-flash admin-flash--success mb-6"><?= e($flash_message) ?></div>
<?php elseif ($flash === 'error'): ?>
  <div class="admin-flash admin-flash--error mb-6"><?= e($flash_message) ?></div>
<?php endif; ?>

<form method="post" id="editForm" autocomplete="off">
  <?= csrf_field() ?>
  <div class="space-y-4">
    <?php foreach ($data as $section_key => $section_val): ?>
      <?php $open = in_array($section_key, ['page', 'design', 'hero', 'pagination'], true); ?>
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
      <p class="text-sm text-stone-500 dark:text-stone-400">저장 후 새로고침하면 실제 반영 화면을 확인할 수 있습니다. 색상은 입력 중에도 아래 화면에 바로 반영됩니다.</p>
    </div>
    <div class="flex flex-wrap gap-2">
      <button type="button" class="admin-btn admin-btn--ghost text-sm" data-preview-theme="light">데이모드로 보기</button>
      <button type="button" class="admin-btn admin-btn--ghost text-sm" data-preview-theme="dark">다크모드로 보기</button>
      <button type="button" class="admin-btn admin-btn--ghost text-sm" data-preview-refresh>미리보기 새로고침</button>
    </div>
  </div>
  <iframe src="../<?= e($preview_map[$page] ?? 'index.php') ?>" data-admin-preview class="h-[520px] w-full rounded-lg border border-stone-200 bg-white dark:border-stone-800"></iframe>
</section>

<?php include __DIR__ . '/_layout_foot.php'; ?>
