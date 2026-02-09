<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Carbon\Carbon;

class UserJournalsController extends Controller
{
    private string $table = 'user_journals';

    /**
     * ✅ Public upload base (store directly in public/)
     * Stored path example:
     *   /user_uploads/journals/{user_uuid}/journal_{uuid}_1700000000.jpg
     */
    private string $publicBaseDir = 'user_uploads/journals';

    /* =========================
     * Auth helpers
     * ========================= */

    private function actor(Request $request): array
    {
        return [
            'role' => $request->attributes->get('auth_role'),
            'type' => $request->attributes->get('auth_tokenable_type'),
            'id'   => (int) ($request->attributes->get('auth_tokenable_id') ?? 0),
        ];
    }

    private function requireRole(Request $r, array $allowed)
    {
        $a = $this->actor($r);
        if (!$a['role'] || !in_array($a['role'], $allowed, true)) {
            return response()->json(['success' => false, 'error' => 'Unauthorized Access'], 403);
        }
        return null;
    }

    private function logWithActor(string $msg, Request $r, array $extra = []): void
    {
        $a = $this->actor($r);
        Log::info($msg, array_merge([
            'actor_role' => $a['role'],
            'actor_id'   => $a['id'],
            'ip'         => $r->ip(),
            'ua'         => (string) $r->userAgent(),
        ], $extra));
    }

    /* =========================
     * ✅ DB Activity Log (user_data_activity_log)
     * ========================= */

