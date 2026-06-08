<?php
declare(strict_types=1);
/* ─────────────────────────────────────────────────────────────────────────────
 * 제네릭 폼 렌더러 — JSON 트리를 재귀적으로 HTML 폼 필드로 변환.
 *
 * 타입 감지 우선순위:
 *   1. {ko, en} 배열                   → i18n 입력 쌍
 *   2. 인덱스 배열 + 원소가 배열        → 오브젝트 리스트 (카드 + 추가/삭제/정렬)
 *   3. 인덱스 배열 + 원소가 스칼라      → 스칼라 리스트 (입력 + 추가/삭제)
 *   4. 연관 배열                        → 중첩 필드셋
 *   5. 이미지 경로처럼 보이는 문자열    → 미리보기 + 경로 입력
 *   6. 나머지 스칼라                    → input / textarea
 * ─────────────────────────────────────────────────────────────────────────── */

function fr_is_i18n($v): bool {
    return is_array($v) && array_key_exists('ko', $v) && array_key_exists('en', $v) && count($v) === 2;
}
function fr_is_obj_list($v): bool {
    if (!is_array($v) || empty($v)) return false;
    return array_keys($v) === range(0, count($v) - 1) && is_array(current($v));
}
function fr_is_scalar_list($v): bool {
    if (!is_array($v) || empty($v)) return false;
    return array_keys($v) === range(0, count($v) - 1) && is_scalar(current($v));
}
function fr_is_image($v): bool {
    return is_string($v) && (bool)preg_match('/\.(jpe?g|jfif|a?png|gif|webp|svg|avif|bmp|tiff?|heic|heif|ico)($|\?)/i', $v);
}
function fr_is_image_key(string $key): bool {
    return in_array($key, ['image', 'cover', 'thumbnail', 'poster', 'favicon_url', 'og_image', 'logo_image'], true)
        || (substr($key, -6) === '_image' && strpos($key, 'alt') === false);
}
function fr_is_font(string $key, $v): bool {
    return is_string($v) && preg_match('/font_url/i', $key);
}
function fr_is_multiline(string $key, $v): bool {
    if (preg_match('/body|description|paragraph|text|overview/i', $key)) return true;
    return is_string($v) && strlen($v) > 100;
}
function fr_is_color(string $key, $v): bool {
    return is_string($v) && preg_match('/color/i', $key);
}
function fr_label(string $key): string {
    $labels = [
        'brand' => '브랜드 텍스트',
        'logo' => '로고 설정',
        'design' => '디자인 설정',
        'nav' => '상단 메뉴 / 드롭다운',
        'footer' => '푸터',
        'meta' => 'SEO / 파비콘 / LLMs 설정',
        'apply_to_all_pages' => '전체/페이지별 적용 방식',
        'background_color' => '페이지 배경',
        'text_color' => '기본 글자',
        'muted_text_color' => '보조 글자',
        'accent_color' => '포인트 색',
        'card_background_color' => '카드/박스 배경',
        'header_background_color' => '헤더 배경',
        'header_text_color' => '헤더 글자/메뉴',
        'brand_script_text' => '로고 스크립트 텍스트',
        'brand_script_size' => '로고 스크립트 크기',
        'brand_main_size' => '브랜드 보조 텍스트 크기',
        'brand_accent_color' => '로고 포인트 색',
        'header_padding_y' => '헤더 상하 여백',
        'show_brand_main' => '브랜드 보조 텍스트 표시',
        'dark_background_color' => '다크 페이지 배경',
        'dark_text_color' => '다크 기본 글자',
        'dark_muted_text_color' => '다크 보조 글자',
        'dark_accent_color' => '다크 포인트 색',
        'dark_card_background_color' => '다크 카드/박스 배경',
        'dark_header_background_color' => '다크 헤더 배경',
        'dark_header_text_color' => '다크 헤더 글자/메뉴',
        'dark_brand_accent_color' => '다크 로고 포인트 색',
        'font_family' => '폰트 이름',
        'font_url' => '폰트 파일',
        'font_apply_target' => '폰트 적용 범위',
        'favicon_url' => '파비콘 이미지',
        'og_image' => '공유 미리보기 이미지',
        'llms_txt' => 'LLMs.txt 내용',
        'default_title' => '사이트 기본 타이틀',
        'default_description' => '사이트 기본 설명',
        'site_name' => '사이트 이름',
        'keywords' => 'SEO 키워드',
        'robots' => '검색엔진 노출 설정',
        'use_image' => '이미지 로고 사용',
        'logo_image' => '로고 이미지',
        'image_width' => '로고 이미지 너비',
        'image' => '이미지',
        'image_alt' => '이미지 설명',
    ];
    if (isset($labels[$key])) return $labels[$key];
    return ucwords(str_replace(['_', '-'], ' ', $key));
}
function fr_help(string $key): string {
    $help = [
        'apply_to_all_pages' => '전체 적용을 선택하면 모든 페이지가 사이트 공통 Design 값을 따릅니다. 페이지별 사용을 선택하면 각 페이지 Design 값이 우선됩니다.',
        'background_color' => '데이모드에서 페이지 전체 배경색입니다.',
        'text_color' => '데이모드에서 제목과 주요 글자색입니다.',
        'muted_text_color' => '데이모드에서 설명문, 보조 텍스트 색입니다.',
        'accent_color' => '데이모드에서 hover, 강조, 포인트에 쓰입니다.',
        'card_background_color' => '데이모드에서 카드/박스/밴드 배경에 쓰입니다.',
        'header_background_color' => '데이모드에서 상단 고정 메뉴 바 배경색입니다.',
        'header_text_color' => '데이모드에서 상단 로고, 메뉴, 아이콘 글자색입니다.',
        'brand_script_text' => '상단에 크게 보이는 rohigraphy 텍스트입니다. 너무 튀면 짧게 바꾸거나 크기를 줄이세요.',
        'brand_script_size' => '상단 로고 스크립트 크기입니다. 예: 0.9rem, 1rem, 1.2rem',
        'brand_main_size' => 'ARCHIVE PORTFOLIO 같은 보조 브랜드 텍스트 크기입니다.',
        'brand_accent_color' => '데이모드 로고 스크립트 색상입니다.',
        'header_padding_y' => '헤더 위아래 여백입니다. UI가 커 보이면 compact 또는 0.75rem을 선택하세요.',
        'show_brand_main' => '보조 브랜드 텍스트를 숨기면 상단 로고 영역이 더 단순해집니다.',
        'dark_background_color' => '다크모드에서 페이지 전체 배경색입니다.',
        'dark_text_color' => '다크모드에서 제목과 주요 글자색입니다.',
        'dark_muted_text_color' => '다크모드에서 설명문, 보조 텍스트 색입니다.',
        'dark_accent_color' => '다크모드에서 hover, 강조, 포인트에 쓰입니다.',
        'dark_card_background_color' => '다크모드에서 카드/박스/밴드 배경에 쓰입니다.',
        'dark_header_background_color' => '다크모드에서 상단 고정 메뉴 바 배경색입니다.',
        'dark_header_text_color' => '다크모드에서 상단 로고, 메뉴, 아이콘 글자색입니다.',
        'dark_brand_accent_color' => '다크모드 로고 스크립트 색상입니다.',
        'font_family' => '업로드 폰트의 이름입니다. 로고/헤더/메뉴에는 적용되지 않고 본문 또는 제목 영역에만 적용됩니다.',
        'font_url' => '업로드한 woff2/woff/ttf/otf 파일 경로입니다.',
        'font_apply_target' => '업로드 폰트를 어디에 적용할지 선택합니다. 로고와 상단 메뉴는 항상 안전한 기본 폰트로 보호됩니다.',
        'favicon_url' => '브라우저 탭, 북마크 등에 표시되는 작은 아이콘입니다. ico/png/svg/webp 권장.',
        'og_image' => '카카오톡, SNS, 메신저 공유 시 보이는 대표 이미지입니다. 1200x630 비율 권장.',
        'llms_txt' => 'AI/LLM이 사이트를 이해하기 쉽게 제공하는 공개 텍스트입니다. 저장 시 /llm.txt와 /llms.txt에 같이 반영되며, 서버 권한 문제로 동기화가 실패해도 JSON 저장은 유지됩니다.',
        'default_title' => '페이지별 타이틀이 없을 때 사용하는 기본 브라우저/검색 결과 제목입니다.',
        'default_description' => '페이지별 설명이 없을 때 사용하는 기본 검색 결과 설명입니다.',
        'site_name' => 'Open Graph, SNS 공유 등에 표시되는 사이트 이름입니다.',
        'keywords' => '검색엔진 참고용 키워드입니다. 쉼표로 구분해서 입력하세요.',
        'robots' => '검색엔진 수집 정책입니다. 보통 index, follow를 사용합니다.',
        'use_image' => '텍스트 로고 대신 업로드한 이미지 로고를 사용할지 선택합니다.',
        'logo_image' => '상단 헤더에 표시할 로고 이미지입니다. 업로드 후 이미지 로고 사용을 켜고 저장하세요.',
        'image_width' => '로고 이미지 표시 너비입니다. 예: 120px, 9rem',
    ];
    return $help[$key] ?? '';
}
function fr_select_options(string $key): ?array {
    switch ($key) {
        case 'apply_to_all_pages':
            return ['1' => '전체 페이지에 공통 색상 적용', '0' => '페이지별 색상 사용'];
        case 'enabled':
            return ['1' => '사용', '0' => '사용 안 함'];
        case 'per_page':
            return ['3' => '3개씩', '4' => '4개씩', '5' => '5개씩', '6' => '6개씩', '8' => '8개씩', '9' => '9개씩', '12' => '12개씩', '16' => '16개씩', '24' => '24개씩'];
        case 'hero_layout':
            return ['image_right' => '이미지 오른쪽', 'image_left' => '이미지 왼쪽', 'image_top' => '이미지 위', 'text_only' => '텍스트만'];
        case 'selected_columns':
        case 'notes_columns':
            return ['2' => '2열', '3' => '3열', '4' => '4열'];
        case 'category_columns':
            return ['1' => '1열', '2' => '2열', '3' => '3열'];
        case 'section_spacing':
            return ['compact' => '좁게', 'normal' => '기본'];
        case 'brand_script_size':
            return ['0.8rem' => '작게', '0.98rem' => '기본', '1.15rem' => '크게', '1.35rem' => '매우 크게'];
        case 'brand_main_size':
            return ['0.58rem' => '작게', '0.72rem' => '기본', '0.86rem' => '크게'];
        case 'header_padding_y':
            return ['0.65rem' => '좁게', '0.9rem' => '약간 좁게', '1.25rem' => '기본', '1.6rem' => '넓게'];
        case 'show_brand_main':
            return ['1' => '표시', '0' => '숨김'];
        case 'font_apply_target':
            return ['none' => '적용 안 함', 'content' => '본문만', 'heading' => '제목만', 'both' => '본문 + 제목'];
        case 'use_image':
            return ['1' => '이미지 로고 사용', '0' => '텍스트 로고 사용'];
        default:
            return null;
    }
}
function fr_item_summary(array $item, int $i): string {
    $title = '';
    foreach (['title', 'label', 'id', 'category_label'] as $key) {
        if (!isset($item[$key])) continue;
        $value = is_array($item[$key]) ? t($item[$key]) : (string)$item[$key];
        if ($value !== '') { $title = $value; break; }
    }
    return '항목 ' . ($i + 1) . ($title !== '' ? ' · ' . $title : '');
}

