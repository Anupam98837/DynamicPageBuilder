<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class GrandHomepageController extends Controller
{
    /**
     * ✅ NEW RESPONSE SHAPE (as you want):
     * {
     *   "success": true,
     *   "hero_carousel": {...},
     *   "career_notices": [...],
     *   "why_us": [...],
     *   ...
     * }
     *
     * If you still need the OLD home.blade.js payload, call:
     *   /api/grand-homepage?legacy=1
     * Then it will return:
     *   { success:true, data:{hero_slides,...} }
     */
    public function index(Request $request)
    {
        $departmentParam = $request->query('department'); // id|uuid|slug supported (best-effort)
        $deptId = $this->resolveDepartmentId($departmentParam);

        $limit = (int) $request->query('limit', 12);
        if ($limit < 1) $limit = 12;
        if ($limit > 50) $limit = 50;

        $legacy = filter_var($request->query('legacy', false), FILTER_VALIDATE_BOOLEAN);

        // bump version so old cache doesn’t clash with new output expectations
        $cacheKey = 'grand_homepage:v2:' . ($deptId ? ('dept:' . $deptId) : 'dept:all') . ':limit:' . $limit;

        $raw = Cache::remember($cacheKey, now()->addSeconds(60), function () use ($deptId, $limit) {
            $now = now();

            // -------------------------
            // 1) Notice Marquee (single)
            // -------------------------
            $noticeMarqueeRow = DB::table('notice_marquee')
                ->when($this->hasColumn('notice_marquee', 'deleted_at'), fn($q) => $q->whereNull('deleted_at'))
                ->whereIn('status', ['published', 'active'])
                ->where(function ($q) use ($now) {
                    $q->whereNull('publish_at')->orWhere('publish_at', '<=', $now);
                })
                ->where(function ($q) use ($now) {
                    $q->whereNull('expire_at')->orWhere('expire_at', '>', $now);
                })
                ->orderByDesc('publish_at')
                ->orderByDesc('id')
                ->first();

            $noticeMarquee = null;
            if ($noticeMarqueeRow) {
                $noticeMarquee = [
                    'items' => $this->json($this->getVal($noticeMarqueeRow, 'notice_items_json'), []),
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

            // -------------------------
            // 2) Hero Carousel (items + settings)
            // -------------------------
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

            $heroCarousel = [
                'items' => $heroItems,
                'settings' => $heroSettings,
            ];

            // -------------------------
            // 3) Career / Why Us / Scholarships (simple lists)
            // -------------------------
            $careerNotices = $this->simpleList($now, 'career_notices', $deptId, $limit, 'career_notices/view/');
            $whyUs         = $this->simpleList($now, 'why_us',         $deptId, $limit, 'why_us/view/');
            $scholarships  = $this->simpleList($now, 'scholarships',   $deptId, $limit, 'scholarships/view/');

            // -------------------------
            // 4) Notices / Center Iframe / Announcements
            // -------------------------
            $notices       = $this->simpleList($now, 'notices',        $deptId, $limit, 'notices/view/');
            $announcements = $this->simpleList($now, 'announcements',  $deptId, $limit, 'announcements/view/');

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

            $centerIframe = null;
            if ($centerIframeRow) {
                $centerIframe = [
                    'uuid' => $this->getVal($centerIframeRow, 'uuid'),
                    'slug' => $this->getVal($centerIframeRow, 'slug'),
                    'title' => $this->getVal($centerIframeRow, 'title'),
                    'iframe_url' => $this->getVal($centerIframeRow, 'iframe_url'),
                    'buttons_json' => $this->json($this->getVal($centerIframeRow, 'buttons_json'), []),
                ];
            }

            // -------------------------
            // 5) Achievements / Student Activities / Placement Notices
            // -------------------------
            $achievements      = $this->simpleList($now, 'achievements',       $deptId, $limit, 'achievements/view/');
            $studentActivities = $this->simpleList($now, 'student_activities', $deptId, $limit, 'student_activities/view/');
            $placementNotices  = $this->placementNoticesList($now, $deptId, $limit);

            // -------------------------
            // 6) Courses Offered (featured list)
            // -------------------------
            $coursesQ = DB::table('courses')
                ->when($this->hasColumn('courses', 'deleted_at'), fn($q) => $q->whereNull('deleted_at'))
                ->where('status', 'published')
                ->where(function ($q) use ($now) {
                    $q->whereNull('publish_at')->orWhere('publish_at', '<=', $now);
                })
                ->where(function ($q) use ($now) {
                    $q->whereNull('expire_at')->orWhere('expire_at', '>', $now);
                })
                ->where('is_featured_home', 1);

            if ($deptId && $this->hasColumn('courses', 'department_id')) $coursesQ->where('department_id', $deptId);

            $courses = $coursesQ
                ->orderBy('sort_order')
                ->orderByDesc('id')
                ->limit($limit)
                ->get()
                ->map(function ($r) {
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
                })
                ->values()
                ->all();

            // -------------------------
            // 7) Key Stats (single “current” = latest published)
            // -------------------------
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

            $stats = null;
            if ($statsRow) {
                $stats = [
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

            // -------------------------
            // 8) Successful Entrepreneurs (featured list)
            // -------------------------
            $entreQ = DB::table('successful_entrepreneurs')
                ->when($this->hasColumn('successful_entrepreneurs', 'deleted_at'), fn($q) => $q->whereNull('deleted_at'))
                ->where('status', 'published')
                ->where(function ($q) use ($now) {
                    $q->whereNull('publish_at')->orWhere('publish_at', '<=', $now);
                })
                ->where(function ($q) use ($now) {
                    $q->whereNull('expire_at')->orWhere('expire_at', '>', $now);
                })
                ->where('is_featured_home', 1);

            if ($deptId && $this->hasColumn('successful_entrepreneurs', 'department_id')) $entreQ->where('department_id', $deptId);

            $successfulEntrepreneurs = $entreQ
                ->orderBy('sort_order')
                ->orderByDesc('id')
                ->limit($limit)
                ->get()
                ->map(function ($r) {
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
                })
                ->values()
                ->all();

            // -------------------------
            // 9) Alumni Speak (single latest published)
            // -------------------------
            $alumniQ = DB::table('alumni_speak')
                ->when($this->hasColumn('alumni_speak', 'deleted_at'), fn($q) => $q->whereNull('deleted_at'))
                ->where('status', 'published')
                ->where(function ($q) use ($now) {
                    $q->whereNull('publish_at')->orWhere('publish_at', '<=', $now);
                })
                ->where(function ($q) use ($now) {
                    $q->whereNull('expire_at')->orWhere('expire_at', '>', $now);
                });

            if ($deptId && $this->hasColumn('alumni_speak', 'department_id')) $alumniQ->where('department_id', $deptId);

            $alumniRow = $alumniQ->orderByDesc('publish_at')->orderByDesc('id')->first();

            $alumniSpeak = null;
            if ($alumniRow) {
                $alumniSpeak = [
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

            // -------------------------
            // 10) Success Stories (featured list)
            // -------------------------
            $ssQ = DB::table('success_stories')
                ->when($this->hasColumn('success_stories', 'deleted_at'), fn($q) => $q->whereNull('deleted_at'))
                ->where('status', 'published')
                ->where(function ($q) use ($now) {
                    $q->whereNull('publish_at')->orWhere('publish_at', '<=', $now);
                })
                ->where(function ($q) use ($now) {
                    $q->whereNull('expire_at')->orWhere('expire_at', '>', $now);
                })
                ->where('is_featured_home', 1);

            if ($deptId && $this->hasColumn('success_stories', 'department_id')) $ssQ->where('department_id', $deptId);

            $successStories = $ssQ
                ->orderBy('sort_order')
                ->orderByDesc('id')
                ->limit($limit)
                ->get()
                ->map(function ($r) {
                    return [
                        'uuid' => $this->getVal($r, 'uuid'),
                        'department_id' => $this->getVal($r, 'department_id'),
                        'slug' => $this->getVal($r, 'slug'),
                        'name' => $this->getVal($r, 'name'),
                        'title' => $this->getVal($r, 'title'),
                        'description' => $this->getVal($r, 'description'),
                        'photo_url' => $this->assetUrl($this->getVal($r, 'photo_url')),
                        'date' => $this->getVal($r, 'date'),
                        'year' => $this->getVal($r, 'year'),
                        'quote' => $this->getVal($r, 'quote'),
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
                })
                ->values()
                ->all();

            // -------------------------
            // 11) Top Recruiters (featured list) ✅ FIXED FALLBACK
            // -------------------------
            $recruiters = $this->homepageRecruiters($deptId, $limit);

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

        // ✅ Normalize to your desired structure (small key fixes)
        $payload = $this->normalizeHomepagePayload($raw);

        // ✅ LEGACY: keep your current home.blade.js working if you call ?legacy=1
        if ($legacy) {
            return response()->json([
                'success' => true,
                'data' => $this->buildFrontendPayload($payload),
            ]);
        }

        // ✅ NEW DEFAULT: root-level keys (no "data", no "raw")
        return response()->json(array_merge(['success' => true], $payload));
    }

    /**
     * ✅ Make the payload closer to the JSON samples you showed:
     * - stats_items_json: uses "key" instead of only "label"
     * - alumni_speak.iframe_urls_json: ensure sort_order exists, keep url/title
     */
    private function normalizeHomepagePayload(array $raw): array
    {
        // Ensure arrays
        foreach ([
            'career_notices', 'why_us', 'scholarships',
            'notices', 'announcements', 'achievements', 'student_activities', 'placement_notices',
            'courses', 'successful_entrepreneurs', 'success_stories', 'recruiters',
        ] as $k) {
            if (!isset($raw[$k]) || !is_array($raw[$k])) $raw[$k] = [];
        }

        // Normalize stats items: label -> key
        if (isset($raw['stats']) && is_array($raw['stats'])) {
            $items = $raw['stats']['stats_items_json'] ?? [];
            if (is_array($items)) {
                $normalized = [];
                $i = 1;
                foreach ($items as $it) {
                    $arr = is_array($it) ? $it : (array) $it;

                    // prefer existing
                    $sort = isset($arr['sort_order']) ? (int)$arr['sort_order'] : $i++;

                    $label = $arr['key'] ?? $arr['label'] ?? $arr['title'] ?? null;

                    $normalized[] = [
                        'sort_order' => $sort,
                        'key'        => $label,
                        'value'      => $arr['value'] ?? $arr['count'] ?? $arr['number'] ?? null,
                        'icon_class' => $arr['icon_class'] ?? $arr['icon'] ?? null,

                        // keep original fields too (safe)
                        'label'      => $arr['label'] ?? null,
                    ];
                }
                $raw['stats']['stats_items_json'] = $normalized;
            }
        }

        // Normalize alumni_speak iframe list: ensure sort_order, keep title/url
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

                        // keep extra fields if present (your DB currently stores these)
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

    /**
     * ✅ FIX: Recruiters were empty mostly because:
     * - homepage query was strict: status='active' AND is_featured_home=1
     * - if none are featured, it returned []
     *
     * Now:
     * 1) Try featured first
     * 2) If empty, fallback to active/published recruiters
     * 3) If department filter causes empty, fallback without dept filter
     */
    private function homepageRecruiters(?int $deptId, int $limit): array
    {
        $mkBase = function (?int $deptId, bool $applyDeptFilter) {
            $q = DB::table('recruiters')
                ->when($this->hasColumn('recruiters', 'deleted_at'), fn($qq) => $qq->whereNull('deleted_at'))
                // allow legacy 'published' too, in case old rows exist
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

        // A) dept filter ON (if dept provided)
        $base = $mkBase($deptId, true);

        // A1) featured first
        $featured = (clone $base)->where('is_featured_home', 1);
        $rows = $fetch($featured);

        // A2) fallback to any active/published (still with dept filter)
        if (empty($rows)) {
            $rows = $fetch($base);
        }

        // B) if still empty and deptId was restricting too much, try without dept filter
        if (empty($rows) && $deptId) {
            $base2 = $mkBase($deptId, false);

            $featured2 = (clone $base2)->where('is_featured_home', 1);
            $rows = $fetch($featured2);

            if (empty($rows)) {
                $rows = $fetch($base2);
            }
        }

        return $rows;
    }

    /**
     * Build the OLD frontend payload expected by your current home.blade.php JS:
     * data.hero_slides, data.announcements, data.career_list, ...
     */
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

        // alumni videos (legacy wants [{url:"..."}])
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

        // recruiters (legacy wants {name,logo})
        $recruiters = [];
        foreach ((array)($raw['recruiters'] ?? []) as $r) {
            $recruiters[] = [
                'name' => $this->text($this->getVal($r, 'title') ?? $this->getVal($r, 'slug')),
                'logo' => $this->assetUrl($this->getVal($r, 'logo_url')),
            ];
        }

        // stats mapping (legacy wants fixed keys)
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

    /**
     * Simple list:
     * [{ title: "...", url: "module/view/{uuid}" }, ...]
     */
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
                    'url'   => 'placement_notices/view/' . ($this->getVal($r, 'uuid') ?: ''),
                ];
            })
            ->values()
            ->all();
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

    /**
     * ✅ Converts scalar/object/array safely into a string (no Array-to-string warnings)
     */
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
