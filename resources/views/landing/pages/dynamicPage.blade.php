{{-- resources/views/test.blade.php --}}
@php
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Schema;
    use Illuminate\Support\Str;

    /* ============================================================
     | ✅ 1) Resolve page by matching CURRENT URL against pages.page_link (and page_url fallback)
     |     ✅ 2) Pull meta tags server-side so they appear in "View Page Source"
     |     ✅ 3) Same for submenus (&submenu=... in path OR ?submenu=...)
     |
     | NOTE: For this to work for header links like /about-us, your route should point those
     | URLs to THIS view (catch-all or explicit routes).
     * ============================================================ */

    if (!function_exists('dp_try_json')) {
        function dp_try_json($v): ?array {
            if ($v === null) return null;
            if (is_array($v)) return $v;
            if (!is_string($v)) return null;
            $s = trim($v);
            if ($s === '' || strtolower($s) === 'null') return null;
            $d = json_decode($s, true);
            return is_array($d) ? $d : null;
        }
    }

    if (!function_exists('dp_pick_obj')) {
        function dp_pick_obj($obj, array $keys, $default = null) {
            if (!$obj) return $default;
            foreach ($keys as $k) {
                if (is_object($obj) && isset($obj->{$k}) && $obj->{$k} !== null && $obj->{$k} !== '') return $obj->{$k};
                if (is_array($obj) && array_key_exists($k, $obj) && $obj[$k] !== null && $obj[$k] !== '') return $obj[$k];
            }
            return $default;
        }
    }

    if (!function_exists('dp_norm_link_candidates')) {
        function dp_norm_link_candidates(string $path, string $baseUrl): array {
    $p = '/' . ltrim($path, '/');
    $pNoTrail = rtrim($p, '/') ?: '/';
    $pTrail   = ($pNoTrail === '/') ? '/' : ($pNoTrail . '/');

    $c = [
        $pNoTrail,
        ltrim($pNoTrail, '/'),
        $pTrail,
        ltrim($pTrail, '/'),
    ];

    try {
        $c[] = rtrim($baseUrl, '/') . $pNoTrail;
        $c[] = rtrim($baseUrl, '/') . $pTrail;
        $c[] = url($pNoTrail);
        $c[] = url($pTrail);
    } catch (\Throwable $e) {}

    return array_values(array_unique(array_filter(array_map('strval', $c))));
}
    }

    if (!function_exists('dp_build_meta_html')) {
        function dp_build_meta_html(array $m, string $fallbackTitle, string $fallbackUrl): array {
            $title = trim((string)($m['title'] ?? $m['meta_title'] ?? $fallbackTitle));
            $desc  = trim((string)($m['description'] ?? $m['meta_description'] ?? ''));
            $keys  = trim((string)($m['keywords'] ?? $m['meta_keywords'] ?? ''));
            $robots= trim((string)($m['robots'] ?? ''));
            $canon = trim((string)($m['canonical'] ?? $m['canonical_url'] ?? $fallbackUrl));

            $og = (is_array($m['og'] ?? null) ? $m['og'] : []);
            $tw = (is_array($m['twitter'] ?? null) ? $m['twitter'] : []);

            $ogTitle = trim((string)($og['title'] ?? $m['og_title'] ?? $title));
            $ogDesc  = trim((string)($og['description'] ?? $m['og_description'] ?? $desc));
            $ogImage = trim((string)($og['image'] ?? $m['og_image'] ?? ''));
            $ogUrl   = trim((string)($og['url'] ?? $m['og_url'] ?? $canon));
            $ogType  = trim((string)($og['type'] ?? $m['og_type'] ?? 'website'));
            $ogSite  = trim((string)($og['site_name'] ?? $m['og_site_name'] ?? config('app.name', '')));

            $twCard  = trim((string)($tw['card'] ?? $m['twitter_card'] ?? 'summary_large_image'));
            $twTitle = trim((string)($tw['title'] ?? $m['twitter_title'] ?? $ogTitle));
            $twDesc  = trim((string)($tw['description'] ?? $m['twitter_description'] ?? $ogDesc));
            $twImage = trim((string)($tw['image'] ?? $m['twitter_image'] ?? $ogImage));

            $out = [];

            // Canonical
            if ($canon) $out[] = '<link rel="canonical" href="'.e($canon).'" data-dp-dynamic-meta="1">';

            // Basic SEO
            if ($desc)  $out[] = '<meta name="description" content="'.e($desc).'" data-dp-dynamic-meta="1">';
            if ($keys)  $out[] = '<meta name="keywords" content="'.e($keys).'" data-dp-dynamic-meta="1">';
            if ($robots)$out[] = '<meta name="robots" content="'.e($robots).'" data-dp-dynamic-meta="1">';

            // Open Graph
            if ($ogTitle) $out[] = '<meta property="og:title" content="'.e($ogTitle).'" data-dp-dynamic-meta="1">';
            if ($ogDesc)  $out[] = '<meta property="og:description" content="'.e($ogDesc).'" data-dp-dynamic-meta="1">';
            if ($ogImage) $out[] = '<meta property="og:image" content="'.e($ogImage).'" data-dp-dynamic-meta="1">';
            if ($ogUrl)   $out[] = '<meta property="og:url" content="'.e($ogUrl).'" data-dp-dynamic-meta="1">';
            if ($ogType)  $out[] = '<meta property="og:type" content="'.e($ogType).'" data-dp-dynamic-meta="1">';
            if ($ogSite)  $out[] = '<meta property="og:site_name" content="'.e($ogSite).'" data-dp-dynamic-meta="1">';

            // Twitter
            if ($twCard)  $out[] = '<meta name="twitter:card" content="'.e($twCard).'" data-dp-dynamic-meta="1">';
            if ($twTitle) $out[] = '<meta name="twitter:title" content="'.e($twTitle).'" data-dp-dynamic-meta="1">';
            if ($twDesc)  $out[] = '<meta name="twitter:description" content="'.e($twDesc).'" data-dp-dynamic-meta="1">';
            if ($twImage) $out[] = '<meta name="twitter:image" content="'.e($twImage).'" data-dp-dynamic-meta="1">';

            return [$title ?: $fallbackTitle, implode("\n    ", $out), $canon ?: $fallbackUrl];
        }
    }

    // --------- Detect base path + submenu slug from URL ----------
    $dpRequestUri = request()->getRequestUri(); // includes query
    $dpPathOnly   = parse_url($dpRequestUri, PHP_URL_PATH) ?? '/';
    $dpPathOnly   = '/' . ltrim($dpPathOnly, '/');
    $dpPathOnly   = rtrim($dpPathOnly, '/') ?: '/';

    $dpSubmenuSlug = '';
    $dpBasePath = $dpPathOnly;

    // Pattern: /something&submenu=slug
    if (preg_match('/^(.*)&submenu=([^\/\?#]+)/', $dpPathOnly, $m)) {
        $dpBasePath = rtrim($m[1], '/') ?: '/';
        $dpSubmenuSlug = urldecode($m[2] ?? '');
    }

    // Also allow ?submenu=slug
    if (!$dpSubmenuSlug) {
        $dpSubmenuSlug = (string) request()->query('submenu', '');
    }
    $dpSubmenuSlug = trim($dpSubmenuSlug);

    // --------- Resolve a page by matching pages.page_url (page_link doesn't exist) ----------
$dpResolvedPage = null;
$dpPageSlugCandidate = trim(Str::afterLast($dpBasePath, '/'));
$dpBaseUrl = request()->getSchemeAndHttpHost();
$dpLinkCandidates = dp_norm_link_candidates($dpBasePath, $dpBaseUrl);

