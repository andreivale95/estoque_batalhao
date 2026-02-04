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
        Schema::create('item_fotos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fk_itens_estoque')->nullable();
            $table->unsignedBigInteger('fk_iten_patrimonial')->nullable();
            $table->unsignedBigInteger('fk_produto')->nullable();
            $table->string('caminho_arquivo');
            $table->string('nome_original')->nullable();
            $table->string('tipo_mime')->nullable();
            $table->bigInteger('tamanho')->nullable();
            $table->integer('ordem')->default(1);
            $table->timestamps();

            // Chaves estrangeiras
            $table->foreign('fk_itens_estoque')
                ->references('id')
                ->on('itens_estoque')
                ->onDelete('cascade');

            $table->foreign('fk_iten_patrimonial')
                ->references('id')
                ->on('itens_patrimoniais')
                ->onDelete('cascade');

            $table->foreign('fk_produto')
                ->references('id')
                ->on('produtos')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_fotos');
    }
};
