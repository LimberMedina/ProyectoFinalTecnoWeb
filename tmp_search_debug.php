<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/bootstrap/app.php';
$kernel = app()->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

function inspectTable(string $table): void
{
    echo "TABLE: $table\n";
    if (!Schema::hasTable($table)) {
        echo "  MISSING\n";
        return;
    }
    $columns = Schema::getColumnListing($table);
    echo "  COLUMNS: " . implode(', ', $columns) . "\n";
    $rows = DB::table($table)->limit(3)->get();
    foreach ($rows as $row) {
        echo "  ROW: " . json_encode($row) . "\n";
    }
}

inspectTable('productos');
inspectTable('promociones');

echo "\nCONTROLLER RESPONSE FOR q=pro:\n";
$request = Illuminate\Http\Request::create('/buscar-global', 'GET', ['q' => 'pro']);
$controller = app(App\Http\Controllers\GlobalSearchController::class);
$response = $controller->__invoke($request);
echo $response->getContent() . "\n";
