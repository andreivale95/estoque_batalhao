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
                <h4>Localização dos Itens</h4>
                <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
                    @php
                        $secoesByItem = [];
                        foreach($todosOsItens as $item) {
                            $secaoId = $item->fk_secao;
                            $secaoNome = $item->secao->nome ?? '-';
                            if (!isset($secoesByItem[$secaoId])) {
                                $secoesByItem[$secaoId] = [
                                    'nome' => $secaoNome,
                                    'itensRaiz' => [],
                                    'todosItens' => []
                                ];
                            }
                            $secoesByItem[$secaoId]['todosItens'][] = $item;
                            // Apenas itens raiz (sem container pai)
                            if(is_null($item->fk_item_pai)) {
                                $secoesByItem[$secaoId]['itensRaiz'][] = $item;
                            }
                        }
                    @endphp

                    @forelse($secoesByItem as $secaoId => $secao)
                        <div class="panel panel-default">
                            <div class="panel-heading" role="tab" id="heading-{{ $secaoId }}">
                                <h4 class="panel-title">
                                    <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse-{{ $secaoId }}" aria-expanded="false" aria-controls="collapse-{{ $secaoId }}">
                                        <i class="fa fa-folder"></i> Seção: {{ $secao['nome'] }}
                                    </a>
                                </h4>
                            </div>
                            <div id="collapse-{{ $secaoId }}" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading-{{ $secaoId }}">
                                <div class="panel-body">
                                    <div class="list-group">
                                        @forelse($secao['itensRaiz'] as $item)
                                            <div class="list-group-item" style="margin-bottom: 15px;">
                                                <h5 style="margin: 0 0 10px 0;">
                                                    @if($item->isContainer())
                                                        <i class="fa fa-briefcase text-primary"></i>
                                                    @else
                                                        <i class="fa fa-cube"></i>
                                                    @endif
                                                    {{ $item->produto->nome ?? 'Sem Nome' }}
                                                </h5>
                                                <p style="margin: 5px 0;">
                                                    <strong>Quantidade:</strong> {{ $item->quantidade }}
                                                </p>
                                                <p style="margin: 5px 0; color: #666;">
                                                    <strong>Localização:</strong> Seção: {{ $secao['nome'] }}
                                                </p>
                                                
                                                @if($item->isContainer())
                                                    <div style="margin-top: 10px; padding: 10px; background-color: #f0f8ff; border-left: 3px solid #0066cc; border-radius: 3px;">
                                                        <p style="margin: 0 0 10px 0; font-weight: bold; color: #0066cc;">
                                                            <i class="fa fa-sitemap"></i> Itens dentro deste container ({{ $item->itensFilhos->count() }}):
                                                        </p>
                                                        @if($item->itensFilhos->count() > 0)
                                                            @foreach($item->itensFilhos as $filho)
                                                                <div style="margin: 8px 0; padding: 8px; background-color: #ffffff; border-left: 2px solid #999; border-radius: 2px;">
                                                                    <p style="margin: 3px 0;">
                                                                        <i class="fa fa-arrow-right text-success"></i> 
                                                                        <strong>{{ $filho->produto->nome ?? 'Sem Nome' }}</strong>
                                                                    </p>
                                                                    <p style="margin: 3px 0; color: #666; font-size: 12px;">
                                                                        Quantidade: <span class="badge bg-green">{{ $filho->quantidade }}</span>
                                                                    </p>
                                                                    <p style="margin: 3px 0; color: #666; font-size: 12px;">
                                                                        <strong>Localização:</strong> {{ $item->produto->nome }} → {{ $filho->getCaminhoHierarquico() }}
                                                                    </p>
                                                                </div>
                                                            @endforeach
                                                        @else
                                                            <p style="color: #999; font-style: italic; margin: 5px 0;">Nenhum item dentro deste container</p>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        @empty
                                            <div class="alert alert-info">
                                                Nenhum item nesta seção
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="alert alert-info">
                            Nenhuma seção com itens deste produto.
                        </div>
                    @endforelse
                </div>

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
