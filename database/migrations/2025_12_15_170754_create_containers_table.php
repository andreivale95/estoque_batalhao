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
        Schema::create('containers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fk_produto');
            $table->string('tipo')->comment('Bolsa, Prateleira, Caixa, Armário, etc.')->nullable();
            $table->string('material')->comment('Plástico, Metal, Madeira, Tecido, etc.')->nullable();
            $table->decimal('capacidade_maxima', 10, 2)->nullable()->comment('Peso máximo ou unidades');
            $table->string('unidade_capacidade')->default('kg')->comment('kg, unidades, litros, etc.');
            $table->integer('compartimentos')->default(0)->comment('Número de compartimentos/seções');
            $table->string('cor')->nullable();
            $table->string('numero_serie')->nullable()->unique();
            $table->text('descricao_adicional')->nullable();
            $table->enum('status', ['ativo', 'danificado', 'em_reparo', 'inativo'])->default('ativo');
            $table->timestamps();

            // Foreign key
            $table->foreign('fk_produto')->references('id')->on('produtos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('containers');
    }
};
