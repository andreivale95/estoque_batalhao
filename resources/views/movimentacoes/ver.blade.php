@extends('layout.app')
@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>Detalhes da Movimentação
            @if($movimentacao->tipo_movimentacao === 'entrada' && $movimentacoesRelacionadas->count() > 1)
                <span class="badge badge-info">{{ $movimentacoesRelacionadas->count() }} itens</span>
            @endif
        </h1>
    </section>
    <section class="content container-fluid">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Informações da Movimentação</h3>
            </div>
            <div class="box-body">
                <table class="table table-bordered table-striped">
                    <tr><th width="25%">Data</th><td>{{ \Carbon\Carbon::parse($movimentacao->data_movimentacao)->format('d/m/Y H:i:s') }}</td></tr>
                    <tr><th>Tipo</th><td><span class="badge badge-{{ $movimentacao->tipo_movimentacao === 'entrada' ? 'success' : 'warning' }}">{{ strtoupper($movimentacao->tipo_movimentacao) }}</span></td></tr>
                    <tr><th>Fornecedor</th><td>{{ $movimentacao->fornecedor ?? '-' }}</td></tr>
                    <tr><th>Nota Fiscal</th><td>{{ $movimentacao->nota_fiscal ?? '-' }}</td></tr>
                    <tr><th>Unidade</th><td>{{ $movimentacao->unidade->nome ?? '-' }}</td></tr>
                    <tr><th>Responsável</th><td>{{ $movimentacao->responsavel }}</td></tr>
                    <tr><th>Proc. SEI</th><td>{{ $movimentacao->sei ?? '-' }}</td></tr>
                    <tr><th>Data TRP</th><td>{{ $movimentacao->data_trp ? \Carbon\Carbon::parse($movimentacao->data_trp)->format('d/m/Y') : '-' }}</td></tr>
                    <tr><th>Fonte</th><td>{{ $movimentacao->fonte ?? '-' }}</td></tr>
                    <tr><th>Observação</th><td>{{ $movimentacao->observacao ?? '-' }}</td></tr>
                </table>
            </div>
        </div>

        @if($movimentacao->tipo_movimentacao === 'entrada' && $movimentacoesRelacionadas->count() > 1)
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-cubes"></i> Itens da Entrada ({{ $movimentacoesRelacionadas->count() }})</h3>
                </div>
                <div class="box-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead style="background-color: #0c5460; color: white;">
                                <tr>
                                    <th width="35%">Produto</th>
                                    <th width="12%">Quantidade</th>
                                    <th width="15%">Valor Unitário</th>
                                    <th width="15%">Valor Total</th>
                                    <th width="15%">Seção</th>
                                    <th width="8%">Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $valorTotalGeral = 0; @endphp
                                @foreach($movimentacoesRelacionadas as $item)
                                    <tr>
                                        <td>
                                            <strong>{{ $item->produto->nome ?? 'Produto desconhecido' }}</strong>
                                            @if($item->produto && $item->produto->tamanho)
                                                <br><small class="text-muted">{{ $item->produto->tamanho->tamanho }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $item->quantidade }}</td>
                                        <td>R$ {{ number_format($item->valor_unitario, 2, ',', '.') }}</td>
                                        <td>R$ {{ number_format($item->valor_total, 2, ',', '.') }}</td>
                                        <td>
                                            @php
                                                // Tentar encontrar a seção do item no estoque
                                                $itemEstoque = \App\Models\Itens_estoque::where('fk_produto', $item->fk_produto)
                                                    ->where('fk_unidade', $item->fk_unidade)
                                                    ->first();
                                            @endphp
                                            {{ $itemEstoque && $itemEstoque->secao ? $itemEstoque->secao->nome : '-' }}
                                        </td>
                                        <td>
                                            <a href="{{ route('movimentacao.ver', $item->id) }}" class="btn btn-sm btn-info" title="Ver detalhes">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @php $valorTotalGeral += $item->valor_total; @endphp
                                @endforeach
                            </tbody>
                            <tfoot style="background-color: #f4f4f4; font-weight: bold;">
                                <tr>
                                    <td colspan="3" style="text-align: right;">TOTAL:</td>
                                    <td colspan="3">R$ {{ number_format($valorTotalGeral, 2, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        @else
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title">Produto</h3>
                </div>
                <div class="box-body">
                    <table class="table table-bordered">
                        <tr><th width="25%">Produto</th><td>{{ $movimentacao->produto->nome ?? '-' }}</td></tr>
                        <tr><th>Quantidade</th><td>{{ $movimentacao->quantidade }}</td></tr>
                        <tr><th>Valor Unitário</th><td>R$ {{ number_format($movimentacao->valor_unitario, 2, ',', '.') }}</td></tr>
                        <tr><th>Valor Total</th><td>R$ {{ number_format($movimentacao->valor_total, 2, ',', '.') }}</td></tr>
                        <tr><th>Origem</th><td>{{ $movimentacao->origem->nome ?? '-' }}</td></tr>
                        <tr><th>Destino</th><td>{{ $movimentacao->destino->nome ?? '-' }}</td></tr>
                        <tr><th>Militar</th><td>{{ $movimentacao->militar ?? '-' }}</td></tr>
                        <tr><th>Setor</th><td>{{ $movimentacao->setor ?? '-' }}</td></tr>
                    </table>
                </div>
            </div>
        @endif

        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">Imagens do Item</h3>
            </div>
            <div class="box-body">
                @php
                    $fotosProduto = $movimentacao->produto ? $movimentacao->produto->fotos->sortBy('ordem') : collect();
                @endphp
                @if($fotosProduto->count() > 0)
                    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                        @foreach($fotosProduto as $foto)
                            <a href="{{ $foto->url }}" target="_blank" title="Abrir imagem">
                                <img src="{{ $foto->url }}" alt="Foto do item" style="width: 120px; height: 120px; object-fit: cover; border: 1px solid #ddd; border-radius: 4px;">
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted">Nenhuma imagem cadastrada para este item.</p>
                @endif
            </div>
        </div>

        <a href="{{ route('movimentacoes.index') }}" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Voltar</a>
    </section>
</div>
@endsection
