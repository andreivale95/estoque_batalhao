@extends('layout/app')
@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Editar Kit
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{ route('estoque.listar') }}">Estoque</a></li>
            <li>Editar Kit</li>
        </ol>
    </section>

    <section class="content container-fluid">
        <div class="panel" style="background-color: #3c8dbc;">
            <div class="panel-body" style="background-color: white;">
                <form action="{{ route('kit.atualizar', $kit->id) }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label for="unidade">Unidade de Origem:</label>
                        <select name="fk_unidade" id="unidade" class="form-control" required>
                            <option value="">Selecione a unidade</option>
                            @foreach ($unidades as $unidade)
                                <option value="{{ $unidade->id }}" {{ $kit->fk_unidade == $unidade->id ? 'selected' : '' }}>
                                    {{ $unidade->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="nome">Nome do Kit:</label>
                        <input type="text" name="nome" class="form-control" value="{{ $kit->nome }}" required>
                    </div>

                    <div class="form-group">
                        <label for="descricao">Descrição:</label>
                        <input type="text" name="descricao" class="form-control" value="{{ $kit->descricao }}">
                    </div>

                    <div class="form-group">
                        <label for="disponivel">Disponível:</label>
                        <select name="disponivel" class="form-control">
                            <option value="S" {{ $kit->disponivel == 'S' ? 'selected' : '' }}>Sim</option>
                            <option value="N" {{ $kit->disponivel == 'N' ? 'selected' : '' }}>Não</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                </form>
            </div>
        </div>
    </section>
</div>
@endsection
