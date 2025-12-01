<?php
// Load Laravel
require 'bootstrap/app.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

// Use Schema facade to list all tables and columns
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

$tables = Schema::connection('mysql')->getTables();

$schema = [];
foreach ($tables as $table) {
    $columns = Schema::connection('mysql')->getColumns($table);
    $schema[$table] = [];
    foreach ($columns as $column) {
        $schema[$table][] = [
            'name' => $column['name'],
            'type' => $column['type'],
            'nullable' => $column['nullable'],
        ];
    }
}

echo json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>
