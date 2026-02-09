<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StickyButtonController extends Controller
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

    /**
     * Safe activity logger (never breaks core flow).
     */
    private function logActivity(
        Request $request,
        string $activity,
        string $module,
        string $tableName,
        ?int $recordId = null,
        ?array $changedFields = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $note = null
    ): void {
        try {
            $actor = $this->actor($request);

            DB::table('user_data_activity_log')->insert([
                'performed_by'       => (int) ($actor['id'] ?? 0),
                'performed_by_role'  => (string) ($actor['role'] ?? ''),
                'ip'                 => $request->ip(),
                'user_agent'         => substr((string) $request->userAgent(), 0, 512),

                'activity'           => $activity,
                'module'             => $module,

                'table_name'         => $tableName,
                'record_id'          => $recordId,

                'changed_fields'     => $changedFields !== null ? json_encode(array_values($changedFields)) : null,
                'old_values'         => $oldValues !== null ? json_encode($oldValues) : null,
                'new_values'         => $newValues !== null ? json_encode($newValues) : null,

                'log_note'           => $note,

                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
        } catch (\Throwable $e) {
            // Intentionally swallow logging failures to avoid breaking any functionality.
        }
    }

    /**
     * Snapshot sticky_buttons row (decoded json for readability).
     */
    private function snapshotSticky($row): ?array
    {
        if (!$row) return null;

        $arr = (array) $row;

        $keep = [
            'id', 'uuid', 'status',
            'buttons_json', 'metadata',
            'deleted_at',
            'created_by', 'created_at', 'updated_at',
            'created_at_ip', 'updated_at_ip',
        ];

        $out = [];
        foreach ($keep as $k) {
            if (array_key_exists($k, $arr)) $out[$k] = $arr[$k];
        }

        $out['buttons_json'] = $this->decodeIfJson($out['buttons_json'] ?? null);
        $out['metadata']     = $this->decodeIfJson($out['metadata'] ?? null);

        return $out;
    }

    /**
     * Diff only meaningful fields (so logs don't get noisy).
     */
    private function diffStickySnapshots(?array $old, ?array $new): array
    {
        $fields = ['status', 'buttons_json', 'metadata', 'deleted_at'];
        $changed = [];

        foreach ($fields as $f) {
            $ov = $old[$f] ?? null;
            $nv = $new[$f] ?? null;

            // Normalize arrays for stable compare
            if (is_array($ov) || is_array($nv)) {
                if (json_encode($ov) !== json_encode($nv)) $changed[] = $f;
            } else {
                if ($ov !== $nv) $changed[] = $f;
            }
        }

        return $changed;
    }

    protected function toUrl(?string $path): ?string
    {
        $path = trim((string) $path);
        if ($path === '') return null;

        if (preg_match('~^https?://~i', $path)) return $path;

        if (str_starts_with($path, '//')) return 'https:' . $path;

        return url('/' . ltrim($path, '/'));
    }

    protected function actionUrlFor(string $key, string $value): ?string
    {
        $k = strtolower(trim($key));
        $v = trim($value);
        if ($v === '') return null;

        if (in_array($k, ['email', 'mail'], true)) return 'mailto:' . $v;

        if (in_array($k, ['phone', 'mobile', 'tel', 'telephone'], true)) {
            $clean = preg_replace('~\s+~', '', $v);
            return 'tel:' . $clean;
        }

        if ($k === 'whatsapp') {
            $digits = preg_replace('~\D+~', '', $v);
            $digits = ltrim($digits, '0');
            return $digits === '' ? null : ('https://wa.me/' . $digits);
        }

        if (in_array($k, ['address', 'location', 'map'], true)) {
            return 'https://www.google.com/maps/search/?api=1&query=' . urlencode($v);
        }

        if (
            in_array($k, ['website', 'site', 'url', 'linkedin', 'facebook', 'instagram', 'twitter', 'x', 'youtube'], true)
            || preg_match('~^https?://~i', $v)
            || str_starts_with($v, '/')
            || str_starts_with($v, '//')
        ) {
            return $this->toUrl($v);
        }

        return null;
    }

    protected function decodeIfJson($value)
    {
        if ($value === null) return null;
        if (is_array($value)) return $value;

        if (is_string($value)) {
            $trim = trim($value);
            if ($trim === '') return null;

            $decoded = json_decode($trim, true);
            return (json_last_error() === JSON_ERROR_NONE) ? $decoded : null;
        }

        return null;
    }

    protected function normalizeRow($row): array
    {
        $arr = (array) $row;

        // buttons_json decode
        $buttons = $this->decodeIfJson($arr['buttons_json'] ?? null);
        if (!is_array($buttons)) $buttons = null;

        // add action_url per button if possible
        if (is_array($buttons)) {
            foreach ($buttons as $i => $b) {
                if (!is_array($b)) continue;
                $key = (string)($b['key'] ?? '');
                $val = (string)($b['value'] ?? '');
                if (!isset($b['action_url'])) {
                    $b['action_url'] = $this->actionUrlFor($key, $val);
                }
                $buttons[$i] = $b;
            }
        }

        $arr['buttons_json'] = $buttons;

        // metadata decode
        $meta = $this->decodeIfJson($arr['metadata'] ?? null);
        $arr['metadata'] = is_array($meta) ? $meta : null;

        return $arr;
    }

    protected function baseQuery(Request $request, bool $includeDeleted = false)
    {
        $q = DB::table('sticky_buttons as s')->select('s.*');

        if (!$includeDeleted) {
            $q->whereNull('s.deleted_at');
        }

        // ?status=active|inactive
        if ($request->filled('status')) {
            $q->where('s.status', (string) $request->query('status'));
        }

        // ?q= (search inside json/text fields)
        if ($request->filled('q')) {
            $term = '%' . trim((string) $request->query('q')) . '%';
            $q->where(function ($sub) use ($term) {
                $sub->where('s.uuid', 'like', $term)
                    ->orWhere('s.buttons_json', 'like', $term)
                    ->orWhere('s.metadata', 'like', $term);
            });
        }

        // sort
        $sort = (string) $request->query('sort', 'id');
        $dir  = strtolower((string) $request->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        $allowed = ['id', 'created_at', 'updated_at', 'status'];
        if (!in_array($sort, $allowed, true)) $sort = 'id';

        $q->orderBy('s.' . $sort, $dir);

        return $q;
    }

    protected function resolveSticky($identifier, bool $includeDeleted = false)
    {
        $q = DB::table('sticky_buttons as s');
        if (!$includeDeleted) $q->whereNull('s.deleted_at');

        if (ctype_digit((string) $identifier)) {
            $q->where('s.id', (int) $identifier);
        } elseif (Str::isUuid((string) $identifier)) {
            $q->where('s.uuid', (string) $identifier);
        } else {
            return null;
        }

        return $q->first();
    }

    /**
     * Build snapshot array from contact_info identifiers (id or uuid),
     * keeping the SAME order as provided.
     * Only uses active + not deleted contact_info.
     */
    protected function buildButtonsFromContactInfoIds(array $ids): array
    {
        $numeric = [];
        $uuids   = [];

        foreach ($ids as $v) {
            $v = is_string($v) || is_int($v) ? (string) $v : '';
            if ($v === '') continue;

            if (ctype_digit($v)) $numeric[] = (int) $v;
            elseif (Str::isUuid($v)) $uuids[] = $v;
        }

        if (!$numeric && !$uuids) return [];

        $rows = DB::table('contact_info as c')
            ->select('c.id','c.uuid','c.type','c.key','c.name','c.icon_class','c.value','c.sort_order','c.status')
            ->whereNull('c.deleted_at')
            ->where('c.status', 'active')
            ->where(function ($w) use ($numeric, $uuids) {
                if ($numeric) $w->whereIn('c.id', $numeric);
                if ($uuids)   $w->orWhereIn('c.uuid', $uuids);
            })
            ->get();

        $byId = [];
        $byUuid = [];
        foreach ($rows as $r) {
            $byId[(string) $r->id] = $r;
            $byUuid[(string) $r->uuid] = $r;
        }

        $out = [];
        foreach ($ids as $v) {
            $k = is_string($v) || is_int($v) ? (string) $v : '';
            if ($k === '') continue;

            $row = null;
            if (ctype_digit($k) && isset($byId[$k])) $row = $byId[$k];
            elseif (isset($byUuid[$k])) $row = $byUuid[$k];

            if (!$row) continue;

            $out[] = [
                'contact_info_id' => (int) $row->id,
                'uuid'            => (string) $row->uuid,
                'type'            => (string) $row->type,
                'key'             => (string) $row->key,
                'name'            => (string) $row->name,
                'icon_class'      => $row->icon_class ? (string) $row->icon_class : null,
                'value'           => (string) $row->value,
                'sort_order'      => (int) ($row->sort_order ?? 0),
                'action_url'      => $this->actionUrlFor((string)$row->key, (string)$row->value),
            ];
        }

        return $out;
    }

    /* ============================================
     | Admin
     |============================================ */

    public function index(Request $request)
    {
        $perPage = max(1, min(200, (int) $request->query('per_page', 20)));

        $includeDeleted = filter_var($request->query('with_trashed', false), FILTER_VALIDATE_BOOLEAN);
        $onlyDeleted    = filter_var($request->query('only_trashed', false), FILTER_VALIDATE_BOOLEAN);

        $query = $this->baseQuery($request, $includeDeleted || $onlyDeleted);

        if ($onlyDeleted) {
            $query->whereNotNull('s.deleted_at');
        }

        $paginator = $query->paginate($perPage);
        $items = array_map(fn($r) => $this->normalizeRow($r), $paginator->items());

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

        $row = $this->resolveSticky($identifier, $includeDeleted);
        if (!$row) return response()->json(['message' => 'Sticky buttons not found'], 404);

        return response()->json([
            'success' => true,
            'item'    => $this->normalizeRow($row),
        ]);
    }

    public function current(Request $request)
    {
        $row = DB::table('sticky_buttons as s')
            ->whereNull('s.deleted_at')
            ->where('s.status', 'active')
            ->orderByDesc('s.id')
            ->first();

        return response()->json([
            'success' => true,
            'item'    => $row ? $this->normalizeRow($row) : null,
        ]);
    }

    public function store(Request $request)
    {
        $actor = $this->actor($request);

        $validated = $request->validate([
            'buttons_json'       => ['nullable'],              // array or json string
            'contact_info_ids'   => ['nullable', 'array'],     // optional helper
            'contact_info_ids.*' => ['nullable'],              // id or uuid
            'status'             => ['nullable', 'in:active,inactive'],
            'metadata'           => ['nullable'],
        ]);

        // metadata normalize
        $metadata = $request->input('metadata', null);
        if (is_string($metadata)) {
            $decoded = json_decode($metadata, true);
            if (json_last_error() === JSON_ERROR_NONE) $metadata = $decoded;
        }

        // buttons: either from contact_info_ids OR direct buttons_json
        $buttons = null;

        if (!empty($validated['contact_info_ids'])) {
            $buttons = $this->buildButtonsFromContactInfoIds((array)$validated['contact_info_ids']);
        } else {
            $buttons = $request->input('buttons_json', null);
            if (is_string($buttons)) {
                $decoded = json_decode($buttons, true);
                if (json_last_error() === JSON_ERROR_NONE) $buttons = $decoded;
            }
        }

        if (!is_array($buttons)) {
            return response()->json([
                'message' => 'buttons_json (array/json) OR contact_info_ids (array) is required.'
            ], 422);
        }

        $uuid = (string) Str::uuid();
        $now  = now();

        $id = DB::table('sticky_buttons')->insertGetId([
            'uuid'          => $uuid,
            'buttons_json'  => json_encode($buttons),
            'status'        => (string) ($validated['status'] ?? 'active'),
            'created_by'    => $actor['id'] ?: null,
            'created_at'    => $now,
            'updated_at'    => $now,
            'created_at_ip' => $request->ip(),
            'updated_at_ip' => $request->ip(),
            'metadata'      => $metadata !== null ? json_encode($metadata) : null,
        ]);

        $row = DB::table('sticky_buttons')->where('id', (int) $id)->first();

        // LOG: create
        $newSnap = $this->snapshotSticky($row);
        $this->logActivity(
            $request,
            'create',
            'sticky_buttons',
            'sticky_buttons',
            (int) $id,
            ['uuid', 'buttons_json', 'status', 'metadata'],
            null,
            $newSnap,
            'StickyButtonController@store'
        );

        return response()->json([
            'success' => true,
            'data'    => $row ? $this->normalizeRow($row) : null,
        ]);
    }

    /**
     * Convenience: upsert the latest record (so UI doesn't need an identifier)
     * POST /sticky-buttons/upsert-current
     */
    public function upsertCurrent(Request $request)
    {
        $actor = $this->actor($request);

        $validated = $request->validate([
            'buttons_json'       => ['nullable'],
            'contact_info_ids'   => ['nullable', 'array'],
            'contact_info_ids.*' => ['nullable'],
            'status'             => ['nullable', 'in:active,inactive'],
            'metadata'           => ['nullable'],
        ]);

        $metadata = $request->input('metadata', null);
        if (is_string($metadata)) {
            $decoded = json_decode($metadata, true);
            if (json_last_error() === JSON_ERROR_NONE) $metadata = $decoded;
        }

        $buttons = null;

        if (!empty($validated['contact_info_ids'])) {
            $buttons = $this->buildButtonsFromContactInfoIds((array)$validated['contact_info_ids']);
        } else {
            $buttons = $request->input('buttons_json', null);
            if (is_string($buttons)) {
                $decoded = json_decode($buttons, true);
                if (json_last_error() === JSON_ERROR_NONE) $buttons = $decoded;
            }
        }

        if (!is_array($buttons)) {
            return response()->json([
                'message' => 'buttons_json (array/json) OR contact_info_ids (array) is required.'
            ], 422);
        }

        $now = now();

        $latest = DB::table('sticky_buttons')
            ->whereNull('deleted_at')
            ->orderByDesc('id')
            ->first();

        if ($latest) {
            $oldSnap = $this->snapshotSticky($latest);

            DB::table('sticky_buttons')->where('id', (int)$latest->id)->update([
                'buttons_json'  => json_encode($buttons),
                'status'        => (string) ($validated['status'] ?? ($latest->status ?? 'active')),
                'updated_at'    => $now,
                'updated_at_ip' => $request->ip(),
                'metadata'      => $metadata !== null ? json_encode($metadata) : ($latest->metadata ?? null),
            ]);

            $fresh = DB::table('sticky_buttons')->where('id', (int)$latest->id)->first();
            $newSnap = $this->snapshotSticky($fresh);
            $changed = $this->diffStickySnapshots($oldSnap, $newSnap);

            // LOG: update (upsert)
            $this->logActivity(
                $request,
                'update',
                'sticky_buttons',
                'sticky_buttons',
                (int) $latest->id,
                $changed,
                $oldSnap,
                $newSnap,
                'StickyButtonController@upsertCurrent (update)'
            );

            return response()->json([
                'success' => true,
                'data'    => $fresh ? $this->normalizeRow($fresh) : null,
            ]);
        }

        // create if none exists
        $uuid = (string) Str::uuid();

        $id = DB::table('sticky_buttons')->insertGetId([
            'uuid'          => $uuid,
            'buttons_json'  => json_encode($buttons),
            'status'        => (string) ($validated['status'] ?? 'active'),
            'created_by'    => $actor['id'] ?: null,
            'created_at'    => $now,
            'updated_at'    => $now,
            'created_at_ip' => $request->ip(),
            'updated_at_ip' => $request->ip(),
            'metadata'      => $metadata !== null ? json_encode($metadata) : null,
        ]);

        $row = DB::table('sticky_buttons')->where('id', (int)$id)->first();

        // LOG: create (upsert)
        $newSnap = $this->snapshotSticky($row);
        $this->logActivity(
            $request,
            'create',
            'sticky_buttons',
            'sticky_buttons',
            (int) $id,
            ['uuid', 'buttons_json', 'status', 'metadata'],
            null,
            $newSnap,
            'StickyButtonController@upsertCurrent (create)'
        );

        return response()->json([
            'success' => true,
            'data'    => $row ? $this->normalizeRow($row) : null,
        ]);
    }

    public function update(Request $request, $identifier)
    {
        $row = $this->resolveSticky($identifier, true);
        if (!$row) return response()->json(['message' => 'Sticky buttons not found'], 404);

        $oldSnap = $this->snapshotSticky($row);

        $validated = $request->validate([
            'buttons_json'       => ['nullable'],
            'contact_info_ids'   => ['nullable', 'array'],
            'contact_info_ids.*' => ['nullable'],
            'status'             => ['nullable', 'in:active,inactive'],
            'metadata'           => ['nullable'],
        ]);

        $update = [
            'updated_at'    => now(),
            'updated_at_ip' => $request->ip(),
        ];

        if (array_key_exists('status', $validated)) {
            $update['status'] = (string) $validated['status'];
        }

        // buttons update (either from contact_info_ids or buttons_json)
        if (!empty($validated['contact_info_ids'])) {
            $buttons = $this->buildButtonsFromContactInfoIds((array)$validated['contact_info_ids']);
            $update['buttons_json'] = json_encode($buttons);
        } elseif ($request->has('buttons_json')) {
            $buttons = $request->input('buttons_json', null);
            if (is_string($buttons)) {
                $decoded = json_decode($buttons, true);
                if (json_last_error() === JSON_ERROR_NONE) $buttons = $decoded;
            }
            if (is_array($buttons)) {
                $update['buttons_json'] = json_encode($buttons);
            }
        }

        if (array_key_exists('metadata', $validated)) {
            $metadata = $request->input('metadata', null);
            if (is_string($metadata)) {
                $decoded = json_decode($metadata, true);
                if (json_last_error() === JSON_ERROR_NONE) $metadata = $decoded;
            }
            $update['metadata'] = $metadata !== null ? json_encode($metadata) : null;
        }

        DB::table('sticky_buttons')->where('id', (int) $row->id)->update($update);

        $fresh = DB::table('sticky_buttons')->where('id', (int) $row->id)->first();

        // LOG: update
        $newSnap = $this->snapshotSticky($fresh);
        $changed = $this->diffStickySnapshots($oldSnap, $newSnap);
        $this->logActivity(
            $request,
            'update',
            'sticky_buttons',
            'sticky_buttons',
            (int) $row->id,
            $changed,
            $oldSnap,
            $newSnap,
            'StickyButtonController@update'
        );

        return response()->json([
            'success' => true,
            'data'    => $fresh ? $this->normalizeRow($fresh) : null,
        ]);
    }

    public function toggleStatus(Request $request, $identifier)
    {
        $row = $this->resolveSticky($identifier, true);
        if (!$row) return response()->json(['message' => 'Sticky buttons not found'], 404);

        $oldSnap = $this->snapshotSticky($row);

        $new = (($row->status ?? 'active') === 'active') ? 'inactive' : 'active';

        DB::table('sticky_buttons')->where('id', (int) $row->id)->update([
            'status'        => $new,
            'updated_at'    => now(),
            'updated_at_ip' => $request->ip(),
        ]);

        $fresh = DB::table('sticky_buttons')->where('id', (int) $row->id)->first();

        // LOG: update (toggle)
        $newSnap = $this->snapshotSticky($fresh);
        $changed = $this->diffStickySnapshots($oldSnap, $newSnap);
        $this->logActivity(
            $request,
            'update',
            'sticky_buttons',
            'sticky_buttons',
            (int) $row->id,
            $changed,
            $oldSnap,
            $newSnap,
            'StickyButtonController@toggleStatus'
        );

        return response()->json([
            'success' => true,
            'data'    => $fresh ? $this->normalizeRow($fresh) : null,
        ]);
    }

    public function destroy(Request $request, $identifier)
    {
        $row = $this->resolveSticky($identifier, false);
        if (!$row) return response()->json(['message' => 'Not found or already deleted'], 404);

        $oldSnap = $this->snapshotSticky($row);

        DB::table('sticky_buttons')->where('id', (int) $row->id)->update([
            'deleted_at'    => now(),
            'updated_at'    => now(),
            'updated_at_ip' => $request->ip(),
        ]);

        $fresh = DB::table('sticky_buttons')->where('id', (int) $row->id)->first();

        // LOG: delete (soft)
        $newSnap = $this->snapshotSticky($fresh);
        $changed = $this->diffStickySnapshots($oldSnap, $newSnap);
        $this->logActivity(
            $request,
            'delete',
            'sticky_buttons',
            'sticky_buttons',
            (int) $row->id,
            $changed,
            $oldSnap,
            $newSnap,
            'StickyButtonController@destroy (soft delete)'
        );

        return response()->json(['success' => true]);
    }

    public function restore(Request $request, $identifier)
    {
        $row = $this->resolveSticky($identifier, true);
        if (!$row || $row->deleted_at === null) {
            return response()->json(['message' => 'Not found in bin'], 404);
        }

        $oldSnap = $this->snapshotSticky($row);

        DB::table('sticky_buttons')->where('id', (int) $row->id)->update([
            'deleted_at'    => null,
            'updated_at'    => now(),
            'updated_at_ip' => $request->ip(),
        ]);

        $fresh = DB::table('sticky_buttons')->where('id', (int) $row->id)->first();

        // LOG: restore
        $newSnap = $this->snapshotSticky($fresh);
        $changed = $this->diffStickySnapshots($oldSnap, $newSnap);
        $this->logActivity(
            $request,
            'restore',
            'sticky_buttons',
            'sticky_buttons',
            (int) $row->id,
            $changed,
            $oldSnap,
            $newSnap,
            'StickyButtonController@restore'
        );

        return response()->json([
            'success' => true,
            'data'    => $fresh ? $this->normalizeRow($fresh) : null,
        ]);
    }

    public function forceDelete(Request $request, $identifier)
    {
        $row = $this->resolveSticky($identifier, true);
        if (!$row) return response()->json(['message' => 'Sticky buttons not found'], 404);

        $oldSnap = $this->snapshotSticky($row);

        DB::table('sticky_buttons')->where('id', (int) $row->id)->delete();

        // LOG: delete (force)
        $this->logActivity(
            $request,
            'delete',
            'sticky_buttons',
            'sticky_buttons',
            (int) $row->id,
            ['force_delete'],
            $oldSnap,
            null,
            'StickyButtonController@forceDelete (force delete)'
        );

        return response()->json(['success' => true]);
    }

    /* ============================================
     | Public (No Auth)
     |============================================ */

    public function publicIndex(Request $request)
    {
        $perPage = max(1, min(200, (int) $request->query('per_page', 50)));

        $q = DB::table('sticky_buttons as s')
            ->select('s.*')
            ->whereNull('s.deleted_at')
            ->where('s.status', 'active')
            ->orderByDesc('s.id');

        $paginator = $q->paginate($perPage);
        $items = array_map(fn($r) => $this->normalizeRow($r), $paginator->items());

        return response()->json([
            'success' => true,
            'data'    => $items,
            'pagination' => [
                'page'      => $paginator->currentPage(),
                'per_page'  => $paginator->perPage(),
                'total'     => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    public function publicCurrent(Request $request)
    {
        $row = DB::table('sticky_buttons as s')
            ->whereNull('s.deleted_at')
            ->where('s.status', 'active')
            ->orderByDesc('s.id')
            ->first();

        return response()->json([
            'success' => true,
            'item'    => $row ? $this->normalizeRow($row) : null,
        ]);
    }

    public function publicShow(Request $request, $identifier)
    {
        $row = $this->resolveSticky($identifier, false);
        if (!$row) return response()->json(['message' => 'Sticky buttons not found'], 404);

        if (($row->status ?? '') !== 'active') {
            return response()->json(['message' => 'Sticky buttons not available'], 404);
        }

        return response()->json([
            'success' => true,
            'item'    => $this->normalizeRow($row),
        ]);
    }
}
