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
 * data/{name}.json 에 배열을 저장. 원자적 쓰기 (tmp + rename) + 파일락.
 * 실패 시 RuntimeException.
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

    $bytes = file_put_contents($tmp, $json, LOCK_EX);
    if ($bytes === false) {
        throw new RuntimeException('임시 파일 쓰기 실패: ' . $tmp);
    }
    @chmod($tmp, 0644);
    if (!@rename($tmp, $path)) {
        @unlink($tmp);
        throw new RuntimeException('데이터 파일 교체 실패: ' . $path);
    }
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
