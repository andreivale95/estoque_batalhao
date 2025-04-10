@extends('layout/app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>Selecionar Militar</h1>
    </section>

    <section class="content container-fluid">
        <form method="GET" action="{{ route('efetivo_produtos.listar') }}" class="form-inline mb-3">
            <input type="text" name="nome" class="form-control mr-2" placeholder="Nome" value="{{ request('nome') }}">
            <select name="fk_unidade" class="form-control mr-2">
                <option value="">Todas Unidades</option>
                @foreach ($unidades as $unidade)
                    <option value="{{ $unidade->id }}" {{ request('fk_unidade') == $unidade->id ? 'selected' : '' }}>
                        {{ $unidade->nome }}
                    </option>
                @endforeach
            </select>
            <select name="posto_graduacao" class="form-control mr-2">
                <option value="">Todos os Postos</option>
                @foreach ($postos as $posto)
                    <option value="{{ $posto }}" {{ request('posto_graduacao') == $posto ? 'selected' : '' }}>
                        {{ $posto }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary">Filtrar</button>
        </form>

        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Matrícula</th>
                    <th>Unidade</th>
                    <th>Posto/Graduação</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($militares as $militar)
                    <tr>
                        <td>{{ $militar->nome }}</td>
                        <td>{{ $militar->matricula }}</td>
                        <td>{{ $militar->unidade->nome ?? '-' }}</td>
                        <td>{{ $militar->posto_graduacao }}</td>
                        <td>
                            <a href="{{ route('efetivo_produtos.visualizar', $militar->id) }}" class="btn btn-success btn-sm">
                                Visualizar
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="text-center">
            {{ $militares->appends(request()->query())->links() }}
        </div>

    </section>
</div>
@endsection
