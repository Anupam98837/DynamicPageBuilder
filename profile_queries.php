<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tables = ['announcements','achievements','notices','student_activities','career_notices','why_us','scholarships','placement_notices','pages'];

echo "Profiling counts:\n";
foreach ($tables as $t) {
    if (!Schema::hasTable($t)) {
        echo "$t: table not found\n";
        continue;
    }
    $st = microtime(true);
    try {
        $count = DB::table($t)->where('request_for_approval', 1)->count();
        $el = microtime(true) - $st;
        echo "$t count: $count elapsed: {$el}s\n";
    } catch (\Exception $e) {
         echo "$t Error: " . $e->getMessage() . "\n";
    }
}
echo "\nChecking memory leaks / leftJoin counts...\n";
