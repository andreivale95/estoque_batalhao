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
            $table->integer('quantidade_devolvida')->default(0)->after('quantidade');
            $table->date('data_devolucao')->nullable()->after('quantidade_devolvida');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cautela_produto', function (Blueprint $table) {
            $table->dropColumn(['quantidade_devolvida', 'data_devolucao']);
        });
    }
};
