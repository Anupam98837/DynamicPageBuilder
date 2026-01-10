<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;

class GrandHomepageController extends Controller
{
    /**
     * DEFAULT (FAST): /api/public/grand-homepage
     * Returns BOOTSTRAP only:
     * - notice_marquee
     * - hero_carousel
     *
     * FULL: /api/public/grand-homepage?full=1  OR /api/public/grand-homepage/full
     * LEGACY: /api/public/grand-homepage?legacy=1
     */
    public function index(Request $request)
    {
        $legacy = filter_var($request->query('legacy', false), FILTER_VALIDATE_BOOLEAN);
        $full   = filter_var($request->query('full', false), FILTER_VALIDATE_BOOLEAN);

        if ($legacy || $full) {
            // keep old behavior available
            return $this->full($request);
        }

        [$deptId, $limit, $now] = $this->baseParams($request);

        $cacheKey = $this->cacheKey('bootstrap', $deptId, $limit);

        $payload = Cache::remember($cacheKey, now()->addSeconds(60), function () use ($now, $limit) {
            $noticeMarquee = $this->fetchNoticeMarquee($now);
            $heroCarousel  = $this->fetchHeroCarousel($now, $limit);

            return [
                'notice_marquee' => $noticeMarquee,
                'hero_carousel'  => $heroCarousel,
            ];
        });

        return response()->json(array_merge(['success' => true], $payload));
    }

    /**
     * FULL payload (new format) or LEGACY (old format)
     * - /api/public/grand-homepage?full=1
     * - /api/public/grand-homepage?legacy=1
     * - /api/public/grand-homepage/full
     */
    public function full(Request $request)
    {
        [$deptId, $limit, $now] = $this->baseParams($request);
        $legacy = filter_var($request->query('legacy', false), FILTER_VALIDATE_BOOLEAN);

        $cacheKey = $this->cacheKey('full', $deptId, $limit);

        $raw = Cache::remember($cacheKey, now()->addSeconds(60), function () use ($now, $deptId, $limit) {
            // 1) Notice Marquee + Hero
            $noticeMarquee = $this->fetchNoticeMarquee($now);
            $heroCarousel  = $this->fetchHeroCarousel($now, $limit);

            // 2) Quick Links
            $careerNotices = $this->simpleList($now, 'career_notices', $deptId, $limit, 'career_notices/view/');
            $whyUs         = $this->simpleList($now, 'why_us',         $deptId, $limit, 'why_us/view/');
            $scholarships  = $this->simpleList($now, 'scholarships',   $deptId, $limit, 'scholarships/view/');

            // 3) Notices / Center Iframe / Announcements
            $notices       = $this->simpleList($now, 'notices',       $deptId, $limit, 'notices/view/');
            $announcements = $this->simpleList($now, 'announcements', $deptId, $limit, 'announcements/view/');
            $centerIframe  = $this->fetchCenterIframe($now);

            // 4) Activities + Placement
            $achievements      = $this->simpleList($now, 'achievements',       $deptId, $limit, 'achievements/view/');
            $studentActivities = $this->simpleList($now, 'student_activities', $deptId, $limit, 'student_activities/view/');
            $placementNotices  = $this->placementNoticesList($now, $deptId, $limit);

            // 5) Courses / Stats
            $courses = $this->fetchCourses($now, $deptId, $limit);
            $stats   = $this->fetchStats($now);

            // 6) Entrepreneurs / Alumni / Success Stories / Recruiters
            $successfulEntrepreneurs = $this->fetchSuccessfulEntrepreneurs($now, $deptId, $limit);
            $alumniSpeak             = $this->fetchAlumniSpeak($now, $deptId);
            $successStories          = $this->fetchSuccessStories($now, $deptId, $limit);
            $recruiters              = $this->homepageRecruiters($deptId, $limit);

            return [
                'notice_marquee' => $noticeMarquee,
                'hero_carousel' => $heroCarousel,

                'career_notices' => $careerNotices,
                'why_us' => $whyUs,
                'scholarships' => $scholarships,

                'notices' => $notices,
                'center_iframe' => $centerIframe,
                'announcements' => $announcements,

                'achievements' => $achievements,
                'student_activities' => $studentActivities,
                'placement_notices' => $placementNotices,

                'courses' => $courses,
                'stats' => $stats,

                'successful_entrepreneurs' => $successfulEntrepreneurs,
                'alumni_speak' => $alumniSpeak,
                'success_stories' => $successStories,
                'recruiters' => $recruiters,
            ];
        });

        // Normalize only where needed (stats items, alumni iframe list)
        $payload = $this->normalizeHomepagePayload($raw);

        // LEGACY mode (old home.blade.js shape)
        if ($legacy) {
            return response()->json([
                'success' => true,
                'data' => $this->buildFrontendPayload($payload),
            ]);
        }

        // NEW full payload
        return response()->json(array_merge(['success' => true], $payload));
    }

    /* =========================================================
     | Section-wise endpoints (Lazy-load friendly)
     |========================================================= */

    public function noticeMarquee(Request $request)
    {
        [$deptId, $limit, $now] = $this->baseParams($request);

        $cacheKey = $this->cacheKey('notice_marquee', $deptId, $limit);
        $data = Cache::remember($cacheKey, now()->addSeconds(60), fn() => $this->fetchNoticeMarquee($now));

        return response()->json(['success' => true, 'notice_marquee' => $data]);
    }

    public function heroCarousel(Request $request)
    {
        [$deptId, $limit, $now] = $this->baseParams($request);

        $cacheKey = $this->cacheKey('hero_carousel', $deptId, $limit);
        $data = Cache::remember($cacheKey, now()->addSeconds(60), fn() => $this->fetchHeroCarousel($now, $limit));

        return response()->json(['success' => true, 'hero_carousel' => $data]);
    }

    /**
     * career_notices, why_us, scholarships
     */
    public function quickLinks(Request $request)
    {
        [$deptId, $limit, $now] = $this->baseParams($request);

        $cacheKey = $this->cacheKey('quick_links', $deptId, $limit);

        $data = Cache::remember($cacheKey, now()->addSeconds(60), function () use ($now, $deptId, $limit) {
            return [
                'career_notices' => $this->simpleList($now, 'career_notices', $deptId, $limit, 'career_notices/view/'),
                'why_us'         => $this->simpleList($now, 'why_us',         $deptId, $limit, 'why_us/view/'),
                'scholarships'   => $this->simpleList($now, 'scholarships',   $deptId, $limit, 'scholarships/view/'),
            ];
        });

        return response()->json(array_merge(['success' => true], $data));
    }

