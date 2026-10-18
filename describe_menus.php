<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$result = \DB::select('DESCRIBE menus');
echo "Menus table schema:\n";
foreach ($result as $row) {
    echo $row->Field . ': ' . $row->Type . ' ' . ($row->Null === 'YES' ? 'NULL' : 'NOT NULL');
    if ($row->Default) {
        echo ' DEFAULT ' . $row->Default;
    }
    echo "\n";
}