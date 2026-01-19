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
            $table->integer('quantidade_cautelada')->default(0)->after('quantidade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('itens_estoque', function (Blueprint $table) {
            $table->dropColumn('quantidade_cautelada');
        });
    }
};
