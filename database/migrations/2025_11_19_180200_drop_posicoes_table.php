<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Remove table 'posicoes' - it's an orphaned table.
     * No models, controllers, or views reference this table.
     */
    public function up(): void
    {
        if (Schema::hasTable('posicoes')) {
            Schema::dropIfExists('posicoes');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('posicoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('escala_id')->constrained('escalas')->cascadeOnDelete();
            $table->string('funcao')->nullable();
            $table->foreignId('militar_id')->nullable()->constrained('militares')->nullOnDelete();
            $table->time('horario_inicio')->nullable();
            $table->time('horario_fim')->nullable();
            $table->text('observacao')->nullable();
            $table->timestamps();
        });
    }
};
