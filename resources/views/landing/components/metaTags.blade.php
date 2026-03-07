{{-- resources/views/landing/components/metaTags.blade.php --}}
@php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$pageId    = $pageId ?? ($dpResolvedPage->id ?? null);
$submenuId = $submenuId ?? ($dpResolvedSubmenu->id ?? null);

// kept for safety (not used for defaults anymore)
$fallbackTitle = $fallbackTitle
  ?? ($dpResolvedSubmenu->title ?? null)
  ?? ($dpResolvedPage->page_title ?? null)
  ?? ($dpResolvedPage->title ?? null)
  ?? config('app.name', 'Website');

// ✅ canonical WITHOUT query params (no ?mode=test etc.) — used ONLY for scope resolving
$canonical = $canonical ?? ($dpCanonical ?? url()->current());

/**
 * ✅ normalize any input URL/path -> "/path" (no query, no trailing slash)
 */
if (!function_exists('dp_norm_path')) {
  function dp_norm_path(?string $raw): ?string {
    if ($raw === null) return null;
    $raw = trim($raw);
    if ($raw === '') return null;

    // if mistakenly like "/http://..." fix it
    if (str_starts_with($raw, '/http://') || str_starts_with($raw, '/https://')) {
      $raw = ltrim($raw, '/');
    }

    $path = parse_url($raw, PHP_URL_PATH);
    $path = $path ?: $raw;

    $path = explode('?', $path, 2)[0];
    $path = explode('#', $path, 2)[0];

    $path = '/' . ltrim($path, '/');
    $path = rtrim($path, '/');

    return $path === '' ? '/' : $path;
  }
}

if (!function_exists('dp_meta_norm_type')) {
  function dp_meta_norm_type(string $type, string $attr): string {
    $t = strtolower(trim($type));
    $a = strtolower(trim($attr));
    if ($t === 'og' || $t === 'open_graph' || $t === 'opengraph') return 'opengraph';
    if ($t === 'http' || $t === 'http_equiv' || $t === 'http-equiv') return 'http';
    if ($t === 'name') return str_starts_with($a, 'twitter:') ? 'twitter' : 'standard';
    if ($t === 'property') return 'opengraph';
    if ($t === 'charset' || $a === 'charset') return 'charset';
    if (str_starts_with($a, 'og:')) return 'opengraph';
    if (str_starts_with($a, 'twitter:')) return 'twitter';
    return $t ?: 'standard';
  }
}

/**
 * ✅ Determine current path for static pages
 * This is the key used by your manageMetaTags page.
 */
$currentPath = dp_norm_path($canonical) ?? dp_norm_path(request()->path() ? ('/' . request()->path()) : '/');
$slugGuess   = trim(basename($currentPath ?? ''), '/');

/**
 * ✅ If pageId not provided, try resolving from pages table safely (no hard-coded columns)
 * We only query columns that exist.
 */
try {
  if (!$pageId && Schema::hasTable('pages')) {
    $q = DB::table('pages')->select('id');

    $candidates = array_values(array_unique(array_filter([
      $currentPath,
      $currentPath ? ltrim($currentPath, '/') : null,
      $currentPath ? ($currentPath . '/') : null,
      $currentPath ? (ltrim($currentPath, '/') . '/') : null,
    ])));

    $hasPageUrl  = Schema::hasColumn('pages', 'page_url');
    $hasPageLink = Schema::hasColumn('pages', 'page_link');
    $hasSlug     = Schema::hasColumn('pages', 'slug');

    $added = false;

    if ($hasPageUrl && count($candidates)) {
      $q->whereIn('page_url', $candidates);
      $added = true;
    }
    if ($hasPageLink && count($candidates)) {
      if ($added) $q->orWhereIn('page_link', $candidates);
      else { $q->whereIn('page_link', $candidates); $added = true; }
    }
    if ($hasSlug && $slugGuess !== '') {
      if ($added) $q->orWhere('slug', $slugGuess);
      else { $q->where('slug', $slugGuess); $added = true; }
    }

    if ($added) {
      $pageId = (int)($q->value('id') ?? 0);
      if (!$pageId) $pageId = null;
    }
  }
} catch (\Throwable $e) {
  // ignore
}

