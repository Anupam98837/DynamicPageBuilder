<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use App\Models\User;
use Throwable;

class MediaController extends Controller
{
    /**
     * Extract authenticated user ID from Bearer token.
     */
    private function getAuthenticatedUserId(Request $request)
    {
        $header = $request->header('Authorization');

        if (!$header || !preg_match('/Bearer\s(\S+)/', $header, $m)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Token not provided',
            ], 401)->throwResponse();
        }

        $bearer = trim($m[1]);

        // ✅ Sanctum tokens are like: "id|plainTextToken"
        $plainToken = $bearer;
        if (str_contains($bearer, '|')) {
            [, $plainToken] = explode('|', $bearer, 2);
        }

        $tokenHash = hash('sha256', $plainToken);

        $record = DB::table('personal_access_tokens')
            ->where('token', $tokenHash)
            // ✅ Sanctum stores tokenable_type as full class name
            ->where('tokenable_type', User::class)
            ->first();

        if (!$record) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Invalid token',
            ], 401)->throwResponse();
        }

        return (int) $record->tokenable_id;
    }

    /**
     * Best-effort: fetch user role (kept flexible to avoid breaking).
     */
    private function getUserRoleById(int $userId): ?string
    {
        try {
            $u = DB::table('users')->where('id', $userId)->first();
            if (!$u) return null;

            // Try common role columns safely
            foreach (['role', 'user_role', 'type', 'user_type'] as $col) {
                if (isset($u->{$col}) && $u->{$col} !== '') {
                    return (string) $u->{$col};
                }
            }
            return null;
        } catch (Throwable $e) {
            return null;
        }
    }

    /**
     * Central activity logger (never breaks main flow).
     */
    private function logActivity(
        Request $request,
        int $performedBy,
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
            $role = $this->getUserRoleById($performedBy);

            // keep within column limits
            $ua = (string) ($request->userAgent() ?? '');
            if (strlen($ua) > 512) $ua = substr($ua, 0, 512);

            DB::table('user_data_activity_log')->insert([
                'performed_by'       => $performedBy,
                'performed_by_role'  => $role,
                'ip'                 => $request->ip(),
                'user_agent'         => $ua,

                'activity'           => $activity,    // create / update / delete
                'module'             => $module,      // e.g., media

                'table_name'         => $tableName,   // e.g., media
                'record_id'          => $recordId,

                'changed_fields'     => $changedFields ? json_encode($changedFields) : null,
                'old_values'         => $oldValues ? json_encode($oldValues) : null,
                'new_values'         => $newValues ? json_encode($newValues) : null,

                'log_note'           => $note,

                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
        } catch (Throwable $e) {
            // Never block API flow due to logging failure
            Log::warning('Activity log insert failed', [
                'err' => $e->getMessage(),
                'module' => $module,
                'activity' => $activity,
                'table' => $tableName,
                'record_id' => $recordId,
            ]);
        }
    }

    /**
     * GET /api/media
     * List all media items for the authenticated user.
     */
    public function index(Request $request)
    {
        $userId = $this->getAuthenticatedUserId($request);
        Log::info('Listing media for user', ['user_id' => $userId]);

        $items = DB::table('media')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status'  => 'success',
            'message' => 'Media items retrieved.',
            'data'    => $items,
        ], 200);
    }

    /**
     * POST /api/media
     * Upload a new media file.
     */
    public function store(Request $request)
    {
        $userId = $this->getAuthenticatedUserId($request);
        Log::info('Uploading media', ['user_id' => $userId]);

        $v = Validator::make($request->all(), [
            'file' => 'required|file|max:10240', // up to 10MB
        ]);

        if ($v->fails()) {
            Log::warning('Media upload validation failed', ['errors' => $v->errors()->all()]);

            // ✅ Activity log (POST failure)
            $this->logActivity(
                $request,
                $userId,
                'create',
                'media',
                'media',
                null,
                ['file'],
                null,
                ['errors' => $v->errors()->toArray()],
                'Validation failed while uploading media.'
            );

            return response()->json([
                'status'  => 'error',
                'message' => 'Validation failed.',
                'errors'  => $v->errors(),
            ], 422);
        }

        try {
            $file = $request->file('file');
            $ext  = $file->getClientOriginalExtension();
            $name = Str::uuid() . '.' . $ext;

            // ensure user directory
            $destDir = public_path("assets/media/{$userId}");
            if (! File::exists($destDir)) {
                File::makeDirectory($destDir, 0755, true);
                Log::info('Created media directory', ['path' => $destDir]);
            }

            $file->move($destDir, $name);

            $relPath = "assets/media/{$userId}/{$name}";
            $url     = asset($relPath);
            $size    = File::size(public_path($relPath));

            $id = DB::table('media')->insertGetId([
                'user_id'    => $userId,
                'url'        => $url,
                'size'       => $size,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Log::info('Media stored', ['media_id' => $id, 'url' => $url]);

            // ✅ Activity log (POST success)
            $newValues = [
                'id'      => $id,
                'user_id' => $userId,
                'url'     => $url,
                'size'    => $size,
            ];
            $this->logActivity(
                $request,
                $userId,
                'create',
                'media',
                'media',
                (int) $id,
                array_keys($newValues),
                null,
                $newValues,
                'Media uploaded successfully.'
            );

            return response()->json([
                'status'  => 'success',
                'message' => 'File uploaded.',
                'data'    => [
                    'id'   => $id,
                    'url'  => $url,
                    'size' => $size,
                ],
            ], 201);
        } catch (Throwable $e) {
            // ✅ Activity log (POST exception)
            $this->logActivity(
                $request,
                $userId,
                'create',
                'media',
                'media',
                null,
                ['file'],
                null,
                null,
                'Exception while uploading media: ' . $e->getMessage()
            );

            // keep behavior: let Laravel handle exception -> 500
            throw $e;
        }
    }

    /**
     * DELETE /api/media/{id}
     * Delete a media file.
     */
    public function destroy(Request $request, $id)
    {
        $userId = $this->getAuthenticatedUserId($request);
        Log::info('Deleting media', ['user_id' => $userId, 'media_id' => $id]);

        try {
            $item = DB::table('media')
                ->where('id', $id)
                ->where('user_id', $userId)
                ->first();

            if (! $item) {
                Log::warning('Media not found or not owned by user', ['media_id' => $id]);

                // ✅ Activity log (DELETE not found)
                $this->logActivity(
                    $request,
                    $userId,
                    'delete',
                    'media',
                    'media',
                    null,
                    null,
                    null,
                    ['media_id' => (int) $id],
                    'Delete failed: Media not found or not owned by user.'
                );

                return response()->json([
                    'status'  => 'error',
                    'message' => 'Media not found.',
                ], 404);
            }

            // capture snapshot before delete
            $oldValues = [
                'id'      => (int) $item->id,
                'user_id' => (int) $item->user_id,
                'url'     => (string) $item->url,
                'size'    => isset($item->size) ? (int) $item->size : null,
            ];

            // remove file from disk
            $path = public_path(parse_url($item->url, PHP_URL_PATH));
            if (File::exists($path)) {
                File::delete($path);
                Log::info('Deleted media file from disk', ['path' => $path]);
            }

            // remove DB record
            DB::table('media')->where('id', $id)->delete();

            // ✅ Activity log (DELETE success)
            $this->logActivity(
                $request,
                $userId,
                'delete',
                'media',
                'media',
                (int) $id,
                array_keys($oldValues),
                $oldValues,
                null,
                'Media deleted successfully.'
            );

            return response()->json([
                'status'  => 'success',
                'message' => 'Media deleted.',
            ], 200);
        } catch (Throwable $e) {
            // ✅ Activity log (DELETE exception)
            $this->logActivity(
                $request,
                $userId,
                'delete',
                'media',
                'media',
                is_numeric($id) ? (int) $id : null,
                null,
                null,
                null,
                'Exception while deleting media: ' . $e->getMessage()
            );

            throw $e;
        }
    }
}