    /**
     * notices + center_iframe + announcements
     */
    public function noticeBoard(Request $request)
    {
        [$deptId, $limit, $now] = $this->baseParams($request);

        $cacheKey = $this->cacheKey('notice_board', $deptId, $limit);

        $data = Cache::remember($cacheKey, now()->addSeconds(60), function () use ($now, $deptId, $limit) {
            return [
                'notices'       => $this->simpleList($now, 'notices',       $deptId, $limit, 'notices/view/'),
                'center_iframe' => $this->fetchCenterIframe($now),
                'announcements' => $this->simpleList($now, 'announcements', $deptId, $limit, 'announcements/view/'),
            ];
        });

        return response()->json(array_merge(['success' => true], $data));
    }

    /**
     * achievements + student_activities
     */
    public function activities(Request $request)
    {
        [$deptId, $limit, $now] = $this->baseParams($request);

        $cacheKey = $this->cacheKey('activities', $deptId, $limit);

        $data = Cache::remember($cacheKey, now()->addSeconds(60), function () use ($now, $deptId, $limit) {
            return [
                'achievements'       => $this->simpleList($now, 'achievements',       $deptId, $limit, 'achievements/view/'),
                'student_activities' => $this->simpleList($now, 'student_activities', $deptId, $limit, 'student_activities/view/'),
            ];
        });

        return response()->json(array_merge(['success' => true], $data));
    }

    /**
     * placement_notices (fixed URL: placement-notices/view/...)
     */
    public function placementNotices(Request $request)
    {
        [$deptId, $limit, $now] = $this->baseParams($request);

        $cacheKey = $this->cacheKey('placement_notices', $deptId, $limit);
        $data = Cache::remember($cacheKey, now()->addSeconds(60), fn() => $this->placementNoticesList($now, $deptId, $limit));

        return response()->json(['success' => true, 'placement_notices' => $data]);
    }

    public function courses(Request $request)
    {
        [$deptId, $limit, $now] = $this->baseParams($request);

        $cacheKey = $this->cacheKey('courses', $deptId, $limit);
        $data = Cache::remember($cacheKey, now()->addSeconds(60), fn() => $this->fetchCourses($now, $deptId, $limit));

        return response()->json(['success' => true, 'courses' => $data]);
    }

    public function stats(Request $request)
    {
        [$deptId, $limit, $now] = $this->baseParams($request);

        $cacheKey = $this->cacheKey('stats', $deptId, $limit);

        $data = Cache::remember($cacheKey, now()->addSeconds(60), fn() => $this->fetchStats($now));

        // normalize stats items to include "key"
        $normalized = $this->normalizeHomepagePayload(['stats' => $data]);
        return response()->json(['success' => true, 'stats' => $normalized['stats'] ?? null]);
    }

    public function successfulEntrepreneurs(Request $request)
    {
        [$deptId, $limit, $now] = $this->baseParams($request);

        $cacheKey = $this->cacheKey('successful_entrepreneurs', $deptId, $limit);
        $data = Cache::remember($cacheKey, now()->addSeconds(60), fn() => $this->fetchSuccessfulEntrepreneurs($now, $deptId, $limit));

        return response()->json(['success' => true, 'successful_entrepreneurs' => $data]);
    }

    public function alumniSpeak(Request $request)
    {
        [$deptId, $limit, $now] = $this->baseParams($request);

        $cacheKey = $this->cacheKey('alumni_speak', $deptId, $limit);
        $data = Cache::remember($cacheKey, now()->addSeconds(60), fn() => $this->fetchAlumniSpeak($now, $deptId));

        // normalize iframe list sort_order/url
        $normalized = $this->normalizeHomepagePayload(['alumni_speak' => $data]);
        return response()->json(['success' => true, 'alumni_speak' => $normalized['alumni_speak'] ?? null]);
    }

    public function successStories(Request $request)
    {
        [$deptId, $limit, $now] = $this->baseParams($request);

        $cacheKey = $this->cacheKey('success_stories', $deptId, $limit);
        $data = Cache::remember($cacheKey, now()->addSeconds(60), fn() => $this->fetchSuccessStories($now, $deptId, $limit));

        return response()->json(['success' => true, 'success_stories' => $data]);
    }

    public function recruiters(Request $request)
    {
        [$deptId, $limit, $now] = $this->baseParams($request);

        $cacheKey = $this->cacheKey('recruiters', $deptId, $limit);
        $data = Cache::remember($cacheKey, now()->addSeconds(60), fn() => $this->homepageRecruiters($deptId, $limit));

        return response()->json(['success' => true, 'recruiters' => $data]);
    }