/**
 * ✅ ONLY DB TAGS (NO DEFAULTS, NO AUTO CANONICAL)
 */
$tags = [
  'charset'   => null,
  'standard'  => [],
  'opengraph' => [],
  'twitter'   => [],
  'http'      => [],
];

try {
  if (Schema::hasTable('meta_tags')) {

    $q = DB::table('meta_tags');

    // ✅ IMPORTANT: ignore soft-deleted
    if (Schema::hasColumn('meta_tags', 'deleted_at')) {
      $q->whereNull('deleted_at');
    }

    $scoped = false;

    // ✅ PRIMARY: page_id based (dynamic pages)
    if ($pageId && Schema::hasColumn('meta_tags', 'page_id')) {
      $q->where('page_id', (int)$pageId);
      $scoped = true;
    }

    // ✅ SECONDARY: page_link based (static pages)
    if (!$scoped && $currentPath && Schema::hasColumn('meta_tags', 'page_link')) {
      $q->where('page_link', $currentPath);
      $scoped = true;
    }

    // ✅ optional submenu scoping (only if columns exist)
    if ($submenuId && $scoped) {
      if (Schema::hasColumn('meta_tags', 'page_submenu_id')) {
        $q->where('page_submenu_id', (int)$submenuId);
      } elseif (Schema::hasColumn('meta_tags', 'submenu_id')) {
        $q->where('submenu_id', (int)$submenuId);
      }
    }

    // only fetch if we could scope (avoid returning whole table accidentally)
    if ($scoped) {
      $rows = $q->orderByDesc('id')->limit(500)->get();

      foreach ($rows as $r) {
        $type = (string)($r->tag_type ?? $r->type ?? 'standard');

        // ✅ support new + old column names
        $attr = trim((string)($r->attribute ?? $r->tag_attribute ?? $r->attr_value ?? ''));
        $val  = trim((string)($r->content ?? $r->tag_attribute_value ?? $r->value ?? ''));

        // ✅ detect type FIRST (so charset works even when attribute is empty)
        $typeKey = dp_meta_norm_type($type, $attr);

        // ✅ charset: ONLY if DB provided a value (NO UTF-8 fallback)
        if ($typeKey === 'charset') {
          if (!$tags['charset'] && $val !== '') $tags['charset'] = $val;
          continue;
        }

        // for others, attribute is required
        if ($attr === '') continue;

        if (!isset($tags[$typeKey])) $tags[$typeKey] = [];

        // keep first (because rows are desc)
        if (!array_key_exists($attr, $tags[$typeKey])) {
          $tags[$typeKey][$attr] = $val;
        }
      }
    }
  }
} catch (\Throwable $e) {}
@endphp

{{-- ✅ DB-only output (NO canonical link, NO injected defaults) --}}

@if(!empty($tags['charset']))
  <meta charset="{{ e($tags['charset']) }}" data-dp-dynamic-meta="1">
@endif

@foreach($tags['standard'] as $name => $content)
  @if(trim((string)$content) !== '')
    <meta name="{{ e($name) }}" content="{{ e($content) }}" data-dp-dynamic-meta="1">
  @endif
@endforeach

@foreach($tags['opengraph'] as $prop => $content)
  @if(trim((string)$content) !== '')
    <meta property="{{ e($prop) }}" content="{{ e($content) }}" data-dp-dynamic-meta="1">
  @endif
@endforeach

@foreach($tags['twitter'] as $name => $content)
  @if(trim((string)$content) !== '')
    <meta name="{{ e($name) }}" content="{{ e($content) }}" data-dp-dynamic-meta="1">
  @endif
@endforeach

@foreach($tags['http'] as $eq => $content)
  @if(trim((string)$content) !== '')
    <meta http-equiv="{{ e($eq) }}" content="{{ e($content) }}" data-dp-dynamic-meta="1">
  @endif
@endforeach