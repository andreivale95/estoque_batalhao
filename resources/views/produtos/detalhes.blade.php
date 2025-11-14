@extends('layout.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>Detalhes do Produto</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{ route('estoque.listar') }}">Inventário</a></li>
            <li class="active">Detalhes</li>
        </ol>
    </section>

    <section class="content container-fluid">
        <div class="box box-primary">
            <div class="box-body">
                <h3>{{ $produto->nome }}</h3>
                <p><strong>Patrimônio:</strong> {{ $produto->patrimonio ?? '-' }}</p>
                <p><strong>Quantidade total:</strong> {{ $quantidadeTotal }}</p>

                <hr>
                <h4>Quantidade por Seção</h4>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Seção</th>
                            <th>Quantidade</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($detalhesSecao as $d)
                            <tr>
                                <td>{{ $d['secao_nome'] }}</td>
                                <td>{{ $d['quantidade'] }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-warning" data-toggle="modal"
                                        data-target="#modalTransferencia{{ $d['secao_id'] }}"
                                        title="Transferir para outra seção">
                                        <i class="fa fa-exchange-alt"></i>
                                    </button>
                                </td>
                            </tr>

                            <!-- Modal de Transferência entre Seções -->
                            <div class="modal fade" id="modalTransferencia{{ $d['secao_id'] }}" tabindex="-1"
                                role="dialog" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <form action="{{ route('estoque.transferir.secoes') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="fk_produto" value="{{ $produto->id }}">
                                        <input type="hidden" name="fk_secao_origem" value="{{ $d['secao_id'] }}">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Transferir para Outra Seção</h5>
                                                <button type="button" class="close" data-dismiss="modal">
                                                    <span>&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <p><strong>Produto:</strong> {{ $produto->nome }}</p>
                                                <p><strong>Seção origem:</strong> {{ $d['secao_nome'] }}</p>
                                                <p><strong>Disponível:</strong> {{ $d['quantidade'] }}</p>

                                                <div class="form-group">
                                                    <label for="fk_secao_destino">Seção Destino:</label>
                                                    <select class="form-control" name="fk_secao_destino" required>
                                                        <option value="">-- Selecione --</option>
                                                        @foreach($detalhesSecao as $s)
                                                            @if($s['secao_id'] !== $d['secao_id'])
                                                                <option value="{{ $s['secao_id'] }}">{{ $s['secao_nome'] }}</option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="form-group">
                                                    <label for="quantidade">Quantidade:</label>
                                                    <input type="number" name="quantidade" class="form-control"
                                                        min="1" max="{{ $d['quantidade'] }}" required>
                                                </div>

                                                <div class="form-group">
                                                    <label for="observacao">Observação:</label>
                                                    <textarea name="observacao" class="form-control" rows="2"></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                                    Cancelar
                                                </button>
                                                <button type="submit" class="btn btn-primary">
                                                    Transferir
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="3">Nenhuma seção vinculada a esse produto.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <a href="{{ route('estoque.listar') }}" class="btn btn-default">Voltar</a>
            </div>
        </div>
    </section>
</div>
@endsection