/* ────── 진입점 ────── */
function render_node(string $path, $v, string $key): void {
    if      (fr_is_i18n($v))        render_i18n($path, $v, fr_label($key));
    elseif  (fr_is_obj_list($v))    render_obj_list($path, $v, fr_label($key));
    elseif  (fr_is_scalar_list($v)) render_scalar_list($path, $v, fr_label($key));
    elseif  (is_array($v) && $key === 'design') render_design($path, $v, fr_label($key));
    elseif  (is_array($v))          render_assoc($path, $v, fr_label($key));
    elseif  (fr_is_color($key, $v)) render_color($path, (string)$v, fr_label($key));
    elseif  (fr_is_font($key, $v))  render_font($path, (string)$v, fr_label($key));
    elseif  (fr_is_image($v) || fr_is_image_key($key)) render_image($path, (string)$v, fr_label($key));
    elseif  (fr_select_options($key)) render_select($path, (string)$v, fr_label($key), fr_select_options($key) ?? []);
    else                            render_scalar($path, $v, fr_label($key), $key);
}

function render_font(string $path, string $v, string $label): void {
    $key = preg_replace('/^.*\[([^\]]+)\]$/', '$1', $path);
    echo '<div><label class="admin-label">' . e($label) . ' <span class="font-normal normal-case text-xs text-stone-400">(woff2/woff/ttf/otf)</span></label>';
    echo '<div class="flex gap-2">';
    echo '<input class="admin-input" type="text" name="' . e($path) . '" value="' . e($v) . '" data-file-path>';
    echo '<button type="button" class="admin-btn admin-btn--ghost text-xs" data-upload-trigger data-upload-kind="font" data-upload-target="' . e($path) . '">↑ 폰트 업로드</button>';
    echo '</div>';
    if (fr_help($key) !== '') echo '<p class="admin-help">' . e(fr_help($key)) . '</p>';
    echo '</div>';
}

