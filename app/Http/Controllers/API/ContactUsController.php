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
    use \App\Http\Controllers\API\Concerns\DepartmentScopeable;

    /* =========================
     * Activity Log Helpers
     * ========================= */

    private function actor(Request $r): array
    {
        $role = $r->attributes->get('auth_role');
        $id   = $r->attributes->get('auth_tokenable_id');

        return [
            'performed_by'      => is_numeric($id) ? (int) $id : 0,
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

                'activity'   => $activity,
                'module'     => $module,
                'table_name' => $tableName,
                'record_id'  => $recordId,

                'changed_fields' => $changedFields === null ? null : json_encode($changedFields, JSON_UNESCAPED_UNICODE),
                'old_values'     => $oldValues === null ? null : json_encode($oldValues, JSON_UNESCAPED_UNICODE),
                'new_values'     => $newValues === null ? null : json_encode($newValues, JSON_UNESCAPED_UNICODE),

                'log_note'   => $note,

                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Activity log failed (ContactUsController): ' . $e->getMessage());
        }
    }

    private function toNullableBool($v): ?int
    {
        if ($v === null || $v === '') return null;

        // supports true/false, 1/0, "true"/"false", "on"/"off", "yes"/"no"
        $b = filter_var($v, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($b === null) return null;

        return $b ? 1 : 0;
    }

    /**
     * POST /api/contact-us
     * Public contact form submit
     */
    public function store(Request $request)
    {
        $v = Validator::make($request->all(), [
            'name'       => ['required', 'string', 'max:255'],

            // ✅ UPDATED: email nullable (but if present must be valid)
            'email'      => ['nullable', 'email', 'max:255'],

            // ✅ UPDATED: phone required
            'phone'      => ['required', 'string', 'max:20'],

            'message'    => ['nullable', 'string'],

            // ✅ NEW: admission enquiry checker (nullable boolean)
            'is_admission_enquiry' => ['nullable'],

            // ✅ NEW: course ids array (nullable)
            'course_ids'   => ['nullable', 'array'],
            'course_ids.*' => ['integer'],

            // Existing: consent/legal authority json
            'legal_authority_json'   => ['nullable', 'array'],
            'legal_authority_json.*' => ['nullable'],
        ]);

        if ($v->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $v->errors()
            ], 422);
        }

        // Normalize email: store null instead of empty string
        $email = trim((string) $request->input('email', ''));
        $email = $email === '' ? null : $email;

        // Normalize phone: required, trim
        $phone = trim((string) $request->input('phone', ''));
        // phone is required by validation; but still guard:
        if ($phone === '') {
            return response()->json([
                'success' => false,
                'errors'  => ['phone' => ['Phone is required.']]
            ], 422);
        }

        // NEW: nullable boolean
        $isAdmission = $this->toNullableBool($request->input('is_admission_enquiry'));

        // NEW: course ids array (nullable)
        $courseIds = $request->input('course_ids');
        if (is_array($courseIds)) {
            // keep only ints, unique
            $courseIds = array_values(array_unique(array_map('intval', $courseIds)));
        } else {
            $courseIds = null;
        }

        // If frontend didn't send legal_authority_json, store default structure (optional)
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

        $id = (int) DB::table('contact_us')->insertGetId([
            'name'                 => $request->input('name'),
            'email'                => $email,                 // ✅ nullable
            'phone'                => $phone,                 // ✅ required
            'message'              => $request->input('message'),

            // ✅ new columns
            'is_admission_enquiry' => $isAdmission,           // nullable boolean
            'course_ids'           => $courseIds === null ? null : json_encode($courseIds, JSON_UNESCAPED_UNICODE),

            // existing
            'legal_authority_json' => json_encode($legal, JSON_UNESCAPED_UNICODE),
            'is_read'              => 0,

            'created_at'           => $now,
            'updated_at'           => $now,
        ]);

        $this->safeActivityLog(
            $request,
            'create',
            'contact_us',
            'contact_us',
            $id,
            [
                'name','email','phone','message',
                'is_admission_enquiry','course_ids',
                'legal_authority_json','is_read'
            ],
            null,
            [
                'id'                   => $id,
                'name'                 => $request->input('name'),
                'email'                => $email,
                'phone'                => $phone,
                'message'              => $request->input('message'),
                'is_admission_enquiry' => $isAdmission,
                'course_ids'           => $courseIds,
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

        // ✅ updated allowed sorts
        $allowedSorts = ['id', 'name', 'email', 'phone', 'is_admission_enquiry', 'created_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }

        $query = DB::table('contact_us');

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $like = '%' . $q . '%';
                $w->where('name', 'LIKE', $like)
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

        // ✅ decode json for API response
        $data = $data->map(function ($row) {
            if (property_exists($row, 'legal_authority_json')) {
                $row->legal_authority_json = $row->legal_authority_json
                    ? json_decode($row->legal_authority_json, true)
                    : null;
            }
            if (property_exists($row, 'course_ids')) {
                $row->course_ids = $row->course_ids
                    ? json_decode($row->course_ids, true)
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
                    'read_at'    => Carbon::now(), // (existing column)
                    'updated_at' => Carbon::now(),
                ]);

            $msg->is_read = 1;
        }

        if (property_exists($msg, 'legal_authority_json')) {
            $msg->legal_authority_json = $msg->legal_authority_json
                ? json_decode($msg->legal_authority_json, true)
                : null;
        }

        if (property_exists($msg, 'course_ids')) {
            $msg->course_ids = $msg->course_ids
                ? json_decode($msg->course_ids, true)
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
                'read_at'    => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

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

        $this->safeActivityLog(
            $request,
            'delete',
            'contact_us',
            'contact_us',
            (int) $row->id,
            null,
            [
                'id'                   => (int) $row->id,
                'name'                 => $row->name ?? null,
                'email'                => $row->email ?? null,
                'phone'                => $row->phone ?? null,
                'message'              => $row->message ?? null,

                // ✅ new fields
                'is_admission_enquiry' => property_exists($row, 'is_admission_enquiry') ? $row->is_admission_enquiry : null,
                'course_ids'           => property_exists($row, 'course_ids') ? $row->course_ids : null,

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

        $allowedSorts = ['id', 'name', 'email', 'phone', 'is_admission_enquiry', 'created_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }

        $query = DB::table('contact_us');

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $like = '%' . $q . '%';
                $w->where('name', 'LIKE', $like)
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
                'Name',
                'Email',
                'Phone',
                'Admission Enquiry',
                'Course Names',
                'Message',
                'Legal Authority JSON',
                'Created At'
            ]);

            $query->chunk(500, function ($rows) use ($handle) {
                foreach ($rows as $row) {
                    $courseNames = '';
                    if (!empty($row->course_ids)) {
                        $ids = json_decode($row->course_ids, true);
                        if (is_array($ids) && count($ids) > 0) {
                            $titles = DB::table('courses')->whereIn('id', $ids)->pluck('title')->toArray();
                            $courseNames = implode(', ', $titles);
                        }
                    }

                    fputcsv($handle, [
                        $row->id,
                        $row->name ?? '',
                        $row->email ?? '',
                        $row->phone ?? '',
                        isset($row->is_admission_enquiry) ? (string) $row->is_admission_enquiry : '',
                        $courseNames,
                        preg_replace("/\r|\n/", ' ', (string) $row->message),
                        $row->legal_authority_json ?? '',
                        $row->created_at ?? '',
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