@extends('layouts.app')
@section('content')
<div class="container">
    <h2>Cadastrar Produto e Adicionar ao Estoque</h2>
    <form action="{{ route('produtos.estoque.store') }}" method="POST">
        @csrf
        <div class="row">
            <div class="col-md-6">
                <h4>Dados do Produto</h4>
                <div class="form-group">
                    <label for="nome">Nome do Produto</label>
                    <input type="text" name="nome" id="nome" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="categoria_id">Categoria</label>
                    <select name="categoria_id" id="categoria_id" class="form-control" required>
                        <option value="">Selecione</option>
                        @foreach($categorias as $categoria)
                            <option value="{{ $categoria->id }}">{{ $categoria->nome }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="lote">Lote</label>
                    <input type="text" name="lote" id="lote" class="form-control">
                </div>
                <div class="form-group">
                    <label for="descricao">Descrição</label>
                    <textarea name="descricao" id="descricao" class="form-control"></textarea>
                </div>
            </div>
            <div class="col-md-6">
                <h4>Adicionar ao Estoque</h4>
                <div class="form-group">
                    <label for="quantidade">Quantidade Inicial</label>
                    <input type="number" name="quantidade" id="quantidade" class="form-control" min="1" required>
                </div>
                <div class="form-group">
                    <label for="secao_id">Seção</label>
                    <select name="secao_id" id="secao_id" class="form-control">
                        <option value="">Selecione</option>
                        @foreach($secoes as $secao)
                            <option value="{{ $secao->id }}">{{ $secao->nome }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-success mt-3">Cadastrar e Adicionar ao Estoque</button>
    </form>
</div>
@endsection