function render_color(string $path, string $v, string $label): void {
    $v = preg_match('/^#[0-9A-Fa-f]{6}$/', $v) ? strtolower($v) : '#ffffff';
    $key = preg_replace('/^.*\[([^\]]+)\]$/', '$1', $path);
    echo '<div><label class="admin-label">' . e($label) . '</label>';
    echo '<div class="flex gap-2">';
    echo '<input class="h-11 w-16 rounded border border-stone-300 bg-white p-1 dark:border-stone-700 dark:bg-stone-900" type="color" value="' . e($v) . '" data-color-picker>';
    echo '<input class="admin-input" type="text" name="' . e($path) . '" value="' . e($v) . '" pattern="#[0-9A-Fa-f]{6}" maxlength="7" placeholder="#ffffff" data-color-field>';
    echo '</div>';
    if (fr_help($key) !== '') echo '<p class="admin-help">' . e(fr_help($key)) . '</p>';
    echo '</div>';
}

function render_select(string $path, string $v, string $label, array $options): void {
    $key = preg_replace('/^.*\[([^\]]+)\]$/', '$1', $path);
    echo '<div><label class="admin-label">' . e($label) . '</label>';
    echo '<select class="admin-input" name="' . e($path) . '">';
    foreach ($options as $value => $text) {
        echo '<option value="' . e($value) . '"' . ((string)$value === $v ? ' selected' : '') . '>' . e($text) . '</option>';
    }
    echo '</select>';
    if (fr_help($key) !== '') echo '<p class="admin-help">' . e(fr_help($key)) . '</p>';
    echo '</div>';
}

