<?php

namespace App\Http\Controllers\API\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Provides department-based data scoping.
 *
 * Higher authorities (admin, author, principal, director, super_admin) => all data.
 * Other roles with a department_id assigned => only their department's data.
 */
trait DepartmentScopeable
{
    /**
     * Resolve access control for the current actor.
     *
     * Returns:
     *   ['mode' => 'all',        'department_id' => null]
     *   ['mode' => 'department', 'department_id' => <int>]
     *   ['mode' => 'none',       'department_id' => null]
     */
    protected function departmentAccessControl(Request $request): array
    {
        $userId = (int) ($request->attributes->get('auth_tokenable_id') ?? 0);

        if ($userId <= 0) {
            return ['mode' => 'none', 'department_id' => null];
        }

        if (!Schema::hasColumn('users', 'department_id')) {
            return ['mode' => 'all', 'department_id' => null];
        }

        $u = DB::table('users')
            ->select(['id', 'role', 'department_id', 'status'])
            ->where('id', $userId)
            ->first();

        if (!$u || (isset($u->status) && (string) $u->status !== 'active')) {
            return ['mode' => 'none', 'department_id' => null];
        }

        $role   = strtolower(trim((string) ($u->role ?? '')));
        $deptId = $u->department_id !== null ? (int) $u->department_id : null;
        if ($deptId !== null && $deptId <= 0) $deptId = null;

        // Higher authorities see everything
        $higherAuthorities = ['admin', 'author', 'principal', 'director', 'super_admin'];
        if (in_array($role, $higherAuthorities, true)) {
            return ['mode' => 'all', 'department_id' => null];
        }

        // Anyone else with a department → restricted to that dept
        if ($deptId !== null) {
            return ['mode' => 'department', 'department_id' => $deptId];
        }

        // No department set → see all (legacy / fallback)
        return ['mode' => 'all', 'department_id' => null];
    }

    /**
     * Apply department WHERE clause to a query builder.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  array                               $ac    Result of departmentAccessControl()
     * @param  string                              $col   The department_id column expression
     */
    protected function applyDeptScope($query, array $ac, string $col = 'department_id'): void
    {
        if ($ac['mode'] === 'department' && $ac['department_id']) {
            $query->where($col, (int) $ac['department_id']);
        }
    }

    /**
     * Return a 403 JSON response if the actor cannot write to the given department_id.
     * Returns null if allowed.
     */
    protected function guardDeptWrite(array $ac, ?int $targetDeptId): ?\Illuminate\Http\JsonResponse
    {
        if ($ac['mode'] !== 'department') {
            return null; // 'all' or 'none' handled separately
        }
        if ($targetDeptId !== null && $targetDeptId !== $ac['department_id']) {
            return response()->json([
                'success' => false,
                'error'   => 'You can only manage data for your own department.',
            ], 403);
        }
        return null;
    }
}
