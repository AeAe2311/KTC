<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;

$columns = Schema::getColumnListing('menus');
echo "Menus table columns:\n";
foreach ($columns as $col) {
    try {
        $info = Schema::getConnection()->getDoctrineColumn('menus', $col);
        echo $col . ': ' . ($info->getNotnull() ? 'NOT NULL' : 'NULL') . ' - ' . $info->getType()->getName() . "\n";
    } catch (Exception $e) {
        echo $col . ': Error getting info - ' . $e->getMessage() . "\n";
    }
}