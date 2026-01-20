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
        Schema::create('itens_patrimoniais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fk_produto')->constrained('produtos');
            $table->string('patrimonio')->unique();
            $table->string('serie')->nullable();
            $table->foreignId('fk_secao')->constrained('secaos');
            $table->string('condicao')->default('novo');
            $table->dateTime('data_entrada')->nullable();
            $table->dateTime('data_saida')->nullable();
            $table->integer('quantidade_cautelada')->default(0);
            $table->text('observacao')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('itens_patrimoniais');
    }
};
