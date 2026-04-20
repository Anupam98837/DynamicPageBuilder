<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait HasWorkflow
{
    /**
     * Boot the trait.
     */
    public static function bootHasWorkflow()
    {
        static::creating(function ($model) {
            // If workflow_status is already set (e.g. via factory or manual set), skip auto-logic
            if ($model->workflow_status) return;

            $user = Auth::user();
            if (!$user) {
                $model->workflow_status = 'approved'; // Default for system actions
                return;
            }

            $role = (string)($user->role ?? $user->role_short_form ?? 'faculty');
            $role = strtolower($role);

            // Admin / Principal / Director / Author: Auto-Approved
            if (in_array($role, ['admin', 'principal', 'director', 'author', 'it_person'])) {
                $model->workflow_status = 'approved';
            } 
            // HOD: Skips HOD check, goes to Principal approval
            elseif ($role === 'hod') {
                $model->workflow_status = 'checked';
            } 
            // Faculty / Others: Starts as draft or pending_check
            else {
                $model->workflow_status = 'pending_check'; // User wants it to go for checking
            }
        });
    }

    /**
     * Submit for HOD check.
     */
    public function submitForCheck()
    {
        if ($this->workflow_status === 'draft' || $this->workflow_status === 'rejected') {
            $this->workflow_status = 'pending_check';
            $this->save();
            $this->logWorkflowAction('submitted');
        }
        return $this;
    }

    /**
     * HOD Checks and moves to Principal stage.
     */
    public function check($comment = null)
    {
        if ($this->workflow_status === 'pending_check') {
            $this->workflow_status = 'checked';
            $this->save();
            $this->logWorkflowAction('checked', $comment);
        }
        return $this;
    }

    /**
     * Principal/Admin Approves and goes LIVE.
     */
    public function approve($comment = null)
    {
        if (in_array($this->workflow_status, ['pending_check', 'checked'])) {
            // Merge draft data if exists
            if ($this->draft_data) {
                $data = is_string($this->draft_data) ? json_decode($this->draft_data, true) : $this->draft_data;
                if ($data && is_array($data)) {
                    foreach ($data as $key => $value) {
                        $this->{$key} = $value;
                    }
                    $this->draft_data = null;
                }
            }
            $this->workflow_status = 'approved';
            $this->save();
            $this->logWorkflowAction('approved', $comment);
        }
        return $this;
    }

    /**
     * Reject and send back to Faculty.
     */
    public function reject($comment = null)
    {
        $this->workflow_status = 'rejected';
        $this->save();
        $this->logWorkflowAction('rejected', $comment);
        return $this;
    }

    /**
     * Log the action.
     */
    protected function logWorkflowAction($action, $comment = null)
    {
        try {
            DB::table('content_approval_logs')->insert([
                'model_type'  => get_class($this),
                'model_id'    => $this->id,
                'user_id'     => Auth::id() ?: 0,
                'action'      => $action,
                'from_status' => $this->getOriginal('workflow_status'),
                'to_status'   => $this->workflow_status,
                'comment'     => $comment,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        } catch (\Throwable $e) {
            // Fail silently to not break core flow
        }
    }

    /**
     * Scope for live (approved) content.
     */
    public function scopeLive($query)
    {
        return $query->where('workflow_status', 'approved');
    }
}
