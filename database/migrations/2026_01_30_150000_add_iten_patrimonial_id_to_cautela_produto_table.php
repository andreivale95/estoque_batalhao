<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cautela_produto', function (Blueprint $table) {
            $table->foreignId('iten_patrimonial_id')
                ->nullable()
                ->after('estoque_id')
                ->constrained('itens_patrimoniais');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cautela_produto', function (Blueprint $table) {
            $table->dropForeign(['iten_patrimonial_id']);
            $table->dropColumn('iten_patrimonial_id');
        });
    }
};
