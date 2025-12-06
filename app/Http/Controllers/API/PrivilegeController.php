<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Arr;
use Exception;

class PrivilegeController extends Controller
{
    /**
     * List privileges (filter by module_id optional - accepts module id or uuid)
     */
    public function index(Request $request)
    {
        try {
            $perPage   = max(1, min(200, (int) $request->query('per_page', 20)));
            $moduleKey = $request->query('module_id');

            // Build select columns defensively (include module name)
            $cols = [
                'privileges.id',
                'privileges.uuid',
                'privileges.module_id',
                'privileges.action',
                'privileges.description',
                'privileges.created_at',
                'privileges.updated_at',
                'modules.name as module_name',
            ];
            if (Schema::hasColumn('privileges', 'order_no')) {
                $cols[] = 'privileges.order_no';
            }
            if (Schema::hasColumn('privileges', 'status')) {
                $cols[] = 'privileges.status';
            }

            $query = DB::table('privileges')
                ->leftJoin('modules', 'modules.id', '=', 'privileges.module_id')
                ->whereNull('privileges.deleted_at')
                ->select($cols);

            // Module filtering by id or uuid
            if ($moduleKey) {
                if (ctype_digit((string) $moduleKey)) {
                    $query->where('privileges.module_id', (int) $moduleKey);
                } elseif (Str::isUuid((string) $moduleKey)) {
                    $module = DB::table('modules')
                        ->where('uuid', (string) $moduleKey)
                        ->whereNull('deleted_at')
                        ->first();
                    if ($module) {
                        $query->where('privileges.module_id', $module->id);
                    } else {
                        return response()->json([
                            'data'       => [],
                            'pagination' => ['page' => 1, 'per_page' => $perPage, 'total' => 0, 'last_page' => 1],
                        ]);
                    }
                } else {
                    // ignore invalid moduleKey
                }
            }

            // STATUS handling:
            // - if caller passed ?status=... we respect it
            //   - status=archived => only archived
            //   - status=all => include all (no status filter)
            // - if no status provided, exclude archived by default
            if ($request->filled('status') && Schema::hasColumn('privileges', 'status')) {
                $status = (string) $request->query('status');
                if ($status === 'all') {
                    // no status filter; return everything (subject to deleted_at)
                } elseif ($status === 'archived') {
                    $query->where('privileges.status', 'archived');
                } else {
                    $query->where('privileges.status', $status);
                }
            } else {
                // default: exclude archived (if status column exists)
                if (Schema::hasColumn('privileges', 'status')) {
                    $query->where(function ($q) {
                        $q->whereNull('privileges.status')
                          ->orWhere('privileges.status', '!=', 'archived');
                    });
                }
            }

            // stable order for pagination
            $paginator = $query->orderBy('privileges.id', 'desc')->paginate($perPage);

            return response()->json([
                'data'       => $paginator->items(),
                'pagination' => [
                    'page'      => $paginator->currentPage(),
                    'per_page'  => $paginator->perPage(),
                    'total'     => $paginator->total(),
                    'last_page' => $paginator->lastPage(),
                ],
            ]);
        } catch (\Throwable $e) {
            // Log error
            try {
                \Log::error('PrivilegeController::index exception: '.$e->getMessage().' in '.$e->getFile().':'.$e->getLine());
            } catch (\Throwable $inner) {
                // ignore logging failure
            }

            // DEBUG INFO: include message and short trace (remove in production)
            $trace = collect($e->getTrace())->map(function ($t) {
                return Arr::only($t, ['file', 'line', 'function', 'class']);
            })->all();

            return response()->json([
                'message' => 'Server error fetching privileges (see logs)',
                'error'   => $e->getMessage(),
                'trace'   => $trace,
            ], 500);
        }
    }