    private function dbActivityLog(
        Request $r,
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
            $a = $this->actor($r);

            DB::table('user_data_activity_log')->insert([
                'performed_by'       => (int) ($a['id'] ?? 0), // NOT NULL in migration
                'performed_by_role'  => $a['role'] ? (string)$a['role'] : null,
                'ip'                 => $r->ip(),
                'user_agent'         => (string) $r->userAgent(),

                'activity'           => $activity,
                'module'             => $module,

                'table_name'         => $tableName,
                'record_id'          => $recordId,

                'changed_fields'     => $changedFields !== null ? json_encode(array_values($changedFields)) : null,
                'old_values'         => $oldValues !== null ? json_encode($oldValues) : null,
                'new_values'         => $newValues !== null ? json_encode($newValues) : null,

                'log_note'           => $note,

                'created_at'         => Carbon::now(),
                'updated_at'         => Carbon::now(),
            ]);
        } catch (\Throwable $e) {
            // Never break API flow if logging fails
            Log::warning('user_data_activity_log.insert_failed', [
                'error' => $e->getMessage(),
                'activity' => $activity,
                'module' => $module,
                'table_name' => $tableName,
                'record_id' => $recordId,
            ]);
        }
    }

    private function fetchUserByUuid(string $uuid)
    {
        return DB::table('users')
            ->where('uuid', $uuid)
            ->whereNull('deleted_at')
            ->first();
    }

    private function fetchUserById(int $id)
    {
        return DB::table('users')
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->first();
    }

    /**
     * Resolve target user:
     * - If user_uuid provided (and not "me") => by uuid
     * - Else => by token user (auth_tokenable_id)
     */
    private function resolveTargetUser(Request $request, ?string $user_uuid)
    {
        $user_uuid = $user_uuid !== null ? trim($user_uuid) : null;

        if (!$user_uuid || strtolower($user_uuid) === 'me') {
            $actor = $this->actor($request);
            if (!$actor['id']) return null;
            return $this->fetchUserById((int) $actor['id']);
        }

        return $this->fetchUserByUuid($user_uuid);
    }

    private function isHighRole(?string $role): bool
    {
        return in_array($role, ['admin','director','principal','hod','technical_assistant','it_person'], true);
    }

    private function canAccessUser(Request $request, int $targetUserId): bool
    {
        $actor = $this->actor($request);
        if (!$actor['id']) return false;

        if ($actor['id'] === $targetUserId) return true;

        return $this->isHighRole($actor['role']);
    }

    /* =========================
     * Column helpers (safe)
     * ========================= */

    private function hasCol(string $col): bool
    {
        try { return Schema::hasColumn($this->table, $col); }
        catch (\Throwable $e) { return false; }
    }

    private function setIfColumn(array &$arr, string $col, $val): void
    {
        if ($this->hasCol($col)) {
            $arr[$col] = $val;
        }
    }

    /* =========================
     * Metadata helpers
     * ========================= */

    private function decodeMetadataRow($row)
    {
        if ($row && isset($row->metadata) && is_string($row->metadata)) {
            $decoded = json_decode($row->metadata, true);
            $row->metadata = is_array($decoded) ? $decoded : null;
        }
        return $row;
    }

    private function decodeMetadataCollection($rows)
    {
        foreach ($rows as $r) {
            if (isset($r->metadata) && is_string($r->metadata)) {
                $decoded = json_decode($r->metadata, true);
                $r->metadata = is_array($decoded) ? $decoded : null;
            }
        }
        return $rows;
    }

    /**
     * ✅ Accept metadata from:
     * - JSON body: metadata = array
     * - FormData: metadata = stringified JSON
     *
     * @return array [present?, value|null, error|null]
     */
    private function readMetadataFromRequest(Request $request): array
    {
        if (!$request->has('metadata')) return [false, null, null];

        $meta = $request->input('metadata');

        if (is_array($meta)) return [true, $meta, null];

        if (is_string($meta)) {
            $s = trim($meta);
            if ($s === '') return [true, null, null];

            $decoded = json_decode($s, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return [true, null, 'Metadata JSON invalid: ' . json_last_error_msg()];
            }
            if ($decoded !== null && !is_array($decoded)) {
                return [true, null, 'Metadata must decode to an object/array'];
            }
            return [true, $decoded, null];
        }

        return [true, null, 'Metadata must be an array or JSON string'];
    }

    /* =========================
     * Image upload helpers
     * ========================= */

    private function journalUserDir(string $userUuid): string
    {
        return $this->publicBaseDir . '/' . $userUuid; // relative to public/
    }

    private function ensureDir(string $publicRelativeDir): void
    {
        $abs = public_path($publicRelativeDir);
        if (!File::exists($abs)) {
            File::makeDirectory($abs, 0775, true);
        }
    }

    /**
     * Supports either:
     * - image_file
     * - image (file)  [compat]
     */
    private function storeImageFile(Request $request, string $userUuid, string $journalUuid): ?string
    {
        $file = null;

        if ($request->hasFile('image_file')) $file = $request->file('image_file');
        elseif ($request->hasFile('image')) $file = $request->file('image');

        if (!$file || !$file->isValid()) return null;

        $dir = $this->journalUserDir($userUuid);
        $this->ensureDir($dir);

        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $ext = preg_replace('/[^a-z0-9]/i', '', $ext) ?: 'jpg';

        $name = 'journal_' . $journalUuid . '_' . time() . '_' . Str::random(6) . '.' . $ext;
        $file->move(public_path($dir), $name);

        return '/' . $dir . '/' . $name;
    }

    private function tryDeleteOldImage(?string $path): void
    {
        if (!$path) return;

        $p = trim((string)$path);
        if ($p === '') return;

        // don't delete external
        if (str_starts_with($p, 'http://') || str_starts_with($p, 'https://') || str_starts_with($p, '//')) return;

        $rel = str_starts_with($p, '/') ? substr($p, 1) : $p;
        $abs = public_path($rel);

        try {
            if (File::exists($abs) && File::isFile($abs)) {
                File::delete($abs);
            }
        } catch (\Throwable $e) {
            Log::warning('user_journals.image.delete_failed', ['path' => $path, 'error' => $e->getMessage()]);
        }
    }

    /* =========================
     * ✅ Duplicate prevent (double submit)
     * ========================= */

    private function findRecentDuplicate(int $userId, array $data)
    {
        $q = DB::table($this->table)
            ->where('user_id', $userId)
            ->whereNull('deleted_at')
            ->where('title', $data['title']);

        if (array_key_exists('publication_organization', $data)) {
            $data['publication_organization'] === null
                ? $q->whereNull('publication_organization')
                : $q->where('publication_organization', $data['publication_organization']);
        }

        if (array_key_exists('publication_year', $data)) {
            $data['publication_year'] === null
                ? $q->whereNull('publication_year')
                : $q->where('publication_year', $data['publication_year']);
        }

        $q->where('created_at', '>=', Carbon::now()->subSeconds(20));

        return $q->orderBy('id', 'desc')->first();
    }

    /* =========================
     * CRUD
     * ========================= */

    public function index(Request $request, ?string $user_uuid = null)
    {
        if ($resp = $this->requireRole($request, [
            'admin','director','principal','hod','faculty','technical_assistant','it_person','student'
        ])) return $resp;

        $user = $this->resolveTargetUser($request, $user_uuid);
        if (!$user) return response()->json(['success' => false, 'error' => 'User not found'], 404);

        if (!$this->canAccessUser($request, (int)$user->id)) {
            return response()->json(['success' => false, 'error' => 'Unauthorized Access'], 403);
        }

        $rows = DB::table($this->table)
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->orderBy('sort_order', 'asc')
            ->orderByRaw('publication_year IS NULL, publication_year DESC')
            ->orderBy('id', 'desc')
            ->get();

        $rows = $this->decodeMetadataCollection($rows);

        return response()->json(['success' => true, 'data' => $rows]);
    }

    public function show(Request $request, ?string $user_uuid = null, string $journal_uuid = '')
    {
        if ($resp = $this->requireRole($request, [
            'admin','director','principal','hod','faculty','technical_assistant','it_person','student'
        ])) return $resp;

        $user = $this->resolveTargetUser($request, $user_uuid);
        if (!$user) return response()->json(['success' => false, 'error' => 'User not found'], 404);

        if (!$this->canAccessUser($request, (int)$user->id)) {
            return response()->json(['success' => false, 'error' => 'Unauthorized Access'], 403);
        }

        $row = DB::table($this->table)
            ->where('uuid', $journal_uuid)
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->first();

        if (!$row) return response()->json(['success' => false, 'error' => 'Journal not found'], 404);

        $row = $this->decodeMetadataRow($row);

        return response()->json(['success' => true, 'data' => $row]);
    }

    /**
     * ✅ Supports:
     * - JSON body
     * - FormData (multipart) for image_file + metadata as JSON string
     *
     * Fields:
     * - title (required)
     * - publication_organization, publication_year, description, sort_order
     * - image_file (optional upload)
     * - image (optional legacy string)
     * - metadata (optional array or JSON string)
     */
    public function store(Request $request, ?string $user_uuid = null)
    {
        $module = 'user_journals';

        if ($resp = $this->requireRole($request, [
            'admin','director','principal','hod','faculty','technical_assistant','it_person','student'
        ])) {
            $this->dbActivityLog($request, 'create', $module, $this->table, null, null, null, null, 'Unauthorized role');
            return $resp;
        }

        $user = $this->resolveTargetUser($request, $user_uuid);
        if (!$user) {
            $this->dbActivityLog($request, 'create', $module, $this->table, null, null, null, null, 'User not found');
            return response()->json(['success' => false, 'error' => 'User not found'], 404);
        }

        if (!$this->canAccessUser($request, (int)$user->id)) {
            $this->dbActivityLog($request, 'create', $module, $this->table, null, null, null, null, 'Unauthorized access to target user');
            return response()->json(['success' => false, 'error' => 'Unauthorized Access'], 403);
        }

        // helpful log to diagnose "2 entry" (you will see it twice if frontend fires twice)
        $this->logWithActor('user_journals.store.hit', $request, [
            'target_user_id' => (int)$user->id,
        ]);

        $v = Validator::make($request->all(), [
            'title'                    => ['required', 'string', 'max:255'],
            'publication_organization' => ['nullable', 'string', 'max:255'],
            'publication_year'         => ['nullable', 'integer', 'min:1900', 'max:' . (int)date('Y')],
            'description'              => ['nullable', 'string'],
            'sort_order'               => ['nullable', 'integer'],
            'image'                    => ['nullable', 'string', 'max:255'], // legacy string
            'image_file'               => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
        ]);

        if ($v->fails()) {
            $this->dbActivityLog(
                $request,
                'create',
                $module,
                $this->table,
                null,
                array_keys($v->errors()->toArray()),
                null,
                null,
                'Validation failed'
            );
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        [$metaPresent, $metaValue, $metaErr] = $this->readMetadataFromRequest($request);
        if ($metaErr) {
            $this->dbActivityLog($request, 'create', $module, $this->table, null, ['metadata'], null, null, $metaErr);
            return response()->json(['success' => false, 'error' => $metaErr], 422);
        }

        $data  = $v->validated();
        $actor = $this->actor($request);
        $now   = Carbon::now();

        // ✅ Prevent double submit duplicates (same data within 20s)
        $dup = $this->findRecentDuplicate((int)$user->id, [
            'title' => $data['title'],
            'publication_organization' => $data['publication_organization'] ?? null,
            'publication_year' => $data['publication_year'] ?? null,
        ]);
        if ($dup) {
            $this->dbActivityLog(
                $request,
                'create',
                $module,
                $this->table,
                (int)($dup->id ?? 0) ?: null,
                ['title','publication_organization','publication_year'],
                null,
                [
                    'title' => $data['title'],
                    'publication_organization' => $data['publication_organization'] ?? null,
                    'publication_year' => $data['publication_year'] ?? null,
                ],
                'Duplicate submit prevented'
            );

            $dup = $this->decodeMetadataRow($dup);
            return response()->json([
                'success' => true,
                'data'    => $dup,
                'message' => 'Duplicate submit prevented',
            ], 200);
        }

        DB::beginTransaction();
        try {
            $uuid = (string) Str::uuid();

            $imgPath = $this->storeImageFile($request, (string)$user->uuid, $uuid);
            if (!$imgPath && !empty($data['image'])) {
                $imgPath = $data['image'];
            }

            $insert = [
                'uuid'                    => $uuid,
                'user_id'                 => (int)$user->id,
                'title'                   => $data['title'],
                'publication_organization'=> $data['publication_organization'] ?? null,
                'publication_year'        => $data['publication_year'] ?? null,
                'description'             => $data['description'] ?? null,
                'image'                   => $imgPath,
                'sort_order'              => $data['sort_order'] ?? 0,
                'metadata'                => $metaPresent ? ($metaValue !== null ? json_encode($metaValue) : null) : null,
                'created_at'              => $now,
                'updated_at'              => $now,
            ];

            $this->setIfColumn($insert, 'created_by', $actor['id'] ?: null);
            $this->setIfColumn($insert, 'created_at_ip', $request->ip());
            $this->setIfColumn($insert, 'updated_by', $actor['id'] ?: null);
            $this->setIfColumn($insert, 'updated_at_ip', $request->ip());

            $id = DB::table($this->table)->insertGetId($insert);

            DB::commit();

            $this->logWithActor('user_journals.store.success', $request, [
                'id' => $id,
                'target_user_id' => (int)$user->id,
            ]);

            // ✅ DB activity log (one row per request outcome)
            $this->dbActivityLog(
                $request,
                'create',
                $module,
                $this->table,
                (int)$id,
                array_keys($insert),
                null,
                $insert,
                'Created journal'
            );

            $row = DB::table($this->table)->where('id', $id)->first();
            $row = $this->decodeMetadataRow($row);

            return response()->json(['success' => true, 'data' => $row], 201);

        } catch (\Throwable $e) {
            DB::rollBack();

            $this->logWithActor('user_journals.store.failed', $request, [
                'error' => $e->getMessage(),
                'target_user_id' => (int)$user->id,
            ]);

            $this->dbActivityLog(
                $request,
                'create',
                $module,
                $this->table,
                null,
                null,
                null,
                null,
                'Failed to create journal: ' . $e->getMessage()
            );

            return response()->json(['success' => false, 'error' => 'Failed to create journal'], 500);
        }
    }

    public function update(Request $request, ?string $user_uuid = null, string $journal_uuid = '')
    {
        $module = 'user_journals';

        if ($resp = $this->requireRole($request, [
            'admin','director','principal','hod','faculty','technical_assistant','it_person','student'
        ])) {
            $this->dbActivityLog($request, 'update', $module, $this->table, null, null, null, null, 'Unauthorized role');
            return $resp;
        }

        $user = $this->resolveTargetUser($request, $user_uuid);
        if (!$user) {
            $this->dbActivityLog($request, 'update', $module, $this->table, null, null, null, null, 'User not found');
            return response()->json(['success' => false, 'error' => 'User not found'], 404);
        }

        if (!$this->canAccessUser($request, (int)$user->id)) {
            $this->dbActivityLog($request, 'update', $module, $this->table, null, null, null, null, 'Unauthorized access to target user');
            return response()->json(['success' => false, 'error' => 'Unauthorized Access'], 403);
        }

        $row = DB::table($this->table)
            ->where('uuid', $journal_uuid)
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->first();

        if (!$row) {
            $this->dbActivityLog($request, 'update', $module, $this->table, null, null, null, null, 'Journal not found');
            return response()->json(['success' => false, 'error' => 'Journal not found'], 404);
        }

        $v = Validator::make($request->all(), [
            'title'                    => ['sometimes', 'required', 'string', 'max:255'],
            'publication_organization' => ['sometimes', 'nullable', 'string', 'max:255'],
            'publication_year'         => ['sometimes', 'nullable', 'integer', 'min:1900', 'max:' . (int)date('Y')],
            'description'              => ['sometimes', 'nullable', 'string'],
            'sort_order'               => ['sometimes', 'nullable', 'integer'],
            'image'                    => ['sometimes', 'nullable', 'string', 'max:255'], // legacy string
            'image_file'               => ['sometimes', 'nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
        ]);

        if ($v->fails()) {
            $this->dbActivityLog(
                $request,
                'update',
                $module,
                $this->table,
                (int)$row->id,
                array_keys($v->errors()->toArray()),
                null,
                null,
                'Validation failed'
            );
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        [$metaPresent, $metaValue, $metaErr] = $this->readMetadataFromRequest($request);
        if ($metaErr) {
            $this->dbActivityLog($request, 'update', $module, $this->table, (int)$row->id, ['metadata'], null, null, $metaErr);
            return response()->json(['success' => false, 'error' => $metaErr], 422);
        }

        $data   = $v->validated();
        $actor  = $this->actor($request);
        $now    = Carbon::now();
        $update = [];

        foreach (['title','publication_organization','description'] as $f) {
            if (array_key_exists($f, $data)) $update[$f] = $data[$f];
        }
        if (array_key_exists('publication_year', $data)) $update['publication_year'] = $data['publication_year'];
        if (array_key_exists('sort_order', $data)) $update['sort_order'] = $data['sort_order'] ?? 0;

        // ✅ Replace image if uploaded
        $newImg = $this->storeImageFile($request, (string)$user->uuid, (string)$row->uuid);
        if ($newImg) {
            $this->tryDeleteOldImage($row->image ?? null);
            $update['image'] = $newImg;
        } elseif (array_key_exists('image', $data)) {
            $update['image'] = $data['image'];
        }

        if ($metaPresent) {
            $update['metadata'] = $metaValue !== null ? json_encode($metaValue) : null;
        }

        // If nothing to update, return as-is (but still log the PATCH/PUT attempt)
        if (empty($update)) {
            $this->dbActivityLog(
                $request,
                'update',
                $module,
                $this->table,
                (int)$row->id,
                [],
                null,
                null,
                'No changes submitted'
            );
            return response()->json(['success' => true, 'data' => $this->decodeMetadataRow($row)]);
        }

        // Build old/new snapshots for changed fields (exclude audit fields)
        $changedKeys = array_keys($update);
        $oldVals = [];
        $newVals = [];
        foreach ($changedKeys as $k) {
            $oldVals[$k] = $row->$k ?? null;
            $newVals[$k] = $update[$k] ?? null;
        }

        $update['updated_at'] = $now;

        $this->setIfColumn($update, 'updated_by', $actor['id'] ?: null);
        $this->setIfColumn($update, 'updated_at_ip', $request->ip());

        DB::table($this->table)->where('id', $row->id)->update($update);

        $this->logWithActor('user_journals.update.success', $request, [
            'id' => $row->id,
            'target_user_id' => (int)$user->id,
        ]);

        $this->dbActivityLog(
            $request,
            'update',
            $module,
            $this->table,
            (int)$row->id,
            $changedKeys,
            $oldVals,
            $newVals,
            'Updated journal'
        );

        $fresh = DB::table($this->table)->where('id', $row->id)->first();
        $fresh = $this->decodeMetadataRow($fresh);

        return response()->json(['success' => true, 'data' => $fresh]);
    }

    public function destroy(Request $request, ?string $user_uuid = null, string $journal_uuid = '')
    {
        $module = 'user_journals';

        if ($resp = $this->requireRole($request, [
            'admin','director','principal','hod','faculty','technical_assistant','it_person','student'
        ])) {
            $this->dbActivityLog($request, 'delete', $module, $this->table, null, null, null, null, 'Unauthorized role');
            return $resp;
        }

        $user = $this->resolveTargetUser($request, $user_uuid);
        if (!$user) {
            $this->dbActivityLog($request, 'delete', $module, $this->table, null, null, null, null, 'User not found');
            return response()->json(['success' => false, 'error' => 'User not found'], 404);
        }

        if (!$this->canAccessUser($request, (int)$user->id)) {
            $this->dbActivityLog($request, 'delete', $module, $this->table, null, null, null, null, 'Unauthorized access to target user');
            return response()->json(['success' => false, 'error' => 'Unauthorized Access'], 403);
        }

        $row = DB::table($this->table)
            ->where('uuid', $journal_uuid)
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->first();

        if (!$row) {
            $this->dbActivityLog($request, 'delete', $module, $this->table, null, null, null, null, 'Journal not found');
            return response()->json(['success' => false, 'error' => 'Journal not found'], 404);
        }

        $actor = $this->actor($request);
        $now   = Carbon::now();

        $upd = [
            'deleted_at' => $now,
            'updated_at' => $now,
        ];

        $this->setIfColumn($upd, 'updated_by', $actor['id'] ?: null);
        $this->setIfColumn($upd, 'updated_at_ip', $request->ip());

        DB::table($this->table)->where('id', $row->id)->update($upd);

        $this->logWithActor('user_journals.destroy', $request, [
            'id' => $row->id,
            'target_user_id' => (int)$user->id,
        ]);

        $this->dbActivityLog(
            $request,
            'delete',
            $module,
            $this->table,
            (int)$row->id,
            ['deleted_at'],
            ['deleted_at' => $row->deleted_at ?? null],
            ['deleted_at' => (string)$now],
            'Deleted journal (soft delete)'
        );

        return response()->json(['success' => true, 'message' => 'Journal deleted']);
    }
}
