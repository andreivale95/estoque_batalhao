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
        Schema::table('itens_patrimoniais', function (Blueprint $table) {
            $table->string('fornecedor')->nullable()->after('data_saida');
            $table->string('nota_fiscal')->nullable()->after('fornecedor');
            $table->string('lote')->nullable()->after('nota_fiscal');
            $table->string('fonte')->nullable()->after('lote');
            $table->date('data_trp')->nullable()->after('fonte');
            $table->string('sei')->nullable()->after('data_trp');
            $table->decimal('valor_unitario', 10, 2)->nullable()->after('sei');
            $table->decimal('valor_total', 10, 2)->nullable()->after('valor_unitario');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('itens_patrimoniais', function (Blueprint $table) {
            $table->dropColumn([
                'fornecedor',
                'nota_fiscal',
                'lote',
                'fonte',
                'data_trp',
                'sei',
                'valor_unitario',
                'valor_total'
            ]);
        });
    }
};
