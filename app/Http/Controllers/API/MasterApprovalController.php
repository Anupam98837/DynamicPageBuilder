<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class MasterApprovalController extends Controller
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
     * ✅ Activity log writer (silent fail; never breaks API)
     */
    private function logActivity(
        Request $r,
        string $activity,
        string $module,
        string $tableName,
        $recordId = null,
        ?array $changedFields = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $note = null
    ): void {
        try {
            $a = $this->actor($r);

            DB::table('user_data_activity_log')->insert([
                'performed_by'      => (int)($a['id'] ?? 0),
                'performed_by_role' => ($a['role'] ?? null) ?: null,
                'ip'                => $r->ip(),
                'user_agent'        => substr((string)($r->userAgent() ?? ''), 0, 512),

                'activity'   => substr($activity, 0, 50),
                'module'     => substr($module, 0, 100),

                'table_name' => substr($tableName ?: 'unknown', 0, 128),
                'record_id'  => is_null($recordId) ? null : (int)$recordId,

                'changed_fields' => is_null($changedFields) ? null : json_encode(array_values($changedFields)),
                'old_values'     => is_null($oldValues) ? null : json_encode($oldValues),
                'new_values'     => is_null($newValues) ? null : json_encode($newValues),

                'log_note'   => $note,

                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        } catch (\Throwable $e) {
            // Never break main flow due to logging failure.
        }
    }

    private function pickFieldsFromRow($row, array $fields): array
    {
        if (!$row) return [];
        $arr = (array) $row;

        $out = [];
        foreach ($fields as $f) {
            if (array_key_exists($f, $arr)) {
                $out[$f] = $arr[$f];
            }
        }
        return $out;
    }

    protected function toUrl(?string $path): ?string
    {
        $path = trim((string) $path);
        if ($path === '') return null;
        if (preg_match('~^https?://~i', $path)) return $path;
        return url('/' . ltrim($path, '/'));
    }

    /**
     * ✅ Config for all divisions
     * Each module is normalized to same response shape.
     */
    protected function modules(): array
    {
        return [
            'announcements' => [
                'label' => 'Announcements',
                'table' => 'announcements',
                'alias' => 'a',
                'has_department' => true,
                'title_col' => 'title',
                'slug_col'  => 'slug',
                'body_col'  => 'body',
                'image_col' => 'cover_image',
                'attachments_col' => 'attachments_json',
            ],
            'achievements' => [
                'label' => 'Achievements',
                'table' => 'achievements',
                'alias' => 'ac',
                'has_department' => true,
                'title_col' => 'title',
                'slug_col'  => 'slug',
                'body_col'  => 'body',
                'image_col' => 'cover_image',
                'attachments_col' => 'attachments_json',
            ],
            'notices' => [
                'label' => 'Notices',
                'table' => 'notices',
                'alias' => 'n',
                'has_department' => true,
                'title_col' => 'title',
                'slug_col'  => 'slug',
                'body_col'  => 'body',
                'image_col' => 'cover_image',
                'attachments_col' => 'attachments_json',
            ],
            'student_activities' => [
                'label' => 'Student Activities',
                'table' => 'student_activities',
                'alias' => 'sa',
                'has_department' => true,
                'title_col' => 'title',
                'slug_col'  => 'slug',
                'body_col'  => 'body',
                'image_col' => 'cover_image',
                'attachments_col' => 'attachments_json',
            ],
            'career_notices' => [
                'label' => 'Career Notices',
                'table' => 'career_notices',
                'alias' => 'cn',
                'has_department' => false,
                'title_col' => 'title',
                'slug_col'  => 'slug',
                'body_col'  => 'body',
                'image_col' => 'cover_image',
                'attachments_col' => 'attachments_json',
            ],
            'why_us' => [
                'label' => 'Why Us',
                'table' => 'why_us',
                'alias' => 'wu',
                'has_department' => false,
                'title_col' => 'title',
                'slug_col'  => 'slug',
                'body_col'  => 'body',
                'image_col' => 'cover_image',
                'attachments_col' => 'attachments_json',
            ],
            'scholarships' => [
                'label' => 'Scholarships',
                'table' => 'scholarships',
                'alias' => 's',
                'has_department' => true,
                'title_col' => 'title',
                'slug_col'  => 'slug',
                'body_col'  => 'body',
                'image_col' => 'cover_image',
                'attachments_col' => 'attachments_json',
            ],
            'placement_notices' => [
                'label' => 'Placement Notices',
                'table' => 'placement_notices',
                'alias' => 'pn',
                'has_department' => false, // (department_ids is JSON)
                'title_col' => 'title',
                'slug_col'  => 'slug',
                'body_col'  => 'description',
                'image_col' => 'banner_image_url', // special
                'attachments_col' => null,
            ],
        ];
    }

    /**
     * Base query builder per module (with joins for creator + department)
     */
    protected function moduleQuery(string $key, Request $request, bool $includeDeleted = false)
    {
        $mods = $this->modules();
        if (!isset($mods[$key])) return null;

        $cfg = $mods[$key];
        $t   = $cfg['table'];
        $a   = $cfg['alias'];

        $q = DB::table($t . " as {$a}")
            ->leftJoin('users as u', 'u.id', '=', "{$a}.created_by");

        if ($cfg['has_department']) {
            $q->leftJoin('departments as d', 'd.id', '=', "{$a}.department_id");
        }

        // Soft delete respect
        if (!$includeDeleted) {
            $q->whereNull("{$a}.deleted_at");
        }

        // optional search: ?q=
        if ($request->filled('q')) {
            $term = '%' . trim((string) $request->query('q')) . '%';

            $titleCol = $cfg['title_col'];
            $slugCol  = $cfg['slug_col'];
            $bodyCol  = $cfg['body_col'];

            $q->where(function ($sub) use ($a, $term, $titleCol, $slugCol, $bodyCol) {
                $sub->where("{$a}.{$titleCol}", 'like', $term)
                    ->orWhere("{$a}.{$slugCol}", 'like', $term);

                if (!empty($bodyCol)) {
                    $sub->orWhere("{$a}.{$bodyCol}", 'like', $term);
                }
            });
        }

        // select everything + standardized meta
        $select = [
            "{$a}.*",
            DB::raw("'" . addslashes($key) . "' as division_key"),
            DB::raw("'" . addslashes($cfg['label']) . "' as division_label"),

            // creator
            "u.id as creator_id",
            "u.uuid as creator_uuid",
            "u.name as creator_name",
            "u.email as creator_email",
        ];

        if ($cfg['has_department']) {
            $select[] = "d.title as department_title";
            $select[] = "d.slug as department_slug";
            $select[] = "d.uuid as department_uuid";
        } else {
            $select[] = DB::raw("NULL as department_title");
            $select[] = DB::raw("NULL as department_slug");
            $select[] = DB::raw("NULL as department_uuid");
        }

        $q->select($select);

        // default sort
        $q->orderBy("{$a}.created_at", 'desc');

        return $q;
    }

    /**
     * Normalizes each row with:
     * - creator object
     * - department object (if exists)
     * - decoded attachments_json + metadata
     * - cover/banner URL normalization
     * - record contains full row
     */
    protected function normalizeRow(array $cfg, $row): array
    {
        $arr = (array) $row;

        // decode attachments_json
        $attCol = $cfg['attachments_col'] ?? null;
        if ($attCol && isset($arr[$attCol]) && is_string($arr[$attCol])) {
            $decoded = json_decode($arr[$attCol], true);
            $arr[$attCol] = (json_last_error() === JSON_ERROR_NONE) ? $decoded : null;
        }

        // decode metadata
        if (isset($arr['metadata']) && is_string($arr['metadata'])) {
            $decoded = json_decode($arr['metadata'], true);
            $arr['metadata'] = (json_last_error() === JSON_ERROR_NONE) ? $decoded : null;
        }

        // media url (cover_image or banner_image_url)
        $imgCol = $cfg['image_col'] ?? null;
        $mediaUrl = null;

        if ($imgCol && isset($arr[$imgCol])) {
            $mediaUrl = $this->toUrl($arr[$imgCol]);
        }

        // unified creator
        $creator = [
            'id'    => isset($arr['creator_id']) ? (int) $arr['creator_id'] : null,
            'uuid'  => $arr['creator_uuid'] ?? null,
            'name'  => $arr['creator_name'] ?? null,
            'email' => $arr['creator_email'] ?? null,
        ];

        // unified department (if any)
        $department = null;
        if (!empty($arr['department_uuid']) || !empty($arr['department_title']) || !empty($arr['department_slug'])) {
            $department = [
                'id'    => isset($arr['department_id']) ? (int) $arr['department_id'] : null,
                'uuid'  => $arr['department_uuid'] ?? null,
                'title' => $arr['department_title'] ?? null,
                'slug'  => $arr['department_slug'] ?? null,
            ];
        }

        // standardized output
        return [
            'division' => [
                'key'   => $arr['division_key'] ?? null,
                'label' => $arr['division_label'] ?? null,
            ],

            'id'   => isset($arr['id']) ? (int) $arr['id'] : null,
            'uuid' => $arr['uuid'] ?? null,

            'title' => $arr[$cfg['title_col']] ?? null,
            'slug'  => $arr[$cfg['slug_col']] ?? null,

            'status'               => $arr['status'] ?? null,
            'is_featured_home'     => isset($arr['is_featured_home']) ? (int) $arr['is_featured_home'] : null,
            'request_for_approval' => isset($arr['request_for_approval']) ? (int) $arr['request_for_approval'] : 0,
            'is_approved'          => isset($arr['is_approved']) ? (int) $arr['is_approved'] : 0,

            'created_at'    => $arr['created_at'] ?? null,
            'updated_at'    => $arr['updated_at'] ?? null,
            'created_at_ip' => $arr['created_at_ip'] ?? null,
            'updated_at_ip' => $arr['updated_at_ip'] ?? null,

            'publish_at' => $arr['publish_at'] ?? null,
            'expire_at'  => $arr['expire_at'] ?? null,

            'creator'    => $creator,
            'department' => $department,

            'media' => [
                'path'   => $imgCol ? ($arr[$imgCol] ?? null) : null,
                'url'    => $mediaUrl,
                'column' => $imgCol,
            ],

            // ✅ full row includes ALL details from table
            'record' => $arr,
        ];
    }

    /**
     * Fetch rows for a given tab
     * - pending: request_for_approval=1 AND is_approved=0
     * - approved: is_approved=1
     * - requests: request_for_approval=1 (all)
     */
    protected function fetchTab(string $tab, Request $request): array
    {
        $includeDeleted = filter_var($request->query('with_trashed', false), FILTER_VALIDATE_BOOLEAN);
        $perDivision    = max(1, min(500, (int) $request->query('per_division', 200)));

        $mods = $this->modules();
        $out  = [];

        foreach ($mods as $key => $cfg) {
            $q = $this->moduleQuery($key, $request, $includeDeleted);
            if (!$q) continue;

            $a = $cfg['alias'];

            if ($tab === 'pending') {
                $q->where("{$a}.request_for_approval", 1)
                  ->where("{$a}.is_approved", 0);
            } elseif ($tab === 'approved') {
                $q->where("{$a}.is_approved", 1);
            } else { // requests
                $q->where("{$a}.request_for_approval", 1);
            }

            $rows = $q->limit($perDivision)->get();

            foreach ($rows as $r) {
                $out[] = $this->normalizeRow($cfg, $r);
            }
        }

        // Global sort by created_at desc
        usort($out, function ($x, $y) {
            $tx = isset($x['created_at']) ? strtotime($x['created_at']) : 0;
            $ty = isset($y['created_at']) ? strtotime($y['created_at']) : 0;
            return $ty <=> $tx;
        });

        return array_values($out);
    }

    /**
     * Notifications/Divisions counts + latest pending list
     */
    protected function buildNotifications(Request $request): array
    {
        $includeDeleted = filter_var($request->query('with_trashed', false), FILTER_VALIDATE_BOOLEAN);
        $latestLimit    = max(1, min(50, (int) $request->query('latest_limit', 5)));

        $mods = $this->modules();

        $divisions = [];
        $totals = [
            'pending'  => 0,
            'approved' => 0,
            'requests' => 0,
        ];

        foreach ($mods as $key => $cfg) {
            $t = $cfg['table'];
            $a = $cfg['alias'];

            $base = DB::table($t . " as {$a}");

            if (!$includeDeleted) {
                $base->whereNull("{$a}.deleted_at");
            }

            $pendingCount = (clone $base)
                ->where("{$a}.request_for_approval", 1)
                ->where("{$a}.is_approved", 0)
                ->count();

            $approvedCount = (clone $base)
                ->where("{$a}.is_approved", 1)
                ->count();

            $requestsCount = (clone $base)
                ->where("{$a}.request_for_approval", 1)
                ->count();

            // latest pending rows
            $latestPending = [];
            $q = $this->moduleQuery($key, $request, $includeDeleted);
            if ($q) {
                $q->where("{$a}.request_for_approval", 1)
                  ->where("{$a}.is_approved", 0)
                  ->limit($latestLimit);

                $rows = $q->get();
                foreach ($rows as $r) {
                    $latestPending[] = $this->normalizeRow($cfg, $r);
                }
            }

            $divisions[$key] = [
                'key'   => $key,
                'label' => $cfg['label'],
                'counts' => [
                    'pending'  => (int) $pendingCount,
                    'approved' => (int) $approvedCount,
                    'requests' => (int) $requestsCount,
                ],
                'latest_pending' => $latestPending,
            ];

            $totals['pending']  += (int) $pendingCount;
            $totals['approved'] += (int) $approvedCount;
            $totals['requests'] += (int) $requestsCount;
        }

        return [
            'totals'    => $totals,
            'divisions' => $divisions,
        ];
    }

    /* ============================================
     | ✅ NEW API: FINAL (Approved only)
     |============================================ */

    /**
     * ✅ GET: /api/master-approval/final
     * Returns ONLY approved data from:
     * announcements, achievements, notices, student_activities,
     * career_notices, why_us, scholarships, placement_notices
     */
    public function final(Request $request)
    {
        $actor = $this->actor($request);

        // ✅ Only approved items across all divisions
        $approved = $this->fetchTab('approved', $request);

        return response()->json([
            'success' => true,
            'message' => 'Master approval final (approved only)',
            'actor'   => $actor,
            'approved' => [
                'count' => count($approved),
                'items' => $approved,
            ],
        ]);
    }

    /* ============================================
     | EXISTING API (unchanged)
     |============================================ */

    /**
     * ✅ ONE API for Master Approval Page:
     * - requests: request_for_approval=1
     * - tabs.pending: request_for_approval=1 AND is_approved=0
     * - tabs.approved: is_approved=1
     * - notifications: division-wise counts + latest pending list
     */
    public function overview(Request $request)
    {
        $actor = $this->actor($request);

        $pending  = $this->fetchTab('pending', $request);
        $approved = $this->fetchTab('approved', $request);
        $requests = $this->fetchTab('requests', $request);

        $notifications = $this->buildNotifications($request);

        return response()->json([
            'success' => true,
            'message' => 'Master approval overview',
            'actor'   => $actor,

            // ✅ This is what your UI will use for 2 tabs
            'tabs' => [
                'not_approved' => [
                    'label' => 'Not Approved (Pending Requests)',
                    'count' => count($pending),
                    'items' => $pending,
                ],
                'approved' => [
                    'label' => 'Approved',
                    'count' => count($approved),
                    'items' => $approved,
                ],
            ],

            // ✅ all requests_for_approval=1 (your "all requests list")
            'requests' => [
                'count' => count($requests),
                'items' => $requests,
            ],

            // ✅ division-wise notifications (counts + latest pending)
            'notifications' => $notifications,
        ]);
    }

    /* ============================================================
     | Helper: Find which module table contains the UUID
     |============================================================ */
    private function moduleTableMap(): array
    {
        return [
            'announcements'      => 'announcements',
            'achievements'       => 'achievements',
            'notices'            => 'notices',
            'student_activities' => 'student_activities',
            'career_notices'     => 'career_notices',
            'why_us'             => 'why_us',
            'scholarships'       => 'scholarships',
            'placement_notices'  => 'placement_notices',
        ];
    }

    private function resolveTargetByUuid(string $uuid, ?string $hintDivisionKey = null): ?array
    {
        $map = $this->moduleTableMap();

        // ✅ If frontend ever sends division_key as hint, try it first (faster)
        if ($hintDivisionKey && isset($map[$hintDivisionKey])) {
            $table = $map[$hintDivisionKey];
            if (Schema::hasTable($table)) {
                $row = DB::table($table)->where('uuid', $uuid)->first();
                if ($row) {
                    return ['division_key' => $hintDivisionKey, 'table' => $table, 'row' => $row];
                }
            }
        }

        // ✅ Otherwise scan all known module tables
        foreach ($map as $divisionKey => $table) {
            if (!Schema::hasTable($table)) continue;

            $row = DB::table($table)->where('uuid', $uuid)->first();
            if ($row) {
                return ['division_key' => $divisionKey, 'table' => $table, 'row' => $row];
            }
        }

        return null;
    }

    private function buildSafeUpdatePayload(string $table, array $updates, Request $request): array
    {
        $final = [];

        foreach ($updates as $col => $val) {
            if (Schema::hasColumn($table, $col)) {
                $final[$col] = $val;
            }
        }

        // ✅ Common audit fields (only if exist)
        if (Schema::hasColumn($table, 'updated_at')) {
            $final['updated_at'] = Carbon::now();
        }
        if (Schema::hasColumn($table, 'updated_at_ip')) {
            $final['updated_at_ip'] = $request->ip();
        }

        return $final;
    }

    /* ============================================================
     | ✅ POST: /api/master-approval/{uuid}/approve
     |============================================================ */
    public function approve(Request $request, string $uuid)
    {
        $uuid = trim($uuid);
        $hint = $request->input('division_key'); // optional

        try {
            $target = $this->resolveTargetByUuid($uuid, $hint);

            if (!$target) {
                // ✅ Log: approve attempt but record not found
                $this->logActivity(
                    $request,
                    'approve',
                    'master_approval',
                    'unknown',
                    null,
                    null,
                    null,
                    null,
                    "Approve failed: record not found. uuid={$uuid}, hint_division_key=" . ($hint ?: '')
                );

                return response()->json([
                    'success' => false,
                    'message' => 'Record not found for approval.',
                    'uuid'    => $uuid,
                ], 404);
            }

            $table  = $target['table'];
            $before = $target['row'];

            DB::beginTransaction();

            // ✅ Approve logic (only updates columns that exist)
            $payload = $this->buildSafeUpdatePayload($table, [
                'is_approved'          => 1,
                'request_for_approval' => 0,
                'is_rejected'          => 0,
                'rejected_reason'      => null,
                'rejection_reason'     => null,
                'approval_status'      => 'approved',
                'approved_at'          => Carbon::now(),
                'approved_by'          => (int)($request->attributes->get('auth_tokenable_id') ?? 0),
            ], $request);

            if (empty($payload)) {
                DB::rollBack();

                // ✅ Log: no updatable columns
                $this->logActivity(
                    $request,
                    'approve',
                    'master_approval',
                    $table,
                    isset($before->id) ? (int)$before->id : null,
                    [],
                    [],
                    [],
                    "Approve blocked: no updatable approval columns. uuid={$uuid}, division_key={$target['division_key']}"
                );

                return response()->json([
                    'success' => false,
                    'message' => "No updatable approval columns found on table: {$table}",
                ], 422);
            }

            DB::table($table)->where('uuid', $uuid)->update($payload);

            $updated = DB::table($table)->where('uuid', $uuid)->first();

            DB::commit();

            // ✅ Log AFTER commit (so it always persists on success)
            $changedFields = array_keys($payload);
            $oldValues = $this->pickFieldsFromRow($before, $changedFields);
            $newValues = $this->pickFieldsFromRow($updated, $changedFields);

            $recordId = null;
            if ($updated && isset($updated->id)) $recordId = (int)$updated->id;
            elseif ($before && isset($before->id)) $recordId = (int)$before->id;

            $this->logActivity(
                $request,
                'approve',
                'master_approval',
                $table,
                $recordId,
                $changedFields,
                $oldValues,
                $newValues,
                "Approved successfully. uuid={$uuid}, division_key={$target['division_key']}"
            );

            return response()->json([
                'success' => true,
                'message' => 'Approved successfully.',
                'division_key' => $target['division_key'],
                'table' => $table,
                'item' => $updated,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            // ✅ Log error (outside transaction)
            $this->logActivity(
                $request,
                'approve',
                'master_approval',
                $target['table'] ?? 'unknown',
                isset($target['row']->id) ? (int)$target['row']->id : null,
                null,
                null,
                null,
                "Approve failed (exception): {$e->getMessage()}. uuid={$uuid}, hint_division_key=" . ($hint ?: '')
            );

            return response()->json([
                'success' => false,
                'message' => 'Approve failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /* ============================================================
     | ✅ POST: /api/master-approval/{uuid}/reject
     |============================================================ */
    public function reject(Request $request, string $uuid)
    {
        $uuid   = trim($uuid);
        $reason = trim((string)$request->input('reason', ''));
        $hint   = $request->input('division_key'); // optional

        try {
            $target = $this->resolveTargetByUuid($uuid, $hint);

            if (!$target) {
                // ✅ Log: reject attempt but record not found
                $this->logActivity(
                    $request,
                    'reject',
                    'master_approval',
                    'unknown',
                    null,
                    null,
                    null,
                    null,
                    "Reject failed: record not found. uuid={$uuid}, hint_division_key=" . ($hint ?: '') . ", reason=" . ($reason ?: '')
                );

                return response()->json([
                    'success' => false,
                    'message' => 'Record not found for rejection.',
                    'uuid'    => $uuid,
                ], 404);
            }

            $table  = $target['table'];
            $before = $target['row'];

            DB::beginTransaction();

            // ✅ Reject logic (only updates columns that exist)
            $payload = $this->buildSafeUpdatePayload($table, [
                'is_approved'          => 0,
                'request_for_approval' => 0,
                'is_rejected'          => 1,
                'rejected_reason'      => $reason ?: null,
                'rejection_reason'     => $reason ?: null,
                'approval_status'      => 'rejected',
                'rejected_at'          => Carbon::now(),
                'rejected_by'          => (int)($request->attributes->get('auth_tokenable_id') ?? 0),
            ], $request);

            if (empty($payload)) {
                DB::rollBack();

                // ✅ Log: no updatable columns
                $this->logActivity(
                    $request,
                    'reject',
                    'master_approval',
                    $table,
                    isset($before->id) ? (int)$before->id : null,
                    [],
                    [],
                    [],
                    "Reject blocked: no updatable approval columns. uuid={$uuid}, division_key={$target['division_key']}, reason=" . ($reason ?: '')
                );

                return response()->json([
                    'success' => false,
                    'message' => "No updatable approval columns found on table: {$table}",
                ], 422);
            }

            DB::table($table)->where('uuid', $uuid)->update($payload);

            $updated = DB::table($table)->where('uuid', $uuid)->first();

            DB::commit();

            // ✅ Log AFTER commit
            $changedFields = array_keys($payload);
            $oldValues = $this->pickFieldsFromRow($before, $changedFields);
            $newValues = $this->pickFieldsFromRow($updated, $changedFields);

            $recordId = null;
            if ($updated && isset($updated->id)) $recordId = (int)$updated->id;
            elseif ($before && isset($before->id)) $recordId = (int)$before->id;

            $this->logActivity(
                $request,
                'reject',
                'master_approval',
                $table,
                $recordId,
                $changedFields,
                $oldValues,
                $newValues,
                "Rejected successfully. uuid={$uuid}, division_key={$target['division_key']}, reason=" . ($reason ?: '')
            );

            return response()->json([
                'success' => true,
                'message' => 'Rejected successfully.',
                'division_key' => $target['division_key'],
                'table' => $table,
                'reason' => $reason,
                'item' => $updated,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            // ✅ Log error (outside transaction)
            $this->logActivity(
                $request,
                'reject',
                'master_approval',
                $target['table'] ?? 'unknown',
                isset($target['row']->id) ? (int)$target['row']->id : null,
                null,
                null,
                null,
                "Reject failed (exception): {$e->getMessage()}. uuid={$uuid}, hint_division_key=" . ($hint ?: '') . ", reason=" . ($reason ?: '')
            );

            return response()->json([
                'success' => false,
                'message' => 'Reject failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