try {
    if (Schema::hasTable('pages')) {
        $hasUrl  = Schema::hasColumn('pages', 'page_url');
        $hasSlug = Schema::hasColumn('pages', 'slug');

        if ($hasUrl) {
            $dpResolvedPage = DB::table('pages')
                ->whereIn('page_url', $dpLinkCandidates)
                ->orderByDesc('id')
                ->first();
        }

        if (!$dpResolvedPage && $hasSlug) {
    $slugTry = '';

    // prefer /page/{slug} pattern
    $segs = explode('/', trim($dpBasePath, '/'));
    if (count($segs) >= 2 && strtolower($segs[0]) === 'page') {
        $slugTry = $segs[1];
    } else {
        $slugTry = end($segs) ?: '';
    }

    $slugTry = Str::slug(trim((string)$slugTry), '-');

    if ($slugTry) {
        $dpResolvedPage = DB::table('pages')
            ->where('slug', $slugTry)
            ->orderByDesc('id')
            ->first();
    }
}
    }
} catch (\Throwable $e) {
    $dpResolvedPage = null;
}

    // --------- Resolve submenu (if present) for meta-tags ----------
    $dpResolvedSubmenu = null;
    try {
        $submenuTables = ['page_submenus', 'page_sub_menus', 'page_submenu'];
        $submenuTable = null;
        foreach ($submenuTables as $t) {
            if (Schema::hasTable($t)) { $submenuTable = $t; break; }
        }

        if ($submenuTable && $dpSubmenuSlug) {
            $q = DB::table($submenuTable)->where('slug', $dpSubmenuSlug);

            // If submenu table has page_id and we resolved page, scope it
            if ($dpResolvedPage && Schema::hasColumn($submenuTable, 'page_id')) {
                $q->where('page_id', (int)($dpResolvedPage->id ?? 0));
            }

            $dpResolvedSubmenu = $q->orderByDesc('id')->first();
        }
    } catch (\Throwable $e) {
        $dpResolvedSubmenu = null;
    }

    // --------- Load meta tags from a meta table (if exists) ----------
    $dpMetaArr = [];
    $dpMetaRow = null;
    $dpMetaTable = null;

    try {
        $metaTables = ['meta_tags', 'meta_tag_managers', 'meta_tag_manager', 'metatags', 'meta_tag'];
        foreach ($metaTables as $t) {
            if (Schema::hasTable($t)) { $dpMetaTable = $t; break; }
        }

        if ($dpMetaTable) {
            $q = DB::table($dpMetaTable);

            // Prefer submenu-specific meta if possible
            if ($dpResolvedSubmenu) {
                if (Schema::hasColumn($dpMetaTable, 'submenu_id')) {
                    $q->where('submenu_id', (int)($dpResolvedSubmenu->id ?? 0));
                } elseif (Schema::hasColumn($dpMetaTable, 'page_submenu_id')) {
                    $q->where('page_submenu_id', (int)($dpResolvedSubmenu->id ?? 0));
                } elseif (Schema::hasColumn($dpMetaTable, 'submenu_slug')) {
                    $q->where('submenu_slug', $dpSubmenuSlug);
                }
            }

            // Also scope by page if columns exist
            if ($dpResolvedPage) {
                $pid = (int)($dpResolvedPage->id ?? 0);
                $pslug = (string)($dpResolvedPage->slug ?? '');
                if (Schema::hasColumn($dpMetaTable, 'page_id')) $q->orWhere('page_id', $pid);
                if ($pslug && Schema::hasColumn($dpMetaTable, 'page_slug')) $q->orWhere('page_slug', $pslug);
                if (Schema::hasColumn($dpMetaTable, 'page_link')) $q->orWhereIn('page_link', $dpLinkCandidates);
            } else {
                // No resolved page: try link candidates (header links still can match meta table directly)
                if (Schema::hasColumn($dpMetaTable, 'page_link')) $q->whereIn('page_link', $dpLinkCandidates);
            }

            $dpMetaRow = $q->orderByDesc('id')->first();

            if ($dpMetaRow) {
                // Try JSON columns first
                foreach (['meta_json','meta_data','meta','data','payload','json'] as $col) {
                    if (Schema::hasColumn($dpMetaTable, $col)) {
                        $arr = dp_try_json($dpMetaRow->{$col});
                        if ($arr) { $dpMetaArr = array_merge($dpMetaArr, $arr); break; }
                    }
                }

                // Merge common scalar columns if present
                foreach ([
                    'meta_title' => ['meta_title','title'],
                    'meta_description' => ['meta_description','description'],
                    'meta_keywords' => ['meta_keywords','keywords'],
                    'robots' => ['robots'],
                    'canonical_url' => ['canonical_url','canonical'],
                    'og_title' => ['og_title'],
                    'og_description' => ['og_description'],
                    'og_image' => ['og_image'],
                    'og_url' => ['og_url'],
                    'og_type' => ['og_type'],
                    'twitter_card' => ['twitter_card'],
                    'twitter_title' => ['twitter_title'],
                    'twitter_description' => ['twitter_description'],
                    'twitter_image' => ['twitter_image'],
                ] as $target => $cols) {
                    foreach ($cols as $c) {
                        if (Schema::hasColumn($dpMetaTable, $c) && !empty($dpMetaRow->{$c})) {
                            $dpMetaArr[$target] = $dpMetaRow->{$c};
                            break;
                        }
                    }
                }
            }
        }
    } catch (\Throwable $e) {
        $dpMetaRow = null;
        $dpMetaTable = null;
    }

    // --------- As a fallback, try meta columns directly from page/submenu rows ----------
    $dpMetaContext = $dpResolvedSubmenu ?: $dpResolvedPage;
    if ($dpMetaContext) {
        foreach ([
            'meta_title' => ['meta_title','seo_title','page_title','title'],
            'meta_description' => ['meta_description','seo_description','description'],
            'meta_keywords' => ['meta_keywords','seo_keywords','keywords'],
            'robots' => ['robots'],
            'canonical_url' => ['canonical_url','canonical'],
            'og_title' => ['og_title'],
            'og_description' => ['og_description'],
            'og_image' => ['og_image'],
            'og_url' => ['og_url'],
            'og_type' => ['og_type'],
            'twitter_card' => ['twitter_card'],
            'twitter_title' => ['twitter_title'],
            'twitter_description' => ['twitter_description'],
            'twitter_image' => ['twitter_image'],
        ] as $k => $cols) {
            if (!isset($dpMetaArr[$k]) || $dpMetaArr[$k] === '') {
                $v = dp_pick_obj($dpMetaContext, $cols, null);
                if ($v !== null && $v !== '') $dpMetaArr[$k] = $v;
            }
        }
    }

    // --------- Build meta HTML + final <title> for view-source ----------
    $dpFallbackTitle = (string)(
        dp_pick_obj($dpResolvedSubmenu, ['title','name','label'], null)
        ?: dp_pick_obj($dpResolvedPage, ['page_title','title','name'], null)
        ?: 'Dynamic Page'
    );

    $dpFallbackUrl = url()->current();
    [$dpHeadTitle, $dpMetaHtml, $dpCanonical] = dp_build_meta_html($dpMetaArr, $dpFallbackTitle, $dpFallbackUrl);

    // --------- Provide server page payload to JS (so header-link pages load correctly) ----------
    $dpServerPageForJs = null;
    if ($dpResolvedPage) {
        $dpServerPageForJs = [
            'id' => (int)($dpResolvedPage->id ?? 0),
            'slug' => (string)($dpResolvedPage->slug ?? ''),
            'title' => (string)($dpResolvedPage->title ?? $dpResolvedPage->page_title ?? ''),
            'content_html' => (string)($dpResolvedPage->content_html ?? $dpResolvedPage->html ?? ''),
            'page_link' => (string)($dpResolvedPage->page_url ?? ''),
        ];
    }

    $dpServerSubmenuForJs = null;
    if ($dpResolvedSubmenu) {
        $dpServerSubmenuForJs = [
            'id' => (int)($dpResolvedSubmenu->id ?? 0),
            'slug' => (string)($dpResolvedSubmenu->slug ?? ''),
            'title' => (string)(dp_pick_obj($dpResolvedSubmenu, ['title','name','label'], '') ?? ''),
        ];
    }
@endphp
@php

if (!function_exists('dp_clean_canonical_url')) {
  function dp_clean_canonical_url(string $url): string {
    try {
      $u = parse_url($url);
      if (!$u) return $url;

      $scheme = $u['scheme'] ?? request()->getScheme();
      $host   = $u['host'] ?? request()->getHost();
      $port   = isset($u['port']) ? (':' . $u['port']) : '';
      $path   = $u['path'] ?? '/';

      // ✅ canonical WITHOUT query by default (prevents ?mode=test, tokens, etc.)
      return $scheme . '://' . $host . $port . $path;
    } catch (\Throwable $e) {
      return $url;
    }
  }
}

$dpHeadTitle = (string)(
  ($dpResolvedSubmenu->title ?? null)
  ?: ($dpResolvedPage->page_title ?? null)
  ?: ($dpResolvedPage->title ?? null)
  ?: 'Dynamic Page'
);

// ✅ canonical should NOT be fullUrl (no ?mode=test)
$dpCanonical = dp_clean_canonical_url(url()->current());

// ✅ build meta html ONLY from your saved rows (page_id based)
$dpMetaHtml = '';
try {
  $metaTable = 'meta_tags';

  // expected columns from your manager
  $hasCols = Schema::hasTable($metaTable)
    && Schema::hasColumn($metaTable, 'page_id')
    && Schema::hasColumn($metaTable, 'tag_type')
    && Schema::hasColumn($metaTable, 'attribute')
    && Schema::hasColumn($metaTable, 'content');

  if ($hasCols && $dpResolvedPage) {
    $q = DB::table($metaTable)->where('page_id', (int)$dpResolvedPage->id);

    // ✅ if you support submenu-specific tags, scope them too (optional)
    if ($dpResolvedSubmenu) {
      if (Schema::hasColumn($metaTable, 'page_submenu_id')) {
        $q->where('page_submenu_id', (int)$dpResolvedSubmenu->id);
      } elseif (Schema::hasColumn($metaTable, 'submenu_id')) {
        $q->where('submenu_id', (int)$dpResolvedSubmenu->id);
      }
    }

    $rows = $q->orderByDesc('updated_at')->orderByDesc('id')->get();

    $out  = [];
    $seen = [];

    // ✅ always output canonical (clean)
    $out[] = '<link rel="canonical" href="'.e($dpCanonical).'" data-dp-dynamic-meta="1">';

    foreach ($rows as $t) {
      $type = strtolower(trim((string)($t->tag_type ?? 'standard')));
      $attr = trim((string)($t->attribute ?? ''));
      $val  = trim((string)($t->content ?? ''));

      if ($attr === '') continue;

      // OPTIONAL: allow canonical via manager (if user saves attribute canonical/canonical_url)
      if (in_array(strtolower($attr), ['canonical','canonical_url'], true)) {
        if ($val) {
          $dpCanonical = dp_clean_canonical_url($val);
          $out[0] = '<link rel="canonical" href="'.e($dpCanonical).'" data-dp-dynamic-meta="1">';
        }
        continue;
      }

      // normalize type
      if (in_array($type, ['og','open_graph','opengraph'], true)) $type = 'opengraph';
      if (in_array($type, ['http','http_equiv','http-equiv'], true)) $type = 'http';
      if ($type === 'name') $type = (str_starts_with(strtolower($attr), 'twitter:') ? 'twitter' : 'standard');
      if (str_starts_with(strtolower($attr), 'og:')) $type = 'opengraph';
      if (str_starts_with(strtolower($attr), 'twitter:')) $type = 'twitter';

      $key = $type . '::' . strtolower($attr);
      if (isset($seen[$key])) continue;   // keep newest because ordered desc
      $seen[$key] = 1;

      if ($type === 'charset') {
        // you already output <meta charset="UTF-8"> in head → skip
        continue;
      } elseif ($type === 'opengraph') {
        // ✅ if og:url is missing in DB, force canonical later (optional)
        $out[] = '<meta property="'.e($attr).'" content="'.e($val).'" data-dp-dynamic-meta="1">';
      } elseif ($type === 'http') {
        $out[] = '<meta http-equiv="'.e($attr).'" content="'.e($val).'" data-dp-dynamic-meta="1">';
      } else {
        $out[] = '<meta name="'.e($attr).'" content="'.e($val).'" data-dp-dynamic-meta="1">';
      }
    }

    // ✅ if they didn’t save og:url, make it canonical (recommended)
    $hasOgUrl = false;
    foreach ($out as $x) {
      if (stripos($x, 'property="og:url"') !== false) { $hasOgUrl = true; break; }
    }
    if (!$hasOgUrl) {
      $out[] = '<meta property="og:url" content="'.e($dpCanonical).'" data-dp-dynamic-meta="1">';
    }

    $dpMetaHtml = implode("\n    ", $out);
  }
} catch (\Throwable $e) {
  $dpMetaHtml = '';
}
@endphp
@php
    echo '<!-- Candidates: ' . json_encode($dpLinkCandidates) . ' -->';
    if ($dpResolvedPage) {
        echo '<!-- Resolved page: ' . $dpResolvedPage->id . ' -->';
    } else {
        echo '<!-- No page resolved -->';
    }
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

