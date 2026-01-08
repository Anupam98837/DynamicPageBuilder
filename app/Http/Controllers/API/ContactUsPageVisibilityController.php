<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ContactUsPageVisibilityController extends Controller
{
    private const TABLE = 'contact_us_page_visibility';

    /* ============================================
     | Helpers
     |============================================ */

    private function actor(Request $r): array
    {
        return [
            'id'   => (int) ($r->attributes->get('auth_tokenable_id') ?? optional($r->user())->id ?? 0),
            'role' => (string) ($r->attributes->get('auth_role') ?? ($r->user()->role ?? '')),
            'type' => (string) ($r->attributes->get('auth_tokenable_type') ?? ($r->user() ? get_class($r->user()) : '')),
            'uuid' => (string) ($r->attributes->get('auth_user_uuid') ?? ($r->user()->uuid ?? '')),
        ];
    }

    private function ip(Request $r): ?string
    {
        return $r->ip();
    }

    private function currentRow()
    {
        return DB::table(self::TABLE)->orderByDesc('id')->first();
    }

    private function ensureRowExists(Request $request)
    {
        $row = $this->currentRow();
        if ($row) return $row;

        $actor = $this->actor($request);
        $ip    = $this->ip($request);

        $id = DB::table(self::TABLE)->insertGetId([
            // defaults (match your migration defaults)
            'show_address'     => 1,
            'show_call'        => 1,
            'show_recruitment' => 1,
            'show_email'       => 1,
            'show_form'        => 1,
            'show_map'         => 1,

            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table(self::TABLE)->where('id', $id)->first();
    }

    /* ============================================
     | Admin
     |============================================ */

    // GET /api/admin/contact-us/visibility
    public function Show(Request $request)
    {
        $row = $this->ensureRowExists($request);

        return response()->json([
            'success' => true,
            'data'    => $row,
        ]);
    }

    // PUT /api/admin/contact-us/visibility
    public function Update(Request $request)
    {
        $payload = $request->validate([
            'show_address'     => ['nullable', 'boolean'],
            'show_call'        => ['nullable', 'boolean'],
            'show_recruitment' => ['nullable', 'boolean'],
            'show_email'       => ['nullable', 'boolean'],
            'show_form'        => ['nullable', 'boolean'],
            'show_map'         => ['nullable', 'boolean'],
        ]);

        $row = $this->ensureRowExists($request);

        // Merge: if a key is not sent, keep existing value
        $updates = [
            'show_address'     => array_key_exists('show_address', $payload) ? (bool) $payload['show_address'] : (bool) $row->show_address,
            'show_call'        => array_key_exists('show_call', $payload) ? (bool) $payload['show_call'] : (bool) $row->show_call,
            'show_recruitment' => array_key_exists('show_recruitment', $payload) ? (bool) $payload['show_recruitment'] : (bool) $row->show_recruitment,
            'show_email'       => array_key_exists('show_email', $payload) ? (bool) $payload['show_email'] : (bool) $row->show_email,
            'show_form'        => array_key_exists('show_form', $payload) ? (bool) $payload['show_form'] : (bool) $row->show_form,
            'show_map'         => array_key_exists('show_map', $payload) ? (bool) $payload['show_map'] : (bool) $row->show_map,

            'updated_at' => now(),
        ];

        DB::table(self::TABLE)->where('id', $row->id)->update($updates);

        $fresh = $this->currentRow();

        return response()->json([
            'success' => true,
            'message' => 'Contact Us visibility updated.',
            'data'    => $fresh,
        ]);
    }

    /* ============================================
     | Public (optional)
     |============================================ */

    // GET /api/public/contact-us/visibility
    public function publicShow(Request $request)
    {
        $row = $this->ensureRowExists($request);

        return response()->json([
            'success' => true,
            'data'    => $row,
        ]);
    }
}
