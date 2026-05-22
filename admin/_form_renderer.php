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
    return is_string($v) && (bool)preg_match('/\.(jpe?g|png|gif|webp|svg)($|\?)/i', $v);
}
function fr_is_multiline(string $key, $v): bool {
    if (preg_match('/body|description|paragraph|text|overview/i', $key)) return true;
    return is_string($v) && strlen($v) > 100;
}
function fr_is_color(string $key, $v): bool {
    return is_string($v) && preg_match('/color/i', $key);
}
function fr_label(string $key): string {
    return ucwords(str_replace(['_', '-'], ' ', $key));
}
function fr_select_options(string $key): ?array {
    switch ($key) {
        case 'apply_to_all_pages':
            return ['1' => '전체 페이지에 공통 색상 적용', '0' => '페이지별 색상 사용'];
        case 'enabled':
            return ['1' => '사용', '0' => '사용 안 함'];
        case 'per_page':
            return ['3' => '3개씩', '4' => '4개씩', '5' => '5개씩', '6' => '6개씩', '8' => '8개씩', '9' => '9개씩', '12' => '12개씩', '16' => '16개씩', '24' => '24개씩'];
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
    elseif  (is_array($v))          render_assoc($path, $v, fr_label($key));
    elseif  (fr_is_color($key, $v)) render_color($path, (string)$v, fr_label($key));
    elseif  (fr_is_image($v))       render_image($path, (string)$v, fr_label($key));
    elseif  (fr_select_options($key)) render_select($path, (string)$v, fr_label($key), fr_select_options($key) ?? []);
    else                            render_scalar($path, $v, fr_label($key), $key);
}

function render_color(string $path, string $v, string $label): void {
    $v = preg_match('/^#[0-9A-Fa-f]{6}$/', $v) ? strtolower($v) : '#ffffff';
    echo '<div><label class="admin-label">' . e($label) . '</label>';
    echo '<div class="flex gap-2">';
    echo '<input class="h-11 w-16 rounded border border-stone-300 bg-white p-1 dark:border-stone-700 dark:bg-stone-900" type="color" value="' . e($v) . '" data-color-picker>';
    echo '<input class="admin-input" type="text" name="' . e($path) . '" value="' . e($v) . '" pattern="#[0-9A-Fa-f]{6}" maxlength="7" placeholder="#ffffff" data-color-field>';
    echo '</div></div>';
}

function render_select(string $path, string $v, string $label, array $options): void {
    echo '<div><label class="admin-label">' . e($label) . '</label>';
    echo '<select class="admin-input" name="' . e($path) . '">';
    foreach ($options as $value => $text) {
        echo '<option value="' . e($value) . '"' . ((string)$value === $v ? ' selected' : '') . '>' . e($text) . '</option>';
    }
    echo '</select></div>';
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
    echo '<input class="admin-input" type="text" name="' . e($path) . '" value="' . e($v) . '" data-image-path>';
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
