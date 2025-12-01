<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Remove table 'permutas' - it's an orphaned table.
     * No models, controllers, or views reference this table.
     */
    public function up(): void
    {
        if (Schema::hasTable('permutas')) {
            Schema::dropIfExists('permutas');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('permutas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('militar_origem')->constrained('militares')->cascadeOnDelete();
            $table->foreignId('militar_substituto')->nullable()->constrained('militares')->nullOnDelete();
            $table->date('data');
            $table->decimal('horas', 5, 2)->nullable();
            $table->string('processo_sei')->nullable();
            $table->foreignId('aprovado_por')->nullable()->constrained('users', 'cpf')->nullOnDelete();
            $table->string('status');
            $table->timestamps();
        });
    }
};
