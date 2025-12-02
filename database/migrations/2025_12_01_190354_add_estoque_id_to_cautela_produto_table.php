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
            $table->foreignId('estoque_id')->nullable()->after('produto_id')->constrained('itens_estoque');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cautela_produto', function (Blueprint $table) {
            $table->dropForeign(['estoque_id']);
            $table->dropColumn('estoque_id');
        });
    }
};
