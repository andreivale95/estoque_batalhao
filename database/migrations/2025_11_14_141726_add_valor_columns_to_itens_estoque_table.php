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
        Schema::table('itens_estoque', function (Blueprint $table) {
            $table->decimal('valor_total', 12, 2)->nullable()->after('quantidade');
            $table->decimal('valor_unitario', 12, 2)->nullable()->after('valor_total');
            $table->integer('quantidade_inicial')->nullable()->after('valor_unitario');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('itens_estoque', function (Blueprint $table) {
            $table->dropColumn(['valor_total', 'valor_unitario', 'quantidade_inicial']);
        });
    }
};