    /* =========================================================
     | Fetchers (section data)
     |========================================================= */

// GrandHomepageController.php

private function fetchNoticeMarquee($now): ?array
{
    $q = DB::table('notice_marquee');

    if ($this->hasColumn('notice_marquee', 'deleted_at')) {
        $q->whereNull('deleted_at');
    }

    // status is often "0"/"1" in this table
    $q->where(function ($w) {
        $w->where('status', '1')
          ->orWhere('status', 1)
          ->orWhereIn('status', ['published', 'active']); // legacy
    });

    if ($this->hasColumn('notice_marquee', 'publish_at')) {
        $q->where(function ($w) use ($now) {
            $w->whereNull('publish_at')->orWhere('publish_at', '<=', $now);
        });
    }

    if ($this->hasColumn('notice_marquee', 'expire_at')) {
        $q->where(function ($w) use ($now) {
            $w->whereNull('expire_at')->orWhere('expire_at', '>', $now);
        });
    }

    if ($this->hasColumn('notice_marquee', 'updated_at')) {
        $q->orderByDesc('updated_at');
    } elseif ($this->hasColumn('notice_marquee', 'publish_at')) {
        $q->orderByDesc('publish_at');
    }

    $noticeMarqueeRow = $q->orderByDesc('id')->first();
    if (! $noticeMarqueeRow) return null;

    // ✅ normalize items to consistent shape: {text,url} + backward aliases
    $rawItems = $this->json($this->getVal($noticeMarqueeRow, 'notice_items_json'), []);
    $normItems = [];

    foreach ((array)$rawItems as $it) {
        if (is_string($it)) {
            $txt = trim($it);
            if ($txt === '') continue;
            $normItems[] = [
                'text'  => $txt,
                'url'   => '',
                'title' => $txt,
                'link'  => '',
                'href'  => '',
            ];
            continue;
        }

        $arr = is_array($it) ? $it : (array)$it;

        $text = trim((string)($arr['text'] ?? $arr['title'] ?? $arr['label'] ?? $arr['name'] ?? $arr['message'] ?? ''));
        $url  = trim((string)($arr['url'] ?? $arr['link'] ?? $arr['href'] ?? ''));

        if ($text === '' && $url === '') continue;

        $normItems[] = [
            'text'  => $text,
            'url'   => $url,
            'title' => $text,  // aliases for old frontends
            'link'  => $url,
            'href'  => $url,
            'sort_order' => $arr['sort_order'] ?? null,
        ];
    }

    return [
        'items' => $normItems,
        'settings' => [
            'auto_scroll'       => (int) $this->getVal($noticeMarqueeRow, 'auto_scroll', 1),
            'scroll_speed'      => (int) $this->getVal($noticeMarqueeRow, 'scroll_speed', 60),
            'scroll_latency_ms' => (int) $this->getVal($noticeMarqueeRow, 'scroll_latency_ms', 0),
            'loop'              => (int) $this->getVal($noticeMarqueeRow, 'loop', 1),
            'pause_on_hover'    => (int) $this->getVal($noticeMarqueeRow, 'pause_on_hover', 1),
            'direction'         => (string) $this->getVal($noticeMarqueeRow, 'direction', 'left'),
        ],
    ];
}



