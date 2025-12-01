<?php
/**
 * Script para analisar colunas não utilizadas no banco de dados
 * Executa: php artisan tinker < analyze_unused_columns.php
 * Ou: php analyze_unused_columns.php (com laravel bootstrap)
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// Listar todas as tabelas
$tables = DB::getDoctrineSchemaManager()->listTableNames();

echo "\n=== ANÁLISE DE SCHEMA DO BANCO DE DADOS ===\n";
echo "Total de tabelas: " . count($tables) . "\n\n";

$allColumns = [];

foreach ($tables as $table) {
    $columns = DB::getDoctrineSchemaManager()->listTableColumns($table);
    echo "TABELA: $table\n";
    echo str_repeat("-", 60) . "\n";
    
    foreach ($columns as $col) {
        $nullable = $col->getNotnull() ? "NOT NULL" : "nullable";
        $default = $col->getDefault() ? " (default: {$col->getDefault()})" : "";
        echo "  ✓ " . $col->getName() . " (" . $col->getType() . ") - $nullable$default\n";
        
        // Armazenar para análise cruzada
        $allColumns[$table][] = $col->getName();
    }
    echo "\n";
}

// Gravar para análise posterior
file_put_contents(__DIR__ . '/storage/logs/db_schema_dump.json', json_encode($allColumns, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

echo "\n✓ Schema exportado para: storage/logs/db_schema_dump.json\n";
