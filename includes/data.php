<?php
declare(strict_types=1);

/**
 * data/{name}.json 을 배열로 로드. 실패 시 빈 배열.
 */
function load_data(string $name): array
{
    $path = DATA_DIR . '/' . basename($name) . '.json';
    if (!is_file($path)) {
        return [];
    }
    $raw = file_get_contents($path);
    if ($raw === false || $raw === '') {
        return [];
    }
    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        error_log("Invalid JSON in {$path}: " . json_last_error_msg());
        return [];
    }
    return $decoded;
}

/**
 * data/{name}.json 에 배열을 저장.
 * 1차: tmp + rename 원자적 저장
 * 2차: 서버 권한 때문에 tmp 생성이 막힌 경우 기존 파일에 직접 저장
 */
function save_data(string $name, array $payload): void
{
    if (!is_dir(DATA_DIR)) {
        if (!@mkdir(DATA_DIR, 0755, true) && !is_dir(DATA_DIR)) {
            throw new RuntimeException('데이터 디렉터리 생성 실패: ' . DATA_DIR);
        }
    }
    $path = DATA_DIR . '/' . basename($name) . '.json';
    $tmp  = $path . '.tmp.' . bin2hex(random_bytes(4));

    $json = json_encode(
        $payload,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
    if ($json === false) {
        throw new RuntimeException('JSON 인코딩 실패: ' . json_last_error_msg());
    }

    $tmpOk = @file_put_contents($tmp, $json, LOCK_EX);
    if ($tmpOk !== false) {
        @chmod($tmp, is_file($path) ? (fileperms($path) & 0777) : 0644);
        if (@rename($tmp, $path)) {
            clearstatcache(true, $path);
            return;
        }
        @unlink($tmp);
    }

    $directOk = @file_put_contents($path, $json, LOCK_EX);
    if ($directOk !== false) {
        clearstatcache(true, $path);
        return;
    }

    throw new RuntimeException(
        '데이터 저장 실패. 서버에서 data 폴더 또는 JSON 파일 쓰기 권한을 확인해야 합니다: ' . $path
    );
}

/**
 * 사이트 공통 데이터 (브랜드/네비/푸터) 로드. 캐시.
 */
function site_data(): array
{
    static $cache = null;
    if ($cache === null) {
        $cache = load_data('site');
    }
    return $cache;
}

/**
 * projects.json 에서 id 로 단건 반환. 없으면 null.
 */
function find_project(string $id): ?array
{
    $projects = load_data('projects')['items'] ?? [];
    foreach ($projects as $p) {
        if (($p['id'] ?? '') === $id) {
            return $p;
        }
    }
    return null;
}

/**
 * 카테고리 ID 로 work.json 의 라벨을 찾아준다 (필터/카드 라벨에 활용).
 */
function category_label(string $id): array
{
    static $map = null;
    if ($map === null) {
        $map = [];
        foreach ((load_data('work')['filters'] ?? []) as $f) {
            if (isset($f['id'])) {
                $map[$f['id']] = $f['label'] ?? ['ko' => '', 'en' => ''];
            }
        }
    }
    return $map[$id] ?? ['ko' => '', 'en' => $id];
}

function active_filter_from(array $filters): string
{
    $requested = isset($_GET['filter']) ? (string)$_GET['filter'] : 'all';
    $ids = array_map(static fn($f) => (string)($f['id'] ?? ''), $filters);
    return in_array($requested, $ids, true) ? $requested : 'all';
}

function filter_by_category(array $items, string $filter): array
{
    if ($filter === 'all') {
        return array_values($items);
    }
    return array_values(array_filter($items, static fn($item) => (string)($item['category_id'] ?? '') === $filter));
}

function pagination_settings(array $data, int $defaultPerPage = 12): array
{
    $p = is_array($data['pagination'] ?? null) ? $data['pagination'] : [];
    $enabled = (string)($p['enabled'] ?? '1') !== '0';
    $perPage = max(1, min(48, (int)($p['per_page'] ?? $defaultPerPage)));
    $page = max(1, (int)($_GET['page'] ?? 1));
    return ['enabled' => $enabled, 'per_page' => $perPage, 'page' => $page];
}

function paginate_array(array $items, array $settings): array
{
    if (!$settings['enabled']) {
        return ['items' => $items, 'page' => 1, 'total_pages' => 1, 'total' => count($items)];
    }
    $total = count($items);
    $totalPages = max(1, (int)ceil($total / $settings['per_page']));
    $page = min((int)$settings['page'], $totalPages);
    $offset = ($page - 1) * (int)$settings['per_page'];
    return [
        'items' => array_slice($items, $offset, (int)$settings['per_page']),
        'page' => $page,
        'total_pages' => $totalPages,
        'total' => $total,
    ];
}

function page_url(array $extra): string
{
    $query = array_merge($_GET, $extra);
    foreach ($query as $key => $value) {
        if ($value === null || $value === '' || $value === 'all' && $key === 'filter' || $value === 1 && $key === 'page') {
            unset($query[$key]);
        }
    }
    $path = basename(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: 'index.php');
    return './' . $path . ($query ? '?' . http_build_query($query) : '');
}

function hex_color($value, string $fallback): string
{
    return is_string($value) && preg_match('/^#[0-9A-Fa-f]{6}$/', $value) ? $value : $fallback;
}

function design_defaults(): array
{
    return [
        'background_color' => '#f8f6f3',
        'text_color' => '#1c1917',
        'muted_text_color' => '#57534e',
        'accent_color' => '#047857',
        'card_background_color' => '#ffffff',
        'header_background_color' => '#f8f6f3',
        'header_text_color' => '#44403c',
        'brand_script_text' => 'rohigraphy',
        'brand_script_size' => '0.98rem',
        'brand_main_size' => '0.72rem',
        'brand_accent_color' => '#047857',
        'header_padding_y' => '1.25rem',
        'show_brand_main' => '1',
        'dark_background_color' => '#0f0f10',
        'dark_text_color' => '#f5f5f4',
        'dark_muted_text_color' => '#c4beb8',
        'dark_accent_color' => '#34d399',
        'dark_card_background_color' => '#1c1917',
        'dark_header_background_color' => '#0f0f10',
        'dark_header_text_color' => '#e7e5e4',
        'dark_brand_accent_color' => '#34d399',
        'font_family' => 'Inter',
        'font_url' => '',
        'font_apply_target' => 'logo',
    ];
}

function resolved_design(array $pageData = []): array
{
    $siteDesign = site_data()['design'] ?? [];
    $pageDesign = $pageData['design'] ?? [];
    $defaults = design_defaults();
    $applyAll = (string)($siteDesign['apply_to_all_pages'] ?? '0') === '1';
    $base = array_merge($defaults, is_array($siteDesign) ? $siteDesign : []);
    if (!$applyAll && is_array($pageDesign)) {
        $base = array_merge($base, $pageDesign);
    }
    foreach ($defaults as $key => $fallback) {
        if (strpos($key, 'color') !== false) {
            $base[$key] = hex_color($base[$key] ?? null, $fallback);
        } else {
            $base[$key] = is_string($base[$key] ?? null) ? (string)$base[$key] : $fallback;
        }
    }
    return $base;
}

function page_design_style(array $pageData = []): string
{
    $d = resolved_design($pageData);
    $fontName = "'" . str_replace("'", '', $d['font_family']) . "'";
    $fontTarget = (string)($d['font_apply_target'] ?? 'logo');
    $bodyFont = $fontTarget === 'body' ? $fontName : 'Inter';
    $headingFont = $fontTarget === 'heading' ? $fontName : 'inherit';
    $brandFont = $fontTarget === 'logo' ? $fontName : "Georgia, 'Times New Roman', serif";
    return sprintf(
        '--home-bg:%s;--home-text:%s;--home-muted:%s;--home-accent:%s;--home-card:%s;--header-bg:%s;--header-text:%s;--brand-accent:%s;--brand-script-size:%s;--brand-main-size:%s;--header-padding-y:%s;--dark-home-bg:%s;--dark-home-text:%s;--dark-home-muted:%s;--dark-home-accent:%s;--dark-home-card:%s;--dark-header-bg:%s;--dark-header-text:%s;--dark-brand-accent:%s;--site-font:%s;--heading-font:%s;--brand-font:%s;',
        $d['background_color'],
        $d['text_color'],
        $d['muted_text_color'],
        $d['accent_color'],
        $d['card_background_color'],
        $d['header_background_color'],
        $d['header_text_color'],
        $d['brand_accent_color'],
        $d['brand_script_size'],
        $d['brand_main_size'],
        $d['header_padding_y'],
        $d['dark_background_color'],
        $d['dark_text_color'],
        $d['dark_muted_text_color'],
        $d['dark_accent_color'],
        $d['dark_card_background_color'],
        $d['dark_header_background_color'],
        $d['dark_header_text_color'],
        $d['dark_brand_accent_color'],
        $bodyFont,
        $headingFont,
        $brandFont
    );
}
