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
        // Disable foreign key constraints
        Schema::disableForeignKeyConstraints();
        
        // Drop existing table
        Schema::dropIfExists('itens_estoque');
        
        // Recreate the table with all fields
        Schema::create('itens_estoque', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fk_produto')->nullable();
            $table->integer('quantidade')->default(0);
            $table->decimal('preco_unitario', 12, 2)->nullable();
            $table->unsignedBigInteger('unidade')->nullable();
            $table->unsignedBigInteger('fk_secao')->nullable();
            $table->unsignedBigInteger('fk_item_pai')->nullable();
            $table->dateTime('data_entrada')->nullable();
            $table->dateTime('data_saida')->nullable();
            $table->string('lote')->nullable();
            $table->string('fornecedor')->nullable();
            $table->string('nota_fiscal')->nullable();
            $table->text('observacao')->nullable();
            $table->string('sei')->nullable();
            $table->date('data_trp')->nullable();
            $table->string('fonte')->nullable();
            $table->decimal('valor_total', 12, 2)->nullable();
            $table->decimal('valor_unitario', 12, 2)->nullable();
            $table->integer('quantidade_inicial')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('fk_produto')->references('id')->on('produtos')->onDelete('cascade');
            $table->foreign('fk_secao')->references('id')->on('secaos')->onDelete('set null');
            $table->foreign('fk_item_pai')->references('id')->on('itens_estoque')->onDelete('cascade');
        });
        
        // Re-enable foreign key constraints
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('itens_estoque');
        Schema::enableForeignKeyConstraints();
    }
};
