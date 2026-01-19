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
        Schema::table('cautelas', function (Blueprint $table) {
            $table->string('responsavel_unidade')->nullable()->after('instituicao');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cautelas', function (Blueprint $table) {
            $table->dropColumn('responsavel_unidade');
        });
    }
};