    /**
     * Bin (soft-deleted privileges)
     */
    public function bin(Request $request)
    {
        $perPage = max(1, min(200, (int) $request->query('per_page', 20)));

        $query = DB::table('privileges')->whereNotNull('deleted_at')->orderBy('deleted_at', 'desc');

        $paginator = $query->paginate($perPage);

        return response()->json([
            'data'       => $paginator->items(),
            'pagination' => [
                'page'      => $paginator->currentPage(),
                'per_page'  => $paginator->perPage(),
                'total'     => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    /**
     * Store privilege(s) (action unique per module).
     * Accepts:
     *  - Single: module_id, action, description
     *  - Bulk:  module_id, privileges: [ {action, description, order_no?}, ... ]
     *    (module_id is shared for all items in bulk)
     */
    public function store(Request $request)
    {
        // ---- BULK MODE: privileges[] ----
        if ($request->has('privileges') && is_array($request->input('privileges'))) {
            $v = Validator::make($request->all(), [
                'module_id'        => 'required',
                'privileges'       => 'required|array|min:1',
                'privileges.*.action'      => 'required|string|max:50',
                'privileges.*.description' => 'nullable|string',
                'privileges.*.order_no'    => 'nullable|integer',
            ]);

            if ($v->fails()) {
                return response()->json(['errors' => $v->errors()], 422);
            }

            // Resolve module_id: allow numeric id or uuid
            $rawModule = $request->input('module_id');
            $module    = null;
            if (ctype_digit((string) $rawModule)) {
                $module = DB::table('modules')
                    ->where('id', (int) $rawModule)
                    ->whereNull('deleted_at')
                    ->first();
            } elseif (Str::isUuid((string) $rawModule)) {
                $module = DB::table('modules')
                    ->where('uuid', (string) $rawModule)
                    ->whereNull('deleted_at')
                    ->first();
            } else {
                return response()->json(['errors' => ['module_id' => ['Invalid module identifier']]], 422);
            }

            if (! $module) {
                return response()->json(['errors' => ['module_id' => ['Module not found']]], 422);
            }

            $moduleId     = (int) $module->id;
            $userId       = optional($request->user())->id ?? null;
            $ip           = $request->ip();
            $now          = now();
            $created      = [];
            $skipped      = []; // conflicts / duplicates
            $errors       = [];
            $seenActions  = []; // to prevent duplicates in same payload

            try {
                DB::transaction(function () use ($request, $moduleId, $userId, $ip, $now, &$created, &$skipped, &$errors, &$seenActions) {
                    foreach ($request->input('privileges', []) as $idx => $row) {
                        $action = trim((string) ($row['action'] ?? ''));

                        if ($action === '') {
                            $errors[] = [
                                'index'  => $idx,
                                'action' => $action,
                                'error'  => 'Action is empty',
                            ];
                            continue;
                        }

                        // Avoid duplicate actions within same payload
                        if (in_array(strtolower($action), $seenActions, true)) {
                            $skipped[] = [
                                'index'  => $idx,
                                'action' => $action,
                                'reason' => 'Duplicate action in same request payload',
                            ];
                            continue;
                        }

                        // Composite uniqueness (module_id + action) in DB
                        $exists = DB::table('privileges')
                            ->where('module_id', $moduleId)
                            ->where('action', $action)
                            ->whereNull('deleted_at')
                            ->exists();

                        if ($exists) {
                            $skipped[] = [
                                'index'  => $idx,
                                'action' => $action,
                                'reason' => 'Action already exists for this module',
                            ];
                            continue;
                        }

                        $payload = [
                            'uuid'        => (string) Str::uuid(),
                            'module_id'   => $moduleId,
                            'action'      => $action,
                            'description' => $row['description'] ?? null,
                            'created_at'  => $now,
                            'updated_at'  => $now,
                            'created_by'  => $userId,
                            'created_at_ip' => $ip,
                            'deleted_at'  => null,
                        ];

                        if (Schema::hasColumn('privileges', 'order_no') && isset($row['order_no'])) {
                            $payload['order_no'] = (int) $row['order_no'];
                        }

                        if (Schema::hasColumn('privileges', 'status') && isset($row['status'])) {
                            $payload['status'] = $row['status'];
                        }

                        $id = DB::table('privileges')->insertGetId($payload);
                        $created[] = DB::table('privileges')->where('id', $id)->first();
                        $seenActions[] = strtolower($action);
                    }
                });

                return response()->json([
                    'created'          => $created,
                    'skipped_conflict' => $skipped,
                    'errors'           => $errors,
                    'message'          => 'Bulk privileges processed',
                ], 201);
            } catch (Exception $e) {
                return response()->json([
                    'message' => 'Could not create privileges (bulk)',
                    'error'   => $e->getMessage(),
                ], 500);
            }
        }

        // ---- SINGLE MODE (existing behavior) ----
        $v = Validator::make($request->all(), [
            'module_id'   => 'required',
            'action'      => 'required|string|max:50',
            'description' => 'nullable|string',
        ]);

        if ($v->fails()) {
            return response()->json(['errors' => $v->errors()], 422);
        }

        // Resolve module_id: allow numeric id or uuid
        $rawModule = $request->input('module_id');
        $module    = null;
        if (ctype_digit((string) $rawModule)) {
            $module = DB::table('modules')->where('id', (int) $rawModule)->whereNull('deleted_at')->first();
        } elseif (Str::isUuid((string) $rawModule)) {
            $module = DB::table('modules')->where('uuid', (string) $rawModule)->whereNull('deleted_at')->first();
        } else {
            return response()->json(['errors' => ['module_id' => ['Invalid module identifier']]], 422);
        }

        if (! $module) {
            return response()->json(['errors' => ['module_id' => ['Module not found']]], 422);
        }

        $moduleId = (int) $module->id;
        $action   = trim($request->input('action'));

        // Composite uniqueness (module_id + action)
        $exists = DB::table('privileges')
            ->where('module_id', $moduleId)
            ->where('action', $action)
            ->whereNull('deleted_at')
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Action already exists for this module'], 409);
        }

        $userId = optional($request->user())->id ?? null;
        $ip     = $request->ip();

        try {
            $id = DB::transaction(function () use ($moduleId, $action, $request, $userId, $ip) {
                $payload = [
                    'uuid'         => (string) Str::uuid(),
                    'module_id'    => $moduleId,
                    'action'       => $action,
                    'description'  => $request->input('description'),
                    'created_at'   => now(),
                    'updated_at'   => now(),
                    'created_by'   => $userId,
                    'created_at_ip'=> $ip,
                    'deleted_at'   => null,
                ];

                if (Schema::hasColumn('privileges', 'order_no') && $request->has('order_no')) {
                    $payload['order_no'] = (int) $request->input('order_no');
                }

                if (Schema::hasColumn('privileges', 'status') && $request->has('status')) {
                    $payload['status'] = $request->input('status');
                }

                return DB::table('privileges')->insertGetId($payload);
            });

            $priv = DB::table('privileges')->where('id', $id)->first();
            return response()->json(['privilege' => $priv], 201);
        } catch (Exception $e) {
            return response()->json(['message' => 'Could not create privilege', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Resolve privilege by numeric id or uuid.
     */
    protected function resolvePrivilege($identifier, $includeDeleted = false)
    {
        $q = DB::table('privileges');
        if (! $includeDeleted) {
            $q->whereNull('deleted_at');
        }

        if (ctype_digit((string) $identifier)) {
            $q->where('id', (int) $identifier);
        } elseif (Str::isUuid((string) $identifier)) {
            $q->where('uuid', (string) $identifier);
        } else {
            return null;
        }

        return $q->first();
    }

    /**
     * Show privilege (accepts id or uuid)
     */
    public function show(Request $request, $identifier)
    {
        $priv = $this->resolvePrivilege($identifier, false);
        if (! $priv) {
            return response()->json(['message' => 'Privilege not found'], 404);
        }
        return response()->json(['privilege' => $priv]);
    }

    /**
     * Update single privilege (accepts id or uuid). module_id may be id or uuid.
     */
    public function update(Request $request, $identifier)
    {
        $priv = $this->resolvePrivilege($identifier, false);
        if (! $priv) {
            return response()->json(['message' => 'Privilege not found'], 404);
        }

        $v = Validator::make($request->all(), [
            'module_id'   => 'sometimes|required',
            'action'      => 'sometimes|required|string|max:50',
            'description' => 'nullable|string',
        ]);

        if ($v->fails()) {
            return response()->json(['errors' => $v->errors()], 422);
        }

        // determine new module id (if provided) else keep existing
        $newModuleId = $priv->module_id;
        if ($request->has('module_id')) {
            $rawModule = $request->input('module_id');
            $module    = null;
            if (ctype_digit((string) $rawModule)) {
                $module = DB::table('modules')->where('id', (int) $rawModule)->whereNull('deleted_at')->first();
            } elseif (Str::isUuid((string) $rawModule)) {
                $module = DB::table('modules')->where('uuid', (string) $rawModule)->whereNull('deleted_at')->first();
            } else {
                return response()->json(['errors' => ['module_id' => ['Invalid module identifier']]], 422);
            }
            if (! $module) {
                return response()->json(['errors' => ['module_id' => ['Module not found']]], 422);
            }
            $newModuleId = (int) $module->id;
        }

        $newAction = $request->has('action') ? trim($request->input('action')) : $priv->action;

        // Check composite uniqueness (except current record)
        $exists = DB::table('privileges')
            ->where('module_id', $newModuleId)
            ->where('action', $newAction)
            ->whereNull('deleted_at')
            ->where('id', '!=', $priv->id)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Action already exists for this module'], 409);
        }

        $update = array_filter([
            'module_id'   => $request->has('module_id') ? $newModuleId : null,
            'action'      => $request->has('action') ? $newAction : null,
            'description' => $request->has('description') ? $request->input('description') : null,
            'updated_at'  => now(),
        ], function ($v) {
            return $v !== null;
        });

        if (empty($update) || (count($update) === 1 && array_key_exists('updated_at', $update))) {
            return response()->json(['message' => 'Nothing to update'], 400);
        }

        try {
            DB::transaction(function () use ($priv, $update) {
                DB::table('privileges')->where('id', $priv->id)->update($update);
            });

            $priv = DB::table('privileges')->where('id', $priv->id)->first();
            return response()->json(['privilege' => $priv]);
        } catch (Exception $e) {
            return response()->json(['message' => 'Could not update privilege', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * BULK UPDATE privileges.
     *
     * Expected payload:
     * {
     *   "privileges": [
     *     {
     *       "id" : 1 OR "uuid" : "....",
     *       "module_id": 3 or "module_uuid": "...", // optional, to move module
     *       "action": "add",                        // optional
     *       "description": "..."                    // optional
     *     },
     *     ...
     *   ]
     * }
     *
     * For uniqueness we still enforce (module_id + action) per record.
     */
    public function bulkUpdate(Request $request)
    {
        $v = Validator::make($request->all(), [
            'privileges'               => 'required|array|min:1',
            'privileges.*.id'          => 'nullable|integer',
            'privileges.*.uuid'        => 'nullable|string',
            'privileges.*.module_id'   => 'nullable',
            'privileges.*.action'      => 'nullable|string|max:50',
            'privileges.*.description' => 'nullable|string',
        ]);

        if ($v->fails()) {
            return response()->json(['errors' => $v->errors()], 422);
        }

        $updated  = [];
        $skipped  = [];
        $errors   = [];

        try {
            DB::transaction(function () use ($request, &$updated, &$skipped, &$errors) {
                $rows = $request->input('privileges', []);

                foreach ($rows as $idx => $row) {
                    $identifier = $row['id'] ?? $row['uuid'] ?? null;
                    if (! $identifier) {
                        $errors[] = [
                            'index'  => $idx,
                            'error'  => 'id or uuid is required for bulk update item',
                        ];
                        continue;
                    }

                    // Resolve current privilege
                    $priv = $this->resolvePrivilege($identifier, false);
                    if (! $priv) {
                        $errors[] = [
                            'index'      => $idx,
                            'identifier' => $identifier,
                            'error'      => 'Privilege not found',
                        ];
                        continue;
                    }

                    // Determine new module id if given, else existing
                    $newModuleId = $priv->module_id;
                    if (isset($row['module_id'])) {
                        $rawModule = $row['module_id'];
                        $module    = null;

                        if (ctype_digit((string) $rawModule)) {
                            $module = DB::table('modules')
                                ->where('id', (int) $rawModule)
                                ->whereNull('deleted_at')
                                ->first();
                        } elseif (Str::isUuid((string) $rawModule)) {
                            $module = DB::table('modules')
                                ->where('uuid', (string) $rawModule)
                                ->whereNull('deleted_at')
                                ->first();
                        } else {
                            $errors[] = [
                                'index'      => $idx,
                                'identifier' => $identifier,
                                'error'      => 'Invalid module identifier',
                            ];
                            continue;
                        }

                        if (! $module) {
                            $errors[] = [
                                'index'      => $idx,
                                'identifier' => $identifier,
                                'error'      => 'Module not found',
                            ];
                            continue;
                        }

                        $newModuleId = (int) $module->id;
                    }

                    $newAction = array_key_exists('action', $row)
                        ? trim((string) $row['action'])
                        : $priv->action;

                    // Check composite uniqueness (except current record)
                    $exists = DB::table('privileges')
                        ->where('module_id', $newModuleId)
                        ->where('action', $newAction)
                        ->whereNull('deleted_at')
                        ->where('id', '!=', $priv->id)
                        ->exists();

                    if ($exists) {
                        $skipped[] = [
                            'index'      => $idx,
                            'identifier' => $identifier,
                            'reason'     => 'Action already exists for this module',
                        ];
                        continue;
                    }

                    $update = [
                        'updated_at' => now(),
                    ];

                    if (isset($row['module_id'])) {
                        $update['module_id'] = $newModuleId;
                    }
                    if (array_key_exists('action', $row)) {
                        $update['action'] = $newAction;
                    }
                    if (array_key_exists('description', $row)) {
                        $update['description'] = $row['description'];
                    }

                    if (Schema::hasColumn('privileges', 'order_no') && array_key_exists('order_no', $row)) {
                        $update['order_no'] = (int) $row['order_no'];
                    }

                    if (Schema::hasColumn('privileges', 'status') && array_key_exists('status', $row)) {
                        $update['status'] = $row['status'];
                    }

                    // If nothing except updated_at -> skip as no-op
                    if (count($update) === 1) {
                        $skipped[] = [
                            'index'      => $idx,
                            'identifier' => $identifier,
                            'reason'     => 'Nothing to update',
                        ];
                        continue;
                    }

                    DB::table('privileges')->where('id', $priv->id)->update($update);

                    $updated[] = DB::table('privileges')->where('id', $priv->id)->first();
                }
            });

            return response()->json([
                'updated'          => $updated,
                'skipped_conflict' => $skipped,
                'errors'           => $errors,
                'message'          => 'Bulk update processed',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Could not perform bulk update',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Soft delete privilege (accepts id or uuid)
     */
    public function destroy(Request $request, $identifier)
    {
        $priv = $this->resolvePrivilege($identifier, false);
        if (! $priv) {
            return response()->json(['message' => 'Privilege not found or already deleted'], 404);
        }

        try {
            DB::table('privileges')->where('id', $priv->id)->update(['deleted_at' => now(), 'updated_at' => now()]);
            return response()->json(['message' => 'Privilege soft-deleted']);
        } catch (Exception $e) {
            return response()->json(['message' => 'Could not delete privilege', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Restore privilege (accepts id or uuid)
     */
    public function restore(Request $request, $identifier)
    {
        $priv = $this->resolvePrivilege($identifier, true);
        if (! $priv || $priv->deleted_at === null) {
            return response()->json(['message' => 'Privilege not found or not deleted'], 404);
        }

        try {
            DB::table('privileges')->where('id', $priv->id)->update(['deleted_at' => null, 'updated_at' => now()]);
            $priv = DB::table('privileges')->where('id', $priv->id)->first();
            return response()->json(['privilege' => $priv, 'message' => 'Privilege restored']);
        } catch (Exception $e) {
            return response()->json(['message' => 'Could not restore privilege', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Archived privileges (status = 'archived', not soft-deleted)
     */
    public function archived(Request $request)
    {
        try {
            $perPage = max(1, min(200, (int) $request->query('per_page', 20)));

            // ensure we select only existing columns to avoid "unknown column" issues
            $cols = [
                'privileges.id',
                'privileges.uuid',
                'privileges.module_id',
                'privileges.action',
                'privileges.description',
                'privileges.created_at',
                'privileges.updated_at',
            ];
            if (Schema::hasColumn('privileges', 'order_no')) {
                $cols[] = 'privileges.order_no';
            }
            if (Schema::hasColumn('privileges', 'status')) {
                $cols[] = 'privileges.status';
            }

            $query = DB::table('privileges')
                ->whereNull('deleted_at')
                ->select($cols)
                ->where(function ($q) {
                    if (Schema::hasColumn('privileges', 'status')) {
                        $q->where('privileges.status', 'archived');
                    } else {
                        // no status column -> return empty
                        $q->whereRaw('0 = 1');
                    }
                })
                ->orderBy('privileges.id', 'desc');

            $paginator = $query->paginate($perPage);

            return response()->json([
                'data'       => $paginator->items(),
                'pagination' => [
                    'page'      => $paginator->currentPage(),
                    'per_page'  => $paginator->perPage(),
                    'total'     => $paginator->total(),
                    'last_page' => $paginator->lastPage(),
                ],
            ], 200);
        } catch (\Throwable $e) {
            \Log::error('PrivilegeController::archived exception: '.$e->getMessage().' in '.$e->getFile().':'.$e->getLine());
            return response()->json(['message' => 'Server error fetching archived privileges'], 500);
        }
    }

    /**
     * Archive a privilege (set status = 'archived') - only if `status` column exists
     */
    public function archive(Request $request, $identifier)
    {
        if (! Schema::hasColumn('privileges', 'status')) {
            return response()->json(['message' => 'Archive not supported for privileges (no status column)'], 400);
        }

        $priv = $this->resolvePrivilege($identifier, false);
        if (! $priv) {
            return response()->json(['message' => 'Privilege not found'], 404);
        }

        try {
            DB::table('privileges')->where('id', $priv->id)->update(['status' => 'archived', 'updated_at' => now()]);
            return response()->json(['message' => 'Privilege archived']);
        } catch (Exception $e) {
            return response()->json(['message' => 'Could not archive privilege', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Unarchive a privilege (set status = 'draft') - only if `status` column exists
     */
    public function unarchive(Request $request, $identifier)
    {
        if (! Schema::hasColumn('privileges', 'status')) {
            return response()->json(['message' => 'Unarchive not supported for privileges (no status column)'], 400);
        }

        $priv = $this->resolvePrivilege($identifier, false);
        if (! $priv) {
            return response()->json(['message' => 'Privilege not found'], 404);
        }

        try {
            DB::table('privileges')->where('id', $priv->id)->update(['status' => 'draft', 'updated_at' => now()]);
            return response()->json(['message' => 'Privilege unarchived']);
        } catch (Exception $e) {
            return response()->json(['message' => 'Could not unarchive privilege', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Force delete permanently (irreversible)
     */
    public function forceDelete(Request $request, $identifier)
    {
        $priv = $this->resolvePrivilege($identifier, true);
        if (! $priv) {
            return response()->json(['message' => 'Privilege not found'], 404);
        }

        try {
            DB::transaction(function () use ($priv) {
                DB::table('privileges')->where('id', $priv->id)->delete();
            });
            return response()->json(['message' => 'Privilege permanently deleted']);
        } catch (Exception $e) {
            return response()->json(['message' => 'Could not permanently delete privilege', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Reorder privileges — expects { ids: [id1,id2,id3,...] }
     * It will update order_no according to array position (0..n-1)
     * Requires privileges.order_no column to exist.
     */
    public function reorder(Request $request)
    {
        if (! Schema::hasColumn('privileges', 'order_no')) {
            return response()->json(['message' => 'Reorder not supported: privileges.order_no column missing'], 400);
        }

        $v = Validator::make($request->all(), [
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer|min:1',
        ]);
        if ($v->fails()) {
            return response()->json(['errors' => $v->errors()], 422);
        }

        $ids = $request->input('ids');

        try {
            DB::transaction(function () use ($ids) {
                foreach ($ids as $idx => $id) {
                    DB::table('privileges')->where('id', $id)->update(['order_no' => $idx, 'updated_at' => now()]);
                }
            });
            return response()->json(['message' => 'Order updated']);
        } catch (Exception $e) {
            return response()->json(['message' => 'Could not update order', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Return privileges for a specific module (accepts numeric id or uuid)
     */
    public function forModule($identifier, Request $request = null)
    {
        try {
            // resolve module id
            $module = null;
            if (ctype_digit((string) $identifier)) {
                $module = DB::table('modules')->where('id', (int) $identifier)->whereNull('deleted_at')->first();
            } elseif (Str::isUuid((string) $identifier)) {
                $module = DB::table('modules')->where('uuid', (string) $identifier)->whereNull('deleted_at')->first();
            }

            if (! $module) {
                return response()->json([
                    'data'       => [],
                    'pagination' => ['page' => 1, 'per_page' => 20, 'total' => 0, 'last_page' => 1],
                ], 200);
            }

            $perPage = max(1, min(200, (int) request()->query('per_page', 20)));

            $query = DB::table('privileges')
                ->whereNull('deleted_at')
                ->where('module_id', $module->id)
                ->orderBy('id', 'desc');

            $paginator = $query->paginate($perPage);

            return response()->json([
                'data'       => $paginator->items(),
                'pagination' => [
                    'page'      => $paginator->currentPage(),
                    'per_page'  => $paginator->perPage(),
                    'total'     => $paginator->total(),
                    'last_page' => $paginator->lastPage(),
                ],
            ], 200);
        } catch (\Throwable $e) {
            \Log::error('PrivilegeController::forModule error: '.$e->getMessage().' in '.$e->getFile().':'.$e->getLine());
            return response()->json(['message' => 'Unable to fetch privileges for module'], 500);
        }
    }
}