    private function fetchHeroCarousel($now, int $limit): array
    {
        $heroItems = DB::table('hero_carousel')
            ->when($this->hasColumn('hero_carousel', 'deleted_at'), fn($q) => $q->whereNull('deleted_at'))
            ->where('status', 'published')
            ->where(function ($q) use ($now) {
                $q->whereNull('publish_at')->orWhere('publish_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('expire_at')->orWhere('expire_at', '>', $now);
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->limit($limit)
            ->get()
            ->map(function ($r) {
                return [
                    'image_url'        => $this->assetUrl($this->getVal($r, 'image_url')),
                    'mobile_image_url' => $this->assetUrl($this->getVal($r, 'mobile_image_url')),
                    'overlay_text'     => $this->getVal($r, 'overlay_text'),
                    'alt_text'         => $this->getVal($r, 'alt_text'),
                ];
            })
            ->values()
            ->all();

        $heroSettingsRow = DB::table('hero_carousel_settings')
            ->when($this->hasColumn('hero_carousel_settings', 'deleted_at'), fn($q) => $q->whereNull('deleted_at'))
            ->orderByDesc('id')
            ->first();

        $heroSettings = [
            'autoplay'           => (int) $this->getVal($heroSettingsRow, 'autoplay', 1),
            'autoplay_delay_ms'  => (int) $this->getVal($heroSettingsRow, 'autoplay_delay_ms', 4000),
            'loop'               => (int) $this->getVal($heroSettingsRow, 'loop', 1),
            'pause_on_hover'     => (int) $this->getVal($heroSettingsRow, 'pause_on_hover', 1),
            'show_arrows'        => (int) $this->getVal($heroSettingsRow, 'show_arrows', 1),
            'show_dots'          => (int) $this->getVal($heroSettingsRow, 'show_dots', 1),
            'transition'         => (string) $this->getVal($heroSettingsRow, 'transition', 'slide'),
            'transition_ms'      => (int) $this->getVal($heroSettingsRow, 'transition_ms', 450),
        ];

        return [
            'items' => $heroItems,
            'settings' => $heroSettings,
        ];
    }

    private function fetchCenterIframe($now): ?array
    {
        $centerIframeRow = DB::table('center_iframes')
            ->when($this->hasColumn('center_iframes', 'deleted_at'), fn($q) => $q->whereNull('deleted_at'))
            ->whereIn('status', ['active', 'published'])
            ->where(function ($q) use ($now) {
                $q->whereNull('publish_at')->orWhere('publish_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('expire_at')->orWhere('expire_at', '>', $now);
            })
            ->orderByDesc('publish_at')
            ->orderByDesc('id')
            ->first();

        if (!$centerIframeRow) return null;

        return [
            'uuid' => $this->getVal($centerIframeRow, 'uuid'),
            'slug' => $this->getVal($centerIframeRow, 'slug'),
            'title' => $this->getVal($centerIframeRow, 'title'),
            'iframe_url' => $this->getVal($centerIframeRow, 'iframe_url'),
            'buttons_json' => $this->json($this->getVal($centerIframeRow, 'buttons_json'), []),
        ];
    }

    private function fetchCourses($now, ?int $deptId, int $limit): array
{
    $base = DB::table('courses')
        ->when($this->hasColumn('courses', 'deleted_at'), fn($q) => $q->whereNull('deleted_at'))
        ->where('status', 'published')
        ->where(function ($q) use ($now) {
            $q->whereNull('publish_at')->orWhere('publish_at', '<=', $now);
        })
        ->where(function ($q) use ($now) {
            $q->whereNull('expire_at')->orWhere('expire_at', '>', $now);
        });

    // ✅ dept filter: include global + dept rows (homepage-friendly)
    if ($deptId && $this->hasColumn('courses', 'department_id')) {
        $base->where(function ($q) use ($deptId) {
            $q->whereNull('department_id')->orWhere('department_id', (int) $deptId);
        });
    }

    $map = function ($r) {
        return [
            'uuid' => $this->getVal($r, 'uuid'),
            'department_id' => $this->getVal($r, 'department_id'),
            'title' => $this->getVal($r, 'title'),
            'slug' => $this->getVal($r, 'slug'),
            'summary' => $this->getVal($r, 'summary'),
            'body' => $this->getVal($r, 'body'),

            'cover_image' => $this->assetUrl($this->getVal($r, 'cover_image')),
            'attachments_json' => $this->json($this->getVal($r, 'attachments_json'), []),

            'program_level' => $this->getVal($r, 'program_level'),
            'program_type' => $this->getVal($r, 'program_type'),
            'mode' => $this->getVal($r, 'mode'),
            'duration_value' => (int) $this->getVal($r, 'duration_value', 0),
            'duration_unit' => $this->getVal($r, 'duration_unit'),
            'credits' => $this->getVal($r, 'credits'),

            'eligibility' => $this->getVal($r, 'eligibility'),
            'highlights' => $this->getVal($r, 'highlights'),
            'syllabus_url' => $this->assetUrl($this->getVal($r, 'syllabus_url')),
            'career_scope' => $this->getVal($r, 'career_scope'),

            'is_featured_home' => (int) $this->getVal($r, 'is_featured_home', 0),
            'sort_order' => (int) $this->getVal($r, 'sort_order', 0),
            'status' => $this->getVal($r, 'status'),

            'publish_at' => $this->iso($this->getVal($r, 'publish_at')),
            'expire_at' => $this->iso($this->getVal($r, 'expire_at')),
            'views_count' => (int) $this->getVal($r, 'views_count', 0),
            'created_at' => $this->iso($this->getVal($r, 'created_at')),
            'updated_at' => $this->iso($this->getVal($r, 'updated_at')),
            'metadata' => $this->json($this->getVal($r, 'metadata'), null),

            'url' => 'courses/view/' . ($this->getVal($r, 'uuid') ?: ($this->getVal($r, 'slug') ?: '')),
        ];
    };

    $fetch = function ($q) use ($limit, $map) {
        // homepage ordering (featured first + sort_order + latest)
        if ($this->hasColumn('courses', 'is_featured_home')) $q->orderBy('is_featured_home', 'desc');
        if ($this->hasColumn('courses', 'sort_order'))       $q->orderBy('sort_order', 'asc');

        $q->orderByRaw('COALESCE(publish_at, created_at) desc');

        return $q->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->map($map)
            ->values()
            ->all();
    };

    // ✅ try featured-only first, fallback to all published
    if ($this->hasColumn('courses', 'is_featured_home')) {
        $featuredRows = $fetch((clone $base)->where('is_featured_home', 1));
        if (!empty($featuredRows)) return $featuredRows;
    }

    return $fetch($base);
}


    private function fetchStats($now): ?array
    {
        $statsRow = DB::table('stats')
            ->when($this->hasColumn('stats', 'deleted_at'), fn($q) => $q->whereNull('deleted_at'))
            ->where('status', 'published')
            ->where(function ($q) use ($now) {
                $q->whereNull('publish_at')->orWhere('publish_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('expire_at')->orWhere('expire_at', '>', $now);
            })
            ->orderByDesc('publish_at')
            ->orderByDesc('id')
            ->first();

        if (!$statsRow) return null;

        return [
            'uuid' => $this->getVal($statsRow, 'uuid'),
            'slug' => $this->getVal($statsRow, 'slug'),
            'background_image_url' => $this->assetUrl($this->getVal($statsRow, 'background_image_url')),
            'stats_items_json' => $this->json($this->getVal($statsRow, 'stats_items_json'), []),

            'auto_scroll' => (int) $this->getVal($statsRow, 'auto_scroll', 1),
            'scroll_latency_ms' => (int) $this->getVal($statsRow, 'scroll_latency_ms', 3000),
            'loop' => (int) $this->getVal($statsRow, 'loop', 1),
            'show_arrows' => (int) $this->getVal($statsRow, 'show_arrows', 1),
            'show_dots' => (int) $this->getVal($statsRow, 'show_dots', 0),

            'status' => $this->getVal($statsRow, 'status'),
            'publish_at' => $this->iso($this->getVal($statsRow, 'publish_at')),
            'expire_at' => $this->iso($this->getVal($statsRow, 'expire_at')),
            'views_count' => (int) $this->getVal($statsRow, 'views_count', 0),
            'created_at' => $this->iso($this->getVal($statsRow, 'created_at')),
            'updated_at' => $this->iso($this->getVal($statsRow, 'updated_at')),
            'metadata' => $this->json($this->getVal($statsRow, 'metadata'), null),
        ];
    }

    private function fetchSuccessfulEntrepreneurs($now, ?int $deptId, int $limit): array
{
    $base = DB::table('successful_entrepreneurs')
        ->when($this->hasColumn('successful_entrepreneurs', 'deleted_at'), fn($q) => $q->whereNull('deleted_at'))
        ->where('status', 'published');

    // publish window (guard columns)
    if ($this->hasColumn('successful_entrepreneurs', 'publish_at')) {
        $base->where(function ($q) use ($now) {
            $q->whereNull('publish_at')->orWhere('publish_at', '<=', $now);
        });
    }
    if ($this->hasColumn('successful_entrepreneurs', 'expire_at')) {
        $base->where(function ($q) use ($now) {
            $q->whereNull('expire_at')->orWhere('expire_at', '>', $now);
        });
    }

    // ✅ dept filter: include global + dept (homepage-friendly)
    if ($deptId && $this->hasColumn('successful_entrepreneurs', 'department_id')) {
        $base->where(function ($q) use ($deptId) {
            $q->whereNull('department_id')->orWhere('department_id', $deptId);
        });
    }

    $mapRow = function ($r) {
        return [
            'uuid' => $this->getVal($r, 'uuid'),
            'department_id' => $this->getVal($r, 'department_id'),
            'user_id' => $this->getVal($r, 'user_id'),
            'slug' => $this->getVal($r, 'slug'),
            'name' => $this->getVal($r, 'name'),
            'title' => $this->getVal($r, 'title'),
            'description' => $this->getVal($r, 'description'),
            'photo_url' => $this->assetUrl($this->getVal($r, 'photo_url')),
            'company_name' => $this->getVal($r, 'company_name'),
            'company_logo_url' => $this->assetUrl($this->getVal($r, 'company_logo_url')),
            'company_website_url' => $this->getVal($r, 'company_website_url'),
            'industry' => $this->getVal($r, 'industry'),
            'founded_year' => $this->getVal($r, 'founded_year'),
            'achievement_date' => $this->getVal($r, 'achievement_date'),
            'highlights' => $this->getVal($r, 'highlights'),
            'social_links_json' => $this->json($this->getVal($r, 'social_links_json'), []),

            'is_featured_home' => (int) $this->getVal($r, 'is_featured_home', 0),
            'sort_order' => (int) $this->getVal($r, 'sort_order', 0),
            'status' => $this->getVal($r, 'status'),

            'publish_at' => $this->iso($this->getVal($r, 'publish_at')),
            'expire_at' => $this->iso($this->getVal($r, 'expire_at')),
            'views_count' => (int) $this->getVal($r, 'views_count', 0),
            'created_at' => $this->iso($this->getVal($r, 'created_at')),
            'updated_at' => $this->iso($this->getVal($r, 'updated_at')),
            'metadata' => $this->json($this->getVal($r, 'metadata'), null),
        ];
    };

    $fetch = function ($q) use ($limit, $mapRow) {
        if ($this->hasColumn('successful_entrepreneurs', 'sort_order')) {
            $q->orderBy('sort_order');
        }
        // public-like ordering
        if ($this->hasColumn('successful_entrepreneurs', 'publish_at')) {
            $q->orderByRaw('COALESCE(publish_at, created_at) desc');
        } else {
            $q->orderByDesc('created_at');
        }

        return $q->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->map($mapRow)
            ->values()
            ->all();
    };

    // ✅ try featured first, fallback to all
    if ($this->hasColumn('successful_entrepreneurs', 'is_featured_home')) {
        $featured = (clone $base)->where('is_featured_home', 1);
        $rows = $fetch($featured);
        if (!empty($rows)) return $rows;
    }

    return $fetch($base);
}


    private function fetchAlumniSpeak($now, ?int $deptId): ?array
    {
        $alumniQ = DB::table('alumni_speak')
            ->when($this->hasColumn('alumni_speak', 'deleted_at'), fn($q) => $q->whereNull('deleted_at'))
            ->where('status', 'published')
            ->where(function ($q) use ($now) {
                $q->whereNull('publish_at')->orWhere('publish_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('expire_at')->orWhere('expire_at', '>', $now);
            });

        if ($deptId && $this->hasColumn('alumni_speak', 'department_id')) {
            $alumniQ->where('department_id', $deptId);
        }

        $alumniRow = $alumniQ->orderByDesc('publish_at')->orderByDesc('id')->first();
        if (!$alumniRow) return null;

        return [
            'uuid' => $this->getVal($alumniRow, 'uuid'),
            'department_id' => $this->getVal($alumniRow, 'department_id'),
            'slug' => $this->getVal($alumniRow, 'slug'),
            'title' => $this->getVal($alumniRow, 'title'),
            'description' => $this->getVal($alumniRow, 'description'),
            'iframe_urls_json' => $this->json($this->getVal($alumniRow, 'iframe_urls_json'), []),

            'auto_scroll' => (int) $this->getVal($alumniRow, 'auto_scroll', 1),
            'scroll_latency_ms' => (int) $this->getVal($alumniRow, 'scroll_latency_ms', 3000),
            'loop' => (int) $this->getVal($alumniRow, 'loop', 1),
            'show_arrows' => (int) $this->getVal($alumniRow, 'show_arrows', 1),
            'show_dots' => (int) $this->getVal($alumniRow, 'show_dots', 1),
            'sort_order' => (int) $this->getVal($alumniRow, 'sort_order', 0),

            'status' => $this->getVal($alumniRow, 'status'),
            'publish_at' => $this->iso($this->getVal($alumniRow, 'publish_at')),
            'expire_at' => $this->iso($this->getVal($alumniRow, 'expire_at')),
            'views_count' => (int) $this->getVal($alumniRow, 'views_count', 0),
            'created_at' => $this->iso($this->getVal($alumniRow, 'created_at')),
            'updated_at' => $this->iso($this->getVal($alumniRow, 'updated_at')),
            'metadata' => $this->json($this->getVal($alumniRow, 'metadata'), null),
        ];
    }

    private function fetchSuccessStories($now, ?int $deptId, int $limit): array
{
    $base = DB::table('success_stories as s')
        ->when($this->hasColumn('success_stories', 'deleted_at'), fn($q) => $q->whereNull('s.deleted_at'))
        ->where('s.status', 'published');

    // publish window (guard columns)
    if ($this->hasColumn('success_stories', 'publish_at')) {
        $base->where(function ($q) use ($now) {
            $q->whereNull('s.publish_at')->orWhere('s.publish_at', '<=', $now);
        });
    }
    if ($this->hasColumn('success_stories', 'expire_at')) {
        $base->where(function ($q) use ($now) {
            $q->whereNull('s.expire_at')->orWhere('s.expire_at', '>', $now);
        });
    }

    // ✅ dept filter: include global + dept rows for homepage
    if ($deptId && $this->hasColumn('success_stories', 'department_id')) {
        $base->where(function ($q) use ($deptId) {
            $q->whereNull('s.department_id')->orWhere('s.department_id', (int)$deptId);
        });
    }

    $map = function ($r) {
        return [
            'uuid' => $this->getVal($r, 'uuid'),
            'department_id' => $this->getVal($r, 'department_id'),
            'slug' => $this->getVal($r, 'slug'),
            'name' => $this->getVal($r, 'name'),
            'title' => $this->getVal($r, 'title'),
            'description' => $this->getVal($r, 'description'),
            'quote' => $this->getVal($r, 'quote'),
            'date' => $this->iso($this->getVal($r, 'date')),
            'year' => $this->getVal($r, 'year'),
            'photo_url' => $this->assetUrl($this->getVal($r, 'photo_url')),
            'social_links_json' => $this->json($this->getVal($r, 'social_links_json'), []),
            'is_featured_home' => (int) $this->getVal($r, 'is_featured_home', 0),
            'sort_order' => (int) $this->getVal($r, 'sort_order', 0),
            'publish_at' => $this->iso($this->getVal($r, 'publish_at')),
            'expire_at' => $this->iso($this->getVal($r, 'expire_at')),
            'views_count' => (int) $this->getVal($r, 'views_count', 0),
            'created_at' => $this->iso($this->getVal($r, 'created_at')),
            'updated_at' => $this->iso($this->getVal($r, 'updated_at')),
            'metadata' => $this->json($this->getVal($r, 'metadata'), null),
        ];
    };

    $fetch = function ($q) use ($limit, $map) {
        // match your publicIndex ordering
        if ($this->hasColumn('success_stories', 'is_featured_home')) $q->orderBy('s.is_featured_home', 'desc');
        if ($this->hasColumn('success_stories', 'sort_order'))       $q->orderBy('s.sort_order', 'asc');

        if ($this->hasColumn('success_stories', 'publish_at')) {
            $q->orderByRaw('COALESCE(s.publish_at, s.created_at) desc');
        } else {
            $q->orderByDesc('s.created_at');
        }

        return $q->orderByDesc('s.id')
            ->limit($limit)
            ->get()
            ->map($map)
            ->values()
            ->all();
    };

    // ✅ featured first, fallback to all
    if ($this->hasColumn('success_stories', 'is_featured_home')) {
        $rows = $fetch((clone $base)->where('s.is_featured_home', 1));
        if (!empty($rows)) return $rows;
    }

    return $fetch($base);
}


    /* =========================================================
     | Normalizers (same as yours, reused)
     |========================================================= */

    private function normalizeHomepagePayload(array $raw): array
    {
        foreach ([
            'career_notices', 'why_us', 'scholarships',
            'notices', 'announcements', 'achievements', 'student_activities', 'placement_notices',
            'courses', 'successful_entrepreneurs', 'success_stories', 'recruiters',
        ] as $k) {
            if (!isset($raw[$k]) || !is_array($raw[$k])) $raw[$k] = [];
        }

        if (isset($raw['stats']) && is_array($raw['stats'])) {
            $items = $raw['stats']['stats_items_json'] ?? [];
            if (is_array($items)) {
                $normalized = [];
                $i = 1;
                foreach ($items as $it) {
                    $arr = is_array($it) ? $it : (array) $it;
                    $sort = isset($arr['sort_order']) ? (int)$arr['sort_order'] : $i++;
                    $label = $arr['key'] ?? $arr['label'] ?? $arr['title'] ?? null;

                    $normalized[] = [
                        'sort_order' => $sort,
                        'key'        => $label,
                        'value'      => $arr['value'] ?? $arr['count'] ?? $arr['number'] ?? null,
                        'icon_class' => $arr['icon_class'] ?? $arr['icon'] ?? null,
                        'label'      => $arr['label'] ?? null,
                    ];
                }
                $raw['stats']['stats_items_json'] = $normalized;
            }
        }

        if (isset($raw['alumni_speak']) && is_array($raw['alumni_speak'])) {
            $list = $raw['alumni_speak']['iframe_urls_json'] ?? [];
            if (is_array($list)) {
                $out = [];
                $i = 1;
                foreach ($list as $row) {
                    $arr = is_array($row) ? $row : (array) $row;
                    $out[] = [
                        'sort_order' => isset($arr['sort_order']) ? (int)$arr['sort_order'] : $i++,
                        'title' => $arr['title'] ?? null,
                        'url'   => $arr['url'] ?? $arr['iframe_url'] ?? null,
                        'iframe'    => $arr['iframe'] ?? null,
                        'provider'  => $arr['provider'] ?? null,
                        'video_id'  => $arr['video_id'] ?? null,
                    ];
                }
                $raw['alumni_speak']['iframe_urls_json'] = $out;
            }
        }

        return $raw;
    }

    /* =========================================================
     | Legacy builder (unchanged from yours)
     |========================================================= */

    private function buildFrontendPayload(array $raw): array
    {
        // hero slides
        $heroSlides = [];
        $heroItems = $raw['hero_carousel']['items'] ?? [];
        foreach ((array)$heroItems as $it) {
            $heroSlides[] = [
                'image'        => $this->assetUrl($this->getVal($it, 'image_url')),
                'mobile_image' => $this->assetUrl($this->getVal($it, 'mobile_image_url')),
                'icon'         => 'fa-solid fa-graduation-cap',
                'kicker'       => $this->text($this->getVal($it, 'alt_text')),
                'title'        => $this->text($this->getVal($it, 'overlay_text')),
                'buttons'      => [],
            ];
        }

        // marquee announcements
        $announcements = [];
        $mItems = $raw['notice_marquee']['items'] ?? [];
        foreach ((array)$mItems as $it) {
            $url = null;
            $text = '';

            if (is_array($it)) {
                $text = $this->text($it['title'] ?? $it['label'] ?? $it['text'] ?? '');
                $url  = $this->text($it['url'] ?? $it['link'] ?? '');
                if ($url === '') $url = null;
            } else {
                $text = $this->text($it);
            }

            if ($text === '') continue;
            $announcements[] = ['text' => $text, 'url' => $url];
        }

        $mapTextUrl = function ($rows) {
            $out = [];
            foreach ((array)$rows as $r) {
                $title = $this->text($this->getVal($r, 'title'));
                $url   = $this->text($this->getVal($r, 'url'));
                if ($title === '') continue;
                $out[] = ['text' => $title, 'url' => ($url === '' ? null : $url)];
            }
            return $out;
        };

        // testimonials
        $testimonials = [];
        foreach ((array)($raw['successful_entrepreneurs'] ?? []) as $t) {
            $name = $this->text($this->getVal($t, 'name'));
            $title = $this->text($this->getVal($t, 'title'));
            $company = $this->text($this->getVal($t, 'company_name'));
            $role = trim($title . ($company !== '' ? (', ' . $company) : ''));

            $testimonials[] = [
                'avatar' => $this->assetUrl($this->getVal($t, 'photo_url')),
                'text'   => $this->text($this->getVal($t, 'description')),
                'name'   => ($name !== '' ? $name : '—'),
                'role'   => ($role !== '' ? $role : ''),
            ];
        }

        // alumni videos
        $alumniVideos = [];
        $iframeUrls = $raw['alumni_speak']['iframe_urls_json'] ?? [];
        foreach ((array)$iframeUrls as $u) {
            $u = is_array($u) ? ($u['url'] ?? '') : $u;
            $u = $this->text($u);
            if ($u === '') continue;
            $alumniVideos[] = ['url' => $u];
        }

        // success stories cards
        $successStories = [];
        foreach ((array)($raw['success_stories'] ?? []) as $s) {
            $successStories[] = [
                'image'       => $this->assetUrl($this->getVal($s, 'photo_url')),
                'description' => $this->text($this->getVal($s, 'description')),
                'name'        => $this->text($this->getVal($s, 'name')),
                'role'        => $this->text($this->getVal($s, 'title') ?? $this->getVal($s, 'year')),
            ];
        }

        // courses cards
        $courses = [];
        foreach ((array)($raw['courses'] ?? []) as $c) {
            $url = $this->text($this->getVal($c, 'url'));
            if ($url === '') $url = '#';

            $courses[] = [
                'image'        => $this->assetUrl($this->getVal($c, 'cover_image')),
                'name'         => $this->text($this->getVal($c, 'title')),
                'description'  => $this->text($this->getVal($c, 'summary')),
                'vision_link'  => $url,
                'peo_link'     => $url,
                'faculty_link' => $url,
                'dept_link'    => $url,
            ];
        }

        // recruiters
        $recruiters = [];
        foreach ((array)($raw['recruiters'] ?? []) as $r) {
            $recruiters[] = [
                'name' => $this->text($this->getVal($r, 'title') ?? $this->getVal($r, 'slug')),
                'logo' => $this->assetUrl($this->getVal($r, 'logo_url')),
            ];
        }

        // stats mapping
        $statsCounters = $this->extractStatsCounters($raw['stats']['stats_items_json'] ?? []);
        $stats = [
            'courses'    => $statsCounters['courses'] ?? null,
            'facilities' => $statsCounters['facilities'] ?? null,
            'students'   => $statsCounters['students'] ?? null,
            'alumni'     => $statsCounters['alumni'] ?? null,
        ];

        return [
            'hero_slides'       => $heroSlides,
            'announcements'     => $announcements,

            'career_list'       => $mapTextUrl($raw['career_notices'] ?? []),
            'why_msit'          => $mapTextUrl($raw['why_us'] ?? []),
            'scholarships'      => $mapTextUrl($raw['scholarships'] ?? []),

            'notices'           => $mapTextUrl($raw['notices'] ?? []),
            'announcement_list' => $mapTextUrl($raw['announcements'] ?? []),
            'placement_notices' => $mapTextUrl($raw['placement_notices'] ?? []),

            'achievements'      => $mapTextUrl($raw['achievements'] ?? []),
            'activities'        => $mapTextUrl($raw['student_activities'] ?? []),

            'testimonials'      => $testimonials,
            'alumni_videos'     => $alumniVideos,
            'success_stories'   => $successStories,

            'courses'           => $courses,
            'recruiters'        => $recruiters,

            'main_video'        => $this->text($raw['center_iframe']['iframe_url'] ?? ''),
            'stats'             => $stats,
        ];
    }

    private function extractStatsCounters($items): array
    {
        $out = ['courses' => null, 'facilities' => null, 'students' => null, 'alumni' => null];

        $pickVal = function ($it) {
            $rawVal = null;
            if (is_array($it)) {
                $rawVal = $it['value'] ?? $it['count'] ?? $it['number'] ?? null;
            } elseif (is_object($it)) {
                $rawVal = $it->value ?? $it->count ?? $it->number ?? null;
            }

            $v = $this->text($rawVal);
            if ($v === '') return null;

            $digits = preg_replace('/[^\d]/', '', $v);
            return ($digits === '') ? null : (int)$digits;
        };

        foreach ((array)$items as $it) {
            $labelRaw = null;
            if (is_array($it)) $labelRaw = $it['label'] ?? $it['key'] ?? $it['title'] ?? null;
            if (is_object($it)) $labelRaw = $it->label ?? $it->key ?? $it->title ?? null;

            $label = strtolower($this->text($labelRaw));
            $val = $pickVal($it);
            if ($val === null) continue;

            if (str_contains($label, 'course'))        $out['courses'] ??= $val;
            else if (str_contains($label, 'facilit'))  $out['facilities'] ??= $val;
            else if (str_contains($label, 'student'))  $out['students'] ??= $val;
            else if (str_contains($label, 'alumni'))   $out['alumni'] ??= $val;
        }

        return $out;
    }

    /* =========================================================
     | Lists + Recruiters (yours, with placement URL fix)
     |========================================================= */

    private function simpleList($now, string $table, ?int $deptId, int $limit, string $urlPrefix)
    {
        $q = DB::table($table);

        if ($this->hasColumn($table, 'deleted_at')) $q->whereNull('deleted_at');

        if ($this->hasColumn($table, 'status')) {
            if ($table === 'center_iframes') $q->whereIn('status', ['active', 'published']);
            else $q->where('status', 'published');
        }

        if ($this->hasColumn($table, 'publish_at')) {
            $q->where(function ($qq) use ($now) {
                $qq->whereNull('publish_at')->orWhere('publish_at', '<=', $now);
            });
        }
        if ($this->hasColumn($table, 'expire_at')) {
            $q->where(function ($qq) use ($now) {
                $qq->whereNull('expire_at')->orWhere('expire_at', '>', $now);
            });
        }

        if ($deptId && $this->hasColumn($table, 'department_id')) {
            $q->where(function ($qq) use ($deptId) {
                $qq->whereNull('department_id')->orWhere('department_id', $deptId);
            });
        }

        if ($this->hasColumn($table, 'publish_at')) $q->orderByDesc('publish_at');
        $q->orderByDesc('id');

        $rows = $q->limit($limit)->get();

        return $rows->map(function ($r) use ($urlPrefix) {
            $title = $this->getVal($r, 'title', '-');
            $uuidOrSlug = $this->getVal($r, 'uuid') ?: ($this->getVal($r, 'slug') ?: '');
            return [
                'title' => $title ?? '-',
                'url'   => $urlPrefix . $uuidOrSlug,
            ];
        })->values()->all();
    }

    private function placementNoticesList($now, ?int $deptId, int $limit)
    {
        $q = DB::table('placement_notices')
            ->when($this->hasColumn('placement_notices', 'deleted_at'), fn($qq) => $qq->whereNull('deleted_at'))
            ->where('status', 'published')
            ->where(function ($qq) use ($now) {
                $qq->whereNull('publish_at')->orWhere('publish_at', '<=', $now);
            })
            ->where(function ($qq) use ($now) {
                $qq->whereNull('expire_at')->orWhere('expire_at', '>', $now);
            });

        if ($deptId) {
            $q->where(function ($qq) use ($deptId) {
                $qq->whereNull('department_ids')
                    ->orWhereRaw("JSON_CONTAINS(department_ids, ?)", [json_encode($deptId)]);
            });
        }

        return $q->orderBy('sort_order')->orderByDesc('id')->limit($limit)->get()
            ->map(function ($r) {
                return [
                    'title' => $this->getVal($r, 'title', '-'),
                    // ✅ FIXED: kebab-case
                    'url'   => 'placement-notices/view/' . ($this->getVal($r, 'uuid') ?: ''),
                ];
            })
            ->values()
            ->all();
    }

    private function homepageRecruiters(?int $deptId, int $limit): array
    {
        $mkBase = function (?int $deptId, bool $applyDeptFilter) {
            $q = DB::table('recruiters')
                ->when($this->hasColumn('recruiters', 'deleted_at'), fn($qq) => $qq->whereNull('deleted_at'))
                ->whereIn('status', ['active', 'published']);

            if ($applyDeptFilter && $deptId && $this->hasColumn('recruiters', 'department_id')) {
                $q->where(function ($qq) use ($deptId) {
                    $qq->whereNull('department_id')->orWhere('department_id', $deptId);
                });
            }

            return $q;
        };

        $fetch = function ($q) use ($limit) {
            return $q->orderBy('is_featured_home', 'desc')
                ->orderBy('sort_order', 'asc')
                ->orderByDesc('id')
                ->limit($limit)
                ->get()
                ->map(function ($r) {
                    return [
                        'uuid' => $this->getVal($r, 'uuid'),
                        'department_id' => $this->getVal($r, 'department_id'),
                        'slug' => $this->getVal($r, 'slug'),
                        'title' => $this->getVal($r, 'title'),
                        'description' => $this->getVal($r, 'description'),
                        'logo_url' => $this->assetUrl($this->getVal($r, 'logo_url')),
                        'job_roles_json' => $this->json($this->getVal($r, 'job_roles_json'), []),
                        'is_featured_home' => (int) $this->getVal($r, 'is_featured_home', 0),
                        'sort_order' => (int) $this->getVal($r, 'sort_order', 0),
                        'status' => $this->getVal($r, 'status'),
                        'created_at' => $this->iso($this->getVal($r, 'created_at')),
                        'updated_at' => $this->iso($this->getVal($r, 'updated_at')),
                        'metadata' => $this->json($this->getVal($r, 'metadata'), null),
                    ];
                })
                ->values()
                ->all();
        };

        $base = $mkBase($deptId, true);

        $featured = (clone $base)->where('is_featured_home', 1);
        $rows = $fetch($featured);

        if (empty($rows)) $rows = $fetch($base);

        if (empty($rows) && $deptId) {
            $base2 = $mkBase($deptId, false);

            $featured2 = (clone $base2)->where('is_featured_home', 1);
            $rows = $fetch($featured2);

            if (empty($rows)) $rows = $fetch($base2);
        }

        return $rows;
    }

    /* =========================================================
     | Core helpers
     |========================================================= */

    private function baseParams(Request $request): array
    {
        $departmentParam = $request->query('department');
        $deptId = $this->resolveDepartmentId($departmentParam);

        $limit = (int) $request->query('limit', 12);
        if ($limit < 1) $limit = 12;
        if ($limit > 50) $limit = 50;

        return [$deptId, $limit, now()];
    }

    private function cacheKey(string $section, ?int $deptId, int $limit): string
    {
        // bump version when response shapes change
        return 'grand_homepage:v3:' . $section . ':' . ($deptId ? ('dept:' . $deptId) : 'dept:all') . ':limit:' . $limit;
    }

    private function json($value, $default)
    {
        if ($value === null || $value === '') return $default;
        if (is_array($value)) return $value;

        $decoded = json_decode($value, true);
        return (json_last_error() === JSON_ERROR_NONE) ? $decoded : $default;
    }

    private function iso($value): ?string
    {
        if ($value === null || $value === '') return null;

        try {
            if ($value instanceof \DateTimeInterface) {
                return Carbon::instance($value)->toIso8601String();
            }
            return Carbon::parse((string)$value)->toIso8601String();
        } catch (\Throwable $e) {
            return (string)$value;
        }
    }

    private function assetUrl($path): ?string
    {
        $p = $this->text($path, '');
        if ($p === '') return null;

        if (preg_match('~^https?://~i', $p)) return $p;
        if (strpos($p, '//') === 0) return 'https:' . $p;

        return url('/' . ltrim($p, '/'));
    }

    private function resolveDepartmentId($departmentParam): ?int
    {
        if (!$departmentParam) return null;

        if (is_numeric($departmentParam)) return (int) $departmentParam;

        try {
            $row = DB::table('departments')
                ->select('id')
                ->where('uuid', (string)$departmentParam)
                ->orWhere('slug', (string)$departmentParam)
                ->first();

            return $row ? (int) $row->id : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function hasColumn(string $table, string $column): bool
    {
        try {
            $cols = Cache::remember("tbl_cols:$table", now()->addMinutes(10), function () use ($table) {
                return DB::getSchemaBuilder()->getColumnListing($table);
            });
            return in_array($column, $cols, true);
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function getVal($row, string $key, $default = null)
    {
        if (is_array($row)) return $row[$key] ?? $default;

        if (is_object($row)) {
            return property_exists($row, $key) ? $row->{$key} : $default;
        }

        return $default;
    }

    private function text($v, string $fallback = ''): string
    {
        if ($v === null) return $fallback;

        if (is_string($v) || is_numeric($v) || is_bool($v)) {
            $s = trim((string)$v);
            return $s === '' ? $fallback : $s;
        }

        if (is_array($v)) {
            foreach (['text','title','label','name','url','value','count','number'] as $k) {
                if (isset($v[$k]) && !is_array($v[$k]) && !is_object($v[$k])) {
                    $s = trim((string)$v[$k]);
                    if ($s !== '') return $s;
                }
            }

            $flat = [];
            array_walk_recursive($v, function ($x) use (&$flat) {
                if (is_scalar($x)) $flat[] = trim((string)$x);
            });

            $flat = array_values(array_filter($flat, fn($x) => $x !== ''));
            if (!empty($flat)) return implode(' ', $flat);

            return $fallback;
        }

        if (is_object($v)) {
            if (method_exists($v, '__toString')) {
                $s = trim((string)$v);
                return $s === '' ? $fallback : $s;
            }
            return $fallback;
        }

        return $fallback;
    }
}
