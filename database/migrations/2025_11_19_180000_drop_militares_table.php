<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Remove table 'militares' - it's an orphaned table, replaced by 'efetivo_militar'.
     * No models, controllers, or views reference this table.
     */
    public function up(): void
    {
        if (Schema::hasTable('militares')) {
            Schema::dropIfExists('militares');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('militares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nome_guerra');
            $table->string('posto_grad')->nullable();
            $table->string('nome_completo')->nullable();
            $table->string('matricula')->nullable();
            $table->string('ct')->nullable();
            $table->string('rg')->nullable();
            $table->string('cpf')->nullable();
            $table->date('data_nasc')->nullable();
            $table->foreignId('ubm_id')->nullable()->constrained('ubms')->nullOnDelete();
            $table->string('contato')->nullable();
            $table->text('qualificacoes')->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
    }
};
