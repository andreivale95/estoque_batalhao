@extends('layout.app')
@section('content')
<div class="container" style="background: #fff; padding: 24px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
    <h1>Cadastrar Novo Tipo de Item</h1>
    <form action="{{ route('inventario.salvar') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="nome">Nome do Item</label>
            <input type="text" name="nome" id="nome" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="categoria">Categoria</label>
            <select name="categoria_id" id="categoria" class="form-control">
                @foreach($categorias as $categoria)
                    <option value="{{ $categoria->id }}">{{ $categoria->nome }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="unidade">Unidade</label>
            <select name="unidade_id" id="unidade" class="form-control">
                @foreach($unidades as $unidade)
                    <option value="{{ $unidade->id }}">{{ $unidade->nome }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="secao">Seção (opcional)</label>
            <select name="secao_id" id="secao" class="form-control">
                <option value="">-- Nenhuma --</option>
                @foreach($secoes as $secao)
                    <option value="{{ $secao->id }}">{{ $secao->nome }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="quantidade_inicial">Quantidade Inicial (opcional)</label>
            <input type="number" name="quantidade_inicial" id="quantidade_inicial" class="form-control" min="0" value="0">
        </div>
        <button type="submit" class="btn btn-primary">Salvar</button>
        <a href="{{ route('inventario.index') }}" class="btn btn-secondary">Voltar</a>
    </form>
</div>
@endsection