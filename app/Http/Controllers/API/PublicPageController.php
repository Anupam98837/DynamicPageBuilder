<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PublicPageController extends Controller
{
    private function normSlug(?string $s): string
    {
        $s = trim((string) $s);
        return $s === '' ? '' : Str::slug($s, '-');
    }

    /**
     * GET /api/public/pages/resolve?slug=about-us
     */
    public function resolve(Request $r)
    {
        $slug = $this->normSlug($r->query('slug', ''));
        if ($slug === '') {
            return response()->json(['error' => 'Missing slug'], 422);
        }

        $q = DB::table('pages')->whereNull('deleted_at');

        // If pages table has active column, enforce it (public pages)
        if (Schema::hasColumn('pages', 'active')) {
            $q->where('active', true);
        }

        $page = $q->where('slug', $slug)->first();

        if (!$page) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $pageArr = (array) $page;

        // Try to pick the content column defensively
        $contentHtml =
            ($pageArr['content_html'] ?? null) ??
            ($pageArr['content'] ?? null) ??
            ($pageArr['body'] ?? null) ??
            ($pageArr['description'] ?? null) ??
            '';

        $title =
            ($pageArr['title'] ?? null) ??
            ($pageArr['name'] ?? null) ??
            $slug;

        // Compute submenu existence from pages_submenu (no extra column needed)
        $submenuExists = false;
        if (Schema::hasTable('pages_submenu')) {
            $sq = DB::table('pages_submenu')
                ->where('page_id', (int) $page->id)
                ->whereNull('deleted_at');

            if (Schema::hasColumn('pages_submenu', 'active')) {
                $sq->where('active', true);
            }

            $submenuExists = $sq->exists();
        }

        return response()->json([
            'success' => true,
            'page' => [
                'id'            => (int) $page->id,
                'title'         => $title,
                'slug'          => $pageArr['slug'] ?? $slug,
                'shortcode'     => $pageArr['shortcode'] ?? ($pageArr['page_shortcode'] ?? null),
                'submenu_exists'=> $submenuExists ? 'yes' : 'no',
                'content_html'  => (string) $contentHtml,
            ],
        ]);
    }
}
