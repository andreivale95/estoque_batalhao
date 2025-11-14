@extends('layout.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>Inserir Produto</h1>
    </section>

    <section class="content container-fluid">
        <div class="box box-primary">
            <div class="box-body">
                @if(session('warning'))
                    <div class="alert alert-warning">{{ session('warning') }}</div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('produtos.cadastrar') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label>Nome</label>
                        <input type="text" name="nome" class="form-control" value="{{ old('nome') }}" required>
                    </div>

                    <div class="form-group">
                        <label>Descrição</label>
                        <textarea name="descricao" class="form-control">{{ old('descricao') }}</textarea>
                    </div>

                    <div class="form-group">
                        <label>Marca</label>
                        <input type="text" name="marca" class="form-control" value="{{ old('marca') }}">
                    </div>

                    <div class="form-group">
                        <label>Tamanho</label>
                        <input type="text" name="tamanho" class="form-control" value="{{ old('tamanho') }}">
                    </div>

                    <div class="form-group">
                        <label>Unidade</label>
                        <select name="unidade" class="form-control">
                            <option value="">-- selecione --</option>
                            @foreach($unidades as $u)
                                <option value="{{ $u->id }}" {{ old('unidade') == $u->id ? 'selected' : '' }}>{{ $u->nome }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Valor (em centavos, ex: 150000 = R$ 1.500,00)</label>
                        <input type="text" name="valor" class="form-control" value="{{ old('valor') }}">
                    </div>

                    <div class="form-group">
                        <label>Categoria</label>
                        <select name="categoria" class="form-control">
                            <option value="">-- selecione --</option>
                            @foreach($categorias as $c)
                                <option value="{{ $c->id }}" {{ old('categoria') == $c->id ? 'selected' : '' }}>{{ $c->nome }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">Cadastrar</button>
                    <a href="{{ route('produtos.listar') }}" class="btn btn-default">Voltar</a>
                </form>
            </div>
        </div>
    </section>
</div>
@endsection
