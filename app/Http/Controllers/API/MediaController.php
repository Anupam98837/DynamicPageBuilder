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
            return response()->json([
                'status'  => 'error',
                'message' => 'Validation failed.',
                'errors'  => $v->errors(),
            ], 422);
        }

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

        return response()->json([
            'status'  => 'success',
            'message' => 'File uploaded.',
            'data'    => [
                'id'   => $id,
                'url'  => $url,
                'size' => $size,
            ],
        ], 201);
    }

    /**
     * DELETE /api/media/{id}
     * Delete a media file.
     */
    public function destroy(Request $request, $id)
    {
        $userId = $this->getAuthenticatedUserId($request);
        Log::info('Deleting media', ['user_id' => $userId, 'media_id' => $id]);

        $item = DB::table('media')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (! $item) {
            Log::warning('Media not found or not owned by user', ['media_id' => $id]);
            return response()->json([
                'status'  => 'error',
                'message' => 'Media not found.',
            ], 404);
        }

        // remove file from disk
        $path = public_path(parse_url($item->url, PHP_URL_PATH));
        if (File::exists($path)) {
            File::delete($path);
            Log::info('Deleted media file from disk', ['path' => $path]);
        }

        // remove DB record
        DB::table('media')->where('id', $id)->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Media deleted.',
        ], 200);
    }
}
