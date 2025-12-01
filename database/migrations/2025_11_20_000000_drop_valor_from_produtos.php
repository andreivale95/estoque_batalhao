<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropValorFromProdutos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('produtos') && Schema::hasColumn('produtos', 'valor')) {
            Schema::table('produtos', function (Blueprint $table) {
                $table->dropColumn('valor');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('produtos') && !Schema::hasColumn('produtos', 'valor')) {
            Schema::table('produtos', function (Blueprint $table) {
                $table->decimal('valor', 10, 2)->default(0)->nullable();
            });
        }
    }
}
