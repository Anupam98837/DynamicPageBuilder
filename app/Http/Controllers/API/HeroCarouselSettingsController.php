<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HeroCarouselSettingsController extends Controller
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
     * Never break main flow because logging failed.
     */
    private function logActivity(
        Request $r,
        string $activity,
        string $module,
        string $tableName,
        ?int $recordId = null,
        ?string $note = null,
        $changedFields = null,
        $oldValues = null,
        $newValues = null
    ): void {
        try {
            $actor = $this->actor($r);

            $ua = (string) ($r->userAgent() ?? '');
            if (strlen($ua) > 512) $ua = substr($ua, 0, 512);

            $payload = [
                'performed_by'      => (int) ($actor['id'] ?: 0),
                'performed_by_role' => ($actor['role'] !== '') ? (string) $actor['role'] : null,
                'ip'                => $r->ip(),
                'user_agent'        => ($ua !== '') ? $ua : null,

                'activity'          => $activity,
                'module'            => $module,

                'table_name'        => $tableName,
                'record_id'         => $recordId,

                'changed_fields'    => $changedFields !== null ? json_encode($changedFields, JSON_UNESCAPED_UNICODE) : null,
                'old_values'        => $oldValues !== null ? json_encode($oldValues, JSON_UNESCAPED_UNICODE) : null,
                'new_values'        => $newValues !== null ? json_encode($newValues, JSON_UNESCAPED_UNICODE) : null,

                'log_note'          => $note,

                'created_at'        => now(),
                'updated_at'        => now(),
            ];

            DB::table('user_data_activity_log')->insert($payload);
        } catch (\Throwable $e) {
            // swallow (do not hurt functionality)
        }
    }

    protected function baseQuery(Request $request, bool $includeDeleted = false)
    {
        $q = DB::table('hero_carousel_settings as s')
            ->leftJoin('users as u', 'u.id', '=', 's.created_by')
            ->select([
                's.*',
                'u.name as created_by_name',
                'u.email as created_by_email',
            ]);

        if (! $includeDeleted) {
            $q->whereNull('s.deleted_at');
        }

        // ?q= (search transition/uuid)
        if ($request->filled('q')) {
            $term = '%' . trim((string) $request->query('q')) . '%';
            $q->where(function ($w) use ($term) {
                $w->where('s.uuid', 'like', $term)
                  ->orWhere('s.transition', 'like', $term);
            });
        }

        // sort
        $sort = (string) $request->query('sort', 'updated_at');
        $dir  = strtolower((string) $request->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        $allowed = ['id', 'created_at', 'updated_at', 'autoplay_delay_ms', 'transition_ms', 'transition'];
        if (! in_array($sort, $allowed, true)) $sort = 'updated_at';

        $q->orderBy('s.' . $sort, $dir);

        return $q;
    }

    protected function resolveSetting($identifier, bool $includeDeleted = false)
    {
        $q = DB::table('hero_carousel_settings as s');
        if (! $includeDeleted) $q->whereNull('s.deleted_at');

        if (ctype_digit((string) $identifier)) {
            $q->where('s.id', (int) $identifier);
        } elseif (Str::isUuid((string) $identifier)) {
            $q->where('s.uuid', (string) $identifier);
        } else {
            // not id/uuid -> not found
            return null;
        }

        return $q->first();
    }

    protected function normalizeRow($row): array
    {
        $arr = (array) $row;

        // decode metadata
        $meta = $arr['metadata'] ?? null;
        if (is_string($meta)) {
            $decoded = json_decode($meta, true);
            $arr['metadata'] = (json_last_error() === JSON_ERROR_NONE) ? $decoded : null;
        }

        // cast booleans to int-friendly values (optional)
        foreach (['autoplay','loop','pause_on_hover','show_arrows','show_dots'] as $k) {
            if (array_key_exists($k, $arr) && $arr[$k] !== null) {
                $arr[$k] = (int) ((bool) $arr[$k]);
            }
        }

        return $arr;
    }

    protected function normalizeMetadataInput(Request $request)
    {
        $metadata = $request->input('metadata', null);

        if (is_string($metadata)) {
            $decoded = json_decode($metadata, true);
            if (json_last_error() === JSON_ERROR_NONE) $metadata = $decoded;
        }

        // allow array/object -> store JSON; null -> null
        return $metadata;
    }

    /**
     * Internal create to avoid duplicated logic and to control logging (upsert).
     */
    protected function createRow(Request $request, array $validated, array $actor)
    {
        $uuid = (string) Str::uuid();
        $now  = now();

        $metadata = $this->normalizeMetadataInput($request);

        $id = DB::table('hero_carousel_settings')->insertGetId([
            'uuid'              => $uuid,
            'autoplay'          => array_key_exists('autoplay', $validated) ? (int) $validated['autoplay'] : 1,
            'autoplay_delay_ms' => array_key_exists('autoplay_delay_ms', $validated) ? (int) $validated['autoplay_delay_ms'] : 4000,
            'loop'              => array_key_exists('loop', $validated) ? (int) $validated['loop'] : 1,
            'pause_on_hover'    => array_key_exists('pause_on_hover', $validated) ? (int) $validated['pause_on_hover'] : 1,
            'show_arrows'       => array_key_exists('show_arrows', $validated) ? (int) $validated['show_arrows'] : 1,
            'show_dots'         => array_key_exists('show_dots', $validated) ? (int) $validated['show_dots'] : 1,
            'transition'        => array_key_exists('transition', $validated) && $validated['transition'] !== null
                                    ? (string) $validated['transition'] : 'slide',
            'transition_ms'     => array_key_exists('transition_ms', $validated) ? (int) $validated['transition_ms'] : 450,

            'created_by'        => $actor['id'] ?: null,
            'created_at'        => $now,
            'updated_at'        => $now,
            'created_at_ip'     => $request->ip(),
            'updated_at_ip'     => $request->ip(),
            'metadata'          => $metadata !== null ? json_encode($metadata) : null,
        ]);

        return DB::table('hero_carousel_settings')->where('id', (int) $id)->first();
    }

    /**
     * Internal update to avoid duplicated logic and to control logging (upsert).
     */
    protected function updateRow(Request $request, $row, array $validated)
    {
        $update = [
            'updated_at'    => now(),
            'updated_at_ip' => $request->ip(),
        ];

        foreach (['autoplay','loop','pause_on_hover','show_arrows','show_dots'] as $k) {
            if (array_key_exists($k, $validated)) $update[$k] = $validated[$k] !== null ? (int) $validated[$k] : null;
        }
        if (array_key_exists('autoplay_delay_ms', $validated)) $update['autoplay_delay_ms'] = $validated['autoplay_delay_ms'] !== null ? (int) $validated['autoplay_delay_ms'] : null;
        if (array_key_exists('transition', $validated)) $update['transition'] = $validated['transition'] !== null ? (string) $validated['transition'] : null;
        if (array_key_exists('transition_ms', $validated)) $update['transition_ms'] = $validated['transition_ms'] !== null ? (int) $validated['transition_ms'] : null;

        if (array_key_exists('metadata', $validated)) {
            $metadata = $this->normalizeMetadataInput($request);
            $update['metadata'] = $metadata !== null ? json_encode($metadata) : null;
        }

        DB::table('hero_carousel_settings')->where('id', (int) $row->id)->update($update);

        return DB::table('hero_carousel_settings')->where('id', (int) $row->id)->first();
    }

    /* ============================================
     | CRUD
     |============================================ */

    // List (Admin)
    public function index(Request $request)
    {
        $perPage = max(1, min(200, (int) $request->query('per_page', 20)));

        $includeDeleted = filter_var($request->query('with_trashed', false), FILTER_VALIDATE_BOOLEAN);
        $onlyDeleted    = filter_var($request->query('only_trashed', false), FILTER_VALIDATE_BOOLEAN);

        $q = $this->baseQuery($request, $includeDeleted || $onlyDeleted);

        if ($onlyDeleted) $q->whereNotNull('s.deleted_at');

        $p = $q->paginate($perPage);

        $items = array_map(function ($r) {
            return $this->normalizeRow($r);
        }, $p->items());

        return response()->json([
            'success' => true,
            'data' => $items,
            'pagination' => [
                'page'      => $p->currentPage(),
                'per_page'  => $p->perPage(),
                'total'     => $p->total(),
                'last_page' => $p->lastPage(),
            ],
        ]);
    }

    // Trash list
    public function trash(Request $request)
    {
        $request->query->set('only_trashed', '1');
        return $this->index($request);
    }

    // Current (latest active) settings (useful for frontend)
    public function current(Request $request)
    {
        $row = DB::table('hero_carousel_settings')
            ->whereNull('deleted_at')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first();

        return response()->json([
            'success' => true,
            'item' => $row ? $this->normalizeRow($row) : null,
        ]);
    }

    // Show by id/uuid
    public function show(Request $request, $identifier)
    {
        $includeDeleted = filter_var($request->query('with_trashed', false), FILTER_VALIDATE_BOOLEAN);

        $row = $this->resolveSetting($identifier, $includeDeleted);
        if (! $row) return response()->json(['message' => 'Hero carousel settings not found'], 404);

        return response()->json([
            'success' => true,
            'item' => $this->normalizeRow($row),
        ]);
    }

    // Create new settings row  (POST)
    public function store(Request $request, bool $skipLog = false)
    {
        $actor = $this->actor($request);

        $validated = $request->validate([
            'autoplay'           => ['nullable', 'in:0,1', 'boolean'],
            'autoplay_delay_ms'  => ['nullable', 'integer', 'min:0', 'max:600000'],
            'loop'               => ['nullable', 'in:0,1', 'boolean'],
            'pause_on_hover'     => ['nullable', 'in:0,1', 'boolean'],
            'show_arrows'        => ['nullable', 'in:0,1', 'boolean'],
            'show_dots'          => ['nullable', 'in:0,1', 'boolean'],
            'transition'         => ['nullable', 'string', 'max:20', 'in:slide,fade'],
            'transition_ms'      => ['nullable', 'integer', 'min:0', 'max:600000'],
            'metadata'           => ['nullable'],
        ]);

        $row = $this->createRow($request, $validated, $actor);

        if (! $skipLog) {
            $newArr = $row ? $this->normalizeRow($row) : null;

            $this->logActivity(
                $request,
                'create',
                'hero_carousel_settings',
                'hero_carousel_settings',
                $row ? (int) $row->id : null,
                $row ? ('Created hero carousel settings (id='.$row->id.', uuid='.$row->uuid.')') : 'Created hero carousel settings',
                $newArr ? array_keys($newArr) : ['created'],
                null,
                $newArr
            );
        }

        return response()->json([
            'success' => true,
            'item'    => $row ? $this->normalizeRow($row) : null,
        ], 201);
    }

    // Upsert current (if one exists, update it; else create) (POST)
    public function upsertCurrent(Request $request)
    {
        $actor = $this->actor($request);

        $validated = $request->validate([
            'autoplay'           => ['nullable', 'in:0,1', 'boolean'],
            'autoplay_delay_ms'  => ['nullable', 'integer', 'min:0', 'max:600000'],
            'loop'               => ['nullable', 'in:0,1', 'boolean'],
            'pause_on_hover'     => ['nullable', 'in:0,1', 'boolean'],
            'show_arrows'        => ['nullable', 'in:0,1', 'boolean'],
            'show_dots'          => ['nullable', 'in:0,1', 'boolean'],
            'transition'         => ['nullable', 'string', 'max:20', 'in:slide,fade'],
            'transition_ms'      => ['nullable', 'integer', 'min:0', 'max:600000'],
            'metadata'           => ['nullable'],
        ]);

        $existing = DB::table('hero_carousel_settings')
            ->whereNull('deleted_at')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first();

        // Create
        if (! $existing) {
            $row = $this->createRow($request, $validated, $actor);
            $newArr = $row ? $this->normalizeRow($row) : null;

            $this->logActivity(
                $request,
                'upsert',
                'hero_carousel_settings',
                'hero_carousel_settings',
                $row ? (int) $row->id : null,
                $row ? ('Upsert created hero carousel settings (id='.$row->id.', uuid='.$row->uuid.')') : 'Upsert created hero carousel settings',
                $newArr ? array_keys($newArr) : ['created'],
                null,
                $newArr
            );

            return response()->json([
                'success' => true,
                'item'    => $row ? $this->normalizeRow($row) : null,
            ], 201);
        }

        // Update
        $oldArr = $this->normalizeRow($existing);
        $fresh  = $this->updateRow($request, $existing, $validated);
        $newArr = $fresh ? $this->normalizeRow($fresh) : null;

        $fieldsConsidered = array_values(array_unique(array_merge(array_keys($validated), ['updated_at','updated_at_ip'])));
        $changed = [];
        $oldVals = [];
        $newVals = [];

        foreach ($fieldsConsidered as $k) {
            $ov = $oldArr[$k] ?? null;
            $nv = $newArr[$k] ?? null;
            if ($ov !== $nv) {
                $changed[] = $k;
                $oldVals[$k] = $ov;
                $newVals[$k] = $nv;
            }
        }

        $this->logActivity(
            $request,
            'upsert',
            'hero_carousel_settings',
            'hero_carousel_settings',
            $fresh ? (int) $fresh->id : (int) $existing->id,
            $fresh ? ('Upsert updated hero carousel settings (id='.$fresh->id.', uuid='.$fresh->uuid.')') : ('Upsert updated hero carousel settings (id='.$existing->id.', uuid='.$existing->uuid.')'),
            $changed,
            $oldVals,
            $newVals
        );

        return response()->json([
            'success' => true,
            'item'    => $fresh ? $this->normalizeRow($fresh) : null,
        ]);
    }

    // Update by id/uuid (PUT/PATCH)
    public function update(Request $request, $identifier, bool $skipLog = false)
    {
        $row = $this->resolveSetting($identifier, true);
        if (! $row) return response()->json(['message' => 'Hero carousel settings not found'], 404);

        $validated = $request->validate([
            'autoplay'           => ['nullable', 'in:0,1', 'boolean'],
            'autoplay_delay_ms'  => ['nullable', 'integer', 'min:0', 'max:600000'],
            'loop'               => ['nullable', 'in:0,1', 'boolean'],
            'pause_on_hover'     => ['nullable', 'in:0,1', 'boolean'],
            'show_arrows'        => ['nullable', 'in:0,1', 'boolean'],
            'show_dots'          => ['nullable', 'in:0,1', 'boolean'],
            'transition'         => ['nullable', 'string', 'max:20', 'in:slide,fade'],
            'transition_ms'      => ['nullable', 'integer', 'min:0', 'max:600000'],
            'metadata'           => ['nullable'],
        ]);

        $oldArr = $this->normalizeRow($row);

        $fresh = $this->updateRow($request, $row, $validated);
        $newArr = $fresh ? $this->normalizeRow($fresh) : null;

        if (! $skipLog) {
            $fieldsConsidered = array_values(array_unique(array_merge(array_keys($validated), ['updated_at','updated_at_ip'])));
            $changed = [];
            $oldVals = [];
            $newVals = [];

            foreach ($fieldsConsidered as $k) {
                $ov = $oldArr[$k] ?? null;
                $nv = $newArr[$k] ?? null;
                if ($ov !== $nv) {
                    $changed[] = $k;
                    $oldVals[$k] = $ov;
                    $newVals[$k] = $nv;
                }
            }

            $this->logActivity(
                $request,
                'update',
                'hero_carousel_settings',
                'hero_carousel_settings',
                $fresh ? (int) $fresh->id : (int) $row->id,
                $fresh ? ('Updated hero carousel settings (id='.$fresh->id.', uuid='.$fresh->uuid.', identifier='.$identifier.')') : ('Updated hero carousel settings (id='.$row->id.', uuid='.$row->uuid.', identifier='.$identifier.')'),
                $changed,
                $oldVals,
                $newVals
            );
        }

        return response()->json([
            'success' => true,
            'item'    => $fresh ? $this->normalizeRow($fresh) : null,
        ]);
    }

    // Soft delete (DELETE)
    public function destroy(Request $request, $identifier)
    {
        $row = $this->resolveSetting($identifier, false);
        if (! $row) return response()->json(['message' => 'Not found or already deleted'], 404);

        $oldArr = $this->normalizeRow($row);

        DB::table('hero_carousel_settings')->where('id', (int) $row->id)->update([
            'deleted_at'    => now(),
            'updated_at'    => now(),
            'updated_at_ip' => $request->ip(),
        ]);

        $fresh = DB::table('hero_carousel_settings')->where('id', (int) $row->id)->first();
        $newArr = $fresh ? $this->normalizeRow($fresh) : null;

        $changed = ['deleted_at','updated_at','updated_at_ip'];
        $oldVals = [
            'deleted_at'    => $oldArr['deleted_at'] ?? null,
            'updated_at'    => $oldArr['updated_at'] ?? null,
            'updated_at_ip' => $oldArr['updated_at_ip'] ?? null,
        ];
        $newVals = [
            'deleted_at'    => $newArr['deleted_at'] ?? null,
            'updated_at'    => $newArr['updated_at'] ?? null,
            'updated_at_ip' => $newArr['updated_at_ip'] ?? null,
        ];

        $this->logActivity(
            $request,
            'delete',
            'hero_carousel_settings',
            'hero_carousel_settings',
            (int) $row->id,
            'Soft deleted hero carousel settings (id='.$row->id.', uuid='.$row->uuid.', identifier='.$identifier.')',
            $changed,
            $oldVals,
            $newVals
        );

        return response()->json(['success' => true]);
    }

    // Restore from trash (POST/PATCH depending on route)
    public function restore(Request $request, $identifier)
    {
        $row = $this->resolveSetting($identifier, true);
        if (! $row || $row->deleted_at === null) {
            return response()->json(['message' => 'Not found in bin'], 404);
        }

        $oldArr = $this->normalizeRow($row);

        DB::table('hero_carousel_settings')->where('id', (int) $row->id)->update([
            'deleted_at'    => null,
            'updated_at'    => now(),
            'updated_at_ip' => $request->ip(),
        ]);

        $fresh = DB::table('hero_carousel_settings')->where('id', (int) $row->id)->first();
        $newArr = $fresh ? $this->normalizeRow($fresh) : null;

        $changed = ['deleted_at','updated_at','updated_at_ip'];
        $oldVals = [
            'deleted_at'    => $oldArr['deleted_at'] ?? null,
            'updated_at'    => $oldArr['updated_at'] ?? null,
            'updated_at_ip' => $oldArr['updated_at_ip'] ?? null,
        ];
        $newVals = [
            'deleted_at'    => $newArr['deleted_at'] ?? null,
            'updated_at'    => $newArr['updated_at'] ?? null,
            'updated_at_ip' => $newArr['updated_at_ip'] ?? null,
        ];

        $this->logActivity(
            $request,
            'restore',
            'hero_carousel_settings',
            'hero_carousel_settings',
            (int) $row->id,
            'Restored hero carousel settings (id='.$row->id.', uuid='.$row->uuid.', identifier='.$identifier.')',
            $changed,
            $oldVals,
            $newVals
        );

        return response()->json([
            'success' => true,
            'item'    => $fresh ? $this->normalizeRow($fresh) : null,
        ]);
    }

    // Hard delete (DELETE)
    public function forceDelete(Request $request, $identifier)
    {
        $row = $this->resolveSetting($identifier, true);
        if (! $row) return response()->json(['message' => 'Hero carousel settings not found'], 404);

        $oldArr = $this->normalizeRow($row);

        DB::table('hero_carousel_settings')->where('id', (int) $row->id)->delete();

        $this->logActivity(
            $request,
            'force_delete',
            'hero_carousel_settings',
            'hero_carousel_settings',
            (int) $row->id,
            'Force deleted hero carousel settings (id='.$row->id.', uuid='.$row->uuid.', identifier='.$identifier.')',
            ['force_delete'],
            $oldArr,
            null
        );

        return response()->json(['success' => true]);
    }
}
