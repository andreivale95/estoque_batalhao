<?php


use App\Http\Controllers\EstoqueController;

use App\Models\Categoria;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\KitController;
use App\Http\Controllers\EfetivoMilitarProdutoController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\SaidaEstoqueController;
use App\Http\Controllers\ProdutoController;
use App\Http\Controllers\UnidadeController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\MovimentacaoController;
use App\Http\Controllers\SecaoController;

Route::get('/movimentacoes', [MovimentacaoController::class, 'index'])->name('movimentacoes.index');
Route::put('movimentacoes/desfazer/{id}', [MovimentacaoController::class, 'desfazer'])->name('movimentacao.desfazer');
Route::get('movimentacoes/ver/{id}', [MovimentacaoController::class, 'ver'])->name('movimentacao.ver');




Route::middleware(['auth', 'verified'])->controller(PerfilController::class)->group(function () {
    Route::get('perfis', 'listarPerfis')->name('pf.listar');
    Route::post('perfis/criar', 'criarPerfil')->name('pf.criar');
    Route::post('perfis/atualizar/{id}', 'atualizarPerfil')->name('pf.atualizar');
    Route::get('perfis/form', 'formPerfil')->name('pf.form');
    Route::get('perfis/perfil/{id}', 'verPerfil')->name('pf.ver');
    Route::get('perfis/editar/{id}', 'editarPerfilForm')->name('pf.editarForm');
    Route::post('perfis/editar/{id}', 'editarPerfil')->name('pf.editar');
    Route::delete('perfis/excluir/{id}', 'excluirPerfil')->name('pf.excluir');
});

Route::middleware(['auth', 'verified'])->controller(EstoqueController::class)->group(function () {
    Route::get('registros/estoque/listar', 'listarEstoque')->name('estoque.listar');
    Route::post('registros/estoque/entrada/', 'entradaEstoque')->name('estoque.entrada');
    Route::post('registros/estoque/entradanovoproduto/', 'entradaProdutoEstoque')->name('estoque.entrada_novoproduto');
    Route::post('registros/estoque/saida/', 'saidaEstoque')->name('estoque.saida');
    Route::get('registros/estoque/form_entrada/{id}', 'formEntrada')->name('entrada.form');
    Route::get('registros/estoque/form_saida/{id}', 'formSaida')->name('saida.form');
    Route::post('/estoque/transferencia', [EstoqueController::class, 'transferir'])->name('estoque.transferir');
    Route::post('/estoque/saida-multiplos', [EstoqueController::class, 'saidaMultiplos'])->name('estoque.saidaMultiplos');
    Route::get('/estoque/recibo/{saida}',  [EstoqueController::class, 'recibo'])->name('estoque.recibo');
    Route::get('registros/estoque/saida-multipla', 'saidaMultiplosForm')->name('saida_estoque.saida_multipla');

});


Route::middleware(['auth', 'verified'])->controller(KitController::class)->group(function () {
    Route::get('registros/kits/listar', 'listarKits')->name('kits.listar'); // Listar kits
    Route::get('registros/kits/form', 'formKit')->name('kits.criar'); // Formulário de criação
    Route::post('registros/kits/salvar', 'criarKit')->name('kits.salvar'); // Criar kit
    Route::get('registros/kits/{kit}/editar', 'editarKit')->name('kit.editar'); // Formulário de edição
    Route::post('registros/kits/{kit}/atualizar', 'atualizarKit')->name('kit.atualizar'); // Atualizar kit
    Route::delete('registros/kits/{kit}/deletar', 'deletarKit')->name('kit.deletar'); // Remover kit
    Route::patch('/kits/{id}/toggle-disponibilidade', 'toggleDisponibilidade')->name('kits.toggleDisponibilidade');

});


Route::middleware(['auth', 'verified'])->controller(SaidaEstoqueController::class)->group(function () {
    Route::get('registros/saida-estoque/form', 'index')->name('saida_estoque.index'); // Formulário de seleção de militar e kit
    Route::get('registros/saida-estoque/confirmar', 'selecionarKit')->name('saida_estoque.selecionar_kit'); // View de confirmação dos produtos
    Route::post('registros/saida-estoque/confirmar-saida', 'confirmarSaida')->name('saida_estoque.confirmar_saida'); // Processar saída do kit no estoque
});


Route::middleware(['auth', 'verified'])->controller(ProdutoController::class)->group(function () {
    Route::post('registros/produto/ativar/{id}', 'ativarProduto')->name('produto.ativar');
    Route::delete('registros/produto/excluir/{id}', 'excluirProduto')->name('produto.excluir');
    Route::get('registros/produto/listar', 'listarProdutos')->name('produtos.listar');
    Route::get('registros/produto/ver/{id}', 'verProduto')->name('produto.ver');
    Route::get('registros/produto/form', 'formProduto')->name('produto.form');
    Route::get('registros/produto/editar', 'editarProduto')->name('produto.editar');
    Route::post('registros/produto/criar', 'cadastrarProduto')->name('produto.cadastrar');
    Route::get('registros/produto/forminserir', 'inserirProdutoForm')->name('produtoinserir.form');
    Route::get('registros/produto/entrada', 'inserirProdutoForm')->name('produtos.entrada');
    Route::get('registros/produto/editar/{id}', 'editarProduto')->name('produto.editar');
    Route::post('registros/produto/atualizar/{id}', 'atualizarProduto')->name('produto.atualizar');
    Route::get('/api/produtos/unidade/{unidade}', [ProdutoController::class, 'getProdutosPorUnidade']);

}); //testehhhhhhhh


