<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Mock request
$request = new \Illuminate\Http\Request();
$request->attributes->set('auth_tokenable_id', 1); // Mock Admin

class MyController extends \App\Http\Controllers\API\MasterApprovalController {
    public function testQuery($key, $req) {
        $st = microtime(true);
        $q = $this->moduleQuery($key, $req, false);
        if (!$q) {
             echo "$key: moduleQuery returned null\n";
             return;
        }
        $q->where("pg.request_for_approval", 1)->where("pg.is_approved", 0); // fallback alias just to isolate
        
        $cfg = $this->modules()[$key];
        $a = $cfg['alias'];
        
        // Reset query for genericAlias
        $q = $this->moduleQuery($key, $req, false);
        $q->where("{$a}.request_for_approval", 1)->where("{$a}.is_approved", 0);
        
        try {
            $rows = $q->get();
            $el = microtime(true) - $st;
            echo "$key moduleQuery + get() elapsed: {$el}s, rows: " . count($rows) . "\n";
        } catch (\Exception $e) {
            echo "$key Error: " . $e->getMessage() . "\n";
        }
    }
}

$c = new MyController();
$tables = ['announcements','achievements','notices','student_activities','career_notices','why_us','scholarships','placement_notices','pages'];

echo "Profiling moduleQuery():\n";
foreach ($tables as $t) {
    if ($t === 'pages') $c->testQuery('pages', $request);
    else {
        // Find configuration key
        $c->testQuery($t, $request);
    }
}
echo "\nDone.\n";
