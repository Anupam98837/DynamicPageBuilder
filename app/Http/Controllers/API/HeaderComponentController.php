<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HeaderComponentController extends Controller
{
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

    /**
     * ✅ Partner logos now = recruiters.id[] (stored in partner_logos_json)
     * Accepts: array|json-string of [1,2,3] OR [{id:1},{recruiter_id:1}, ...]
     */
    protected function normalizeRecruiterIds($value): array
    {
        $arr = $this->decodeJsonish($value, []);
        $arr = $this->ensureArray($arr);

        $ids = [];
        foreach ($arr as $v) {
            if (is_int($v) || ctype_digit((string) $v)) {
                $i = (int) $v;
                if ($i > 0) $ids[] = $i;
                continue;
            }
            if (is_array($v)) {
                $maybe = $v['recruiter_id'] ?? $v['id'] ?? null;
                if ($maybe !== null && (is_int($maybe) || ctype_digit((string) $maybe))) {
                    $i = (int) $maybe;
                    if ($i > 0) $ids[] = $i;
                }
            }
        }

        return array_values(array_unique($ids));
    }

    protected function assertRecruitersExist(array $ids): void
    {
        if (empty($ids)) return;

        $found = DB::table('recruiters')
            ->whereIn('id', $ids)
            ->pluck('id')
            ->map(fn ($x) => (int) $x)
            ->all();

        $missing = array_values(array_diff($ids, $found));
        if (!empty($missing)) {
            abort(response()->json([
                'success' => false,
                'message' => 'Some selected recruiters do not exist: ' . implode(',', $missing),
            ], 422));
        }
    }

    /**
     * For output: returns recruiter cards in same order as ids.
     */
    protected function fetchRecruitersForIds(array $ids): array
    {
        if (empty($ids)) return [];

        $rows = DB::table('recruiters')
            ->select('id', 'uuid', 'slug', 'title', 'logo_url', 'status')
            ->whereIn('id', $ids)
            ->get();

        $byId = [];
        foreach ($rows as $r) {
            $byId[(int) $r->id] = [
                'id'            => (int) $r->id,
                'uuid'          => (string) ($r->uuid ?? ''),
                'slug'          => (string) ($r->slug ?? ''),
                'title'         => (string) ($r->title ?? ''),
                'logo_url'      => $r->logo_url !== null ? (string) $r->logo_url : null,
                'logo_full_url' => $this->toUrl($r->logo_url ?? null),
                'status'        => (string) ($r->status ?? ''),
            ];
        }

        $out = [];
        foreach ($ids as $id) {
            if (isset($byId[$id])) $out[] = $byId[$id];
        }
        return $out;
    }

    /**
     * Legacy file-based list normalizer (still used for affiliation_logos_json)
     */
    protected function normalizeLogosList($list): array
    {
        $list = $this->ensureArray($list);
        $out  = [];

        foreach ($list as $item) {
            // string path
            if (is_string($item)) {
                $p = trim($item);
                if ($p !== '') {
                    $out[] = [
                        'path' => $p,
                        'url'  => $this->toUrl($p),
                    ];
                }
                continue;
            }

            // object
            if (is_array($item)) {
                $p = trim((string) ($item['path'] ?? $item['url'] ?? ''));
                if ($p === '') continue;

                $row = $item;
                $row['url_full'] = $this->toUrl($p);
                $out[] = $row;
                continue;
            }
        }

        return array_values($out);
    }

    /* ============================================================
     | ✅ Affiliation uploads FIX (store uploaded files into DB JSON)
     |============================================================ */

    protected function extractLogoPath($item): string
    {
        if (is_string($item)) return trim($item);
        if (is_array($item)) return trim((string) ($item['url'] ?? $item['path'] ?? ''));
        return '';
    }

    /**
     * Builds final affiliation_logos_json:
     * [
     *   {"url":"depy_uploads/.../x.jpg","caption":"Opening ceremony"},
     *   ...
     * ]
     */
    protected function buildAffiliationLogos(Request $request, array $keepExisting, string $dirRel, string $prefix): array
    {
        $out = [];

        // normalize "kept" items (store as {url, caption?})
        foreach ($keepExisting as $it) {
            $p = $this->extractLogoPath($it);
            if ($p === '') continue;

            $cap = '';
            if (is_array($it)) $cap = trim((string) ($it['caption'] ?? ''));

            $row = ['url' => $p];
            if ($cap !== '') $row['caption'] = $cap;
            $out[] = $row;
        }

        // append newly uploaded files
        $files = $request->file('affiliation_logos', []);
        if (!is_array($files)) $files = [];

        $caps = $request->input('affiliation_logos_captions', []);
        if (!is_array($caps)) $caps = [];

        foreach ($files as $i => $file) {
            if (!$file || !$file->isValid()) continue;

            $up = $this->uploadFileToPublic($file, $dirRel, $prefix . '-affil-' . ($i + 1));
            $cap = trim((string) ($caps[$i] ?? ''));

            $row = ['url' => $up['path']];
            if ($cap !== '') $row['caption'] = $cap;

            $out[] = $row;
        }

        return array_values($out);
    }

    protected function pathsFromAffiliationList($list): array
    {
        $list = $this->ensureArray($list);
        $paths = [];

        foreach ($list as $it) {
            $p = $this->extractLogoPath($it);
            if ($p !== '') $paths[] = $p;
        }

        return array_values(array_unique($paths));
    }

    /* ============================================
     | Normalizer / Utils
     |============================================ */

    protected function normalizeRow($row): array
    {
        $arr = (array) $row;

        // decode JSON columns
        foreach (['rotating_text_json', 'partner_logos_json', 'affiliation_logos_json', 'metadata'] as $k) {
            if (array_key_exists($k, $arr) && is_string($arr[$k])) {
                $decoded = json_decode($arr[$k], true);
                $arr[$k] = (json_last_error() === JSON_ERROR_NONE) ? $decoded : null;
            }
        }

        // urls
        $arr['primary_logo_full_url']    = $this->toUrl($arr['primary_logo_url'] ?? null);
        $arr['secondary_logo_full_url']  = $this->toUrl($arr['secondary_logo_url'] ?? null);
        $arr['admission_badge_full_url'] = $this->toUrl($arr['admission_badge_url'] ?? null);
        $arr['admission_link_full_url']  = $this->toUrl($arr['admission_link_url'] ?? null);

        // ✅ Partner recruiters (IDs)
        $partnerIds = $this->normalizeRecruiterIds($arr['partner_logos_json'] ?? []);
        $arr['partner_logos_json']     = $partnerIds; // keep stored format as ids
        $arr['partner_recruiter_ids']  = $partnerIds;
        $arr['partner_recruiters']     = $this->fetchRecruitersForIds($partnerIds);

        // Affiliation stays legacy file-list (works with {url,...} too)
        $arr['affiliation_logos'] = $this->normalizeLogosList($arr['affiliation_logos_json'] ?? []);

        return $arr;
    }

    protected function ensureUniqueSlug(string $slug, ?string $ignoreUuid = null): string
    {
        $base = $slug;
        $i = 2;

        while (
            DB::table('header_components')
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

    protected function resolveHeaderComponent($identifier, bool $includeDeleted = false)
    {
        $q = DB::table('header_components');
        if (! $includeDeleted) $q->whereNull('deleted_at');

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
        $q = DB::table('header_components as h');

        if (! $includeDeleted) {
            $q->whereNull('h.deleted_at');
        }

        if ($request->filled('q')) {
            $term = '%' . trim((string) $request->query('q')) . '%';
            $q->where(function ($sub) use ($term) {
                $sub->where('h.slug', 'like', $term)
                    ->orWhere('h.header_text', 'like', $term)
                    ->orWhere('h.primary_logo_url', 'like', $term)
                    ->orWhere('h.secondary_logo_url', 'like', $term);
            });
        }

        $sort = (string) $request->query('sort', 'created_at');
        $dir  = strtolower((string) $request->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        $allowed = ['created_at', 'updated_at', 'header_text', 'slug', 'id'];
        if (!in_array($sort, $allowed, true)) $sort = 'created_at';

        $q->orderBy('h.' . $sort, $dir);

        return $q;
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

        if ($onlyDeleted) {
            $query->whereNotNull('h.deleted_at');
        }

        $paginator = $query->paginate($perPage);
        $items = array_map(fn ($r) => $this->normalizeRow($r), $paginator->items());

        return response()->json([
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

        $row = $this->resolveHeaderComponent($identifier, $includeDeleted);
        if (! $row) return response()->json(['message' => 'Header component not found'], 404);

        return response()->json([
            'success' => true,
            'item'    => $this->normalizeRow($row),
        ]);
    }

    public function store(Request $request)
    {
        $actor = $this->actor($request);

        $validated = $request->validate([
            'slug'               => ['nullable', 'string', 'max:160'],

            'primary_logo_url'   => ['nullable', 'string', 'max:255'],
            'secondary_logo_url' => ['nullable', 'string', 'max:255'],
            'admission_badge_url'=> ['nullable', 'string', 'max:255'],
            'admission_link_url' => ['nullable', 'string', 'max:255'],

            'header_text'        => ['required', 'string', 'max:255'],

            // optional uploads (if provided they overwrite *_url)
            'primary_logo'       => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif,svg', 'max:5120'],
            'secondary_logo'     => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif,svg', 'max:5120'],
            'admission_badge'    => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif,svg', 'max:5120'],

            // ✅ affiliation uploads (NEW)
            'affiliation_logos'            => ['nullable', 'array'],
            'affiliation_logos.*'          => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif,svg', 'max:5120'],
            'affiliation_logos_captions'   => ['nullable', 'array'],
            'affiliation_logos_captions.*' => ['nullable', 'string', 'max:255'],

            // accept any, parsed below:
            'rotating_text_json'     => ['required'],
            'partner_logos_json'     => ['required'], // ✅ recruiters.id[]
            'affiliation_logos_json' => ['required'], // legacy file list (existing kept items)
            'metadata'               => ['nullable'],
        ]);

        $slug = $this->normSlug($validated['slug'] ?? '');
        if ($slug === '') $slug = Str::slug($validated['header_text'], '-');
        if ($slug === '') $slug = 'header-component';
        $slug = $this->ensureUniqueSlug($slug);

        $uuid = (string) Str::uuid();
        $now  = now();

        $dirRel = 'depy_uploads/header_components/' . $slug;

        // parse JSON columns
        $rotating = $this->decodeJsonish($request->input('rotating_text_json'), []);
        $meta     = $this->decodeJsonish($request->input('metadata'), null);

        // ✅ partner recruiters (IDs)
        $partnerIds = $this->normalizeRecruiterIds($request->input('partner_logos_json'));

        if (!is_array($rotating)) {
            return response()->json(['success' => false, 'message' => 'rotating_text_json must be array or valid JSON'], 422);
        }

        // If someone sends legacy partner structure, reject clearly
        $rawPartner = $this->decodeJsonish($request->input('partner_logos_json'), []);
        if (!empty($rawPartner) && empty($partnerIds)) {
            return response()->json([
                'success' => false,
                'message' => 'partner_logos_json must be an array of recruiter IDs (e.g. [1,2,3])',
            ], 422);
        }

        $this->assertRecruitersExist($partnerIds);

        // handle uploads (overwrite URL/path columns if uploaded)
        $primaryLogoPath   = trim((string) ($validated['primary_logo_url'] ?? ''));
        $secondaryLogoPath = trim((string) ($validated['secondary_logo_url'] ?? ''));
        $badgePath         = trim((string) ($validated['admission_badge_url'] ?? ''));

        if ($request->hasFile('primary_logo')) {
            $f = $request->file('primary_logo');
            if (!$f || !$f->isValid()) return response()->json(['success' => false, 'message' => 'Primary logo upload failed'], 422);
            $up = $this->uploadFileToPublic($f, $dirRel, $slug . '-primary');
            $primaryLogoPath = $up['path'];
        }

        if ($request->hasFile('secondary_logo')) {
            $f = $request->file('secondary_logo');
            if (!$f || !$f->isValid()) return response()->json(['success' => false, 'message' => 'Secondary logo upload failed'], 422);
            $up = $this->uploadFileToPublic($f, $dirRel, $slug . '-secondary');
            $secondaryLogoPath = $up['path'];
        }

        if ($request->hasFile('admission_badge')) {
            $f = $request->file('admission_badge');
            if (!$f || !$f->isValid()) return response()->json(['success' => false, 'message' => 'Admission badge upload failed'], 422);
            $up = $this->uploadFileToPublic($f, $dirRel, $slug . '-badge');
            $badgePath = $up['path'];
        }

        // Schema says NOT NULL for these URL/path columns -> enforce not empty
        if ($primaryLogoPath === '')   return response()->json(['success' => false, 'message' => 'primary_logo_url (or primary_logo upload) is required'], 422);
        if ($secondaryLogoPath === '') return response()->json(['success' => false, 'message' => 'secondary_logo_url (or secondary_logo upload) is required'], 422);
        if ($badgePath === '')         return response()->json(['success' => false, 'message' => 'admission_badge_url (or admission_badge upload) is required'], 422);

        // ✅ Affiliation: merge keepExisting + new uploaded files into final JSON
        $affilKeepRaw = $request->input('affiliation_logos_json');
        $affilKeep = $this->decodeJsonish($affilKeepRaw, []);
        if (!is_array($affilKeep)) {
            return response()->json(['success' => false, 'message' => 'affiliation_logos_json must be array or valid JSON'], 422);
        }
        $affilKeep = $this->ensureArray($affilKeep);

        $affilFinal = $this->buildAffiliationLogos($request, $affilKeep, $dirRel, $slug);

        $id = DB::table('header_components')->insertGetId([
            'uuid'                  => $uuid,
            'slug'                  => $slug,

            'primary_logo_url'      => $primaryLogoPath,
            'secondary_logo_url'    => $secondaryLogoPath,
            'header_text'           => $validated['header_text'],

            'rotating_text_json'    => json_encode($rotating),

            'admission_badge_url'   => $badgePath,
            'admission_link_url'    => $validated['admission_link_url'] ?? null,

            // ✅ recruiters.id[]
            'partner_logos_json'    => json_encode($partnerIds),

            // ✅ affiliation stored in DB (including newly uploaded files)
            'affiliation_logos_json'=> json_encode($affilFinal),

            'created_by'            => $actor['id'] ?: null,

            'created_at'            => $now,
            'updated_at'            => $now,
            'created_at_ip'         => $request->ip(),
            'updated_at_ip'         => $request->ip(),
            'metadata'              => $meta !== null ? json_encode($meta) : null,
        ]);

        $row = DB::table('header_components')->where('id', (int) $id)->first();

        return response()->json([
            'success' => true,
            'data'    => $row ? $this->normalizeRow($row) : null,
        ]);
    }

    public function update(Request $request, $identifier)
    {
        $row = $this->resolveHeaderComponent($identifier, true);
        if (! $row) return response()->json(['message' => 'Header component not found'], 404);

        $validated = $request->validate([
            'slug'               => ['nullable', 'string', 'max:160'],

            'primary_logo_url'   => ['nullable', 'string', 'max:255'],
            'secondary_logo_url' => ['nullable', 'string', 'max:255'],
            'admission_badge_url'=> ['nullable', 'string', 'max:255'],
            'admission_link_url' => ['nullable', 'string', 'max:255'],

            'header_text'        => ['nullable', 'string', 'max:255'],

            // optional uploads
            'primary_logo'       => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif,svg', 'max:5120'],
            'secondary_logo'     => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif,svg', 'max:5120'],
            'admission_badge'    => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif,svg', 'max:5120'],

            // ✅ affiliation uploads (NEW)
            'affiliation_logos'            => ['nullable', 'array'],
            'affiliation_logos.*'          => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif,svg', 'max:5120'],
            'affiliation_logos_captions'   => ['nullable', 'array'],
            'affiliation_logos_captions.*' => ['nullable', 'string', 'max:255'],

            // remove flags
            'primary_logo_remove'   => ['nullable', 'in:0,1'],
            'secondary_logo_remove' => ['nullable', 'in:0,1'],
            'admission_badge_remove'=> ['nullable', 'in:0,1'],

            // JSON columns (array or JSON string)
            'rotating_text_json'     => ['nullable'],
            'partner_logos_json'     => ['nullable'], // ✅ recruiters.id[]
            'affiliation_logos_json' => ['nullable'], // keepExisting list
            'metadata'               => ['nullable'],
        ]);

        $update = [
            'updated_at'    => now(),
            'updated_at_ip' => $request->ip(),
        ];

        // slug change (unique)
        if (array_key_exists('slug', $validated) && trim((string) $validated['slug']) !== '') {
            $slug = $this->normSlug($validated['slug']);
            if ($slug === '') $slug = (string) ($row->slug ?? 'header-component');
            $slug = $this->ensureUniqueSlug($slug, (string) $row->uuid);
            $update['slug'] = $slug;
        }

        // normal fields
        foreach (['header_text','admission_link_url','primary_logo_url','secondary_logo_url','admission_badge_url'] as $k) {
            if (array_key_exists($k, $validated)) {
                $update[$k] = $validated[$k] !== null ? $validated[$k] : null;
            }
        }

        // JSON columns replace if provided
        if ($request->has('rotating_text_json')) {
            $rotating = $this->decodeJsonish($request->input('rotating_text_json'), []);
            if (!is_array($rotating)) return response()->json(['success' => false, 'message' => 'rotating_text_json must be array or valid JSON'], 422);
            $update['rotating_text_json'] = json_encode($rotating);
        }

        // ✅ Partner recruiters IDs
        if ($request->has('partner_logos_json')) {
            $rawPartner = $this->decodeJsonish($request->input('partner_logos_json'), []);
            $partnerIds = $this->normalizeRecruiterIds($rawPartner);

            if (!empty($rawPartner) && empty($partnerIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'partner_logos_json must be an array of recruiter IDs (e.g. [1,2,3])',
                ], 422);
            }

            $this->assertRecruitersExist($partnerIds);
            $update['partner_logos_json'] = json_encode($partnerIds);
        }

        if ($request->has('metadata')) {
            $meta = $this->decodeJsonish($request->input('metadata'), null);
            $update['metadata'] = $meta !== null ? json_encode($meta) : null;
        }

        $useSlugDir = (string) ($update['slug'] ?? $row->slug ?? 'header-component');
        $dirRel = 'depy_uploads/header_components/' . $useSlugDir;

        /* ✅ Affiliation FIX:
         * - merge keepExisting (affiliation_logos_json) + new uploads (affiliation_logos[])
         * - store final list into affiliation_logos_json
         * - delete removed old files (optional cleanup)
         */
        if ($request->has('affiliation_logos_json') || $request->hasFile('affiliation_logos')) {
            // old list (for cleanup)
            $oldDecoded = [];
            if (!empty($row->affiliation_logos_json)) {
                $oldDecoded = json_decode((string) $row->affiliation_logos_json, true);
                if (json_last_error() !== JSON_ERROR_NONE) $oldDecoded = [];
            }
            if (!is_array($oldDecoded)) $oldDecoded = [];

            $oldPaths = $this->pathsFromAffiliationList($oldDecoded);

            // keepExisting from request (or keep old if not provided)
            $keepExisting = $request->has('affiliation_logos_json')
                ? $this->decodeJsonish($request->input('affiliation_logos_json'), [])
                : $oldDecoded;

            if (!is_array($keepExisting)) {
                return response()->json(['success' => false, 'message' => 'affiliation_logos_json must be array or valid JSON'], 422);
            }
            $keepExisting = $this->ensureArray($keepExisting);

            $affilFinal = $this->buildAffiliationLogos($request, $keepExisting, $dirRel, $useSlugDir);

            // cleanup removed files
            $newPaths = $this->pathsFromAffiliationList($affilFinal);
            $removed = array_values(array_diff($oldPaths, $newPaths));
            foreach ($removed as $p) {
                $this->deletePublicPath($p);
            }

            $update['affiliation_logos_json'] = json_encode($affilFinal);
        }

        // remove files
        if (filter_var($request->input('primary_logo_remove', false), FILTER_VALIDATE_BOOLEAN)) {
            $this->deletePublicPath($row->primary_logo_url ?? null);
            $update['primary_logo_url'] = null;
        }
        if (filter_var($request->input('secondary_logo_remove', false), FILTER_VALIDATE_BOOLEAN)) {
            $this->deletePublicPath($row->secondary_logo_url ?? null);
            $update['secondary_logo_url'] = null;
        }
        if (filter_var($request->input('admission_badge_remove', false), FILTER_VALIDATE_BOOLEAN)) {
            $this->deletePublicPath($row->admission_badge_url ?? null);
            $update['admission_badge_url'] = null;
        }

        // upload overwrite
        if ($request->hasFile('primary_logo')) {
            $f = $request->file('primary_logo');
            if (!$f || !$f->isValid()) return response()->json(['success' => false, 'message' => 'Primary logo upload failed'], 422);
            $this->deletePublicPath($row->primary_logo_url ?? null);
            $up = $this->uploadFileToPublic($f, $dirRel, $useSlugDir . '-primary');
            $update['primary_logo_url'] = $up['path'];
        }

        if ($request->hasFile('secondary_logo')) {
            $f = $request->file('secondary_logo');
            if (!$f || !$f->isValid()) return response()->json(['success' => false, 'message' => 'Secondary logo upload failed'], 422);
            $this->deletePublicPath($row->secondary_logo_url ?? null);
            $up = $this->uploadFileToPublic($f, $dirRel, $useSlugDir . '-secondary');
            $update['secondary_logo_url'] = $up['path'];
        }

        if ($request->hasFile('admission_badge')) {
            $f = $request->file('admission_badge');
            if (!$f || !$f->isValid()) return response()->json(['success' => false, 'message' => 'Admission badge upload failed'], 422);
            $this->deletePublicPath($row->admission_badge_url ?? null);
            $up = $this->uploadFileToPublic($f, $dirRel, $useSlugDir . '-badge');
            $update['admission_badge_url'] = $up['path'];
        }

        // enforce NOT NULL columns after update (schema requirement)
        $finalPrimary   = array_key_exists('primary_logo_url', $update) ? $update['primary_logo_url'] : ($row->primary_logo_url ?? null);
        $finalSecondary = array_key_exists('secondary_logo_url', $update) ? $update['secondary_logo_url'] : ($row->secondary_logo_url ?? null);
        $finalBadge     = array_key_exists('admission_badge_url', $update) ? $update['admission_badge_url'] : ($row->admission_badge_url ?? null);

        if (empty($finalPrimary))   return response()->json(['success' => false, 'message' => 'primary_logo_url cannot be empty (schema NOT NULL)'], 422);
        if (empty($finalSecondary)) return response()->json(['success' => false, 'message' => 'secondary_logo_url cannot be empty (schema NOT NULL)'], 422);
        if (empty($finalBadge))     return response()->json(['success' => false, 'message' => 'admission_badge_url cannot be empty (schema NOT NULL)'], 422);

        DB::table('header_components')->where('id', (int) $row->id)->update($update);

        $fresh = DB::table('header_components')->where('id', (int) $row->id)->first();

        return response()->json([
            'success' => true,
            'data'    => $fresh ? $this->normalizeRow($fresh) : null,
        ]);
    }

    public function destroy(Request $request, $identifier)
    {
        $row = $this->resolveHeaderComponent($identifier, false);
        if (! $row) return response()->json(['message' => 'Not found or already deleted'], 404);

        DB::table('header_components')->where('id', (int) $row->id)->update([
            'deleted_at'    => now(),
            'updated_at'    => now(),
            'updated_at_ip' => $request->ip(),
        ]);

        return response()->json(['success' => true]);
    }

    public function restore(Request $request, $identifier)
    {
        $row = $this->resolveHeaderComponent($identifier, true);
        if (! $row || $row->deleted_at === null) {
            return response()->json(['message' => 'Not found in bin'], 404);
        }

        DB::table('header_components')->where('id', (int) $row->id)->update([
            'deleted_at'    => null,
            'updated_at'    => now(),
            'updated_at_ip' => $request->ip(),
        ]);

        $fresh = DB::table('header_components')->where('id', (int) $row->id)->first();

        return response()->json([
            'success' => true,
            'data'    => $fresh ? $this->normalizeRow($fresh) : null,
        ]);
    }

    public function forceDelete(Request $request, $identifier)
    {
        $row = $this->resolveHeaderComponent($identifier, true);
        if (! $row) return response()->json(['message' => 'Header component not found'], 404);

        // delete direct files
        $this->deletePublicPath($row->primary_logo_url ?? null);
        $this->deletePublicPath($row->secondary_logo_url ?? null);
        $this->deletePublicPath($row->admission_badge_url ?? null);

        // ✅ Only affiliation logos may contain file paths now.
        $decoded = [];
        if (!empty($row->affiliation_logos_json)) {
            $decoded = json_decode((string) $row->affiliation_logos_json, true);
            if (json_last_error() !== JSON_ERROR_NONE) $decoded = [];
        }
        if (is_array($decoded)) {
            foreach ($decoded as $item) {
                if (is_string($item)) {
                    $this->deletePublicPath($item);
                } elseif (is_array($item)) {
                    $p = (string) ($item['path'] ?? $item['url'] ?? '');
                    $this->deletePublicPath($p);
                }
            }
        }

        DB::table('header_components')->where('id', (int) $row->id)->delete();

        return response()->json(['success' => true]);
    }

    public function recruiterOptions(Request $request)
    {
        $onlyActive = filter_var($request->query('only_active', true), FILTER_VALIDATE_BOOLEAN);

        $q = DB::table('recruiters')
            ->select('id','uuid','slug','title','logo_url','status')
            ->orderBy('title','asc');

        if ($onlyActive) {
            $q->where(function ($w) {
                $w->whereNull('status')->orWhere('status', '!=', 'inactive');
            });
        }

        $rows = $q->get();

        $data = [];
        foreach ($rows as $r) {
            $data[] = [
                'id'            => (int) $r->id,
                'uuid'          => (string) ($r->uuid ?? ''),
                'slug'          => (string) ($r->slug ?? ''),
                'title'         => (string) ($r->title ?? ''),
                'logo_url'      => $r->logo_url !== null ? (string) $r->logo_url : null,
                'logo_full_url' => $this->toUrl($r->logo_url ?? null),
                'status'        => (string) ($r->status ?? ''),
            ];
        }

        return response()->json(['success' => true, 'data' => $data]);
    }
}
