<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Remove table 'ubms' - it's an orphaned table.
     * No models, controllers, or views reference this table.
     */
    public function up(): void
    {
        if (Schema::hasTable('ubms')) {
            Schema::dropIfExists('ubms');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('ubms', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('sigla')->nullable();
            $table->text('endereco')->nullable();
            $table->string('telefone')->nullable();
            $table->timestamps();
        });
    }
};