/* ────── i18n 쌍 ────── */
function render_i18n(string $path, array $v, string $label): void {
    $ta = fr_is_multiline($path, $v['en'] ?? '');
    echo '<div class="space-y-1">';
    echo '<p class="admin-label">' . e($label) . '</p>';
    echo '<div class="grid gap-2 sm:grid-cols-2">';
    foreach (['ko' => 'KO 한국어', 'en' => 'EN English'] as $lc => $lname) {
        $n = e($path . '[' . $lc . ']');
        $val = e($v[$lc] ?? '');
        echo '<div><label class="mb-1 block text-[10px] uppercase tracking-wider text-stone-400">' . e($lname) . '</label>';
        if ($ta) echo '<textarea class="admin-textarea" name="' . $n . '" rows="3">' . $val . '</textarea>';
        else     echo '<input class="admin-input" type="text" name="' . $n . '" value="' . $val . '">';
        echo '</div>';
    }
    echo '</div></div>';
}

/* ────── 스칼라 (input / textarea) ────── */
function render_scalar(string $path, $v, string $label, string $key = ''): void {
    $ta = fr_is_multiline($key ?: $path, (string)$v);
    echo '<div><label class="admin-label">' . e($label) . '</label>';
    if ($ta) echo '<textarea class="admin-textarea" name="' . e($path) . '" rows="3">' . e((string)$v) . '</textarea>';
    else     echo '<input class="admin-input" type="text" name="' . e($path) . '" value="' . e((string)$v) . '">';
    echo '</div>';
}

