#!/usr/bin/env bash

set -e

MASTER_MIGRATION="2026_02_05_000000_create_full_schema"

php -r "require 'vendor/autoload.php';
\$app = require 'bootstrap/app.php';
\$app->make(Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap();
\$has = Illuminate\\Support\\Facades\\DB::table('migrations')
    ->where('migration', '${MASTER_MIGRATION}')
    ->exists();
if (!\$has) {
    fwrite(STDERR, 'Master migration not applied. Aborting to avoid destructive migrate.' . PHP_EOL);
    exit(1);
}
" 

php artisan migrate --force