@include('landing.components.metaTags', [
  'pageId' => $dpResolvedPage->id ?? null,
  'submenuId' => $dpResolvedSubmenu->id ?? null,
  'fallbackTitle' => $dpHeadTitle,
  'canonical' => url()->current(),
  'onlySaved' => true, // ✅ IMPORTANT
])

<title>{{ $dpHeadTitle }}</title>

    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/media/images/favicon/msit_logo.jpg') }}">

    {{-- Bootstrap + Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet"/>

    {{-- Common UI --}}
    <link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

    {{-- SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .page-content { padding: 2rem 0; min-height: 70vh; }

        /* ===== Sidebar (hallienz-ish) ===== */
        .hallienz-side{border-radius: 18px;overflow: hidden;background: var(--surface, #fff);border: 1px solid var(--line-strong, #e6c8ca);box-shadow: var(--shadow-2, 0 8px 22px rgba(0,0,0,.08));}
        .hallienz-side__head{background: var(--primary-color, #9E363A);color: #fff;font-weight: 700;padding: 14px 16px;font-size: 20px;letter-spacing: .2px;}
        .hallienz-side__list{margin: 0;padding: 6px 0 0;list-style: none;border-bottom: 0.5rem solid #9E363A;}
        .hallienz-side__item{ position: relative; }

        .hallienz-side__row{ display:flex; align-items:stretch; }

        .hallienz-side__link{flex: 1 1 auto;display: flex;align-items: center;gap: 12px;padding: 10px 14px;text-decoration: none;color: #0b5ed7;border-bottom: 1px dotted rgba(0,0,0,.18);transition: background .25s ease, color .25s ease;min-width: 0;}
        .hallienz-side__link:hover{background: rgba(158, 54, 58, .06);color: var(--primary-color, #9E363A);}
        .hallienz-side__link.active{background: rgba(158, 54, 58, .10);color: var(--primary-color, #9E363A);font-weight: 700;}
        .hallienz-side__text{display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}

        .hallienz-side__toggle{flex: 0 0 auto;width: 44px;display:inline-flex;align-items:center;justify-content:center;border: none;background: transparent;color: rgba(0,0,0,.55);border-bottom: 1px dotted rgba(0,0,0,.18);transition: background .25s ease, color .25s ease, transform .25s ease;cursor:pointer;}
        .hallienz-side__toggle:hover{background: rgba(158, 54, 58, .06);color: var(--primary-color, #9E363A);}
        .hallienz-side__toggle i{ transition: transform .22s ease; }
        .hallienz-side__item.open > .hallienz-side__row .hallienz-side__toggle i{ transform: rotate(90deg); }

        .hallienz-side__children{list-style:none;margin: 0;padding: 0;display:none;border-bottom: 1px dotted rgba(0,0,0,.18);background: rgba(158, 54, 58, .03);}
        .hallienz-side__item.open > .hallienz-side__children{ display:block; }

        .hallienz-side__children .hallienz-side__link{border-bottom: 1px dotted rgba(0,0,0,.14);font-size: 14px;}
        .hallienz-side__children .hallienz-side__toggle{border-bottom: 1px dotted rgba(0,0,0,.14);}

        @media (hover:hover) and (pointer:fine){
            .hallienz-side__item:hover .hallienz-side__children{display:block;}
            .hallienz-side__item:hover > .hallienz-side__row .hallienz-side__toggle i{transform: rotate(90deg);}
        }

        /* ===== Content Card ===== */
        .dp-card{border-radius: 18px;background: var(--surface, #fff);border: 1px solid var(--line-strong, #e6c8ca);box-shadow: var(--shadow-2, 0 8px 22px rgba(0,0,0,.08));padding: 18px;}
        .dp-title{font-weight: 800;margin: 0 0 12px;color: var(--ink, #111);text-align: center;}
        .dp-muted{ color: var(--muted-color, #6b7280); font-size: 13px; margin-bottom: 12px; }
        .dp-loading{ padding: 28px 0; text-align: center; color: var(--muted-color, #6b7280); }
        .dp-iframe{border:1px solid rgba(0,0,0,.1);border-radius:12px;overflow:hidden;}

        :root{ --dp-sticky-top: 16px; }

        @media (min-width: 992px){
            .dp-sticky{position: sticky;top: var(--dp-sticky-top, 16px);z-index: 2;}
        }

        @media (max-width: 991.98px){
            #sidebarCol.dp-side-preload{ display:none !important; }
        }

        .dp-skel-wrap{ padding: 12px 12px 14px; border-bottom: 0.5rem solid #9E363A; background: rgba(158, 54, 58, .02); }
        .dp-skel-stack{ display:grid; gap: 10px; }
        .dp-skel-line{position: relative;height: 14px;border-radius: 12px;overflow: hidden;background: rgba(0,0,0,.08);}
        .dp-skel-line.sm{ height: 12px; }
        .dp-skel-line.lg{ height: 18px; }
        .dp-skel-line::after{content:"";position:absolute;inset:0;transform: translateX(-120%);background: linear-gradient(90deg, transparent, rgba(255,255,255,.65), transparent);animation: dpShimmer 1.15s infinite;}

        @keyframes dpShimmer{
            0%{ transform: translateX(-120%); }
            100%{ transform: translateX(120%); }
        }

        @media (prefers-reduced-motion: reduce){
            .dp-skel-line::after{ animation: none; }
        }

        html.theme-dark .dp-skel-wrap{ background: rgba(255,255,255,.03); }
        html.theme-dark .dp-skel-line{ background: rgba(255,255,255,.10); }
        html.theme-dark .dp-skel-line::after{background: linear-gradient(90deg, transparent, rgba(255,255,255,.18), transparent);}

        /* Carousel */
        .ce-carousel{position:relative;width:100%;margin:0 0 12px 0;border:1px solid #e5e7eb;border-radius:6px;overflow:hidden;background:#f3f4f6;}
        .ce-carousel-viewport{width:100%;height:260px;overflow:hidden;}
        .ce-carousel-track{display:flex;width:100%;height:100%;transform:translateX(0);transition:transform .35s ease;}
        .ce-carousel-slide{flex:0 0 100%;height:100%;}
        .ce-carousel-slide img{width:100%;height:100%;object-fit:cover;display:block;}
        .ce-carousel[data-fit="contain"] .ce-carousel-slide img{object-fit:contain;background:#fff;}
        .ce-carousel-btn{position:absolute;top:50%;transform:translateY(-50%);border:none;background:rgba(17,24,39,.55);color:#fff;width:34px;height:34px;border-radius:999px;cursor:pointer;display:flex;align-items:center;justify-content:center;}
        .ce-carousel-prev{left:10px;}
        .ce-carousel-next{right:10px;}
        .ce-carousel-dots{position:absolute;left:0;right:0;bottom:10px;display:flex;justify-content:center;gap:6px;padding:0 10px;}
        .ce-carousel-dot{width:8px;height:8px;border-radius:999px;border:0;background:rgba(255,255,255,.55);cursor:pointer;}
    </style>
</head>
<body>

{{-- Top Header --}}
@include('landing.components.topHeaderMenu')

{{-- Main Header --}}
@include('landing.components.header')

{{-- Header --}}
@include('landing.components.headerMenu')

<main class="page-content">
    <div class="container">
        <div class="row g-4 align-items-start" id="dpRow">
            {{-- Sidebar --}}
            <aside class="col-12 col-lg-3 dp-side-preload" id="sidebarCol" aria-label="Page Sidebar">
                <div class="hallienz-side" id="sidebarCard">
                    <div class="hallienz-side__head" id="sidebarHeading">Menu</div>

                    {{-- ✅ Real list (hidden until loaded) --}}
                    <ul class="hallienz-side__list d-none" id="submenuList"></ul>

                    {{-- ✅ Skeleton list while page submenus are loading --}}
                    <div id="submenuSkeleton" class="dp-skel-wrap" aria-hidden="true">
                        <div class="dp-skel-stack">
                            <div class="dp-skel-line lg" style="width:72%;"></div>
                            <div class="dp-skel-line" style="width:92%;"></div>
                            <div class="dp-skel-line" style="width:84%;"></div>
                            <div class="dp-skel-line" style="width:88%;"></div>
                            <div class="dp-skel-line" style="width:78%;"></div>
                            <div class="dp-skel-line sm" style="width:60%;"></div>
                        </div>
                    </div>
                </div>
            </aside>

            {{-- Content --}}
            <section class="col-12 col-lg-9" id="contentCol">
                <div class="dp-card" id="contentCard">
                    <div class="dp-loading" id="pageLoading">
                        <div class="spinner-border" role="status" aria-label="Loading"></div>
                        <div class="mt-2" id="loadingText">Loading page…</div>
                    </div>

                    <div id="pageError" class="alert alert-danger d-none mb-0"></div>

                    <div id="pageNotFoundWrap" class="d-none">
                        @include('partials.pageNotFound')
                    </div>

                    <div id="pageComingSoonWrap" class="d-none">
                        @include('partials.comingSoon')
                    </div>

                    <div id="pageWrap" class="d-none">
                        <div class="dp-muted d-none" id="pageMeta"></div>

                        <h1 class="dp-title" id="pageTitle">Dynamic Page</h1>
                        <div id="pageHtml"></div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</main>

{{-- Footer --}}
@include('landing.components.footer')

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

@php
  $apiBase = rtrim(url('/api'), '/');
@endphp

{{-- ✅ Expose server-resolved page/submenu so header links also load correctly --}}
<script>
    window.__DP_SERVER_PAGE__ = @json($dpServerPageForJs);
    window.__DP_SERVER_SUBMENU__ = @json($dpServerSubmenuForJs);
    window.__DP_SERVER_LINK_PATH__ = @json($dpBasePath);
    window.__DP_SERVER_SUBMENU_SLUG__ = @json($dpSubmenuSlug);
</script>

<script>
(function(){
    // ============================================================
    // Carousel initialization (kept as is)
    // ============================================================
    function parseList(raw){
        return (raw||'').split(/\r?\n|\|/g).map(function(s){return (s||'').trim();}).filter(Boolean);
    }

    function getOpts(car){
        var h = parseInt(car.getAttribute('data-height') || '260', 10);
        var interval = parseInt(car.getAttribute('data-interval') || '3000', 10);
        return {
            height: Math.max(120, isNaN(h)?260:h),
            interval: Math.max(800, isNaN(interval)?3000:interval),
            autoplay: car.getAttribute('data-autoplay') === 'true',
            arrows: car.getAttribute('data-arrows') !== 'false',
            dots: car.getAttribute('data-dots') !== 'false',
            loop: car.getAttribute('data-loop') !== 'false',
            fit: (car.getAttribute('data-fit') === 'contain') ? 'contain' : 'cover'
        };
    }

    function ensure(car){
        var viewport = car.querySelector('.ce-carousel-viewport');
        var track = car.querySelector('.ce-carousel-track');
        if(!viewport){ viewport=document.createElement('div'); viewport.className='ce-carousel-viewport'; car.insertBefore(viewport, car.firstChild); }
        if(!track){ track=document.createElement('div'); track.className='ce-carousel-track'; viewport.appendChild(track); }
        var dots = car.querySelector('.ce-carousel-dots');
        if(!dots){ dots=document.createElement('div'); dots.className='ce-carousel-dots'; car.appendChild(dots); }
        var prev = car.querySelector('.ce-carousel-prev');
        var next = car.querySelector('.ce-carousel-next');
        return {viewport:viewport, track:track, dots:dots, prev:prev, next:next};
    }

    function buildSlides(car){
        var el = ensure(car);
        var urls = parseList(car.getAttribute('data-images') || '');
        if(!urls.length){
            urls = Array.prototype.slice.call(car.querySelectorAll('.ce-carousel-slide img')).map(function(img){
                return (img.getAttribute('src')||'').trim();
            }).filter(Boolean);
        }
        if(!urls.length) urls = ['https://placehold.co/600x260'];

        el.track.innerHTML = urls.map(function(u){
            return '<div class="ce-carousel-slide"><img src="'+u.replace(/"/g,'&quot;')+'" alt="Slide"></div>';
        }).join('');

        el.dots.innerHTML = urls.map(function(_,i){
            return '<button type="button" class="ce-carousel-dot" data-idx="'+i+'" aria-label="Go to slide '+(i+1)+'"></button>';
        }).join('');

        return urls;
    }

    function stop(car){
        if(car.__t){ clearInterval(car.__t); car.__t=null; }
    }

    function go(car, idx, restart){
        var o = getOpts(car);
        var el = ensure(car);
        var slides = car.querySelectorAll('.ce-carousel-slide');
        var max = slides.length - 1;
        var i = idx;

        if(o.loop){
            if(i<0) i=max;
            if(i>max) i=0;
        }else{
            i = Math.max(0, Math.min(max, i));
        }

        car.setAttribute('data-index', String(i));
        el.track.style.transform = 'translateX(-' + (i*100) + '%)';

        var dots = car.querySelectorAll('.ce-carousel-dot');
        for(var d=0; d<dots.length; d++){
            if(d===i) dots[d].classList.add('active');
            else dots[d].classList.remove('active');
        }

        if(restart) start(car);
    }

    function start(car){
        var o = getOpts(car);
        stop(car);
        if(!o.autoplay) return;
        car.__t = setInterval(function(){
            var cur = parseInt(car.getAttribute('data-index') || '0', 10) || 0;
            go(car, cur+1, false);
        }, o.interval);
    }

    function init(car){
        var o = getOpts(car);
        var el = ensure(car);

        car.setAttribute('data-fit', o.fit);
        el.viewport.style.height = o.height + 'px';

        buildSlides(car);

        if(el.prev) el.prev.style.display = o.arrows ? '' : 'none';
        if(el.next) el.next.style.display = o.arrows ? '' : 'none';
        el.dots.style.display = o.dots ? 'flex' : 'none';

        if(!car.__bound){
            car.__bound=true;
            car.addEventListener('click', function(e){
                var p = e.target.closest && e.target.closest('.ce-carousel-prev');
                var n = e.target.closest && e.target.closest('.ce-carousel-next');
                var d = e.target.closest && e.target.closest('.ce-carousel-dot');

                if(p){ e.preventDefault(); go(car, (parseInt(car.getAttribute('data-index')||'0',10)||0)-1, true); }
                else if(n){ e.preventDefault(); go(car, (parseInt(car.getAttribute('data-index')||'0',10)||0)+1, true); }
                else if(d){ e.preventDefault(); go(car, parseInt(d.getAttribute('data-idx')||'0',10)||0, true); }
            });
            car.addEventListener('mouseenter', function(){ stop(car); });
            car.addEventListener('mouseleave', function(){ start(car); });
        }

        go(car, parseInt(car.getAttribute('data-index')||'0',10)||0, false);
        start(car);
    }

    function boot(){
        var cars = document.querySelectorAll('.ce-carousel');
        for(var i=0;i<cars.length;i++) init(cars[i]);
    }

    if(document.readyState==='loading') document.addEventListener('DOMContentLoaded', boot);
    else boot();
})();
</script>

<script>
(function(){
    const API_BASE  = @json($apiBase);
    const SITE_BASE = @json(url('/'));

    // ============================================================
    // ✅ Dynamic meta tag applier (client-side for SPA actions)
    // ============================================================
    function clearDynamicMeta(){
        try {
            document.querySelectorAll('[data-dp-dynamic-meta="1"]').forEach(n => n.remove());
        } catch(e){}
    }

    function applyMetaHtml(html){
        const raw = String(html || '').trim();
        if (!raw) return;

        clearDynamicMeta();

        const tpl = document.createElement('template');
        tpl.innerHTML = raw;

        const nodes = Array.from(tpl.content.childNodes).filter(n => n.nodeType === 1);
        nodes.forEach(n => {
            const tag = (n.tagName || '').toUpperCase();
            if (!['META','LINK','TITLE'].includes(tag)) return;

            if (tag === 'TITLE'){
                document.title = (n.textContent || document.title);
                return;
            }

            n.setAttribute('data-dp-dynamic-meta', '1');
            document.head.appendChild(n);
        });
    }

    function applyMetaFromPayload(payload){
        // Accept common keys returned by backend
        const html =
            payload?.meta_html ||
            payload?.metaTagsHtml ||
            payload?.meta_tags_html ||
            payload?.meta_tags ||
            payload?.meta;

        if (typeof html === 'string' && (html.includes('<meta') || html.includes('<link') || html.includes('<title'))) {
            applyMetaHtml(html);
        }
    }

    // ============================================================
    // ✅ UUID helpers for header menu identification
    // ============================================================
    function isUuid(str) {
        const uuidRegex = /^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i;
        return uuidRegex.test(String(str || '').trim());
    }

    function isHeaderMenuUuidToken(str) {
        const token = String(str || '').trim();
        if (!token.startsWith('h-')) return false;
        const uuid = token.substring(2);
        return isUuid(uuid);
    }

    function extractUuidFromHeaderToken(token) {
        const t = String(token || '').trim();
        if (!t.startsWith('h-')) return '';
        return t.substring(2);
    }

    function headerMenuTokenFromUuid(uuid) {
        const u = String(uuid || '').trim();
        if (!u) return '';
        return u.startsWith('h-') ? u : ('h-' + u);
    }

    function readHeaderMenuUuidFromUrl() {
        try {
            const usp = new URLSearchParams(window.location.search || '');
            for (const [key, value] of usp.entries()) {
                if (isHeaderMenuUuidToken(key)) return extractUuidFromHeaderToken(key);
                if (isHeaderMenuUuidToken(value)) return extractUuidFromHeaderToken(value);
            }
            const legacyId = usp.get('header_menu_id') || usp.get('menu_id') || usp.get('headerMenuId');
            if (legacyId) return legacyId;
        } catch (e) {}
        return '';
    }

    function buildSearchWithHeaderUuid(params, headerUuid) {
        const newParams = new URLSearchParams();
        for (const [key, value] of params.entries()) {
            if (!isHeaderMenuUuidToken(key) && key !== 'header_menu_id' && key !== 'menu_id' && key !== 'headerMenuId') {
                newParams.append(key, value);
            }
        }
        if (headerUuid) {
            if (isUuid(headerUuid)) {
                const token = headerMenuTokenFromUuid(headerUuid);
                if (token) newParams.append(token, '1');
            } else {
                newParams.append('header_menu_id', headerUuid);
            }
        }
        return newParams;
    }

    // ============================================================
    // Auth cache + global API auth injection
    // ============================================================
    const TOKEN = (localStorage.getItem('token') || sessionStorage.getItem('token') || '');
    const ROLE  = (sessionStorage.getItem('role') || localStorage.getItem('role') || '');

    window.__AUTH_CACHE__ = window.__AUTH_CACHE__ || { token: TOKEN, role: ROLE };

    (function patchFetch(){
        const origFetch = window.fetch;
        window.fetch = async function(input, init = {}){
            try{
                const url = (typeof input === 'string') ? input : (input?.url || '');
                const isApi = String(url).includes('/api/');
                if (isApi && TOKEN){
                    init.headers = init.headers || {};
                    if (init.headers instanceof Headers){
                        if (!init.headers.get('Authorization')) init.headers.set('Authorization', 'Bearer ' + TOKEN);
                        if (!init.headers.get('Accept')) init.headers.set('Accept', 'application/json');
                    } else {
                        if (!init.headers.Authorization) init.headers.Authorization = 'Bearer ' + TOKEN;
                        if (!init.headers.Accept) init.headers.Accept = 'application/json';
                    }
                }
            } catch(e){}
            return await origFetch(input, init);
        };
    })();

    (function patchXHR(){
        const origOpen = XMLHttpRequest.prototype.open;
        const origSend = XMLHttpRequest.prototype.send;

        XMLHttpRequest.prototype.open = function(method, url){
            this.__dp_url = url;
            return origOpen.apply(this, arguments);
        };

        XMLHttpRequest.prototype.send = function(){
            try{
                const url = String(this.__dp_url || '');
                const isApi = url.includes('/api/');
                if (isApi && TOKEN){
                    try { this.setRequestHeader('Authorization', 'Bearer ' + TOKEN); } catch(e){}
                    try { this.setRequestHeader('Accept', 'application/json'); } catch(e){}
                }
            } catch(e){}
            return origSend.apply(this, arguments);
        };
    })();

    // ============================================================
    // DOM refs
    // ============================================================
    const elLoading   = document.getElementById('pageLoading');
    const elError     = document.getElementById('pageError');
    const elNotFound  = document.getElementById('pageNotFoundWrap');
    const elComingSoon= document.getElementById('pageComingSoonWrap');
    const elWrap      = document.getElementById('pageWrap');
    const elTitle     = document.getElementById('pageTitle');
    const elMeta      = document.getElementById('pageMeta');
    const elHtml      = document.getElementById('pageHtml');
    const elLoadingText = document.getElementById('loadingText');

    const sidebarCol  = document.getElementById('sidebarCol');
    const contentCol  = document.getElementById('contentCol');
    const submenuList = document.getElementById('submenuList');
    const sidebarHead = document.getElementById('sidebarHeading');
    const submenuSkeleton = document.getElementById('submenuSkeleton');

    const sidebarCard = document.getElementById('sidebarCard') || (sidebarCol ? sidebarCol.querySelector('.hallienz-side') : null);
    const contentCard = document.getElementById('contentCard') || (contentCol ? contentCol.querySelector('.dp-card') : null);

    // ============================================================
    // Helpers
    // ============================================================
    function isSameOriginUrl(raw){
        try{
            const u = new URL(String(raw||''), window.location.origin);
            return u.origin === window.location.origin;
        }catch(e){ return false; }
    }

    function normalizeToUrl(raw){
        try { return new URL(String(raw||''), window.location.origin); }
        catch(e){ return null; }
    }

    // ============================================================
    // Skeleton helpers
    // ============================================================
    function showSidebarSkeleton(){
        try{
            if (submenuSkeleton) submenuSkeleton.classList.remove('d-none');
            if (submenuList) submenuList.classList.add('d-none');
        }catch(e){}
    }

    function hideSidebarSkeleton(){
        try{
            if (submenuSkeleton) submenuSkeleton.classList.add('d-none');
            if (submenuList) submenuList.classList.remove('d-none');
        }catch(e){}
    }

    function resetSidebarPreloadState(){
        try{
            if (sidebarCol){
                sidebarCol.classList.remove('d-none');
                sidebarCol.classList.add('dp-side-preload');
            }
            if (contentCol){
                contentCol.className = 'col-12 col-lg-9';
            }
            showSidebarSkeleton();
        }catch(e){}
    }

    function setMeta(text){
        const t = String(text || '').trim();
        if (!t){
            elMeta.textContent = '';
            elMeta.classList.add('d-none');
            return;
        }
        elMeta.textContent = t;
        elMeta.classList.remove('d-none');
    }

    function showLoading(msg){
        elLoadingText.textContent = msg || 'Loading…';
        elError.classList.add('d-none'); elError.textContent = '';
        if (elComingSoon) elComingSoon.classList.add('d-none');
        elWrap.classList.add('d-none');
        if (elNotFound) elNotFound.classList.add('d-none');
        elLoading.classList.remove('d-none');
    }

    function showError(msg){
        elError.textContent = msg;
        elError.classList.remove('d-none');
        if (elComingSoon) elComingSoon.classList.add('d-none');
        elLoading.classList.add('d-none');
        elWrap.classList.add('d-none');
        if (elNotFound) elNotFound.classList.add('d-none');
    }

    function showNotFound(slug){
        try{
            const slot = document.querySelector('[data-dp-notfound-slug]');
            if (slot) slot.textContent = slug || '';
        } catch(e){}

        elError.classList.add('d-none'); elError.textContent = '';
        if (elComingSoon) elComingSoon.classList.add('d-none');
        elLoading.classList.add('d-none');
        elWrap.classList.add('d-none');
        if (elNotFound) elNotFound.classList.remove('d-none');
    }

    function showComingSoon(submenuSlug, payload){
        try{
            const s = String(submenuSlug || '').trim();
            const title = String(payload?.title || 'Coming Soon').trim();
            const msg = String(payload?.message || '').trim();

            const s1 = document.querySelector('[data-dp-comingsoon-slug]');
            if (s1) s1.textContent = s;

            const t1 = document.querySelector('[data-dp-comingsoon-title]');
            if (t1) t1.textContent = title;

            const m1 = document.querySelector('[data-dp-comingsoon-message]');
            if (m1 && msg) m1.textContent = msg;
        }catch(e){}

        elError.classList.add('d-none'); elError.textContent = '';
        elLoading.classList.add('d-none');
        elWrap.classList.add('d-none');
        if (elNotFound) elNotFound.classList.add('d-none');
        if (elComingSoon) elComingSoon.classList.remove('d-none');
    }

    function hideError(){
        elError.classList.add('d-none');
        elError.textContent = '';
    }

    function withTimeout(ms){
        const ctrl = new AbortController();
        const id = setTimeout(() => ctrl.abort(new Error('timeout')), ms);
        return { ctrl, cancel: () => clearTimeout(id) };
    }

    async function fetchJsonWithStatus(url){
        const t = withTimeout(20000);
        try{
            const res = await fetch(url, {
                method: 'GET',
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin',
                signal: t.ctrl.signal
            });

            let data = null;
            try { data = await res.json(); } catch(e) {}

            return { ok: res.ok, status: res.status, data };
        } catch(e){
            return { ok:false, status: 0, data: { error: e?.message || 'Network error' } };
        } finally { t.cancel(); }
    }

    function cleanPathSegments(){
        return window.location.pathname.replace(/^\/+|\/+$/g,'').split('/').filter(Boolean);
    }

    function stripSubmenuFromPath(pathname){
        return String(pathname || '').replace(/&submenu=[^\/?#]*/g, '');
    }

    function readSubmenuFromPathname(){
        const p = String(window.location.pathname || '');
        const m = p.match(/&submenu=([^\/?#]+)/);
        return m ? decodeURIComponent(m[1]) : '';
    }

    // ✅ NEW: prefer server-resolved page slug for header links
    function getSlugCandidate(){
        const serverPage = (window.__DP_SERVER_PAGE__ && typeof window.__DP_SERVER_PAGE__ === 'object') ? window.__DP_SERVER_PAGE__ : null;
        if (serverPage && serverPage.slug) return String(serverPage.slug).trim();

        const qs = new URLSearchParams(window.location.search);
        const qSlug = qs.get('slug') || qs.get('page_slug') || qs.get('selfslug') || qs.get('shortcode');
        if (qSlug && String(qSlug).trim()) return String(qSlug).trim();

        const segs = cleanPathSegments();
        const strip = (s) => String(s || '').split('&submenu=')[0];

        const idx = segs.findIndex(x => String(x || '').toLowerCase() === 'page');
        if (idx !== -1 && segs[idx + 1]) return strip(segs[idx + 1]);

        const last = strip(segs[segs.length - 1] || '');
        return last || '';
    }

    function pick(obj, keys){
        for (const k of keys){
            if (obj && obj[k] !== undefined && obj[k] !== null) return obj[k];
        }
        return null;
    }

    function toLowerSafe(v){
        return String(v ?? '').toLowerCase().trim();
    }

    function safeCssEscape(s){
        try { return CSS.escape(s); } catch(e){ return String(s).replace(/["\\]/g, '\\$&'); }
    }

    function normalizeExternalUrl(raw){
        const s0 = String(raw || '').trim();
        if (!s0) return '';
        const low = s0.toLowerCase();

        const bad = ['null','undefined','#','0','about:blank'];
        if (bad.includes(low)) return '';
        if (low.startsWith('javascript:')) return '';

        try{
            return new URL(s0, window.location.origin).toString();
        }catch(e){
            try{
                if (/^[\w.-]+\.[a-z]{2,}([\/?#]|$)/i.test(s0)){
                    return new URL('https://' + s0).toString();
                }
            }catch(e2){}
            return '';
        }
    }

    // ============================================================
    // Smart Sticky Columns (kept)
    // ============================================================
    let __dpStickyRaf = 0;
    let __dpStickyRO  = null;

    function dpDebounce(fn, ms){
        let t = null;
        return function(){
            const args = arguments;
            clearTimeout(t);
            t = setTimeout(() => fn.apply(this, args), ms);
        };
    }

    function isDesktopSticky(){
        try { return window.matchMedia('(min-width: 992px)').matches; } catch(e){ return window.innerWidth >= 992; }
    }

    function resetStickyMode(){
        if (sidebarCard) sidebarCard.classList.remove('dp-sticky');
        if (contentCard) contentCard.classList.remove('dp-sticky');

        if (sidebarCol) sidebarCol.style.minHeight = '';
        if (contentCol) contentCol.style.minHeight = '';
    }

    function computeStickyTop(){
        let top = 16;

        try{
            const nodes = Array.from(document.querySelectorAll('.fixed-top, .sticky-top, header, nav'));
            const used = new Set();
            let sum = 0;

            nodes.forEach((el) => {
                if (!el || used.has(el)) return;

                const st = window.getComputedStyle(el);
                const pos = (st.position || '').toLowerCase();
                if (pos !== 'fixed' && pos !== 'sticky') return;

                const topVal = parseFloat(st.top || '0');
                if (isNaN(topVal) || topVal > 2) return;

                const h = Math.max(0, el.getBoundingClientRect().height || 0);
                if (h > 0 && h < 220) sum += h;

                used.add(el);
            });

            top += Math.min(sum, 220);
        }catch(e){}

        document.documentElement.style.setProperty('--dp-sticky-top', top + 'px');
    }

    function updateStickyMode(){
        if (!isDesktopSticky()){
            resetStickyMode();
            return;
        }
        if (!sidebarCol || sidebarCol.classList.contains('d-none') || !sidebarCard || !contentCard){
            resetStickyMode();
            return;
        }

        computeStickyTop();

        sidebarCol.style.minHeight = '';
        contentCol.style.minHeight = '';

        const sH = Math.ceil(sidebarCard.getBoundingClientRect().height || 0);
        const cH = Math.ceil(contentCard.getBoundingClientRect().height || 0);

        resetStickyMode();

        const THRESH = 40;
        if (!sH || !cH || Math.abs(sH - cH) < THRESH) return;

        if (sH > cH){
            contentCol.style.minHeight = sH + 'px';
            contentCard.classList.add('dp-sticky');
        } else {
            sidebarCol.style.minHeight = cH + 'px';
            sidebarCard.classList.add('dp-sticky');
        }
    }

    function scheduleStickyUpdate(){
        cancelAnimationFrame(__dpStickyRaf);
        __dpStickyRaf = requestAnimationFrame(updateStickyMode);
    }

    function setupStickyObservers(){
        if (__dpStickyRO) return;
        if (!('ResizeObserver' in window)) return;

        __dpStickyRO = new ResizeObserver(() => scheduleStickyUpdate());
        try{
            if (sidebarCard) __dpStickyRO.observe(sidebarCard);
            if (contentCard) __dpStickyRO.observe(contentCard);
        }catch(e){}
    }

    window.addEventListener('resize', dpDebounce(scheduleStickyUpdate, 120));
    window.addEventListener('load', () => scheduleStickyUpdate());

    // ============================================================
    // Dept helpers (kept)
    // ============================================================
    function isDeptToken(x){
        return /^d-[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i.test(String(x || '').trim());
    }

    function deptTokenFromUuid(uuid){
        const u = String(uuid || '').trim();
        if (!u) return '';
        return u.startsWith('d-') ? u : ('d-' + u);
    }

    function readDeptTokenFromUrl(){
        try{
            const usp = new URLSearchParams(window.location.search || '');
            for (const [k, v] of usp.entries()){
                if (isDeptToken(k)) return k;
                if (k === 'd' && isDeptToken(v)) return v;
            }
        }catch(e){}

        const raw = String(window.location.search || '').replace(/^\?/, '').split('&').filter(Boolean);
        for (const part of raw){
            const decoded = decodeURIComponent(part);
            if (isDeptToken(decoded)) return decoded;
        }
        return '';
    }

    function buildSearchWithDept(params, deptToken){
        const newParams = new URLSearchParams();

        for (const [key, value] of params.entries()) {
            if (!isDeptToken(key) && key !== 'd' && key !== 'department_uuid') {
                newParams.append(key, value);
            }
        }

        if (deptToken && isDeptToken(deptToken)) {
            newParams.append(deptToken, '1');
        }

        return newParams;
    }

    function pushUrlStateSubmenu(submenuSlug, headerUuid, deptTokenMaybe){
        const u = new URL(window.location.href);

        let path = stripSubmenuFromPath(u.pathname);
        const s = String(submenuSlug || '').trim();
        if (s) path += '&submenu=' + encodeURIComponent(s);

        let params = new URLSearchParams(u.search);

        const headerUuidFinal = headerUuid ||
            (window.__DP_PAGE_SCOPE__?.header_menu_uuid) ||
            readHeaderMenuUuidFromUrl();

        params = buildSearchWithHeaderUuid(params, headerUuidFinal);

        const deptTokenFinal = (deptTokenMaybe && isDeptToken(deptTokenMaybe))
            ? deptTokenMaybe
            : readDeptTokenFromUrl();

        const finalParams = buildSearchWithDept(params, deptTokenFinal);

        const search = finalParams.toString() ? '?' + finalParams.toString() : '';
        window.history.pushState({}, '', path + search + u.hash);
    }

    // ============================================================
    // Core content injection helpers (kept)
    // ============================================================
    function setInnerHTMLWithScripts(el, html){
        el.innerHTML = '';

        const tpl = document.createElement('template');
        tpl.innerHTML = String(html || '');

        const scripts = Array.from(tpl.content.querySelectorAll('script'));
        scripts.forEach(s => s.remove());

        el.appendChild(tpl.content);

        runWithDomReadyShim(() => {
            scripts.forEach((oldScript) => {
                const s = document.createElement('script');
                for (const attr of oldScript.attributes) s.setAttribute(attr.name, attr.value);
                s.textContent = oldScript.textContent || '';
                document.body.appendChild(s);
            });
        });
    }

    function clearModuleAssets(){
        document.querySelectorAll('[data-dp-asset="style"]').forEach(n => n.remove());
        document.querySelectorAll('[data-dp-asset="script"]').forEach(n => n.remove());
    }

    function injectModuleStyles(stylesHtml){
        document.querySelectorAll('[data-dp-asset="style"]').forEach(n => n.remove());

        const tpl = document.createElement('template');
        tpl.innerHTML = String(stylesHtml || '');

        const SITE_ORIGIN = (() => {
            try { return new URL(SITE_BASE, window.location.origin).origin; }
            catch(e){ return window.location.origin; }
        })();

        const isSameOrigin = (url) => {
            try {
                const u = new URL(url, window.location.href);
                return u.origin === SITE_ORIGIN;
            } catch(e){
                return false;
            }
        };

        const isBlockedStyleHref = (href) => {
            const h = String(href || '').toLowerCase();
            if (h.includes('bootstrap')) return true;
            if (h.includes('font-awesome') || h.includes('fontawesome')) return true;
            if (h.includes('/assets/css/common/main.css')) return true;
            if (h.includes('cdn.jsdelivr.net')) return true;
            if (h.includes('cdnjs.cloudflare.com')) return true;
            return false;
        };

        [...tpl.content.children].forEach((node) => {
            const tag = (node.tagName || '').toUpperCase();
            if (!['LINK','STYLE','META'].includes(tag)) return;

            if (tag === 'LINK'){
                const rel = String(node.getAttribute('rel') || '').toLowerCase();
                const href = node.getAttribute('href') || '';
                if (rel !== 'stylesheet') return;
                if (!href) return;
                if (isBlockedStyleHref(href)) return;
                if (/^https?:\/\//i.test(href) && !isSameOrigin(href)) return;
            }

            node.setAttribute('data-dp-asset', 'style');
            document.head.appendChild(node);
        });
    }

    function runWithDomReadyShim(fn){
        const origAdd = document.addEventListener;

        document.addEventListener = function(type, listener, options){
            if (type === 'DOMContentLoaded' && document.readyState !== 'loading') {
                try { listener.call(document, new Event('DOMContentLoaded')); } catch(e){ console.error(e); }
                return;
            }
            return origAdd.call(document, type, listener, options);
        };

        try { fn(); } finally { document.addEventListener = origAdd; }
    }

    function injectModuleScripts(scriptsHtml){
        document.querySelectorAll('[data-dp-asset="script"]').forEach(n => n.remove());

        const tpl = document.createElement('template');
        tpl.innerHTML = String(scriptsHtml || '');

        const SITE_ORIGIN = (() => {
            try { return new URL(SITE_BASE, window.location.origin).origin; }
            catch(e){ return window.location.origin; }
        })();

        const isSameOrigin = (url) => {
            try {
                const u = new URL(url, window.location.href);
                return u.origin === SITE_ORIGIN;
            } catch(e){
                return false;
            }
        };

        const isBlockedScriptSrc = (src) => {
            const s = String(src || '').toLowerCase();
            if (s.includes('bootstrap')) return true;
            if (s.includes('sweetalert2')) return true;
            if (s.includes('cdn.jsdelivr.net')) return true;
            if (s.includes('cdnjs.cloudflare.com')) return true;
            return false;
        };

        const scripts = tpl.content.querySelectorAll('script');

        runWithDomReadyShim(() => {
            scripts.forEach((oldScript) => {
                const src = oldScript.getAttribute('src') || '';
                if (src){
                    if (isBlockedScriptSrc(src)) return;
                    if (/^https?:\/\//i.test(src) && !isSameOrigin(src)) return;
                }

                const s = document.createElement('script');
                for (const attr of oldScript.attributes) s.setAttribute(attr.name, attr.value);
                s.textContent = oldScript.textContent || '';
                s.setAttribute('data-dp-asset', 'script');
                document.body.appendChild(s);
            });
        });
    }

// ============================================================
// ✅ Page resolver (slug + linkPath support)
// ============================================================
// ✅ Page resolver (slug + linkPath support)
async function resolvePublicPage(headerUuid = '', linkPath = '', slug = ''){
    const rawLink = String(linkPath || '').trim();
    const rawSlug = String(slug || '').trim();

    if (!rawLink && !rawSlug) return null;

    const u = new URL(API_BASE + '/public/pages/resolve', window.location.origin);

    // keep link behavior same
    if (rawLink){
        const pathOnly = (function(){
            try { return (new URL(rawLink, window.location.origin)).pathname; }
            catch(e){ return rawLink; }
        })();
        u.searchParams.set('link', pathOnly);
    }

    // ✅ NEW: send slug too (backend will only use it if link fails/missing)
    if (rawSlug) u.searchParams.set('slug', rawSlug);

    if (headerUuid) {
        if (isUuid(headerUuid)) u.searchParams.set('header_uuid', headerUuid);
        else u.searchParams.set('header_menu_id', headerUuid);
    }

    const r = await fetchJsonWithStatus(u.toString());
    if (r.ok) return (r.data?.data || r.data?.page || null);
    if (r.status === 404) return null;

    throw new Error((r.data && (r.data.message || r.data.error))
        ? (r.data.message || r.data.error)
        : ('Resolve failed: ' + r.status));
}

    // ============================================================
    // Submenu renderer (patched to apply meta + internal link routing)
    // ============================================================
    async function loadSubmenuRightContent(submenuSlug, pageScope, preOpenedWin = null, clickedTitle = ''){
        const sslug = String(submenuSlug || '').trim();
        if (!sslug) {
            try{ if (preOpenedWin && !preOpenedWin.closed) preOpenedWin.close(); }catch(e){}
            return;
        }

        showLoading('Loading submenu…');

        const u = new URL(API_BASE + '/public/page-submenus/render', window.location.origin);
        u.searchParams.set('slug', sslug);

        // Pass header scope if present
        let headerMenuId = null;
        if (pageScope) headerMenuId = pageScope.header_menu_id || pageScope.requested_header_menu_id || pageScope.effective_header_menu_id;

        if (!headerMenuId) {
            const urlParams = new URLSearchParams(window.location.search);
            headerMenuId = urlParams.get('header_menu_id') || urlParams.get('menu_id') || urlParams.get('headerMenuId');
        }

        if (!headerMenuId && window.__DP_PAGE_SCOPE__) {
            headerMenuId = window.__DP_PAGE_SCOPE__.requested_header_menu_id || window.__DP_PAGE_SCOPE__.header_menu_id;
        }

        if (headerMenuId && parseInt(headerMenuId) > 0) u.searchParams.set('header_menu_id', String(headerMenuId));

        const headerUuid = pageScope?.header_menu_uuid || readHeaderMenuUuidFromUrl();
        if (headerUuid && isUuid(headerUuid)) u.searchParams.set('header_uuid', headerUuid);

        if (pageScope?.page_id) u.searchParams.set('page_id', pageScope.page_id);
        else if (pageScope?.page_slug) u.searchParams.set('page_slug', pageScope.page_slug);

        const r = await fetchJsonWithStatus(u.toString());

        if (!r.ok) {
    try{ if (preOpenedWin && !preOpenedWin.closed) preOpenedWin.close(); }catch(e){}

    // ✅ 404 => Coming Soon (old behavior)
    if (r.status === 404) {
        clearModuleAssets();
        showComingSoon(String(submenuSlug || '').trim(), {
            title: String(clickedTitle || 'Coming Soon').trim(),
            message: 'This section is coming soon.'
        });
        scheduleStickyUpdate();
        return;
    }

    // Other errors => real error
    const msg = (r.data && (r.data.message || r.data.error))
        ? (r.data.message || r.data.error)
        : ('Load failed: ' + r.status);

    showError(msg);
    scheduleStickyUpdate();
    return;
}

        const payload = r.data || {};

        // ✅ NEW: apply meta tags if backend returns them for submenu
        applyMetaFromPayload(payload);

        const type = payload.type;

        elTitle.textContent = payload.title || 'Dynamic Page';
        setMeta('');

        if (type === 'coming_soon') {
            try{ if (preOpenedWin && !preOpenedWin.closed) preOpenedWin.close(); }catch(e){}
            clearModuleAssets();
            showComingSoon(sslug, payload);
            scheduleStickyUpdate();
            return;
        }

        if (type === 'includable') {
            try{ if (preOpenedWin && !preOpenedWin.closed) preOpenedWin.close(); }catch(e){}
            if (elComingSoon) elComingSoon.classList.add('d-none');
            injectModuleStyles(payload?.assets?.styles || '');

            const out = payload.html || '';
            setInnerHTMLWithScripts(
                elHtml,
                out ? out : '<p class="text-muted mb-0">No HTML returned from includable section.</p>'
            );

            injectModuleScripts(payload?.assets?.scripts || '');
        }
        else if (type === 'page') {
            try{ if (preOpenedWin && !preOpenedWin.closed) preOpenedWin.close(); }catch(e){}
            if (elComingSoon) elComingSoon.classList.add('d-none');
            clearModuleAssets();
            const out = payload.html || '';
            setInnerHTMLWithScripts(
                elHtml,
                out ? out : '<p class="text-muted mb-0">No HTML returned from page content.</p>'
            );
        }
        else if (type === 'url') {
            if (elComingSoon) elComingSoon.classList.add('d-none');
            clearModuleAssets();

            const rawUrl = payload.url || payload.link || payload.href || '';
            const safeUrl = normalizeExternalUrl(rawUrl) || (rawUrl ? rawUrl : 'about:blank');

            // ✅ NEW: internal links should open in SAME TAB so server can match pages.page_link and render meta in view-source
            if (safeUrl && safeUrl !== 'about:blank' && isSameOriginUrl(safeUrl)) {
                try{ if (preOpenedWin && !preOpenedWin.closed) preOpenedWin.close(); }catch(e){}
                window.location.href = safeUrl;
                return;
            }

            // External: keep old behavior
            let opened = false;
            try{
                if (preOpenedWin && !preOpenedWin.closed && safeUrl && safeUrl !== 'about:blank'){
                    preOpenedWin.location.href = safeUrl;
                    opened = true;
                } else if (safeUrl && safeUrl !== 'about:blank'){
                    const w = window.open(safeUrl, '_blank', 'noopener,noreferrer');
                    opened = !!w;
                }
            }catch(e){}

            setInnerHTMLWithScripts(elHtml, `
              <div class="alert alert-info mb-0">
                ${opened ? 'Opened link in a new tab.' : 'Popup blocked. Please open the link:'}
                <a href="${safeUrl}" target="_blank" rel="noopener noreferrer" class="ms-1">Open link</a>
              </div>
            `);
        }
        else {
            try{ if (preOpenedWin && !preOpenedWin.closed) preOpenedWin.close(); }catch(e){}
            if (elComingSoon) elComingSoon.classList.add('d-none');
            clearModuleAssets();
            setInnerHTMLWithScripts(elHtml, '<p class="text-muted mb-0">Unknown content type.</p>');
        }

        elLoading.classList.add('d-none');
        elWrap.classList.remove('d-none');
        if (elNotFound) elNotFound.classList.add('d-none');
        if (elComingSoon) elComingSoon.classList.add('d-none');
        hideError();

        scheduleStickyUpdate();
    }

    // ============================================================
    // Tree rendering (patched submenu link routing)
    // ============================================================
    function normalizeTree(treeData){
        if (!treeData) return [];
        if (Array.isArray(treeData)) return treeData;

        const arr = pick(treeData, ['tree','items','data','submenus','children','menu']);
        if (Array.isArray(arr)) return arr;

        if (treeData.data && Array.isArray(treeData.data.items)) return treeData.data.items;
        if (treeData.data && Array.isArray(treeData.data)) return treeData.data;

        return [];
    }

    function normalizeChildren(node){
        const c = pick(node, ['children','nodes','items','submenus']);
        return normalizeTree(c);
    }

    function renderTree(nodes, currentLower, parentUl, level = 0){
        let anyActiveInThisList = false;

        nodes.forEach((node) => {
            const li = document.createElement('li');
            li.className = 'hallienz-side__item';

            const children = normalizeChildren(node);
            const hasChildren = children.length > 0;

            const row = document.createElement('div');
            row.className = 'hallienz-side__row';

            const a = document.createElement('a');
            a.className = 'hallienz-side__link';
            a.href = 'javascript:void(0)';

            const nodeSlug = String(pick(node, ['slug']) || '').trim();
            a.dataset.submenuSlug = nodeSlug;
            a.setAttribute('data-submenu-slug', nodeSlug);

            const nodeLink = String(pick(node, ['link','url','href','external_url','externalLink','page_link','page_url']) || '').trim();
            if (nodeLink) a.dataset.submenuLink = nodeLink;

            const nodeTypeHint = String(pick(node, ['type','content_type','submenu_type','render_type']) || '').toLowerCase().trim();
            if (nodeTypeHint === 'url') a.dataset.submenuType = 'url';

            const basePad = 14;
            const indent = Math.min(54, level * 14);
            a.style.paddingLeft = (basePad + indent) + 'px';

            const title = pick(node, ['title','name','label']) || 'Untitled';
            const text = document.createElement('span');
            text.className = 'hallienz-side__text';
            text.textContent = title;
            a.appendChild(text);

            a.addEventListener('click', async (e) => {
                e.preventDefault();
                e.stopPropagation();

                // ✅ CHANGE: If node has a link:
                // - INTERNAL -> open in same tab (so server matches pages.page_link and injects meta in view-source)
                // - EXTERNAL -> open in new tab (old behavior)
                const directLinkRaw = (a.dataset.submenuLink || '').trim();
                const directLink = normalizeExternalUrl(directLinkRaw);

                if (directLink){
                    if (isSameOriginUrl(directLink)){
                        const u = normalizeToUrl(directLink);
                        if (u){
                            // Preserve header token + dept token if needed
                            let params = new URLSearchParams(u.search || '');

                            const headerUuidFinal =
                                (a.dataset.headerUuid || '') ||
                                (window.__DP_PAGE_SCOPE__?.header_menu_uuid) ||
                                readHeaderMenuUuidFromUrl();

                            params = buildSearchWithHeaderUuid(params, headerUuidFinal);

                            const deptTokenFinal = readDeptTokenFromUrl();
                            params = buildSearchWithDept(params, deptTokenFinal);

                            u.search = params.toString();
                            window.location.href = u.toString();
                            return;
                        }
                        window.location.href = directLink;
                        return;
                    }

                    try { window.open(directLink, '_blank', 'noopener,noreferrer'); } catch(err) {}

                    document.querySelectorAll('.hallienz-side__link.active').forEach(x => x.classList.remove('active'));
                    a.classList.add('active');
                    scheduleStickyUpdate();
                    return;
                }

                const raw = (a.dataset.submenuSlug || '').trim();
                const bad = ['null','undefined','#','0'];
                const sslug = (raw && !bad.includes(raw.toLowerCase())) ? raw : '';

                if (!sslug) {
                    if (hasChildren) {
                        li.classList.toggle('open');
                        scheduleStickyUpdate();
                        return;
                    }

                    document.querySelectorAll('.hallienz-side__link.active').forEach(x => x.classList.remove('active'));
                    a.classList.add('active');

                    clearModuleAssets();
                    showComingSoon('', {
                        title: String(title || 'Coming Soon'),
                        message: 'This section is coming soon.'
                    });
                    scheduleStickyUpdate();
                    return;
                }

                document.querySelectorAll('.hallienz-side__link.active').forEach(x => x.classList.remove('active'));
                a.classList.add('active');

                let preWin = null;
                try{
                    const hinted = String(a.dataset.submenuType || '').toLowerCase().trim();
                    if (hinted === 'url'){
                        preWin = window.open('about:blank', '_blank', 'noopener,noreferrer');
                    }
                }catch(e2){}

                await loadSubmenuRightContent(
    sslug,
    window.__DP_PAGE_SCOPE__ || null,
    preWin,
    String(title || 'Coming Soon')
);
                pushUrlStateSubmenu(sslug, window.__DP_PAGE_SCOPE__?.header_menu_uuid || '', readDeptTokenFromUrl());
            });

            row.appendChild(a);

            let childUl = null;
            let childHasActive = false;

            if (hasChildren){
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'hallienz-side__toggle';
                btn.setAttribute('aria-label', 'Toggle children');
                btn.setAttribute('aria-expanded', 'false');

                const ico = document.createElement('i');
                ico.className = 'fa-solid fa-chevron-right';
                btn.appendChild(ico);

                childUl = document.createElement('ul');
                childUl.className = 'hallienz-side__children';

                childHasActive = renderTree(children, currentLower, childUl, level + 1);

                if (childHasActive){
                    li.classList.add('open');
                    btn.setAttribute('aria-expanded', 'true');
                }

                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    const open = li.classList.toggle('open');
                    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
                });

                row.appendChild(btn);
            }

            li.appendChild(row);
            if (childUl) li.appendChild(childUl);
            parentUl.appendChild(li);

            if (childHasActive) anyActiveInThisList = true;
        });

        return anyActiveInThisList;
    }

    async function loadSidebarIfAny(page) {
        showSidebarSkeleton();

        const pageId   = pick(page, ['id']);
        const pageSlug = pick(page, ['slug']);

        const headerFromUrl = readHeaderMenuUuidFromUrl();

        if (!pageId && !pageSlug && !headerFromUrl) {
            hideSidebarSkeleton();
            sidebarCol.classList.add('d-none');
            sidebarCol.classList.remove('dp-side-preload');
            contentCol.className = 'col-12';
            scheduleStickyUpdate();
            return { hasSidebar: false, firstSubmenuSlug: '' };
        }

        const treeUrl = new URL(API_BASE + '/public/page-submenus/tree', window.location.origin);

        if (headerFromUrl) {
            if (isUuid(headerFromUrl)) treeUrl.searchParams.set('header_uuid', headerFromUrl);
            else treeUrl.searchParams.set('header_menu_id', headerFromUrl);
        }

        if (pageId) treeUrl.searchParams.set('page_id', pageId);
        else if (pageSlug) treeUrl.searchParams.set('page_slug', pageSlug);

        const r = await fetchJsonWithStatus(treeUrl.toString());

        if (!r.ok) {
            hideSidebarSkeleton();
            sidebarCol.classList.add('d-none');
            sidebarCol.classList.remove('dp-side-preload');
            contentCol.className = 'col-12';
            scheduleStickyUpdate();
            return { hasSidebar: false, firstSubmenuSlug: '' };
        }

        const body = r.data || {};
        let nodes = normalizeTree(body);

        if (!nodes.length) {
            hideSidebarSkeleton();
            sidebarCol.classList.add('d-none');
            sidebarCol.classList.remove('dp-side-preload');
            contentCol.className = 'col-12';
            scheduleStickyUpdate();
            return { hasSidebar: false, firstSubmenuSlug: '' };
        }

        sidebarCol.classList.remove('d-none');
        sidebarCol.classList.remove('dp-side-preload');
        contentCol.className = 'col-12 col-lg-9';

        submenuList.innerHTML = '';

        const pageTitle = pick(page, ['title']) || 'Menu';
        sidebarHead.textContent = pageTitle;

        renderTree(nodes, '', submenuList, 0);

        hideSidebarSkeleton();
        scheduleStickyUpdate();

        return { hasSidebar: true, firstSubmenuSlug: '' };
    }

    function openAncestorsOfLink(linkEl){
        try{
            let node = linkEl?.closest('.hallienz-side__item');
            while(node){
                node.classList.add('open');
                node = node.parentElement?.closest?.('.hallienz-side__item') || null;
            }
        }catch(e){}
    }

    function setupHeaderMenuClicks() {
        document.addEventListener('click', function(e) {
            const headerLink = e.target.closest('a[data-header-menu]') ||
                              e.target.closest('a[href*="h-"]') ||
                              e.target.closest('a[href*="header_menu_id"]');

            if (headerLink) {
                e.preventDefault();

                let menuUuid = headerLink.getAttribute('data-menu-uuid') ||
                              headerLink.getAttribute('data-header-uuid');

                if (!menuUuid) {
                    const href = headerLink.getAttribute('href') || '';
                    const url = new URL(href, window.location.origin);

                    for (const [key, value] of url.searchParams.entries()) {
                        if (isHeaderMenuUuidToken(key)) { menuUuid = extractUuidFromHeaderToken(key); break; }
                        if (isHeaderMenuUuidToken(value)) { menuUuid = extractUuidFromHeaderToken(value); break; }
                    }

                    if (!menuUuid) {
                        const legacyId = url.searchParams.get('header_menu_id');
                        if (legacyId) menuUuid = legacyId;
                    }
                }

                if (menuUuid) {
                    const currentUrl = new URL(window.location);
                    const newParams = buildSearchWithHeaderUuid(currentUrl.searchParams, menuUuid);
                    currentUrl.search = newParams.toString();
                    currentUrl.searchParams.delete('submenu');
                    window.location.href = currentUrl.toString();
                }
            }
        });
    }

    async function init(){
    hideError();
    setupStickyObservers();

    resetSidebarPreloadState();

    const slugCandidate = getSlugCandidate();

    // ✅ Server resolved link path for header-link routes
    const serverLinkPath = String(window.__DP_SERVER_LINK_PATH__ || window.location.pathname || '').trim();
    const headerFromUrl = readHeaderMenuUuidFromUrl();

        if (!slugCandidate && !serverLinkPath) {
    elLoading.classList.add('d-none');
    clearModuleAssets();
    showComingSoon('', { title: 'Coming Soon', message: 'This page is coming soon.' });

    hideSidebarSkeleton();
    sidebarCol.classList.add('d-none');
    sidebarCol.classList.remove('dp-side-preload');
    contentCol.className = 'col-12';
    scheduleStickyUpdate();
    return;
}

        showLoading('Loading page…');

        // ✅ If server already resolved the page from pages.page_link/page_url, use it
        const serverPage = (window.__DP_SERVER_PAGE__ && typeof window.__DP_SERVER_PAGE__ === 'object') ? window.__DP_SERVER_PAGE__ : null;

        let page = null;
        if (serverPage && parseInt(serverPage.id || '0', 10) > 0) {
            page = serverPage;
        } else {
            page = await resolvePublicPage(headerFromUrl, serverLinkPath, slugCandidate);
        }

        if (!page) {
    clearModuleAssets();
    showComingSoon(slugCandidate || serverLinkPath || '', {
        title: 'Coming Soon',
        message: 'This page is coming soon.'
    });

    hideSidebarSkeleton();
    sidebarCol.classList.add('d-none');
    sidebarCol.classList.remove('dp-side-preload');
    contentCol.className = 'col-12';
    scheduleStickyUpdate();
    return;
}
        // ✅ Apply meta tags if backend returns in page payload (optional)
        applyMetaFromPayload(page);

        window.__DP_PAGE_SCOPE__ = {
            page_id: pick(page, ['id']) || null,
            page_slug: pick(page, ['slug']) || null,
            header_menu_uuid: (isUuid(headerFromUrl) ? headerFromUrl : null) || null,
            requested_header_menu_uuid: (isUuid(headerFromUrl) ? headerFromUrl : null) || null,
            header_menu_id: (!isUuid(headerFromUrl) ? parseInt(headerFromUrl) : null) || null,
            requested_header_menu_id: (!isUuid(headerFromUrl) ? parseInt(headerFromUrl) : null) || null
        };

        elTitle.textContent = pick(page, ['title']) || slugCandidate || 'Dynamic Page';
        setMeta('');

        const html = pick(page, ['content_html','html']) || '';
        setInnerHTMLWithScripts(elHtml, html || '<p class="text-muted mb-0">No content_html returned from pages resolve API.</p>');

        elLoading.classList.add('d-none');
        elWrap.classList.remove('d-none');
        if (elNotFound) elNotFound.classList.add('d-none');
        if (elComingSoon) elComingSoon.classList.add('d-none');

        await loadSidebarIfAny(page);

        // ✅ Submenu load: path &submenu= OR ?submenu=
        let submenuSlug = (readSubmenuFromPathname() || '').trim();
        if (!submenuSlug){
            const qs = new URLSearchParams(window.location.search);
            submenuSlug = (qs.get('submenu') || '').trim();
        }

        if (submenuSlug) {
            const link = document.querySelector('.hallienz-side__link[data-submenu-slug="' + safeCssEscape(submenuSlug) + '"]');
            if (link) {
                document.querySelectorAll('.hallienz-side__link.active').forEach(x => x.classList.remove('active'));
                link.classList.add('active');
                openAncestorsOfLink(link);
            }
            await loadSubmenuRightContent(submenuSlug, window.__DP_PAGE_SCOPE__);
        }

        scheduleStickyUpdate();
    }

    init().catch((e) => {
        console.error(e);
        showError(e?.message || 'Something went wrong.');
        hideSidebarSkeleton();
        sidebarCol.classList.add('d-none');
        sidebarCol.classList.remove('dp-side-preload');
        contentCol.className = 'col-12';
        scheduleStickyUpdate();
    });

    document.addEventListener('DOMContentLoaded', function() {
        setupHeaderMenuClicks();
        scheduleStickyUpdate();
    });

    window.addEventListener('popstate', function() {
        init().catch((e) => {
            console.error(e);
            showError(e?.message || 'Something went wrong.');
            hideSidebarSkeleton();
            sidebarCol.classList.add('d-none');
            sidebarCol.classList.remove('dp-side-preload');
            contentCol.className = 'col-12';
            scheduleStickyUpdate();
        });
    });

})();
</script>

@stack('scripts')
</body>
</html>