/* ────── 이미지 경로 ────── */
function render_image(string $path, string $v, string $label): void {
    echo '<div><label class="admin-label">' . e($label) . ' <span class="font-normal normal-case text-xs text-stone-400">(이미지 경로)</span></label>';
    echo '<div class="flex items-start gap-3">';
    if ($v) echo '<img src="' . e($v) . '" class="h-14 w-14 flex-shrink-0 rounded object-cover border border-stone-200 dark:border-stone-800" alt="preview">';
    echo '<div class="flex-1 space-y-1">';
    echo '<input class="admin-input" type="text" name="' . e($path) . '" value="' . e($v) . '" data-image-path data-file-path>';
    echo '<button type="button" class="admin-btn admin-btn--ghost text-xs" data-upload-trigger data-upload-target="' . e($path) . '">↑ 이미지 업로드</button>';
    echo '</div></div></div>';
}

/* ────── 스칼라 리스트 (project_ids 등) ────── */
function render_scalar_list(string $path, array $v, string $label): void {
    echo '<div><label class="admin-label">' . e($label) . '</label>';
    echo '<div data-scalar-list class="space-y-1.5">';
    foreach ($v as $item) {
        echo '<div class="flex gap-2" data-scalar-item>';
        echo '<input class="admin-input" type="text" name="' . e($path) . '[]" value="' . e((string)$item) . '">';
        echo '<button type="button" class="admin-btn admin-btn--ghost px-2 text-xs text-red-500 dark:text-red-400" data-scalar-delete title="삭제">×</button>';
        echo '</div>';
    }
    echo '</div>';
    echo '<button type="button" class="admin-btn admin-btn--ghost mt-1 text-xs" data-scalar-add data-scalar-path="' . e($path) . '">+ 추가</button></div>';
}

/* ────── 오브젝트 리스트 ────── */
function render_obj_list(string $path, array $items, string $label): void {
    echo '<fieldset class="rounded-lg border border-stone-200 p-4 dark:border-stone-700" data-obj-list="' . e($path) . '">';
    echo '<legend class="admin-label px-2">' . e($label) . '</legend>';
    echo '<div data-obj-items class="space-y-3">';
    foreach ($items as $i => $item) {
        render_obj_item($path, $i, (array)$item);
    }
    echo '</div>';
    echo '<button type="button" class="admin-btn admin-btn--ghost mt-3 text-xs" data-obj-add>+ 항목 추가</button>';
    echo '</fieldset>';
}

function render_obj_item(string $path, int $i, array $item): void {
    echo '<details class="relative rounded-lg border border-stone-200 bg-stone-50 dark:border-stone-700 dark:bg-stone-900/60" data-obj-item>';
    echo '<summary class="cursor-pointer select-none p-3 pr-24 text-sm font-medium text-stone-700 dark:text-stone-200">' . e(fr_item_summary($item, $i)) . '</summary>';
    echo '<div class="absolute right-2 top-2 flex gap-1">';
    echo '<button type="button" class="admin-btn admin-btn--ghost px-1.5 py-0.5 text-xs" data-obj-up title="위로">↑</button>';
    echo '<button type="button" class="admin-btn admin-btn--ghost px-1.5 py-0.5 text-xs" data-obj-down title="아래로">↓</button>';
    echo '<button type="button" class="admin-btn admin-btn--ghost px-1.5 py-0.5 text-xs text-red-500 dark:text-red-400" data-obj-delete title="삭제">×</button>';
    echo '</div><div class="space-y-3 border-t border-stone-200 p-3 dark:border-stone-700">';
    render_assoc_fields($path . '[' . $i . ']', $item);
    echo '</div></details>';
}

