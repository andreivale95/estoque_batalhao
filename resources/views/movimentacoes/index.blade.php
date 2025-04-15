@extends('layout.app')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>Histórico de Movimentações</h1>
        </section>

        <section class="content container-fluid">
            <form method="GET" action="{{ route('movimentacoes.index') }}" class="mb-4">
                <div class="row">
                    <div class="col-md-3">
                        <label>Produto</label>
                        <select name="produto" class="form-control select2">
                            <option value="">Todos</option>
                            @foreach ($produtos as $produto)
                                <option value="{{ $produto->id }}"
                                    {{ request('produto') == $produto->id ? 'selected' : '' }}>
                                    {{ $produto->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label>Tipo</label>
                        <select name="tipo" class="form-control">
                            <option value="">Todos</option>
                            <option value="entrada" {{ request('tipo') == 'entrada' ? 'selected' : '' }}>Entrada</option>
                            <option value="saida" {{ request('tipo') == 'saida' ? 'selected' : '' }}>Saída</option>
                            <option value="transferencia" {{ request('tipo') == 'transferencia' ? 'selected' : '' }}>
                                Transferência</option>
                            <option value="saida_kit" {{ request('tipo') == 'saida_kit' ? 'selected' : '' }}>Saída Kit
                            </option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label>Data Inicial</label>
                        <input type="date" name="data_inicio" class="form-control" value="{{ request('data_inicio') }}">
                    </div>

                    <div class="col-md-2">
                        <label>Data Final</label>
                        <input type="date" name="data_fim" class="form-control" value="{{ request('data_fim') }}">
                    </div>

                    <div class="col-md-2">
                        <label>Responsável</label>
                        <input type="text" name="responsavel" class="form-control" value="{{ request('responsavel') }}">
                    </div>

                    <div class="col-md-1">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary btn-block">Filtrar</button>
                    </div>
                </div>
            </form><br>

            <table class="table table-bordered table-hover">
                <thead class="bg-primary" style="color:white;">
                    <tr>
                        <th>Data</th>
                        <th>Produto</th>
                        <th>Tipo</th>
                        <th>Quantidade</th>
                        <th>Responsável</th>
                        <th>Origem</th>
                        <th>Destino</th>
                        <th>Observação</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movimentacoes as $m)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($m->data_movimentacao)->format('d/m/Y H:i') }}</td>
                            <td>{{ $m->produto->nome ?? '—' }}</td>
                            <td>{{ ucfirst($m->tipo_movimentacao) }}</td>
                            <td>{{ $m->quantidade }}</td>
                            <td>{{ $m->responsavel }}</td>
                            <td>{{ $m->origem->nome ?? '-' }}</td>
                            <td>{{ $m->destino->nome ?? '-' }}</td>
                            <td>{{ $m->observacao }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">Nenhuma movimentação encontrada.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{ $movimentacoes->links() }}
        </section>
    </div>

    <script>
        $(document).ready(function() {
            $('.select2').select2({
                placeholder: "Selecione um Produto",
                allowClear: true,
                width: '100%'
            });
        });
    </script>
@endsection
