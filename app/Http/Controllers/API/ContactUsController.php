<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Carbon\Carbon;

class ContactUsController extends Controller
{
    /* =========================
     * Activity Log Helpers
     * ========================= */

    private function actor(Request $r): array
    {
        // If your auth middleware sets these attributes, we'll pick them up.
        // Otherwise (public contact form), we treat as guest/system.
        $role = $r->attributes->get('auth_role');
        $id   = $r->attributes->get('auth_tokenable_id');

        return [
            'performed_by'      => is_numeric($id) ? (int) $id : 0, // 0 = guest/system (table requires NOT NULL)
            'performed_by_role' => $role ? (string) $role : 'guest',
            'ip'                => $r->ip(),
            'user_agent'        => substr((string) $r->userAgent(), 0, 512),
        ];
    }

    private function safeActivityLog(
        Request $r,
        string $activity,
        string $module,
        string $tableName,
        ?int $recordId = null,
        $changedFields = null,
        $oldValues = null,
        $newValues = null,
        ?string $note = null
    ): void {
        try {
            if (!Schema::hasTable('user_data_activity_log')) return;

            $a = $this->actor($r);

            DB::table('user_data_activity_log')->insert([
                'performed_by'      => $a['performed_by'],
                'performed_by_role' => $a['performed_by_role'],
                'ip'                => $a['ip'],
                'user_agent'        => $a['user_agent'],

                'activity'   => $activity,   // create/update/delete
                'module'     => $module,     // contact_us
                'table_name' => $tableName,  // contact_us
                'record_id'  => $recordId,

                'changed_fields' => $changedFields === null ? null : json_encode($changedFields, JSON_UNESCAPED_UNICODE),
                'old_values'     => $oldValues === null ? null : json_encode($oldValues, JSON_UNESCAPED_UNICODE),
                'new_values'     => $newValues === null ? null : json_encode($newValues, JSON_UNESCAPED_UNICODE),

                'log_note'   => $note,

                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        } catch (\Throwable $e) {
            // Never break functionality because of logging
            Log::warning('Activity log failed (ContactUsController): ' . $e->getMessage());
        }
    }

    /**
     * POST /api/contact-us
     * Public contact form submit
     */
    public function store(Request $request)
    {
        $v = Validator::make($request->all(), [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name'  => ['nullable', 'string', 'max:255'],
            'email'      => ['required', 'email', 'max:255'],
            'phone'      => ['nullable', 'string', 'max:20'],
            'message'    => ['required', 'string'],

            // ✅ JSON array of consent/legal authority
            // Accepts array from frontend; we store as JSON.
            'legal_authority_json'   => ['nullable', 'array'],
            'legal_authority_json.*' => ['nullable'],
        ]);

        if ($v->fails()) {
            // No DB change, so no activity log insert here (keeps log clean & avoids storing invalid payloads)
            return response()->json([
                'success' => false,
                'errors'  => $v->errors()
            ], 422);
        }

        // ✅ If frontend didn't send legal_authority_json, store a default structure (optional)
        $legal = $request->input('legal_authority_json');

        if ($legal === null) {
            $legal = [
                [
                    'key'      => 'terms',
                    'text'     => 'I agree to the Terms and conditions *',
                    'accepted' => null,
                ],
                [
                    'key'      => 'promotions',
                    'text'     => 'I agree to receive communication on newsletters-promotional content-offers an events through SMS-RCS *',
                    'accepted' => null,
                ],
            ];
        }

        $now = Carbon::now();

        // ✅ use insertGetId so we can log record_id
        $id = (int) DB::table('contact_us')->insertGetId([
            'first_name'           => $request->first_name,
            'last_name'            => $request->last_name,
            'email'                => $request->email,
            'phone'                => $request->phone,
            'message'              => $request->message,
            'legal_authority_json' => json_encode($legal, JSON_UNESCAPED_UNICODE),

            'is_read'              => 0, // default unread
            'created_at'           => $now,
            'updated_at'           => $now,
        ]);

        // ✅ Activity Log (POST)
        $this->safeActivityLog(
            $request,
            'create',
            'contact_us',
            'contact_us',
            $id,
            ['first_name', 'last_name', 'email', 'phone', 'message', 'legal_authority_json', 'is_read'],
            null,
            [
                'id'                   => $id,
                'first_name'           => $request->first_name,
                'last_name'            => $request->last_name,
                'email'                => $request->email,
                'phone'                => $request->phone,
                'message'              => $request->message,
                'legal_authority_json' => $legal,
                'is_read'              => 0,
            ],
            'Contact enquiry submitted'
        );

        return response()->json([
            'success' => true,
            'message' => 'Your message has been sent successfully.'
        ], 201);
    }

    /**
     * GET /api/contact-us
     * Admin: list all messages
     */
    public function index(Request $request)
    {
        $page     = max(1, (int) $request->query('page', 1));
        $perPage  = min(100, max(5, (int) $request->query('per_page', 20)));
        $q        = trim((string) $request->query('q', ''));
        $sortBy   = $request->query('sort_by', 'created_at');
        $sortDir  = strtolower($request->query('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        // ✅ updated allowed sorts (no "name" anymore)
        $allowedSorts = ['id', 'first_name', 'last_name', 'email', 'phone', 'created_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }

        $query = DB::table('contact_us');

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $like = '%' . $q . '%';
                $w->where('first_name', 'LIKE', $like)
                    ->orWhere('last_name', 'LIKE', $like)
                    ->orWhere('email', 'LIKE', $like)
                    ->orWhere('phone', 'LIKE', $like)
                    ->orWhere('message', 'LIKE', $like);
            });
        }

        $total = (clone $query)->count();

        $data = $query
            ->orderBy($sortBy, $sortDir)
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        // ✅ decode json for API response (keeps frontend easy)
        $data = $data->map(function ($row) {
            if (property_exists($row, 'legal_authority_json')) {
                $row->legal_authority_json = $row->legal_authority_json
                    ? json_decode($row->legal_authority_json, true)
                    : null;
            }
            return $row;
        });

        return response()->json([
            'success' => true,
            'data'    => $data,
            'meta'    => [
                'page'        => $page,
                'per_page'    => $perPage,
                'total'       => $total,
                'total_pages' => (int) ceil($total / $perPage),
                'sort_by'     => $sortBy,
                'sort_dir'    => $sortDir,
                'q'           => $q,
            ]
        ], 200);
    }

    /**
     * GET /api/contact-us/{id}
     * Admin: view single message
     */
    public function show($id)
    {
        $msg = DB::table('contact_us')->where('id', $id)->first();

        if (!$msg) {
            return response()->json([
                'success' => false,
                'message' => 'Message not found'
            ], 404);
        }

        if ((int) $msg->is_read === 0) {
            DB::table('contact_us')
                ->where('id', $id)
                ->update([
                    'is_read'    => 1,
                    'updated_at' => Carbon::now(),
                ]);

            $msg->is_read = 1;
        }

        // ✅ decode json
        if (property_exists($msg, 'legal_authority_json')) {
            $msg->legal_authority_json = $msg->legal_authority_json
                ? json_decode($msg->legal_authority_json, true)
                : null;
        }

        return response()->json([
            'success' => true,
            'message' => $msg
        ]);
    }

    /**
     * PATCH /api/contact-us/{id}/read
     * Admin: mark message as read
     */
    public function markAsRead(Request $request, $id)
    {
        $msg = DB::table('contact_us')->where('id', $id)->first();

        if (!$msg) {
            // ✅ Activity Log (PATCH attempt - not found)
            $this->safeActivityLog(
                $request,
                'update',
                'contact_us',
                'contact_us',
                is_numeric($id) ? (int) $id : null,
                [],
                null,
                null,
                'Mark as read attempted but message not found'
            );

            return response()->json([
                'success' => false,
                'message' => 'Message not found'
            ], 404);
        }

        if ((int) $msg->is_read === 1) {
            // ✅ Activity Log (PATCH - no change)
            $this->safeActivityLog(
                $request,
                'update',
                'contact_us',
                'contact_us',
                (int) $msg->id,
                [],
                ['is_read' => 1],
                ['is_read' => 1],
                'Message already marked as read (no change)'
            );

            return response()->json([
                'success' => true,
                'message' => 'Message already marked as read'
            ]);
        }

        DB::table('contact_us')
            ->where('id', $id)
            ->update([
                'is_read'    => 1,
                'updated_at' => Carbon::now(),
            ]);

        // ✅ Activity Log (PATCH)
        $this->safeActivityLog(
            $request,
            'update',
            'contact_us',
            'contact_us',
            (int) $msg->id,
            ['is_read'],
            ['is_read' => (int) $msg->is_read],
            ['is_read' => 1],
            'Message marked as read'
        );

        return response()->json([
            'success' => true,
            'message' => 'Message marked as read'
        ]);
    }

    /**
     * DELETE /api/contact-us/{id}
     * Admin: delete message
     */
    public function destroy(Request $request, $id)
    {
        $row = DB::table('contact_us')->where('id', $id)->first();

        if (!$row) {
            // ✅ Activity Log (DELETE attempt - not found)
            $this->safeActivityLog(
                $request,
                'delete',
                'contact_us',
                'contact_us',
                is_numeric($id) ? (int) $id : null,
                [],
                null,
                null,
                'Delete attempted but message not found'
            );

            return response()->json([
                'success' => false,
                'message' => 'Message not found'
            ], 404);
        }

        DB::table('contact_us')->where('id', $id)->delete();

        // ✅ Activity Log (DELETE)
        $this->safeActivityLog(
            $request,
            'delete',
            'contact_us',
            'contact_us',
            (int) $row->id,
            null,
            [
                'id'                   => (int) $row->id,
                'first_name'           => $row->first_name ?? null,
                'last_name'            => $row->last_name ?? null,
                'email'                => $row->email ?? null,
                'phone'                => $row->phone ?? null,
                'message'              => $row->message ?? null,
                'legal_authority_json' => $row->legal_authority_json ?? null,
                'is_read'              => isset($row->is_read) ? (int) $row->is_read : null,
                'created_at'           => $row->created_at ?? null,
            ],
            null,
            'Message deleted successfully'
        );

        return response()->json([
            'success' => true,
            'message' => 'Message deleted successfully'
        ]);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $q       = trim((string) $request->query('q', ''));
        $sortBy  = $request->query('sort_by', 'created_at');
        $sortDir = strtolower($request->query('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        // ✅ updated allowed sorts (no "name" anymore)
        $allowedSorts = ['id', 'first_name', 'last_name', 'email', 'phone', 'created_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }

        $query = DB::table('contact_us');

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $like = '%' . $q . '%';
                $w->where('first_name', 'LIKE', $like)
                    ->orWhere('last_name', 'LIKE', $like)
                    ->orWhere('email', 'LIKE', $like)
                    ->orWhere('phone', 'LIKE', $like)
                    ->orWhere('message', 'LIKE', $like);
            });
        }

        $query->orderBy($sortBy, $sortDir);

        $fileName = 'enquiries_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($query) {

            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'ID',
                'First Name',
                'Last Name',
                'Email',
                'Phone',
                'Message',
                'Legal Authority JSON',
                'Created At'
            ]);

            $query->chunk(500, function ($rows) use ($handle) {
                foreach ($rows as $row) {
                    fputcsv($handle, [
                        $row->id,
                        $row->first_name ?? '',
                        $row->last_name ?? '',
                        $row->email,
                        $row->phone,
                        preg_replace("/\r|\n/", ' ', (string) $row->message),
                        $row->legal_authority_json ?? '',
                        $row->created_at,
                    ]);
                }
            });

            fclose($handle);

        }, $fileName, [
            'Content-Type'  => 'text/csv',
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }
}
