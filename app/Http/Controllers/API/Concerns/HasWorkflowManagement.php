<?php

namespace App\Http\Controllers\API\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

trait HasWorkflowManagement
{
    /**
     * Get initial workflow status based on user role.
     */
    protected function getInitialWorkflowStatus(Request $request): string
    {
        $role = strtolower((string)($request->attributes->get('auth_role') ?? ''));
        
        // Admin / Principal / Director / Author / IT: Auto-Approved
        if (in_array($role, ['admin', 'principal', 'director', 'author', 'it_person', 'super_admin'])) {
            return 'approved';
        }
        
        // HOD: Skips HOD check, goes to Principal approval
        if ($role === 'hod') {
            return 'checked';
        }
        
        // Faculty / Others: Starts as pending_check
        return 'pending_check';
    }

    /**
     * Handle update logic with drafting support for approved content.
     */
    protected function handleWorkflowUpdate(Request $request, string $table, $id, array $payload)
    {
        $current = DB::table($table)->where('id', $id)->first();
        if (!$current) return false;

        $role = strtolower((string)($request->attributes->get('auth_role') ?? ''));
        $isHighRole = in_array($role, ['admin', 'principal', 'director', 'author', 'it_person', 'super_admin']);

        // Check if row has workflow columns
        $hasWorkflow = is_object($current) && isset($current->workflow_status);

        // A record is considered "Live" if it's currently Approved OR it's been previously Approved (status = Active).
        // Any non-admin update to a Live record MUST be drafted into 'draft_data' to protect the public content.
        $isLive = $hasWorkflow && (
            (string)$current->workflow_status === 'approved' || 
            (isset($current->status) && strtolower((string)$current->status) === 'active') ||
            (isset($current->is_approved) && (int)$current->is_approved === 1)
        );

        if ($hasWorkflow && $isLive && !$isHighRole) {
            // Drafting logic: Keep live content, store changes in draft_data
            DB::table($table)->where('id', $id)->update([
                'draft_data' => json_encode($payload),
                'workflow_status' => ($role === 'hod') ? 'checked' : 'pending_check',
                'updated_at' => Carbon::now(),
            ]);
            $this->logWorkflowAction($request, $table, $id, 'updated_draft', ($role === 'hod' ? 'checked' : 'pending_check'));
            return 'drafted';
        } else {
            // Direct update for High Roles or items not yet approved
            $updateData = $payload;

            // Reset rejection state on any update/resubmission
            if (Schema::hasColumn($table, 'is_rejected')) {
                $updateData['is_rejected'] = 0;
            }
            if (Schema::hasColumn($table, 'rejected_reason')) {
                $updateData['rejected_reason'] = null;
            }
            if (Schema::hasColumn($table, 'rejection_reason')) {
                $updateData['rejection_reason'] = null;
            }

            if ($hasWorkflow) {
                // If it's a high role, ensure it's approved and clear draft
                if ($isHighRole) {
                    $updateData['workflow_status']      = 'approved';
                    $updateData['draft_data']           = null;
                    $updateData['is_approved']          = 1;
                    $updateData['request_for_approval'] = 0;
                    $updateData['is_rejected']          = 0;
                    $updateData['approved_at']          = Carbon::now();
                    $updateData['approved_by']          = (int) $request->attributes->get('auth_tokenable_id');
                } else {
                    // Faculty/HOD updating non-approved content: keep/set status
                    $newStatus = ($role === 'hod') ? 'checked' : 'pending_check';
                    $updateData['workflow_status']      = $newStatus;
                    $updateData['request_for_approval'] = 1;
                    $updateData['is_approved']          = 0;
                    $updateData['is_rejected']          = 0;
                }
            }
            
            DB::table($table)->where('id', $id)->update($updateData);

            $action = $isHighRole ? 'updated_approved' : 'resubmitted';
            $toStat = $updateData['workflow_status'] ?? ($current->workflow_status ?? 'unknown');
            $this->logWorkflowAction($request, $table, $id, $action, $toStat, 'User updated the record.');

            return 'updated';
        }
    }

    /**
     * Log workflow transitions.
     */
    protected function logWorkflowAction(Request $request, string $table, $id, string $action, string $toStatus, $comment = null)
    {
        try {
            DB::table('content_approval_logs')->insert([
                'model_type' => $table, 
                'model_id'   => $id,
                'user_id'    => (int) $request->attributes->get('auth_tokenable_id'),
                'action'     => $action,
                'to_status'  => $toStatus,
                'comment'    => $comment,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        } catch (\Throwable $e) {
            // Silent fail
        }
    }
}