/* ────── 연관 배열 (중첩 섹션) ────── */
function render_design_group(string $path, array $obj, string $title, string $desc, array $keys): void {
    echo '<div class="admin-design-group">';
    echo '<div class="mb-3">';
    echo '<p class="text-sm font-semibold text-stone-800 dark:text-stone-100">' . e($title) . '</p>';
    echo '<p class="mt-1 text-xs leading-5 text-stone-500 dark:text-stone-400">' . e($desc) . '</p>';
    echo '</div>';
    echo '<div class="grid gap-3 md:grid-cols-2">';
    foreach ($keys as $key) {
        if (array_key_exists($key, $obj)) {
            render_node($path . '[' . $key . ']', $obj[$key], $key);
        }
    }
    echo '</div></div>';
}

function render_design(string $path, array $v, string $label): void {
    echo '<div class="rounded border border-stone-100 p-3 dark:border-stone-800">';
    echo '<div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm leading-6 text-emerald-950 dark:border-emerald-900/60 dark:bg-emerald-950/25 dark:text-emerald-100">';
    echo '<p class="font-semibold">Design 설정 구조</p>';
    echo '<p class="mt-1">데이모드 색상과 다크모드 색상을 따로 저장합니다. 사이트 상단의 테마 버튼을 누르면 현재 모드에 맞는 색상 세트가 즉시 적용됩니다.</p>';
    echo '<p class="mt-1">헤더 색상은 페이지 배경과 별도입니다. 상단 메뉴 바만 바꾸려면 헤더 배경/헤더 글자 항목만 수정하세요.</p>';
    echo '</div>';
    echo '<div class="space-y-4">';
    if (array_key_exists('apply_to_all_pages', $v)) {
        render_design_group($path, $v, '전체 적용 방식', '전체 페이지를 한 번에 통일할지, 각 페이지별 Design을 사용할지 선택합니다.', ['apply_to_all_pages']);
    }
    render_design_group($path, $v, '데이모드 · 페이지 색상', '밝은 모드에서 페이지 본문에 적용되는 색상입니다.', ['background_color', 'text_color', 'muted_text_color', 'accent_color', 'card_background_color']);
    render_design_group($path, $v, '데이모드 · 헤더 색상', '밝은 모드에서 상단 고정 메뉴 바에만 적용됩니다.', ['header_background_color', 'header_text_color']);
    render_design_group($path, $v, '헤더 로고 설정', '상단 rohigraphy 로고 텍스트, 크기, 여백을 조절합니다. UI가 커 보이면 여기서 줄이세요.', ['brand_script_text', 'brand_script_size', 'brand_main_size', 'brand_accent_color', 'dark_brand_accent_color', 'header_padding_y', 'show_brand_main']);
    render_design_group($path, $v, '다크모드 · 페이지 색상', '어두운 모드에서 페이지 본문에 적용되는 색상입니다.', ['dark_background_color', 'dark_text_color', 'dark_muted_text_color', 'dark_accent_color', 'dark_card_background_color']);
    render_design_group($path, $v, '다크모드 · 헤더 색상', '어두운 모드에서 상단 고정 메뉴 바에만 적용됩니다.', ['dark_header_background_color', 'dark_header_text_color']);
    render_design_group($path, $v, '폰트 설정', '업로드한 폰트를 본문/제목에 적용합니다. 로고와 상단 메뉴는 깨지지 않도록 제외됩니다.', ['font_family', 'font_url', 'font_apply_target']);
    echo '</div></div>';
}

function render_assoc(string $path, array $v, string $label): void {
    echo '<div class="rounded border border-stone-100 p-3 dark:border-stone-800">';
    echo '<p class="mb-3 text-xs font-semibold uppercase tracking-[0.15em] text-stone-400">' . e($label) . '</p>';
    echo '<div class="space-y-3">';
    render_assoc_fields($path, $v);
    echo '</div></div>';
}
function render_assoc_fields(string $path, array $obj): void {
    foreach ($obj as $key => $val) {
        render_node($path . '[' . $key . ']', $val, (string)$key);
    }
}
