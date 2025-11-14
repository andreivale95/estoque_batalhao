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
        Schema::create('cautelas', function (Blueprint $table) {
            $table->id();
            $table->string('nome_responsavel');
            $table->string('telefone');
            $table->string('instituicao');
            $table->date('data_cautela');
            $table->date('data_prevista_devolucao');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cautelas');
    }
};
