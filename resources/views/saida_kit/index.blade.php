@extends('layout/app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>Saída de Produtos por Kit</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">Saída de Produtos</li>
        </ol>
    </section>

    <section class="content container-fluid">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Buscar Militar</h3>
            </div>

            <form method="GET" action="{{ route('saida_estoque.index') }}">
                <div class="box-body">
                    <div class="row">
                        <div class="form-group col-md-4">
                            <label>Nome</label>
                            <input type="text" name="nome" value="{{ request('nome') }}" class="form-control" placeholder="Buscar por nome...">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Unidade</label>
                            <select name="unidade_id" class="form-control">
                                <option value="">-- Todas --</option>
                                @foreach ($unidades as $unidade)
                                    <option value="{{ $unidade->id }}" {{ request('unidade_id') == $unidade->id ? 'selected' : '' }}>
                                        {{ $unidade->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Posto/Graduação</label>
                            <select name="posto_graduacao" class="form-control">
                                <option value="">-- Todos --</option>
                                @foreach ($postos as $posto)
                                    <option value="{{ $posto }}" {{ request('posto_graduacao') == $posto ? 'selected' : '' }}>
                                        {{ $posto }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="text-right">
                        <button type="submit" class="btn btn-default">
                            <i class="fa fa-search"></i> Filtrar
                        </button>
                    </div>
                </div>
            </form>

            @if($militares->count())
            <div class="box-body">
                <form method="GET" action="{{ route('saida_estoque.selecionar_kit') }}">
                    <input type="hidden" name="militar_id" id="militar_id" value="{{ request('militar_id') }}">

                    <div class="form-group">
                        <label>Militar Selecionado</label>
                        <input type="text" class="form-control" id="militar_nome" readonly placeholder="Selecione um militar abaixo">
                    </div>

                    <div class="form-group">
                        <label>Kit</label>
                        <select name="kit_id" class="form-control" required>
                            <option value="">-- Selecione o Kit --</option>
                            @foreach ($kits as $kit)
                                <option value="{{ $kit->id }}">{{ $kit->nome }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="box-footer text-right">
                        <a href="{{ route('dashboard') }}" class="btn btn-default">
                            <i class="fa fa-arrow-left"></i> Voltar
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="fa fa-arrow-down"></i> Dar Saída
                        </button>
                    </div>
                </form>

                <hr>

                <h4>Resultados:</h4>
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Matrícula</th>
                            <th>Unidade</th>
                            <th>Posto/Graduação</th>
                            <th>Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($militares as $militar)
                        <tr>
                            <td>{{ $militar->nome }}</td>
                            <td>{{ $militar->matricula }}</td>
                            <td>{{ $militar->unidade->nome ?? '-' }}</td>
                            <td>{{ $militar->posto_graduacao }}</td>
                            <td>
                                <button type="button" class="btn btn-primary btn-xs selecionar-militar"
                                    data-id="{{ $militar->id }}"
                                    data-nome="{{ $militar->nome }} ({{ $militar->matricula }})">
                                    Selecionar
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
                <div class="box-body">
                    <p class="text-center">Nenhum militar encontrado com os filtros selecionados.</p>
                </div>
            @endif
        </div>
    </section>
</div>

<script>
    document.querySelectorAll('.selecionar-militar').forEach(botao => {
        botao.addEventListener('click', () => {
            document.getElementById('militar_id').value = botao.dataset.id;
            document.getElementById('militar_nome').value = botao.dataset.nome;
        });
    });
</script>
@endsection
