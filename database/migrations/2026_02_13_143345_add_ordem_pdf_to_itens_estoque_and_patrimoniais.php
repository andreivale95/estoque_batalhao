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
            $table->integer('ordem_pdf')->nullable()->after('fk_secao');
        });

        Schema::table('itens_patrimoniais', function (Blueprint $table) {
            $table->integer('ordem_pdf')->nullable()->after('fk_secao');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('itens_estoque', function (Blueprint $table) {
            $table->dropColumn('ordem_pdf');
        });

        Schema::table('itens_patrimoniais', function (Blueprint $table) {
            $table->dropColumn('ordem_pdf');
        });
    }
};
