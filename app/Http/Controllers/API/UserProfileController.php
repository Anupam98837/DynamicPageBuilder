<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserProfileController extends Controller
{
    /**
     * GET /api/users/{user_uuid}/profile
     * -----------------------------------
     * PUBLIC SAFE PROFILE API
     * - UUID based
     * - No auth
     * - No sensitive fields
     */
    public function show(Request $request, string $user_uuid)
    {
        /* =========================
         * USER (PUBLIC SAFE)
         * ========================= */
        $user = DB::table('users')
            ->where('uuid', $user_uuid)
            ->whereNull('deleted_at')
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'error'   => 'User not found'
            ], 404);
        }

        /* =========================
         * HELPERS
         * ========================= */

        // single row safe picker
        $safe = function ($row, array $fields) {
            if (!$row) return null;
            return collect($row)->only($fields)->toArray();
        };

        // collection safe picker
        $safeCollection = function ($rows, array $fields) {
            return collect($rows)->map(function ($r) use ($fields) {
                return collect($r)->only($fields)->toArray();
            })->values();
        };

        // decode JSON fields safely
        $decodeJson = function ($value) {
            if (is_string($value)) {
                $decoded = json_decode($value, true);
                return is_array($decoded) ? $decoded : [];
            }
            return is_array($value) ? $value : [];
        };

        /* =========================
         * BUILD PROFILE RESPONSE
         * ========================= */
        $profile = [

            /* ---------- BASIC ---------- */
            'basic' => $safe($user, [
                'uuid',
                'name',
                'slug',
                'role',
                'role_short_form',
                'email',
                'phone_number',
                'alternative_email',
                'alternative_phone_number',
                'whatsapp_number',
                'image',
                'address',
                'status',
                'created_at'
            ]),

            /* ---------- PERSONAL ---------- */
            'personal' => (function () use ($user, $safe, $decodeJson) {
                $row = DB::table('user_personal_information')
                    ->where('user_id', $user->id)
                    ->whereNull('deleted_at')
                    ->first();

                if (!$row) return null;

                $data = $safe($row, [
                    'uuid',
                    'qualification',
                    'affiliation',
                    'specification',
                    'experience',
                    'interest',
                    'administration',
                    'research_project'
                ]);

                // decode qualification tags
                $data['qualification'] = $decodeJson($data['qualification'] ?? null);

                return $data;
            })(),

            /* ---------- EDUCATIONS ---------- */
            'educations' => $safeCollection(
                DB::table('user_educations')
                    ->where('user_id', $user->id)
                    ->whereNull('deleted_at')
                    ->orderBy('passing_year', 'desc')
                    ->orderBy('id', 'desc')
                    ->get(),
                [
                    'uuid',
                    'education_level',
                    'degree_title',
                    'field_of_study',
                    'institution_name',
                    'university_name',
                    'enrollment_year',
                    'passing_year',
                    'grade_type',
                    'grade_value',
                    'location',
                    'certificate',
                    'description'
                ]
            ),

            /* ---------- HONORS ---------- */
            'honors' => $safeCollection(
                DB::table('user_honors')
                    ->where('user_id', $user->id)
                    ->whereNull('deleted_at')
                    ->orderBy('honor_year', 'desc')
                    ->orderBy('id', 'desc')
                    ->get(),
                [
                    'uuid',
                    'title',
                    'honor_type',
                    'honouring_organization',
                    'honor_year',
                    'image',
                    'description'
                ]
            ),

            /* ---------- JOURNALS ---------- */
            'journals' => $safeCollection(
                DB::table('user_journals')
                    ->where('user_id', $user->id)
                    ->whereNull('deleted_at')
                    ->orderByRaw('publication_year IS NULL, publication_year DESC')
                    ->orderBy('id', 'desc')
                    ->get(),
                [
                    'uuid',
                    'title',
                    'publication_organization',
                    'publication_year',
                    'url',
                    'image',
                    'description'
                ]
            ),

            /* ---------- CONFERENCES ---------- */
            'conference_publications' => $safeCollection(
                DB::table('user_conference_publications')
                    ->where('user_id', $user->id)
                    ->whereNull('deleted_at')
                    ->orderByRaw('publication_year IS NULL, publication_year DESC')
                    ->orderBy('id', 'desc')
                    ->get(),
                [
                    'uuid',
                    'conference_name',
                    'title',
                    'publication_organization',
                    'publication_year',
                    'publication_type',
                    'domain',
                    'location',
                    'url',
                    'image',
                    'description'
                ]
            ),

            /* ---------- TEACHING ---------- */
            'teaching_engagements' => $safeCollection(
                DB::table('user_teaching_engagements')
                    ->where('user_id', $user->id)
                    ->whereNull('deleted_at')
                    ->orderBy('id', 'desc')
                    ->get(),
                [
                    'uuid',
                    'organization_name',
                    'domain',
                    'description'
                ]
            ),

            /* ---------- SOCIAL ---------- */
            'social_media' => $safeCollection(
                DB::table('user_social_media')
                    ->where('user_id', $user->id)
                    ->where('active', true)
                    ->whereNull('deleted_at')
                    ->orderBy('sort_order', 'asc')
                    ->get(),
                [
                    'uuid',
                    'platform',
                    'icon',
                    'link'
                ]
            ),
        ];

        return response()->json([
            'success' => true,
            'data'    => $profile
        ]);
    }
}
