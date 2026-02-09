<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class FooterComponentController extends Controller
{
    private const TABLE = 'footer_components';
    private const LOG_TABLE = 'user_data_activity_log';

    /** cache schema checks */
    protected array $colCache = [];

    /* ============================================
     | Helpers
     |============================================ */

    private function actor(Request $r): array
    {
        return [
            'id'   => (int) ($r->attributes->get('auth_tokenable_id') ?? optional($r->user())->id ?? 0),
            'role' => (string) ($r->attributes->get('auth_role') ?? ($r->user()->role ?? '')),
            'type' => (string) ($r->attributes->get('auth_tokenable_type') ?? ($r->user() ? get_class($r->user()) : '')),
            'uuid' => (string) ($r->attributes->get('auth_user_uuid') ?? ($r->user()->uuid ?? '')),
        ];
    }

    private function normSlug(?string $s): string
    {
        $s = trim((string) $s);
        return $s === '' ? '' : Str::slug($s, '-');
    }

    protected function toUrl(?string $path): ?string
    {
        $path = trim((string) $path);
        if ($path === '') return null;
        if (preg_match('~^https?://~i', $path)) return $path;
        return url('/' . ltrim($path, '/'));
    }

    protected function decodeJsonish($value, $default = null)
    {
        if ($value === null) return $default;

        if (is_array($value)) return $value;

        if (is_string($value)) {
            $value = trim($value);
            if ($value === '') return $default;
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) return $decoded;
        }

        return $default;
    }

    protected function ensureArray($value): array
    {
        return is_array($value) ? $value : [];
    }

    protected function encode($value): ?string
    {
        if ($value === null) return null;
        return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    protected function hasCol(string $table, string $col): bool
    {
        $key = $table . '.' . $col;
        if (!array_key_exists($key, $this->colCache)) {
            $this->colCache[$key] = Schema::hasTable($table) && Schema::hasColumn($table, $col);
        }
        return (bool) $this->colCache[$key];
    }

    /* ============================================
     | Activity Log Helpers (POST/PUT/PATCH/DELETE)
     |============================================ */

    protected function canLogActivity(): bool
    {
        try {
            return Schema::hasTable(self::LOG_TABLE);
        } catch (Throwable $e) {
            return false;
        }
    }

    protected function pickForLog($rowOrArray, array $keys): array
    {
        $src = is_array($rowOrArray) ? $rowOrArray : (array) $rowOrArray;
        $out = [];
        foreach ($keys as $k) {
            if (array_key_exists($k, $src)) $out[$k] = $src[$k];
        }
        return $out;
    }

    protected function scrubForLog($value, int $maxStr = 8000, int $maxItems = 200, int $depth = 3)
    {
        if ($value === null) return null;

        if (is_string($value)) {
            $v = $value;
            if (mb_strlen($v) > $maxStr) $v = mb_substr($v, 0, $maxStr) . '…';
            return $v;
        }

        if (is_bool($value) || is_int($value) || is_float($value)) return $value;

        if ($depth <= 0) return '[truncated]';

        if (is_object($value)) $value = (array) $value;

        if (is_array($value)) {
            $out = [];
            $i = 0;
            foreach ($value as $k => $v) {
                if ($i++ >= $maxItems) {
                    $out['__truncated__'] = true;
                    break;
                }
                $out[$k] = $this->scrubForLog($v, $maxStr, $maxItems, $depth - 1);
            }
            return $out;
        }

        // fallback
        $v = (string) $value;
        if (mb_strlen($v) > $maxStr) $v = mb_substr($v, 0, $maxStr) . '…';
        return $v;
    }

    protected function logActivity(
        Request $r,
        string $activity,
        string $module,
        string $tableName,
        ?int $recordId = null,
        ?array $changedFields = null,
        $oldValues = null,
        $newValues = null,
        ?string $note = null
    ): void {
        if (!$this->canLogActivity()) return;

        try {
            $actor = $this->actor($r);

            $payload = [
                'performed_by'      => (int) ($actor['id'] ?? 0),
                'performed_by_role' => (string) ($actor['role'] ?? ''),
                'ip'                => (string) ($r->ip() ?? ''),
                'user_agent'        => (string) ($r->userAgent() ?? ''),

                'activity'          => $activity,
                'module'            => $module,

                'table_name'        => $tableName,
                'record_id'         => $recordId,

                'changed_fields'    => $changedFields !== null ? $this->encode($this->scrubForLog(array_values($changedFields))) : null,
                'old_values'        => $oldValues !== null ? $this->encode($this->scrubForLog($oldValues)) : null,
                'new_values'        => $newValues !== null ? $this->encode($this->scrubForLog($newValues)) : null,

                'log_note'          => $note,
                'created_at'        => now(),
                'updated_at'        => now(),
            ];

            DB::table(self::LOG_TABLE)->insert($payload);
        } catch (Throwable $e) {
            // Never break main functionality due to logging failure
        }
    }

    /* ============================================
     | Upload utils (public/...)
     |============================================ */

    protected function uploadFileToPublic($file, string $dirRel, string $prefix): array
    {
        $originalName = $file->getClientOriginalName();
        $mimeType     = $file->getClientMimeType() ?: $file->getMimeType();
        $fileSize     = (int) $file->getSize();
        $ext          = strtolower($file->getClientOriginalExtension() ?: 'bin');

        $dirRel = trim($dirRel, '/');
        $dirAbs = public_path($dirRel);
        if (!is_dir($dirAbs)) @mkdir($dirAbs, 0775, true);

        $filename = $prefix . '-' . Str::random(8) . '.' . $ext;
        $file->move($dirAbs, $filename);

        return [
            'path' => $dirRel . '/' . $filename,
            'name' => $originalName,
            'mime' => $mimeType,
            'size' => $fileSize,
        ];
    }

    protected function deletePublicPath(?string $path): void
    {
        $path = trim((string) $path);
        if ($path === '' || preg_match('~^https?://~i', $path)) return;

        $abs = public_path(ltrim($path, '/'));
        if (is_file($abs)) @unlink($abs);
    }

    /* ============================================
     | Normalizers
     |============================================ */

    /**
     * Accepts:
     *  - {"Title":"url", ...}
     *  - [{title:"", url:""}, ...]
     *  - [{label:"", link:""}, ...]
     */
    protected function normalizeMenuJson($value): array
    {
        $arr = $this->decodeJsonish($value, []);
        $arr = $this->ensureArray($arr);

        // associative object => convert to list
        $isAssoc = array_keys($arr) !== range(0, count($arr) - 1);

        $out = [];
        if ($isAssoc) {
            foreach ($arr as $title => $url) {
                $t = trim((string) $title);
                $u = trim((string) $url);
                if ($t === '' || $u === '') continue;
                $out[] = [
                    'title'    => $t,
                    'url'      => $u,
                    'url_full' => $this->toUrl($u),
                ];
            }
            return $out;
        }

        foreach ($arr as $it) {
            if (!is_array($it)) continue;

            $t = trim((string) ($it['title'] ?? $it['label'] ?? $it['name'] ?? ''));
            $u = trim((string) ($it['url'] ?? $it['link'] ?? $it['href'] ?? ''));

            if ($t === '' || $u === '') continue;

            $row = $it;
            $row['title'] = $t;
            $row['url']   = $u;
            $row['url_full'] = $this->toUrl($u);

            $out[] = $row;
        }

        return array_values($out);
    }

    protected function normalizeSocialLinks($value): array
    {
        $arr = $this->decodeJsonish($value, []);
        $arr = $this->ensureArray($arr);

        $out = [];
        foreach ($arr as $it) {
            if (!is_array($it)) continue;

            $u = trim((string) ($it['url'] ?? $it['link'] ?? ''));
            if ($u === '') continue;

            $out[] = [
                'url'      => $u,
                'url_full' => $this->toUrl($u),
                'platform' => (string) ($it['platform'] ?? $it['type'] ?? ''),
                'icon'     => (string) ($it['icon'] ?? ''),
                'label'    => (string) ($it['label'] ?? ''),
            ];
        }

        return array_values($out);
    }

    /* ============================================
     | Section-2: Header menus (max 4) with submenus
     |============================================ */

    protected function normalizeHeaderMenuIds($value): array
    {
        $arr = $this->decodeJsonish($value, []);
        $arr = $this->ensureArray($arr);

        $ids = [];
        foreach ($arr as $v) {
            if (is_int($v) || ctype_digit((string) $v)) {
                $i = (int) $v;
                if ($i > 0) $ids[] = $i;
            } elseif (is_array($v)) {
                $maybe = $v['id'] ?? $v['header_menu_id'] ?? null;
                if ($maybe !== null && (is_int($maybe) || ctype_digit((string) $maybe))) {
                    $i = (int) $maybe;
                    if ($i > 0) $ids[] = $i;
                }
            }
        }

        $ids = array_values(array_unique($ids));
        if (count($ids) > 4) {
            $ids = array_slice($ids, 0, 4);
        }
        return $ids;
    }

    protected function assertHeaderMenusExist(array $ids): void
    {
        if (empty($ids)) return;
        if (!Schema::hasTable('header_menus')) {
            abort(response()->json([
                'success' => false,
                'message' => 'header_menus table not found. Cannot auto-populate section2.',
            ], 422));
        }

        $found = DB::table('header_menus')->whereIn('id', $ids)->pluck('id')->map(fn ($x) => (int) $x)->all();
        $missing = array_values(array_diff($ids, $found));
        if (!empty($missing)) {
            abort(response()->json([
                'success' => false,
                'message' => 'Some selected header menus do not exist: ' . implode(',', $missing),
            ], 422));
        }
    }

    protected function titleOf($row): string
    {
        $a = (array) $row;
        foreach (['title', 'name', 'label', 'menu_title', 'header_text'] as $k) {
            if (isset($a[$k]) && trim((string) $a[$k]) !== '') return (string) $a[$k];
        }
        return '';
    }

    protected function submenuTitleOf($row): string
    {
        $a = (array) $row;
        foreach (['title', 'name', 'label', 'submenu_title'] as $k) {
            if (isset($a[$k]) && trim((string) $a[$k]) !== '') return (string) $a[$k];
        }
        return '';
    }

    protected function fetchHeaderMenuChildren(int $menuId): array
    {
        // Option A: header_menus is hierarchical (parent_id)
        if (Schema::hasTable('header_menus') && $this->hasCol('header_menus', 'parent_id')) {
            $children = DB::table('header_menus')
                ->where('parent_id', $menuId)
                ->whereNull('deleted_at')
                ->orderBy('id', 'asc')
                ->get();

            if ($children->count() > 0) {
                return $children->map(function ($c) {
                    $a = (array) $c;
                    $title = $this->titleOf($a);
                    $url = $a['url'] ?? $a['link_url'] ?? $a['href'] ?? null;
                    return [
                        'id'    => (int) ($a['id'] ?? 0),
                        'uuid'  => (string) ($a['uuid'] ?? ''),
                        'slug'  => (string) ($a['slug'] ?? ''),
                        'title' => (string) $title,
                        'url'   => $url !== null ? (string) $url : null,
                        'url_full' => $url !== null ? $this->toUrl((string) $url) : null,
                    ];
                })->values()->all();
            }
        }

        // Option B: page_submenus has header_menu_id
        if (Schema::hasTable('page_submenus') && $this->hasCol('page_submenus', 'header_menu_id')) {
            $children = DB::table('page_submenus')
                ->where('header_menu_id', $menuId)
                ->whereNull('deleted_at')
                ->orderBy('id', 'asc')
                ->get();

            return $children->map(function ($c) {
                $a = (array) $c;
                $title = $this->submenuTitleOf($a);
                $url = $a['url'] ?? $a['link_url'] ?? $a['href'] ?? null;
                return [
                    'id'    => (int) ($a['id'] ?? 0),
                    'uuid'  => (string) ($a['uuid'] ?? ''),
                    'slug'  => (string) ($a['slug'] ?? ''),
                    'title' => (string) $title,
                    'url'   => $url !== null ? (string) $url : null,
                    'url_full' => $url !== null ? $this->toUrl((string) $url) : null,
                ];
            })->values()->all();
        }

        // Option C: none detected
        return [];
    }

    protected function fetchHeaderMenusTreeForIds(array $ids): array
    {
        if (empty($ids)) return [];

        $rows = DB::table('header_menus')
            ->whereIn('id', $ids)
            ->whereNull('deleted_at')
            ->get();

        $byId = [];
        foreach ($rows as $r) {
            $a = (array) $r;
            $title = $this->titleOf($a);
            $url = $a['url'] ?? $a['link_url'] ?? $a['href'] ?? null;

            $byId[(int) $a['id']] = [
                'id'    => (int) ($a['id'] ?? 0),
                'uuid'  => (string) ($a['uuid'] ?? ''),
                'slug'  => (string) ($a['slug'] ?? ''),
                'title' => (string) $title,
                'url'   => $url !== null ? (string) $url : null,
                'url_full' => $url !== null ? $this->toUrl((string) $url) : null,
                'submenus' => $this->fetchHeaderMenuChildren((int) $a['id']),
            ];
        }

        // keep selection order
        $out = [];
        foreach ($ids as $id) {
            if (isset($byId[$id])) $out[] = $byId[$id];
        }
        return $out;
    }

    /* ============================================
     | Brand area from Header Components (optional)
     |============================================ */

    protected function resolveHeaderComponent($identifier)
    {
        if (!Schema::hasTable('header_components')) return null;

        $q = DB::table('header_components')->whereNull('deleted_at');

        if ($identifier === null || trim((string) $identifier) === '') {
            // fallback: latest
            return $q->orderBy('id', 'desc')->first();
        }

        $identifier = (string) $identifier;

        if (ctype_digit($identifier)) {
            $q->where('id', (int) $identifier);
        } elseif (Str::isUuid($identifier)) {
            $q->where('uuid', $identifier);
        } else {
            $q->where('slug', $identifier);
        }

        return $q->first();
    }

    protected function pullBrandFromHeaderComponent($hc): array
    {
        if (!$hc) return [null, null, null];

        $a = (array) $hc;

        $logo = $a['primary_logo_url'] ?? $a['secondary_logo_url'] ?? null;
        $title = $a['header_text'] ?? null;

        $rot = [];
        if (!empty($a['rotating_text_json'])) {
            $decoded = json_decode((string) $a['rotating_text_json'], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) $rot = $decoded;
        }

        return [$logo, $title, $rot];
    }

    /* ============================================
     | Section-2 resolver for PUBLIC render
     | Converts saved "blocks config" into full tree
     | so public footer does NOT need /api/header-menus
     |============================================ */

    protected function normalizeIdListSimple($value): array
    {
        $arr = $this->decodeJsonish($value, []);
        $arr = $this->ensureArray($arr);

        $out = [];
        foreach ($arr as $v) {
            if (is_int($v) || ctype_digit((string) $v)) {
                $i = (int) $v;
                if ($i > 0) $out[] = $i;
            }
        }
        return array_values(array_unique($out));
    }

    protected function resolveSection2ForPublic($value): array
    {
        $blocks = $this->decodeJsonish($value, []);
        $blocks = $this->ensureArray($blocks);

        if (empty($blocks)) return [];

        // If already a "tree" (has children/submenus), just normalize and return (max 4)
        $looksTree = false;
        foreach ($blocks as $b) {
            if (!is_array($b)) continue;
            if (
                (isset($b['children']) && is_array($b['children'])) ||
                (isset($b['submenus']) && is_array($b['submenus'])) ||
                (isset($b['childs']) && is_array($b['childs']))
            ) {
                $looksTree = true;
                break;
            }
        }

        if ($looksTree) {
            $out = [];
            foreach ($blocks as $b) {
                if (!is_array($b)) continue;

                $title = trim((string) ($b['title'] ?? $b['menu_title'] ?? $b['name'] ?? $b['label'] ?? ''));
                $mid   = (int) ($b['header_menu_id'] ?? $b['menu_id'] ?? $b['id'] ?? 0);

                $kids = $this->ensureArray($b['children'] ?? $b['submenus'] ?? $b['childs'] ?? []);
                $kidsOut = [];

                foreach ($kids as $ch) {
                    if (!is_array($ch)) continue;

                    $t = trim((string) ($ch['title'] ?? $ch['name'] ?? $ch['label'] ?? ''));
                    if ($t === '') continue;

                    $url = $ch['url'] ?? $ch['link_url'] ?? $ch['href'] ?? null;
                    $slug = (string) ($ch['slug'] ?? '');

                    // build url_full if missing
                    $urlFull = $ch['url_full'] ?? null;
                    if (!$urlFull) {
                        if ($url !== null && trim((string)$url) !== '') $urlFull = $this->toUrl((string)$url);
                        elseif ($slug !== '') $urlFull = $this->toUrl('/' . ltrim($slug, '/'));
                    }

                    $kidsOut[] = [
                        'id'       => (int) ($ch['id'] ?? 0),
                        'uuid'     => (string) ($ch['uuid'] ?? ''),
                        'slug'     => $slug,
                        'title'    => $t,
                        'url'      => $url !== null ? (string) $url : null,
                        'url_full' => $urlFull,
                    ];
                }

                $out[] = [
                    'header_menu_id' => $mid > 0 ? $mid : null,
                    'title'          => $title !== '' ? $title : 'Menu',
                    'children'       => $kidsOut,
                ];

                if (count($out) >= 4) break;
            }

            return $out;
        }

        // Otherwise: it's your "block config" shape: {title, header_menu_id, child_ids}
        $menuIds = [];
        foreach ($blocks as $b) {
            if (!is_array($b)) continue;
            $mid = (int) ($b['header_menu_id'] ?? $b['menu_id'] ?? $b['id'] ?? 0);
            if ($mid > 0) $menuIds[] = $mid;
        }
        $menuIds = array_values(array_unique($menuIds));
        if (empty($menuIds) || !Schema::hasTable('header_menus')) return [];

        $rows = DB::table('header_menus')
            ->whereIn('id', $menuIds)
            ->whereNull('deleted_at')
            ->get();

        $menuMap = [];
        foreach ($rows as $r) {
            $menuMap[(int) $r->id] = (array) $r;
        }

        $out = [];
        foreach ($blocks as $b) {
            if (!is_array($b)) continue;

            $mid = (int) ($b['header_menu_id'] ?? $b['menu_id'] ?? $b['id'] ?? 0);
            if ($mid <= 0) continue;

            $menuRow = $menuMap[$mid] ?? null;
            $menuTitle = $menuRow ? trim((string) $this->titleOf($menuRow)) : '';

            $blockTitle = trim((string) ($b['title'] ?? ''));
            $finalTitle = $blockTitle !== '' ? $blockTitle : ($menuTitle !== '' ? $menuTitle : 'Menu');

            $childIds = $this->normalizeIdListSimple($b['child_ids'] ?? $b['submenu_ids'] ?? $b['children_ids'] ?? []);
            $childrenAll = $this->fetchHeaderMenuChildren($mid);

            if (!empty($childIds)) {
                $set = array_flip($childIds);
                $childrenAll = array_values(array_filter($childrenAll, function ($ch) use ($set) {
                    $id = (int) ($ch['id'] ?? 0);
                    return $id > 0 && isset($set[$id]);
                }));
            }

            $out[] = [
                'header_menu_id' => $mid,
                'title'          => $finalTitle,
                'children'       => $childrenAll,
            ];

            if (count($out) >= 4) break;
        }

        return $out;
    }

    /* ============================================
     | Row normalization for output
     |============================================ */

    protected function normalizeRow($row): array
    {
        $arr = (array) $row;

        foreach ([
            'section1_menu_json',
            'section2_header_menu_json',
            'section3_menu_json',
            'rotating_text_json',
            'social_links_json',
            'section5_menu_json',
            'metadata',
        ] as $k) {
            if (array_key_exists($k, $arr) && is_string($arr[$k])) {
                $decoded = json_decode($arr[$k], true);
                $arr[$k] = (json_last_error() === JSON_ERROR_NONE) ? $decoded : null;
            }
        }

        // Decorate menus + social
        $arr['section1_menu'] = $this->normalizeMenuJson($arr['section1_menu_json'] ?? []);
        $arr['section3_menu'] = $this->normalizeMenuJson($arr['section3_menu_json'] ?? []);
        $arr['section5_menu'] = $this->normalizeMenuJson($arr['section5_menu_json'] ?? []);
        $arr['social_links']  = $this->normalizeSocialLinks($arr['social_links_json'] ?? []);

        // Brand logo full url
        $arr['brand_logo_full_url'] = $this->toUrl($arr['brand_logo_url'] ?? null);

        // Section2: keep raw saved JSON (your admin needs it),
        // and ALSO provide a resolved tree for public render.
        $arr['section2_header_menus'] = $this->ensureArray($arr['section2_header_menu_json'] ?? []);
        $arr['section2_header_menus_resolved'] = $this->resolveSection2ForPublic($arr['section2_header_menu_json'] ?? []);

        // ✅ Same-as-header flag (only if column exists)
        if ($this->hasCol(self::TABLE, 'same_as_header')) {
            $flag = (bool) ($arr['same_as_header'] ?? false);
            $arr['same_as_header'] = $flag;

            // optional aliases (handy for older UIs)
            $arr['section_4_same_as_header'] = $flag;
            $arr['is_same_as_header'] = $flag;
        }

        return $arr;
    }

    protected function ensureUniqueSlug(string $slug, ?string $ignoreUuid = null): string
    {
        $base = $slug;
        $i = 2;

        while (
            DB::table(self::TABLE)
                ->where('slug', $slug)
                ->when($ignoreUuid, function ($q) use ($ignoreUuid) {
                    $q->where('uuid', '!=', $ignoreUuid);
                })
                ->whereNull('deleted_at')
                ->exists()
        ) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    protected function resolveFooterComponent($identifier, bool $includeDeleted = false)
    {
        $q = DB::table(self::TABLE);
        if (!$includeDeleted) $q->whereNull('deleted_at');

        if (ctype_digit((string) $identifier)) {
            $q->where('id', (int) $identifier);
        } elseif (Str::isUuid((string) $identifier)) {
            $q->where('uuid', (string) $identifier);
        } else {
            $q->where('slug', (string) $identifier);
        }

        return $q->first();
    }

    protected function baseQuery(Request $request, bool $includeDeleted = false)
    {
        $q = DB::table(self::TABLE . ' as f');

        if (!$includeDeleted) $q->whereNull('f.deleted_at');

        if ($request->filled('q')) {
            $term = '%' . trim((string) $request->query('q')) . '%';
            $q->where(function ($sub) use ($term) {
                $sub->where('f.slug', 'like', $term)
                    ->orWhere('f.brand_title', 'like', $term)
                    ->orWhere('f.copyright_text', 'like', $term);
            });
        }

        if ($request->filled('status')) {
            $q->where('f.status', (int) $request->query('status'));
        }

        $sort = (string) $request->query('sort', 'created_at');
        $dir  = strtolower((string) $request->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        $allowed = ['created_at', 'updated_at', 'slug', 'brand_title', 'id', 'status'];
        if (!in_array($sort, $allowed, true)) $sort = 'created_at';

        $q->orderBy('f.' . $sort, $dir);

        return $q;
    }

    /* ============================================
     | Extra endpoints for UI
     |============================================ */

    public function headerMenuOptions(Request $request)
    {
        if (!Schema::hasTable('header_menus')) {
            return response()->json(['success' => false, 'data' => [], 'message' => 'header_menus table not found'], 200);
        }

        $rows = DB::table('header_menus')
            ->whereNull('deleted_at')
            ->orderBy('id', 'asc')
            ->get();

        $data = [];
        foreach ($rows as $r) {
            $a = (array) $r;
            $data[] = [
                'id'    => (int) ($a['id'] ?? 0),
                'uuid'  => (string) ($a['uuid'] ?? ''),
                'slug'  => (string) ($a['slug'] ?? ''),
                'title' => (string) $this->titleOf($a),
            ];
        }

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function headerComponentOptions(Request $request)
    {
        if (!Schema::hasTable('header_components')) {
            return response()->json(['success' => false, 'data' => [], 'message' => 'header_components table not found'], 200);
        }

        $rows = DB::table('header_components')
            ->whereNull('deleted_at')
            ->orderBy('id', 'desc')
            ->get();

        $data = [];
        foreach ($rows as $r) {
            $a = (array) $r;
            $logo = $a['primary_logo_url'] ?? $a['secondary_logo_url'] ?? null;

            $data[] = [
                'id' => (int) ($a['id'] ?? 0),
                'uuid' => (string) ($a['uuid'] ?? ''),
                'slug' => (string) ($a['slug'] ?? ''),
                'header_text' => (string) ($a['header_text'] ?? ''),
                'logo_url' => $logo !== null ? (string) $logo : null,
                'logo_full_url' => $logo !== null ? $this->toUrl((string) $logo) : null,
            ];
        }

        return response()->json(['success' => true, 'data' => $data]);
    }

    /* ============================================
     | CRUD
     |============================================ */

    public function index(Request $request)
    {
        $perPage = max(1, min(200, (int) $request->query('per_page', 20)));

        $includeDeleted = filter_var($request->query('with_trashed', false), FILTER_VALIDATE_BOOLEAN);
        $onlyDeleted    = filter_var($request->query('only_trashed', false), FILTER_VALIDATE_BOOLEAN);

        $query = $this->baseQuery($request, $includeDeleted || $onlyDeleted);

        if ($onlyDeleted) $query->whereNotNull('f.deleted_at');

        $paginator = $query->paginate($perPage);
        $items = array_map(fn ($r) => $this->normalizeRow($r), $paginator->items());

        return response()->json([
            'success' => true,
            'data' => $items,
            'pagination' => [
                'page'      => $paginator->currentPage(),
                'per_page'  => $paginator->perPage(),
                'total'     => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    public function trash(Request $request)
    {
        $request->query->set('only_trashed', '1');
        return $this->index($request);
    }

    public function show(Request $request, $identifier)
    {
        $includeDeleted = filter_var($request->query('with_trashed', false), FILTER_VALIDATE_BOOLEAN);

        $row = $this->resolveFooterComponent($identifier, $includeDeleted);
        if (!$row) return response()->json(['success' => false, 'message' => 'Footer component not found'], 404);

        return response()->json([
            'success' => true,
            'item'    => $this->normalizeRow($row),
        ]);
    }

    public function store(Request $request)
    {
        $actor = $this->actor($request);

        $validated = $request->validate([
            'slug' => ['nullable', 'string', 'max:160'],

            'section1_menu_json' => ['required'],

            // Section2 (choose header menus)
            'section2_use_header_menus' => ['nullable', 'in:0,1,true,false'],
            'section2_header_menu_ids'  => ['nullable'], // array|json
            'section2_title_override'   => ['nullable', 'string', 'max:150'],
            'section2_header_menu_json' => ['nullable'], // manual mode fallback

            'section3_menu_json' => ['nullable'],

            // ✅ Same-as-header (persist flag)
            'same_as_header' => ['nullable', 'in:0,1,true,false'],

            // Brand area
            'use_header_component_brand' => ['nullable', 'in:0,1,true,false'],
            'header_component_identifier' => ['nullable', 'string', 'max:200'],

            'brand_logo_url' => ['nullable', 'string', 'max:255'],
            'brand_logo'     => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif,svg', 'max:5120'],

            'brand_title'        => ['nullable', 'string', 'max:255'],
            'rotating_text_json' => ['nullable'], // required unless pulled from header component

            'social_links_json' => ['nullable'],
            'address_text'      => ['nullable', 'string'],

            'section5_menu_json' => ['nullable'],

            'copyright_text' => ['required', 'string', 'max:255'],

            'status' => ['nullable', 'in:0,1'],

            'metadata' => ['nullable'],
        ]);

        $slug = $this->normSlug($validated['slug'] ?? '');
        if ($slug === '') $slug = 'default-footer';
        $slug = $this->ensureUniqueSlug($slug);

        $uuid = (string) Str::uuid();
        $now  = now();

        // Section1
        $section1 = $this->normalizeMenuJson($request->input('section1_menu_json'));
        if (empty($section1)) {
            return response()->json(['success' => false, 'message' => 'section1_menu_json is required and must not be empty'], 422);
        }

        // Section3 / Section5
        $section3 = $this->normalizeMenuJson($request->input('section3_menu_json'));
        $section5 = $this->normalizeMenuJson($request->input('section5_menu_json'));

        // Social
        $social = $this->normalizeSocialLinks($request->input('social_links_json'));

        // Section2 mode
        $useHeaderMenus = filter_var($request->input('section2_use_header_menus', false), FILTER_VALIDATE_BOOLEAN);

        $section2 = null;
        if ($useHeaderMenus) {
            $ids = $this->normalizeHeaderMenuIds($request->input('section2_header_menu_ids'));
            if (empty($ids)) {
                return response()->json(['success' => false, 'message' => 'section2_header_menu_ids is required when section2_use_header_menus is checked'], 422);
            }
            if (count($ids) > 4) {
                return response()->json(['success' => false, 'message' => 'Maximum 4 header menus can be selected'], 422);
            }
            $this->assertHeaderMenusExist($ids);
            $section2 = $this->fetchHeaderMenusTreeForIds($ids);
        } else {
            // manual JSON is allowed (nullable)
            if ($request->has('section2_header_menu_json')) {
                $tmp = $this->decodeJsonish($request->input('section2_header_menu_json'), null);
                $section2 = is_array($tmp) ? $tmp : null;
            }
        }

        // ✅ Same-as-header flag (alias for "use_header_component_brand")
        $sameAsHeader = filter_var($request->input('same_as_header', false), FILTER_VALIDATE_BOOLEAN);

        // Brand area mode
        $useHeaderBrand = $sameAsHeader || filter_var($request->input('use_header_component_brand', false), FILTER_VALIDATE_BOOLEAN);

        $dirRel = 'depy_uploads/footer_components/' . $slug;

        $brandLogo = trim((string) ($validated['brand_logo_url'] ?? ''));
        $brandTitle = trim((string) ($validated['brand_title'] ?? ''));
        $rotating = $this->decodeJsonish($request->input('rotating_text_json'), []);

        if ($useHeaderBrand) {
            $hc = $this->resolveHeaderComponent($request->input('header_component_identifier'));
            if (!$hc) {
                return response()->json([
                    'success' => false,
                    'message' => 'Header component not found to auto-fill brand area.',
                ], 422);
            }
            [$logo, $title, $rot] = $this->pullBrandFromHeaderComponent($hc);
            $brandLogo  = (string) ($logo ?? '');
            $brandTitle = (string) ($title ?? '');
            $rotating   = is_array($rot) ? $rot : [];
        } else {
            // Manual mode: allow upload for brand logo
            if ($request->hasFile('brand_logo')) {
                $f = $request->file('brand_logo');
                if (!$f || !$f->isValid()) return response()->json(['success' => false, 'message' => 'Brand logo upload failed'], 422);
                $up = $this->uploadFileToPublic($f, $dirRel, $slug . '-brand');
                $brandLogo = $up['path'];
            }
        }

        // Enforce required brand_title + rotating_text_json (NOT NULL in schema)
        if ($brandTitle === '') {
            return response()->json(['success' => false, 'message' => 'brand_title is required (or enable same_as_header / use_header_component_brand)'], 422);
        }
        if (!is_array($rotating) || empty($rotating)) {
            return response()->json(['success' => false, 'message' => 'rotating_text_json is required (or enable same_as_header / use_header_component_brand)'], 422);
        }

        // If NOT using header brand, brand logo must be provided (your original enforcement)
        if (!$useHeaderBrand && trim($brandLogo) === '') {
            return response()->json(['success' => false, 'message' => 'brand_logo_url or brand_logo upload is required when same_as_header/use_header_component_brand is not checked'], 422);
        }

        // Metadata
        $meta = $this->decodeJsonish($request->input('metadata'), null);

        $insert = [
            'uuid' => $uuid,
            'slug' => $slug,

            'section1_menu_json' => $this->encode($section1),

            'section2_header_menu_json' => $section2 !== null ? $this->encode($section2) : null,
            'section2_title_override'   => $validated['section2_title_override'] ?? null,

            'section3_menu_json' => !empty($section3) ? $this->encode($section3) : null,

            'brand_logo_url'     => $brandLogo !== '' ? $brandLogo : null,
            'brand_title'        => $brandTitle,
            'rotating_text_json' => $this->encode($rotating),

            'social_links_json'  => !empty($social) ? $this->encode($social) : null,
            'address_text'       => $validated['address_text'] ?? null,

            'section5_menu_json' => !empty($section5) ? $this->encode($section5) : null,

            'copyright_text' => $validated['copyright_text'],
            'status' => array_key_exists('status', $validated) ? (int) $validated['status'] : 1,

            'created_by'    => $actor['id'] ?: null,
            'created_at'    => $now,
            'updated_at'    => $now,
            'created_at_ip' => $request->ip(),
            'updated_at_ip' => $request->ip(),
            'metadata'      => $meta !== null ? $this->encode($meta) : null,
        ];

        // ✅ persist same_as_header only if column exists
        if ($this->hasCol(self::TABLE, 'same_as_header')) {
            $insert['same_as_header'] = $sameAsHeader ? 1 : 0;
        }

        $id = DB::table(self::TABLE)->insertGetId($insert);

        $row = DB::table(self::TABLE)->where('id', (int) $id)->first();

        // ✅ LOG: create
        $newForLog = array_merge(['id' => (int) $id], $insert);
        $this->logActivity(
            $request,
            'create',
            'footer_components',
            self::TABLE,
            (int) $id,
            array_keys($newForLog),
            null,
            $newForLog,
            'Created footer component (slug: ' . $slug . ')'
        );

        return response()->json([
            'success' => true,
            'data'    => $row ? $this->normalizeRow($row) : null,
        ]);
    }

    public function update(Request $request, $identifier)
    {
        $row = $this->resolveFooterComponent($identifier, true);
        if (!$row) return response()->json(['success' => false, 'message' => 'Footer component not found'], 404);

        $oldRowArr = (array) $row;

        $validated = $request->validate([
            'slug' => ['nullable', 'string', 'max:160'],

            'section1_menu_json' => ['nullable'],

            'section2_use_header_menus' => ['nullable', 'in:0,1,true,false'],
            'section2_header_menu_ids'  => ['nullable'],
            'section2_title_override'   => ['nullable', 'string', 'max:150'],
            'section2_header_menu_json' => ['nullable'],

            'section3_menu_json' => ['nullable'],

            // ✅ Same-as-header (persist flag)
            'same_as_header' => ['nullable', 'in:0,1,true,false'],

            'use_header_component_brand' => ['nullable', 'in:0,1,true,false'],
            'header_component_identifier' => ['nullable', 'string', 'max:200'],

            'brand_logo_url' => ['nullable', 'string', 'max:255'],
            'brand_logo'     => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif,svg', 'max:5120'],

            'brand_title'        => ['nullable', 'string', 'max:255'],
            'rotating_text_json' => ['nullable'],

            'social_links_json' => ['nullable'],
            'address_text'      => ['nullable', 'string'],

            'section5_menu_json' => ['nullable'],

            'copyright_text' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:0,1'],
            'metadata' => ['nullable'],
        ]);

        $update = [
            'updated_at'    => now(),
            'updated_at_ip' => $request->ip(),
        ];

        // slug
        $finalSlug = (string) ($row->slug ?? 'default-footer');
        if ($request->filled('slug')) {
            $s = $this->normSlug($request->input('slug'));
            if ($s !== '') {
                $s = $this->ensureUniqueSlug($s, (string) ($row->uuid ?? null));
                $update['slug'] = $s;
                $finalSlug = $s;
            }
        }

        $dirRel = 'depy_uploads/footer_components/' . $finalSlug;

        // ✅ same_as_header handling (persist + act as alias for header-brand)
        $sameAsHeader = null;
        if ($request->has('same_as_header')) {
            $sameAsHeader = filter_var($request->input('same_as_header', false), FILTER_VALIDATE_BOOLEAN);

            if ($this->hasCol(self::TABLE, 'same_as_header')) {
                $update['same_as_header'] = $sameAsHeader ? 1 : 0;
            }
        } else {
            // if request doesn't send it, keep existing (when column exists)
            if ($this->hasCol(self::TABLE, 'same_as_header')) {
                $sameAsHeader = (bool) ($row->same_as_header ?? false);
            }
        }

        // Section1 (NOT NULL in schema: if provided, must not be empty)
        if ($request->has('section1_menu_json')) {
            $section1 = $this->normalizeMenuJson($request->input('section1_menu_json'));
            if (empty($section1)) {
                return response()->json(['success' => false, 'message' => 'section1_menu_json cannot be empty'], 422);
            }
            $update['section1_menu_json'] = $this->encode($section1);
        }

        // Section3 / Section5
        if ($request->has('section3_menu_json')) {
            $section3 = $this->normalizeMenuJson($request->input('section3_menu_json'));
            $update['section3_menu_json'] = !empty($section3) ? $this->encode($section3) : null;
        }
        if ($request->has('section5_menu_json')) {
            $section5 = $this->normalizeMenuJson($request->input('section5_menu_json'));
            $update['section5_menu_json'] = !empty($section5) ? $this->encode($section5) : null;
        }

        // Social
        if ($request->has('social_links_json')) {
            $social = $this->normalizeSocialLinks($request->input('social_links_json'));
            $update['social_links_json'] = !empty($social) ? $this->encode($social) : null;
        }

        // Address
        if ($request->has('address_text')) {
            $update['address_text'] = $validated['address_text'] ?? null;
        }

        // Section2 title override
        if ($request->has('section2_title_override')) {
            $update['section2_title_override'] = $validated['section2_title_override'] ?? null;
        }

        // Section2 mode
        if ($request->has('section2_use_header_menus') || $request->has('section2_header_menu_ids') || $request->has('section2_header_menu_json')) {
            $useHeaderMenus = filter_var($request->input('section2_use_header_menus', false), FILTER_VALIDATE_BOOLEAN);

            if ($useHeaderMenus) {
                $ids = $this->normalizeHeaderMenuIds($request->input('section2_header_menu_ids'));
                if (empty($ids)) {
                    return response()->json(['success' => false, 'message' => 'section2_header_menu_ids is required when section2_use_header_menus is checked'], 422);
                }
                if (count($ids) > 4) {
                    return response()->json(['success' => false, 'message' => 'Maximum 4 header menus can be selected'], 422);
                }
                $this->assertHeaderMenusExist($ids);
                $section2 = $this->fetchHeaderMenusTreeForIds($ids);
                $update['section2_header_menu_json'] = $this->encode($section2);
            } else {
                if ($request->has('section2_header_menu_json')) {
                    $tmp = $this->decodeJsonish($request->input('section2_header_menu_json'), null);
                    $update['section2_header_menu_json'] = is_array($tmp) ? $this->encode($tmp) : null;
                }
            }
        }

        // Brand area mode
        $explicitUseHeaderBrand = filter_var($request->input('use_header_component_brand', false), FILTER_VALIDATE_BOOLEAN);
        $useHeaderBrand = ($sameAsHeader === true) || ($request->has('use_header_component_brand') && $explicitUseHeaderBrand);

        if ($useHeaderBrand) {
            $hc = $this->resolveHeaderComponent($request->input('header_component_identifier'));
            if (!$hc) {
                return response()->json(['success' => false, 'message' => 'Header component not found to auto-fill brand area.'], 422);
            }

            [$logo, $title, $rot] = $this->pullBrandFromHeaderComponent($hc);

            $update['brand_logo_url'] = $logo !== null ? (string) $logo : null;
            $update['brand_title']    = (string) ($title ?? '');
            $update['rotating_text_json'] = $this->encode(is_array($rot) ? $rot : []);
        } else {
            // Manual edits
            if ($request->has('brand_logo_url')) {
                $update['brand_logo_url'] = trim((string) ($validated['brand_logo_url'] ?? '')) ?: null;
            }

            if ($request->hasFile('brand_logo')) {
                $f = $request->file('brand_logo');
                if (!$f || !$f->isValid()) return response()->json(['success' => false, 'message' => 'Brand logo upload failed'], 422);

                // delete old only if local file
                $this->deletePublicPath($row->brand_logo_url ?? null);

                $up = $this->uploadFileToPublic($f, $dirRel, $finalSlug . '-brand');
                $update['brand_logo_url'] = $up['path'];
            }

            if ($request->has('brand_title')) {
                $update['brand_title'] = trim((string) ($validated['brand_title'] ?? ''));
            }

            if ($request->has('rotating_text_json')) {
                $rotating = $this->decodeJsonish($request->input('rotating_text_json'), []);
                if (!is_array($rotating) || empty($rotating)) {
                    return response()->json(['success' => false, 'message' => 'rotating_text_json must be a non-empty array'], 422);
                }
                $update['rotating_text_json'] = $this->encode($rotating);
            }
        }

        // Copyright / status / metadata
        if ($request->has('copyright_text')) {
            $update['copyright_text'] = $validated['copyright_text'];
        }
        if ($request->has('status')) {
            $update['status'] = (int) $validated['status'];
        }
        if ($request->has('metadata')) {
            $meta = $this->decodeJsonish($request->input('metadata'), null);
            $update['metadata'] = $meta !== null ? $this->encode($meta) : null;
        }

        // Final enforcement for NOT NULL columns:
        $finalBrandTitle = array_key_exists('brand_title', $update) ? $update['brand_title'] : ($row->brand_title ?? '');
        $finalRot        = array_key_exists('rotating_text_json', $update) ? $update['rotating_text_json'] : ($row->rotating_text_json ?? null);
        $finalCopyright  = array_key_exists('copyright_text', $update) ? $update['copyright_text'] : ($row->copyright_text ?? '');

        if (trim((string) $finalBrandTitle) === '') {
            return response()->json(['success' => false, 'message' => 'brand_title cannot be empty'], 422);
        }
        if (empty($finalRot)) {
            return response()->json(['success' => false, 'message' => 'rotating_text_json cannot be empty'], 422);
        }
        if (trim((string) $finalCopyright) === '') {
            return response()->json(['success' => false, 'message' => 'copyright_text cannot be empty'], 422);
        }

        DB::table(self::TABLE)->where('id', (int) $row->id)->update($update);

        $fresh = DB::table(self::TABLE)->where('id', (int) $row->id)->first();
        $freshArr = $fresh ? (array) $fresh : [];

        // ✅ LOG: update (store only changed fields snapshot)
        $changedFields = array_keys($update);
        $oldForLog = $this->pickForLog($oldRowArr, $changedFields);
        $newForLog = $this->pickForLog($freshArr, $changedFields);

        $this->logActivity(
            $request,
            'update',
            'footer_components',
            self::TABLE,
            (int) $row->id,
            $changedFields,
            $oldForLog,
            $newForLog,
            'Updated footer component (id: ' . (int) $row->id . ')'
        );

        return response()->json([
            'success' => true,
            'data'    => $fresh ? $this->normalizeRow($fresh) : null,
        ]);
    }

    public function destroy(Request $request, $identifier)
    {
        $row = $this->resolveFooterComponent($identifier, false);
        if (!$row) return response()->json(['success' => false, 'message' => 'Not found or already deleted'], 404);

        $oldRowArr = (array) $row;

        $now = now();
        DB::table(self::TABLE)->where('id', (int) $row->id)->update([
            'deleted_at'    => $now,
            'updated_at'    => $now,
            'updated_at_ip' => $request->ip(),
        ]);

        $fresh = DB::table(self::TABLE)->where('id', (int) $row->id)->first();
        $freshArr = $fresh ? (array) $fresh : [];

        // ✅ LOG: soft delete
        $changed = ['deleted_at', 'updated_at', 'updated_at_ip'];
        $this->logActivity(
            $request,
            'delete',
            'footer_components',
            self::TABLE,
            (int) $row->id,
            $changed,
            $this->pickForLog($oldRowArr, $changed),
            $this->pickForLog($freshArr, $changed),
            'Soft deleted footer component (id: ' . (int) $row->id . ')'
        );

        return response()->json(['success' => true]);
    }

    public function restore(Request $request, $identifier)
    {
        $row = $this->resolveFooterComponent($identifier, true);
        if (!$row || $row->deleted_at === null) {
            return response()->json(['success' => false, 'message' => 'Not found in bin'], 404);
        }

        $oldRowArr = (array) $row;

        $now = now();
        DB::table(self::TABLE)->where('id', (int) $row->id)->update([
            'deleted_at'    => null,
            'updated_at'    => $now,
            'updated_at_ip' => $request->ip(),
        ]);

        $fresh = DB::table(self::TABLE)->where('id', (int) $row->id)->first();
        $freshArr = $fresh ? (array) $fresh : [];

        // ✅ LOG: restore
        $changed = ['deleted_at', 'updated_at', 'updated_at_ip'];
        $this->logActivity(
            $request,
            'restore',
            'footer_components',
            self::TABLE,
            (int) $row->id,
            $changed,
            $this->pickForLog($oldRowArr, $changed),
            $this->pickForLog($freshArr, $changed),
            'Restored footer component (id: ' . (int) $row->id . ')'
        );

        return response()->json([
            'success' => true,
            'data'    => $fresh ? $this->normalizeRow($fresh) : null,
        ]);
    }

    public function forceDelete(Request $request, $identifier)
    {
        $row = $this->resolveFooterComponent($identifier, true);
        if (!$row) return response()->json(['success' => false, 'message' => 'Footer component not found'], 404);

        $oldRowArr = (array) $row;

        // delete local brand logo file (if any)
        $this->deletePublicPath($row->brand_logo_url ?? null);

        DB::table(self::TABLE)->where('id', (int) $row->id)->delete();

        // ✅ LOG: force delete (record removed)
        $minimalOld = $this->pickForLog($oldRowArr, ['id', 'uuid', 'slug', 'brand_logo_url', 'brand_title', 'status', 'deleted_at']);
        $this->logActivity(
            $request,
            'force_delete',
            'footer_components',
            self::TABLE,
            (int) ($oldRowArr['id'] ?? 0),
            ['force_deleted'],
            $minimalOld,
            null,
            'Force deleted footer component (id: ' . (int) ($oldRowArr['id'] ?? 0) . ')'
        );

        return response()->json(['success' => true]);
    }
}
