@extends('layout.app')
@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-folder"></i> Itens da Seção: {{ $secao->nome }}
            <small>{{ $itens->count() }} {{ $itens->count() == 1 ? 'item' : 'itens' }}</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{ route('secoes.index', $secao->fk_unidade) }}">Seções</a></li>
            <li class="active">{{ $secao->nome }}</li>
        </ol>
    </section>

    <section class="content container-fluid">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <!-- Tabela de Itens -->
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-cubes"></i> Lista de Itens</h3>
            </div>
            <div class="box-body table-responsive">
                @if($itensPorProduto->count() > 0)
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th width="60%">Produto</th>
                                <th width="13%">Solto</th>
                                <th width="13%">Containers</th>
                                <th width="14%">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($itensPorProduto as $produtoId => $dados)
                                @php
                                    $quantidadeSolta = $dados['quantidadeSolta'] ?? 0;
                                    $quantidadeEmContainers = $dados['itensEmContainers']->sum(function($grupo) { 
                                        return $grupo->sum('quantidade'); 
                                    });
                                    $total = $quantidadeSolta + $quantidadeEmContainers;
                                @endphp
                                <tr style="cursor: pointer;" onclick="toggleDetalhes(this)">
                                    <td>
                                        <strong>{{ $dados['produto']->nome ?? 'Sem Nome' }}</strong>
                                    </td>
                                    <td>{{ $quantidadeSolta }}</td>
                                    <td>{{ $quantidadeEmContainers }}</td>
                                    <td>{{ $total }}</td>
                                </tr>
                                <!-- Linha de detalhes (oculta por padrão) -->
                                <tr class="detalhe-row" style="display: none;">
                                    <td colspan="4" style="padding: 15px;">
                                        {{-- Itens Soltos --}}
                                        @if($quantidadeSolta > 0)
                                            <div style="margin-bottom: 20px;">
                                                <h5 style="margin: 0 0 10px 0;">Itens Soltos</h5>
                                                @foreach($dados['itens'] as $item)
                                                    @if(is_null($item->fk_item_pai))
                                                        <div style="padding: 8px 0; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;">
                                                            <p style="margin: 3px 0; flex: 1;">
                                                                <strong>Quantidade:</strong> {{ $item->quantidade }}
                                                                | <strong>Lote:</strong> {{ $item->lote ?? 'N/A' }}
                                                                | <strong>Entrada:</strong> 
                                                                @if($item->data_entrada)
                                                                    @if(is_string($item->data_entrada))
                                                                        {{ \Carbon\Carbon::parse($item->data_entrada)->format('d/m/Y') }}
                                                                    @else
                                                                        {{ $item->data_entrada->format('d/m/Y') }}
                                                                    @endif
                                                                @else
                                                                    N/A
                                                                @endif
                                                            </p>
                                                            <a href="{{ route('estoque.item.mover.form', $item->id) }}" 
                                                               class="btn btn-xs btn-warning" 
                                                               title="Mover item para um container">
                                                                <i class="fa fa-exchange"></i>
                                                            </a>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endif

                                        {{-- Em Containers --}}
                                        @if($dados['itensEmContainers']->count() > 0)
                                            <div>
                                                <h5 style="margin: 0 0 10px 0;">Em Containers</h5>
                                                @foreach($dados['itensEmContainers'] as $containerId => $itensNoContainer)
                                                    @php
                                                        $container = $itensNoContainer->first()->itemPai;
                                                    @endphp
                                                    <div style="margin-bottom: 15px; padding-left: 15px; border-left: 2px solid #ccc;">
                                                        <p style="margin: 0 0 8px 0; font-weight: bold;">
                                                            {{ $container->produto->nome ?? 'Container' }}
                                                        </p>
                                                        @foreach($itensNoContainer as $item)
                                                            <p style="margin: 4px 0; display: flex; justify-content: space-between; align-items: center;">
                                                                <span>
                                                                    <strong>Quantidade:</strong> {{ $item->quantidade }}
                                                                    | <strong>Lote:</strong> {{ $item->lote ?? 'N/A' }}
                                                                </span>
                                                                <a href="{{ route('estoque.item.mover.form', $item->id) }}" 
                                                                   class="btn btn-xs btn-warning" 
                                                                   title="Mover item para outro container ou seção">
                                                                    <i class="fa fa-exchange"></i>
                                                                </a>
                                                            </p>
                                                        @endforeach
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i> Nenhum item vinculado a esta seção.
                    </div>
                @endif
            </div>
        </div>

        <!-- Botões de Ação -->
        <div style="margin-top: 15px;">
            <a href="{{ route('secoes.index', $secao->fk_unidade) }}" class="btn btn-default">
                <i class="fa fa-arrow-left"></i> Voltar
            </a>
            @if($itens->count() > 0)
                <a href="{{ route('secoes.transferir_lote_form', ['unidade' => $secao->fk_unidade, 'secao' => $secao->id]) }}" class="btn btn-warning">
                    <i class="fa fa-exchange"></i> Transferir Itens
                </a>
            @endif
        </div>
    </section>
</div>

<script>
    function toggleDetalhes(row) {
        const detalheRow = row.nextElementSibling;
        if (detalheRow && detalheRow.classList.contains('detalhe-row')) {
            if (detalheRow.style.display === 'none') {
                detalheRow.style.display = 'table-row';
                row.style.backgroundColor = '#f0f0f0';
            } else {
                detalheRow.style.display = 'none';
                row.style.backgroundColor = '';
            }
        }
    }
</script>
@endsection

