<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        $tables = [
            'item_fotos',
            'historico_movimentacoes',
            'cautela_produto',
            'cautelas',
            'itens_patrimoniais',
            'containers',
            'itens_estoque',
            'produtos',
            'kit_produto',
            'kits',
            'tipoprodutos',
            'tamanhos',
            'condicoes',
            'fontes',
            'categorias',
            'secaos',
            'users',
            'perfil_permissao',
            'perfis',
            'permissoes',
            'modulos',
            'efetivo_militar_produto',
            'efetivo_militar',
            'config',
            'modelos',
            'tipos',
            'enderecos',
            'unidades',
            'personal_access_tokens',
        ];

        foreach ($tables as $table) {
            Schema::dropIfExists($table);
        }

        Schema::enableForeignKeyConstraints();

        Schema::create('unidades', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('sigla')->nullable();
            $table->timestamps();
        });

        Schema::create('modulos', function (Blueprint $table) {
            $table->unsignedBigInteger('id_modulo')->primary();
            $table->string('nome');
            $table->timestamps();
        });

        Schema::create('permissoes', function (Blueprint $table) {
            $table->unsignedBigInteger('id_permissao')->primary();
            $table->unsignedBigInteger('modulo');
            $table->string('nome');
            $table->timestamps();

            $table->foreign('modulo')->references('id_modulo')->on('modulos')->onDelete('cascade');
        });

        Schema::create('perfis', function (Blueprint $table) {
            $table->bigIncrements('id_perfil');
            $table->string('nome');
            $table->string('status', 1)->default('s');
            $table->timestamps();
        });

        Schema::create('perfil_permissao', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fk_perfil');
            $table->unsignedBigInteger('fk_permissao');
            $table->timestamps();

            $table->foreign('fk_perfil')->references('id_perfil')->on('perfis')->onDelete('cascade');
            $table->foreign('fk_permissao')->references('id_permissao')->on('permissoes')->onDelete('cascade');
        });

        Schema::create('users', function (Blueprint $table) {
            $table->string('cpf')->primary();
            $table->string('nome');
            $table->string('sobrenome')->nullable();
            $table->string('email')->unique();
            $table->string('telefone')->nullable();
            $table->string('status', 1)->default('s');
            $table->string('password');
            $table->unsignedBigInteger('fk_perfil')->nullable();
            $table->unsignedBigInteger('fk_unidade')->nullable();
            $table->string('image')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->foreign('fk_perfil')->references('id_perfil')->on('perfis')->nullOnDelete();
            $table->foreign('fk_unidade')->references('id')->on('unidades')->nullOnDelete();
        });

        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->timestamps();
        });

        Schema::create('tipoprodutos', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->unsignedBigInteger('fk_categoria')->nullable();
            $table->timestamps();

            $table->foreign('fk_categoria')->references('id')->on('categorias')->nullOnDelete();
        });

        Schema::create('tamanhos', function (Blueprint $table) {
            $table->id();
            $table->string('tamanho');
            $table->timestamps();
        });

        Schema::create('condicoes', function (Blueprint $table) {
            $table->id();
            $table->string('condicao');
            $table->timestamps();
        });

        Schema::create('fontes', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->timestamps();
        });

        Schema::create('tipos', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->timestamps();
        });

        Schema::create('modelos', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->timestamps();
        });

        Schema::create('config', function (Blueprint $table) {
            $table->id();
            $table->decimal('upf', 10, 2)->nullable();
            $table->decimal('acp', 10, 2)->nullable();
            $table->string('renovacao_legacy')->nullable();
            $table->timestamps();
        });

        Schema::create('enderecos', function (Blueprint $table) {
            $table->id();
            $table->string('cep')->nullable();
            $table->string('estado')->nullable();
            $table->string('cidade')->nullable();
            $table->string('bairro')->nullable();
            $table->string('endereco')->nullable();
            $table->string('numero')->nullable();
            $table->string('complemento')->nullable();
            $table->timestamps();
        });

        Schema::create('kits', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('descricao')->nullable();
            $table->unsignedBigInteger('fk_unidade')->nullable();
            $table->string('entregue', 1)->default('N');
            $table->string('disponivel', 1)->default('S');
            $table->timestamps();

            $table->foreign('fk_unidade')->references('id')->on('unidades')->nullOnDelete();
        });

        Schema::create('secaos', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->unsignedBigInteger('fk_unidade');
            $table->timestamps();

            $table->foreign('fk_unidade')->references('id')->on('unidades')->onDelete('cascade');
        });

        Schema::create('produtos', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->string('marca')->nullable();
            $table->unsignedBigInteger('fk_categoria')->nullable();
            $table->unsignedBigInteger('fk_kit')->nullable();
            $table->unsignedBigInteger('tamanho')->nullable();
            $table->unsignedBigInteger('unidade')->nullable();
            $table->unsignedBigInteger('fk_condicao')->nullable();
            $table->unsignedBigInteger('fk_secao')->nullable();
            $table->boolean('ativo')->default(true);
            $table->string('patrimonio')->nullable();
            $table->enum('tipo_controle', ['consumo', 'permanente'])->default('consumo');
            $table->timestamps();

            $table->foreign('fk_categoria')->references('id')->on('categorias')->nullOnDelete();
            $table->foreign('fk_kit')->references('id')->on('kits')->nullOnDelete();
            $table->foreign('tamanho')->references('id')->on('tamanhos')->nullOnDelete();
            $table->foreign('unidade')->references('id')->on('unidades')->nullOnDelete();
            $table->foreign('fk_condicao')->references('id')->on('condicoes')->nullOnDelete();
            $table->foreign('fk_secao')->references('id')->on('secaos')->nullOnDelete();
        });

        Schema::create('itens_estoque', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fk_produto')->nullable();
            $table->integer('quantidade')->default(0);
            $table->integer('quantidade_cautelada')->default(0);
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

            $table->foreign('fk_produto')->references('id')->on('produtos')->onDelete('cascade');
            $table->foreign('unidade')->references('id')->on('unidades')->nullOnDelete();
            $table->foreign('fk_secao')->references('id')->on('secaos')->nullOnDelete();
            $table->foreign('fk_item_pai')->references('id')->on('itens_estoque')->onDelete('cascade');
        });

        Schema::create('containers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fk_produto');
            $table->string('tipo')->nullable();
            $table->string('material')->nullable();
            $table->decimal('capacidade_maxima', 10, 2)->nullable();
            $table->string('unidade_capacidade')->default('kg');
            $table->integer('compartimentos')->nullable();
            $table->string('cor')->nullable();
            $table->string('numero_serie')->nullable();
            $table->text('descricao_adicional')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();

            $table->foreign('fk_produto')->references('id')->on('produtos')->onDelete('cascade');
        });

        Schema::create('itens_patrimoniais', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fk_produto');
            $table->string('patrimonio')->unique();
            $table->string('serie')->nullable();
            $table->unsignedBigInteger('fk_secao');
            $table->string('condicao')->default('novo');
            $table->dateTime('data_entrada')->nullable();
            $table->dateTime('data_saida')->nullable();
            $table->integer('quantidade_cautelada')->default(0);
            $table->text('observacao')->nullable();
            $table->timestamps();

            $table->foreign('fk_produto')->references('id')->on('produtos')->onDelete('cascade');
            $table->foreign('fk_secao')->references('id')->on('secaos')->onDelete('cascade');
        });

        Schema::create('cautelas', function (Blueprint $table) {
            $table->id();
            $table->string('nome_responsavel');
            $table->string('telefone');
            $table->string('instituicao');
            $table->string('responsavel_unidade')->nullable();
            $table->date('data_cautela');
            $table->date('data_prevista_devolucao');
            $table->timestamps();
        });

        Schema::create('cautela_produto', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cautela_id');
            $table->unsignedBigInteger('produto_id');
            $table->unsignedBigInteger('estoque_id')->nullable();
            $table->unsignedBigInteger('iten_patrimonial_id')->nullable();
            $table->integer('quantidade');
            $table->integer('quantidade_devolvida')->default(0);
            $table->date('data_devolucao')->nullable();
            $table->timestamps();

            $table->foreign('cautela_id')->references('id')->on('cautelas')->onDelete('cascade');
            $table->foreign('produto_id')->references('id')->on('produtos')->onDelete('cascade');
            $table->foreign('estoque_id')->references('id')->on('itens_estoque')->nullOnDelete();
            $table->foreign('iten_patrimonial_id')->references('id')->on('itens_patrimoniais')->nullOnDelete();
        });

        Schema::create('efetivo_militar', function (Blueprint $table) {
            $table->id();
            $table->string('posto_graduacao')->nullable();
            $table->string('nome');
            $table->string('matricula')->nullable();
            $table->unsignedBigInteger('fk_unidade')->nullable();
            $table->timestamps();

            $table->foreign('fk_unidade')->references('id')->on('unidades')->nullOnDelete();
        });

        Schema::create('historico_movimentacoes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fk_produto')->nullable();
            $table->string('tipo_movimentacao', 50);
            $table->integer('quantidade')->default(0);
            $table->string('responsavel')->nullable();
            $table->text('observacao')->nullable();
            $table->dateTime('data_movimentacao')->nullable();
            $table->unsignedBigInteger('fk_unidade')->nullable();
            $table->unsignedBigInteger('unidade_origem')->nullable();
            $table->unsignedBigInteger('unidade_destino')->nullable();
            $table->unsignedBigInteger('militar')->nullable();
            $table->string('sei')->nullable();
            $table->date('data_trp')->nullable();
            $table->string('fonte')->nullable();
            $table->string('fornecedor')->nullable();
            $table->string('setor')->nullable();
            $table->string('nota_fiscal')->nullable();
            $table->decimal('valor_total', 12, 2)->nullable();
            $table->decimal('valor_unitario', 12, 2)->nullable();
            $table->string('lote_saida')->nullable();
            $table->unsignedBigInteger('movimentacao_origem_id')->nullable();
            $table->timestamps();

            $table->foreign('fk_produto')->references('id')->on('produtos')->nullOnDelete();
            $table->foreign('fk_unidade')->references('id')->on('unidades')->nullOnDelete();
            $table->foreign('unidade_origem')->references('id')->on('unidades')->nullOnDelete();
            $table->foreign('unidade_destino')->references('id')->on('unidades')->nullOnDelete();
            $table->foreign('militar')->references('id')->on('efetivo_militar')->nullOnDelete();
            $table->foreign('movimentacao_origem_id')->references('id')->on('historico_movimentacoes')->nullOnDelete();
        });

        Schema::create('item_fotos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fk_itens_estoque')->nullable();
            $table->unsignedBigInteger('fk_iten_patrimonial')->nullable();
            $table->unsignedBigInteger('fk_produto')->nullable();
            $table->string('caminho_arquivo');
            $table->string('nome_original');
            $table->string('tipo_mime')->nullable();
            $table->unsignedBigInteger('tamanho')->nullable();
            $table->unsignedInteger('ordem')->default(0);
            $table->timestamps();

            $table->foreign('fk_itens_estoque')->references('id')->on('itens_estoque')->nullOnDelete();
            $table->foreign('fk_iten_patrimonial')->references('id')->on('itens_patrimoniais')->nullOnDelete();
            $table->foreign('fk_produto')->references('id')->on('produtos')->nullOnDelete();
        });

        Schema::create('efetivo_militar_produto', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->nullable();
            $table->unsignedBigInteger('fk_efetivo_militar');
            $table->unsignedBigInteger('fk_produto');
            $table->string('entregue', 1)->nullable();
            $table->timestamps();

            $table->foreign('fk_efetivo_militar')->references('id')->on('efetivo_militar')->onDelete('cascade');
            $table->foreign('fk_produto')->references('id')->on('produtos')->onDelete('cascade');
        });

        Schema::create('kit_produto', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fk_kit');
            $table->unsignedBigInteger('fk_produto');
            $table->integer('quantidade')->default(1);
            $table->timestamps();

            $table->foreign('fk_kit')->references('id')->on('kits')->onDelete('cascade');
            $table->foreign('fk_produto')->references('id')->on('produtos')->onDelete('cascade');
        });

        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        $tables = [
            'personal_access_tokens',
            'kit_produto',
            'efetivo_militar_produto',
            'efetivo_militar',
            'item_fotos',
            'historico_movimentacoes',
            'cautela_produto',
            'cautelas',
            'itens_patrimoniais',
            'containers',
            'itens_estoque',
            'produtos',
            'secaos',
            'kits',
            'enderecos',
            'config',
            'modelos',
            'tipos',
            'fontes',
            'condicoes',
            'tamanhos',
            'tipoprodutos',
            'categorias',
            'users',
            'perfil_permissao',
            'perfis',
            'permissoes',
            'modulos',
            'unidades',
        ];

        foreach ($tables as $table) {
            Schema::dropIfExists($table);
        }

        Schema::enableForeignKeyConstraints();
    }
};
