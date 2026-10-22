<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$storeCodes = \DB::table('menu_categories')->select('store_code')->distinct()->get()->pluck('store_code')->toArray();
echo "Store codes with categories: " . implode(', ', $storeCodes) . "\n";

$user = auth()->user();
if ($user) {
    echo "Current user store_code: " . ($user->store_code ?? 'null') . "\n";
} else {
    echo "No authenticated user\n";
}