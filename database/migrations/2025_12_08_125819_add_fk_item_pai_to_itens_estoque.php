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
            $table->unsignedBigInteger('fk_item_pai')->nullable()->after('fk_secao');
            $table->foreign('fk_item_pai')->references('id')->on('itens_estoque')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('itens_estoque', function (Blueprint $table) {
            $table->dropForeign(['fk_item_pai']);
            $table->dropColumn('fk_item_pai');
        });
    }
};