Route::middleware(['auth', 'verified'])->controller(UnidadeController::class)->group(function () {

    Route::get('unidades/listar', 'listarUnidade')->name('unidades.listar');
    Route::get('unidades/form', 'unidadeForm')->name('unidade.form');
    Route::post('unidades/criar', 'cadastrarUnidade')->name('unidade.cadastrar');
    Route::get('unidades/editar/{id}', 'editarUnidade')->name('unidade.editar');
    Route::get('unidades/ver/{id}', 'verUnidade')->name('unidade.ver');
    Route::post('unidades/atualizar/{id}', 'atualizarUnidade')->name('unidade.atualizar');
});


Route::middleware(['auth', 'verified'])->controller(CategoriaController::class)->group(function () {

    Route::get('categorias/listar', 'listarCategoria')->name('categorias.listar');
    Route::get('categorias/form', 'categoriaForm')->name('categoria.form');
    Route::post('categorias/criar', 'cadastrarCategoria')->name('categoria.cadastrar');
    Route::get('categorias/editar/{id}', 'editarCategoria')->name('categoria.editar');
    Route::get('categorias/ver/{id}', 'verCategoria')->name('categoria.ver');
    Route::post('categorias/atualizar/{id}', 'atualizarCategoria')->name('categoria.atualizar');
    Route::post('categorias/excluir/{id}', 'excluirCategoria')->name('categoria.excluir');
});

Route::middleware(['auth', 'verified'])->controller(UserController::class)->group(function () {
    Route::get('user/interno/listar', 'listarUsersInternos')->name('usi.listar');
    Route::delete('user/interno/excluir/{cpf}', 'excluirUserInterno')->name('usi.excluir');
    Route::post('user/interno/criar', 'criarUserInterno')->name('usi.criar');
    Route::get('user/interno/form', 'formUserInterno')->name('usi.form');
    Route::get('user/interno/ver/{id}', 'verUserInterno')->name('usi.ver');
    Route::get('user/interno/editar/{id}', 'editarUserInterno')->name('usi.editar');
    Route::post('user/interno/atualizar/{id}', 'atualizarUserInterno')->name('usi.atualizar'); //tsihgjhgjhvguvi

});//teste upload deploy auto


Route::middleware(['auth', 'verified'])->controller(EfetivoMilitarProdutoController::class)->group(function () {
    Route::get('registros/efetivo-produtos/form', 'Listar')->name('efetivo_produtos.listar'); // Listar
    Route::post('registros/efetivo-produtos/salvar', 'store')->name('efetivo_produtos.salvar'); // Salvar
    Route::get('registros/efetivo-produtos/{militar}/editar', 'edit')->name('efetivo_produtos.editar'); // Editar produtos do militar
    Route::put('registros/efetivo-produtos/{militar}/atualizar', 'update')->name('efetivo_produtos.atualizar'); // Atualizar
    Route::get('/efetivo-produtos/atribuir/{militar}', 'atribuirProdutos')->name('efetivo_produtos.atribuir');
    Route::get('/efetivo-produtos/visualizar/{id}', 'visualizar')->name('efetivo_produtos.visualizar');

}); //teste novo


Route::middleware(['auth', 'verified'])->controller(ProfileController::class)->group(function () {
    Route::get('profile/{id}', 'verProfile')->name('profile.ver');
    Route::post('profile/{id}', 'update')->name('profile.update');
    Route::get('perfis/alterar_foto_perfil/', 'fotoPerfil')->name('foto.perfil');
    Route::post('profile/uplodad_foto/{id}', 'upFoto')->name('foto.update');
});

Route::middleware(['auth', 'verified'])->controller(SiteController::class)->group(function () {
    Route::get('/dashboard', 'dashboard')->name('dashboard');
});
Route::post('/upload-image{id}', [ImageController::class, 'upload'])->name('upload.image');

Route::get('/', [SiteController::class, 'Site'])->name('site');


require __DIR__ . '/auth.php';

Route::prefix('unidades/{unidade}/secoes')->group(function () {
    Route::get('/', [SecaoController::class, 'index'])->name('secoes.index');
    Route::get('/criar', [SecaoController::class, 'create'])->name('secoes.create');
    Route::post('/criar', [SecaoController::class, 'store'])->name('secoes.store');
    Route::get('/{id}/editar', [SecaoController::class, 'edit'])->name('secoes.edit');
    Route::put('/{id}/editar', [SecaoController::class, 'update'])->name('secoes.update');
    Route::delete('/{id}', [SecaoController::class, 'destroy'])->name('secoes.destroy');
});

// Rotas independentes para vincular itens a seções
Route::get('unidades/{unidade}/secoes/{secao}/vincular-itens', [SecaoController::class, 'vincularItensForm'])->name('secoes.vincular_itens_form');
Route::post('unidades/{unidade}/secoes/{secao}/vincular-itens', [SecaoController::class, 'vincularItens'])->name('secoes.vincular_itens');
Route::get('unidades/{unidade}/secoes/{secao}/ver', [SecaoController::class, 'ver'])->name('secoes.ver');
Route::post('unidades/{unidade}/secoes/{secao}/transferir', [SecaoController::class, 'transferirItens'])->name('secoes.transferir');
Route::get('unidades/{unidade}/secoes/{secao}/transferir-lote', [SecaoController::class, 'transferirLoteForm'])->name('secoes.transferir_lote_form');
Route::post('unidades/{unidade}/secoes/{secao}/transferir-lote', [SecaoController::class, 'transferirLote'])->name('secoes.transferir_lote');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('inventario', [App\Http\Controllers\InventarioController::class, 'index'])->name('inventario.index');
    Route::get('inventario/cadastrar', [App\Http\Controllers\InventarioController::class, 'form'])->name('inventario.cadastrar');
    Route::post('inventario/salvar', [App\Http\Controllers\InventarioController::class, 'salvar'])->name('inventario.salvar');
});
