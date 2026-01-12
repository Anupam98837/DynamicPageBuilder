<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Roles that can see ALL departments data
     */
    private const ALL_ACCESS_ROLES = ['admin', 'director', 'principal'];

    /**
     * All roles allowed to call dashboard
     */
    private const ALLOWED_ROLES = [
        'admin',
        'director',
        'principal',
        'hod',
        'faculty',
        'technical_assistant',
        'it_person',
        'placement_officer',
        'student',
    ];

    /** cache schema checks */
    protected array $colCache = [];

    /* =========================
     * Auth / helpers
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
        ], $extra));
    }

    private function hasCol(string $table, string $col): bool
    {
        $k = $table . '.' . $col;
        if (!array_key_exists($k, $this->colCache)) {
            $this->colCache[$k] = Schema::hasColumn($table, $col);
        }
        return (bool) $this->colCache[$k];
    }

    private function maybeJson($v)
    {
        if ($v === null) return null;
        if (is_array($v) || is_object($v)) return $v;
        $s = trim((string)$v);
        if ($s === '') return null;
        try { return json_decode($s, true, 512, JSON_THROW_ON_ERROR); }
        catch (\Throwable $e) { return $v; }
    }

    /**
     * Resolve actor department_id (best-effort)
     * Tries:
     * - request attributes (if your middleware sets it)
     * - users.department_id column (if exists)
     * - users.metadata->department_id (if metadata is JSON)
     */
    private function actorDepartmentId(Request $r): ?int
    {
        $attr = $r->attributes->get('auth_department_id');
        if ($attr !== null && is_numeric($attr)) return (int) $attr;

        $a = $this->actor($r);
        if (!$a['id']) return null;

        $u = DB::table('users')->where('id', $a['id'])->first();
        if (!$u) return null;

        if ($this->hasCol('users', 'department_id')) {
            $did = $u->department_id ?? null;
            if ($did !== null && is_numeric($did)) return (int) $did;
        }

        $meta = $this->maybeJson($u->metadata ?? null);
        if (is_array($meta) && isset($meta['department_id']) && is_numeric($meta['department_id'])) {
            return (int) $meta['department_id'];
        }

        return null;
    }

    /**
     * Decide dashboard scope:
     * - admin/director/principal => all
     * - others => department-limited (if department_id resolved)
     */
    private function scope(Request $r): array
    {
        $a = $this->actor($r);

        $isAll = in_array((string)$a['role'], self::ALL_ACCESS_ROLES, true);
        $dept  = $isAll ? null : $this->actorDepartmentId($r);

        return [
            'actor' => $a,
            'all'   => $isAll,
            'dept'  => $dept, // nullable if not resolved
        ];
    }

    /**
     * Get actor user info (for dashboard header)
     */
    private function actorUser(Request $r): ?object
    {
        $a = $this->actor($r);
        if (!$a['id']) return null;

        $cols = ['id','uuid','slug','name','email','image','role','role_short_form','status','last_login_at','last_login_ip','metadata','created_at','updated_at'];
        if ($this->hasCol('users', 'department_id')) $cols[] = 'department_id';

        return DB::table('users')
            ->select($cols)
            ->where('id', $a['id'])
            ->whereNull('deleted_at')
            ->first();
    }

    /**
     * Department filter for USERS table (used to filter logs by performed_by)
     * - If users.department_id exists => filter by that
     * - Else if users.metadata JSON has department_id => filter by JSON_EXTRACT
     */
    private function applyUsersDeptFilter($query, int $deptId): void
    {
        if ($this->hasCol('users', 'department_id')) {
            $query->where('users.department_id', $deptId);
            return;
        }

        // fallback: metadata JSON
        $query->whereRaw("JSON_EXTRACT(users.metadata, '$.department_id') = ?", [$deptId]);
    }

    /* ============================================================
     * ✅ NEW: GET /api/admin/dashboard  (matches your Blade fetch)
     * - Activity + Recent come from user_data_activity_log
     * - Returns: hero, kpis, activity chart, quick_actions, alerts, recent table
     * ============================================================ */
    public function adminDashboard(Request $request)
    {
        if ($resp = $this->requireRole($request, self::ALLOWED_ROLES)) return $resp;

        $sc   = $this->scope($request);
        $user = $this->actorUser($request);

        // -------------------------
        // KPI counts (same style you used before)
        // -------------------------
        $countTable = function (string $table, ?int $deptId) {
            if (!Schema::hasTable($table)) return 0;

            $q = DB::table($table);

            if (Schema::hasColumn($table, 'deleted_at')) {
                $q->whereNull('deleted_at');
            }

            if ($deptId !== null && Schema::hasColumn($table, 'department_id')) {
                $q->where(function ($w) use ($deptId) {
                    $w->whereNull('department_id')->orWhere('department_id', $deptId);
                });
            }

            return (int) $q->count();
        };

        $countPlacementNotices = function (?int $deptId) {
            if (!Schema::hasTable('placement_notices')) return 0;

            $q = DB::table('placement_notices');
            if (Schema::hasColumn('placement_notices', 'deleted_at')) $q->whereNull('deleted_at');

            if ($deptId !== null) {
                $q->where(function ($w) use ($deptId) {
                    $w->whereNull('department_ids')
                      ->orWhereRaw("JSON_CONTAINS(department_ids, CAST(? AS JSON))", [$deptId]);
                });
            }

            return (int) $q->count();
        };

        $totalUsers = (int) DB::table('users')->whereNull('deleted_at')->count();
        $totalDepartments = (int) (Schema::hasTable('departments')
            ? DB::table('departments')->whereNull('deleted_at')->count()
            : 0);

        $moduleTotals = [
            'announcements'      => $countTable('announcements', $sc['dept']),
            'notices'            => $countTable('notices', $sc['dept']),
            'events'             => $countTable('events', $sc['dept']),
            'student_activities' => $countTable('student_activities', $sc['dept']),
            'gallery'            => $countTable('gallery', $sc['dept']),
            'courses'            => $countTable('courses', $sc['dept']),
            'recruiters'         => $countTable('recruiters', $sc['dept']),
            'success_stories'    => $countTable('success_stories', $sc['dept']),
            'placement_notices'  => $countPlacementNotices($sc['dept']),
            'placed_students'    => $countTable('placed_students', $sc['dept']),
        ];

        // -------------------------
        // ✅ Recent activity from user_data_activity_log
        // -------------------------
        $recentLimit = (int) ($request->query('recent_limit', 20) ?: 20);
        $recentLimit = max(5, min(50, $recentLimit));

        $recentQ = DB::table('user_data_activity_log as l')
            ->leftJoin('users', 'users.id', '=', 'l.performed_by')
            ->select([
                'l.id',
                'l.performed_by',
                'l.performed_by_role',
                'l.ip',
                'l.user_agent',
                'l.activity',
                'l.module',
                'l.table_name',
                'l.record_id',
                'l.changed_fields',
                'l.old_values',
                'l.new_values',
                'l.log_note',
                'l.created_at',

                'users.uuid as user_uuid',
                'users.slug as user_slug',
                'users.name as user_name',
                'users.email as user_email',
                'users.role as user_role',
                'users.role_short_form as user_role_short',
            ])
            ->orderBy('l.created_at', 'desc');

        // if dept-scoped (non-admin) => try to limit logs to actor department users
        if (!$sc['all'] && $sc['dept'] !== null) {
            $this->applyUsersDeptFilter($recentQ, (int)$sc['dept']);
        }

        $recentRows = $recentQ->limit($recentLimit)->get();

        $recent = [];
        foreach ($recentRows as $r) {
            $recent[] = [
                'actor'  => trim((string)($r->user_name ?? '')) !== ''
                    ? (string)$r->user_name
                    : ('User#' . (int)$r->performed_by),
                'role'   => (string)($r->performed_by_role ?? $r->user_role ?? ''),
                'action' => strtoupper((string)($r->activity ?? '')),
                'module' => (string)($r->module ?? ''),
                'target' => (string)($r->table_name ?? ''),
                'record' => $r->record_id !== null ? (int)$r->record_id : null,
                'note'   => (string)($r->log_note ?? ''),
                'ip'     => (string)($r->ip ?? ''),
                'time'   => $r->created_at ? Carbon::parse($r->created_at)->toDateTimeString() : null,

                // optional rich payloads
                'changed_fields' => $this->maybeJson($r->changed_fields),
                'old_values'     => $this->maybeJson($r->old_values),
                'new_values'     => $this->maybeJson($r->new_values),

                // user reference
                'user_uuid'      => (string)($r->user_uuid ?? ''),
                'user_slug'      => (string)($r->user_slug ?? ''),
                'user_role_short'=> (string)($r->user_role_short ?? ''),
            ];
        }

        // -------------------------
        // ✅ Activity chart from logs (Last 7 days)
        // -------------------------
        $days = 7;
        $today = Carbon::now()->startOfDay();
        $from = $today->copy()->subDays($days - 1);

        // initialize labels
        $labels = [];
        $map = [];
        for ($i=0; $i<$days; $i++) {
            $d = $from->copy()->addDays($i);
            $k = $d->format('Y-m-d');
            $labels[] = $d->format('d M'); // e.g. 08 Jan
            $map[$k] = 0;
        }

        $chartQ = DB::table('user_data_activity_log as l')
            ->leftJoin('users', 'users.id', '=', 'l.performed_by')
            ->whereBetween('l.created_at', [$from->copy()->startOfDay(), $today->copy()->endOfDay()]);

        if (!$sc['all'] && $sc['dept'] !== null) {
            $this->applyUsersDeptFilter($chartQ, (int)$sc['dept']);
        }

        $chartRows = $chartQ
            ->select(
                DB::raw("DATE(l.created_at) as d"),
                DB::raw("COUNT(*) as c")
            )
            ->groupBy('d')
            ->get();

        foreach ($chartRows as $cr) {
            $dk = (string)($cr->d ?? '');
            if ($dk !== '' && array_key_exists($dk, $map)) {
                $map[$dk] = (int)($cr->c ?? 0);
            }
        }

        $values = array_values($map);

        // -------------------------
        // Quick actions (still backend-driven)
        // -------------------------
        $role = (string)($sc['actor']['role'] ?? '');
        $quickActions = [];

        // You can adjust URLs as per your admin routing
        $quickActions[] = ['title' => 'Manage Users', 'url' => '/admin/users', 'icon' => 'fa-users', 'hint' => 'Create / update user accounts'];
        $quickActions[] = ['title' => 'Manage Notices', 'url' => '/admin/notices', 'icon' => 'fa-bullhorn', 'hint' => 'Publish important updates'];
        $quickActions[] = ['title' => 'Manage Events', 'url' => '/admin/events', 'icon' => 'fa-calendar-days', 'hint' => 'Add / update events'];

        if (in_array($role, self::ALL_ACCESS_ROLES, true)) {
            $quickActions[] = ['title' => 'Departments', 'url' => '/admin/departments', 'icon' => 'fa-building-columns', 'hint' => 'All departments overview'];
        }

        // -------------------------
        // Alerts (simple example; you can extend rules)
        // -------------------------
        $alerts = [];

        // Example: show if last 24h activity is 0 (scoped)
        $last24Q = DB::table('user_data_activity_log as l')
            ->leftJoin('users', 'users.id', '=', 'l.performed_by')
            ->where('l.created_at', '>=', Carbon::now()->subDay());

        if (!$sc['all'] && $sc['dept'] !== null) {
            $this->applyUsersDeptFilter($last24Q, (int)$sc['dept']);
        }

        $last24 = (int)$last24Q->count();

        if ($last24 === 0) {
            $alerts[] = [
                'type'  => 'warning',
                'icon'  => 'fa-triangle-exclamation',
                'title' => 'No activity in last 24 hours',
                'sub'   => 'Check if logging middleware is running or users are inactive.',
            ];
        }

        // Example: lots of failed/blocked actions could be tracked in log_note or activity = "unauthorized" (if you use it)
        // (kept minimal to avoid wrong assumptions)

        // -------------------------
        // Response shape (matches your Blade JS normalizer)
        // -------------------------
        $kpis = [
            [
                'label' => 'Users',
                'value' => $totalUsers,
                'icon'  => 'fa-user-group',
                'sub'   => $sc['all'] ? 'All users' : 'Visible scope',
            ],
            [
                'label' => 'Departments',
                'value' => $totalDepartments,
                'icon'  => 'fa-building',
                'sub'   => $sc['all'] ? 'All departments' : 'May be limited',
                'badge' => $sc['all'] ? 'ALL' : 'SCOPED',
            ],
            [
                'label' => 'Notices',
                'value' => (int)($moduleTotals['notices'] ?? 0),
                'icon'  => 'fa-bullhorn',
                'sub'   => 'Published in scope',
            ],
            [
                'label' => 'Events',
                'value' => (int)($moduleTotals['events'] ?? 0),
                'icon'  => 'fa-calendar-check',
                'sub'   => 'Published in scope',
            ],
        ];

        $this->logWithActor('msit.admin.dashboard', $request, [
            'scope_all' => $sc['all'],
            'dept_id'   => $sc['dept'],
            'recent_limit' => $recentLimit,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'hero' => [
                    'title'      => 'Admin Dashboard',
                    'sub'        => $sc['all']
                        ? 'You have organization-wide access.'
                        : ('You have department scoped access' . ($sc['dept'] ? ' (Dept ID: '.$sc['dept'].')' : '.')),
                    'role'       => (string)($sc['actor']['role'] ?? ''),
                    'updated_at' => Carbon::now()->toDateTimeString(),
                ],

                // user info for header/profile
                'user' => $user,

                'scope' => [
                    'all_departments' => (bool)$sc['all'],
                    'department_id'   => $sc['dept'],
                ],

                'kpis' => $kpis,
                'kpi_note' => 'All values are fetched dynamically from API & database.',

                'activity' => [
                    'label'  => 'Actions',
                    'labels' => $labels,
                    'values' => $values,
                    'sub'    => 'Last 7 days activity',
                    'hint'   => 'Source: user_data_activity_log',
                ],

                'quick_actions' => $quickActions,
                'alerts'        => $alerts,

                'recent' => [
                    'sub' => 'Latest activity logs',
                    'hint' => 'Source: user_data_activity_log',
                    'columns' => [
                        ['key' => 'actor',  'label' => 'Actor'],
                        ['key' => 'action', 'label' => 'Action'],
                        ['key' => 'module', 'label' => 'Module'],
                        ['key' => 'target', 'label' => 'Table'],
                        ['key' => 'time',   'label' => 'Time', 'align' => 'end'],
                    ],
                    'rows' => array_map(function($x){
                        return [
                            'actor'  => $x['actor'],
                            'action' => $x['action'],
                            'module' => $x['module'],
                            'target' => $x['target'],
                            'time'   => $x['time'],
                        ];
                    }, $recent),
                    'raw' => $recent, // keep full detail for any modal/details UI
                ],
            ],
        ]);
    }

    /* ============================================================
     * (Optional) Keep your old endpoints if you still use them
     * ============================================================ */

    public function summary(Request $request)
    {
        if ($resp = $this->requireRole($request, self::ALLOWED_ROLES)) return $resp;

        $sc = $this->scope($request);

        $now = Carbon::now();
        $from12 = $now->copy()->subMonths(11)->startOfMonth();

        $countTable = function (string $table, ?int $deptId) {
            $q = DB::table($table)->whereNull('deleted_at');

            if ($deptId !== null && Schema::hasColumn($table, 'department_id')) {
                $q->where(function ($w) use ($deptId) {
                    $w->whereNull('department_id')->orWhere('department_id', $deptId);
                });
            }

            return (int) $q->count();
        };

        $countPlacementNotices = function (?int $deptId) {
            $q = DB::table('placement_notices')->whereNull('deleted_at');

            if ($deptId !== null) {
                $q->where(function ($w) use ($deptId) {
                    $w->whereNull('department_ids')
                      ->orWhereRaw("JSON_CONTAINS(department_ids, CAST(? AS JSON))", [$deptId]);
                });
            }

            return (int) $q->count();
        };

        $cards = [
            'departments' => (int) DB::table('departments')->whereNull('deleted_at')->count(),
            'users'       => (int) DB::table('users')->whereNull('deleted_at')->count(),

            'announcements'      => $countTable('announcements', $sc['dept']),
            'achievements'       => $countTable('achievements', $sc['dept']),
            'notices'            => $countTable('notices', $sc['dept']),
            'student_activities' => $countTable('student_activities', $sc['dept']),
            'gallery'            => $countTable('gallery', $sc['dept']),
            'courses'            => $countTable('courses', $sc['dept']),
            'recruiters'         => $countTable('recruiters', $sc['dept']),
            'success_stories'    => $countTable('success_stories', $sc['dept']),
            'events'             => $countTable('events', $sc['dept']),
            'career_notices'     => (int) DB::table('career_notices')->whereNull('deleted_at')->count(),
            'scholarships'       => $countTable('scholarships', $sc['dept']),
            'entrepreneurs'      => $countTable('successful_entrepreneurs', $sc['dept']),

            'placement_notices'  => $countPlacementNotices($sc['dept']),
            'placed_students'    => $countTable('placed_students', $sc['dept']),
        ];

        return response()->json([
            'success' => true,
            'scope'   => [
                'all_departments' => (bool) $sc['all'],
                'department_id'   => $sc['dept'],
            ],
            'cards'    => $cards,
            'charts'   => [
                'monthly_activity' => $this->monthlyActivity($sc['dept'], $from12, $now),
            ],
        ]);
    }

    public function charts(Request $request)
    {
        if ($resp = $this->requireRole($request, self::ALLOWED_ROLES)) return $resp;

        $sc = $this->scope($request);
        $now = Carbon::now();
        $from12 = $now->copy()->subMonths(11)->startOfMonth();

        return response()->json([
            'success' => true,
            'scope'   => [
                'all_departments' => (bool) $sc['all'],
                'department_id'   => $sc['dept'],
            ],
            'charts' => [
                'monthly_activity' => $this->monthlyActivity($sc['dept'], $from12, $now),
            ],
        ]);
    }

    /**
     * Old: Monthly activity across multiple tables (created_at grouped by month)
     */
    private function monthlyActivity(?int $deptId, Carbon $from, Carbon $to): array
    {
        $tables = [
            'announcements',
            'achievements',
            'notices',
            'student_activities',
            'gallery',
            'courses',
            'recruiters',
            'success_stories',
            'events',
            'placement_notices',
            'placed_students',
            'successful_entrepreneurs',
            'career_notices',
            'scholarships',
        ];

        $months = [];
        $cursor = $from->copy();
        while ($cursor->lte($to)) {
            $k = $cursor->format('Y-m');
            $months[$k] = 0;
            $cursor->addMonth();
        }

        foreach ($tables as $t) {
            if (!Schema::hasTable($t)) continue;
            if (!Schema::hasColumn($t, 'created_at')) continue;

            $q = DB::table($t);

            if (Schema::hasColumn($t, 'deleted_at')) $q->whereNull('deleted_at');

            $q->whereBetween('created_at', [$from->copy()->startOfMonth(), $to->copy()->endOfMonth()]);

            if ($t === 'placement_notices') {
                if ($deptId !== null) {
                    $q->where(function ($w) use ($deptId) {
                        $w->whereNull('department_ids')
                          ->orWhereRaw("JSON_CONTAINS(department_ids, CAST(? AS JSON))", [$deptId]);
                    });
                }
            } else {
                if ($deptId !== null && Schema::hasColumn($t, 'department_id')) {
                    $q->where(function ($w) use ($deptId) {
                        $w->whereNull('department_id')->orWhere('department_id', $deptId);
                    });
                }
            }

            $rows = $q->select(
                    DB::raw("DATE_FORMAT(created_at, '%Y-%m') as ym"),
                    DB::raw('COUNT(*) as c')
                )
                ->groupBy('ym')
                ->get();

            foreach ($rows as $r) {
                $ym = (string)($r->ym ?? '');
                if ($ym !== '' && array_key_exists($ym, $months)) {
                    $months[$ym] += (int)($r->c ?? 0);
                }
            }
        }

        $out = [];
        foreach ($months as $ym => $c) {
            $out[] = ['month' => $ym, 'count' => (int)$c];
        }
        return $out;
    }
}
