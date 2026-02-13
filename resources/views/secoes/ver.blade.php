@extends('layout.app')
@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-folder"></i> Itens da Seção: {{ $secao->nome }}
            <small>{{ $totalItensSecao }} {{ $totalItensSecao == 1 ? 'item' : 'itens' }}</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{ route('secoes.index', $secao->fk_unidade) }}">Seções</a></li>
            <li class="active">{{ $secao->nome }}</li>
        </ol>
    </section>

    <section class="content container-fluid">
        <style>
            .secao-table {
                width: 100%;
                border-collapse: collapse;
                margin: 20px 0;
                font-family: Arial, sans-serif;
            }
            .secao-table th,
            .secao-table td {
                border: 1px solid #000;
                padding: 8px;
                text-align: center;
            }
            .secao-table th {
                background-color: #f2f2f2;
                font-weight: bold;
            }
            .secao-table tr:nth-child(even) {
                background-color: #f9f9f9;
            }
        </style>
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <!-- Tabela de Itens -->
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-cubes"></i> Lista de Itens</h3>
            </div>
            <div class="box-body table-responsive">
                @if($consumoAgrupado->count() > 0 || $itensPatrimoniais->count() > 0)
                    <table class="secao-table">
                        <thead>
                            <tr>
                                <th>Nº</th>
                                <th>Item</th>
                                <th>Patrimônio</th>
                                <th>Descrição</th>
                                <th>Quantidade</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $contador = 1; @endphp
                            @foreach($consumoAgrupado as $dados)
                                <tr>
                                    <td>{{ $contador++ }}</td>
                                    <td>{{ $dados['produto']->nome ?? 'Sem Nome' }}</td>
                                    <td>-</td>
                                    <td>{{ $dados['produto']->descricao ?? '-' }}</td>
                                    <td>{{ $dados['quantidade'] }}</td>
                                </tr>
                            @endforeach
                            @foreach($itensPatrimoniais as $patrimonio)
                                <tr>
                                    <td>{{ $contador++ }}</td>
                                    <td>{{ $patrimonio->produto->nome ?? 'Sem Nome' }}</td>
                                    <td>{{ $patrimonio->patrimonio ?? '-' }}</td>
                                    <td>{{ $patrimonio->produto->descricao ?? '-' }}</td>
                                    <td>1</td>
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
            @if($itensConsumo->count() > 0 || $itensPatrimoniais->count() > 0)
                <a href="{{ route('secoes.transferir_lote_form', ['unidade' => $secao->fk_unidade, 'secao' => $secao->id]) }}" class="btn btn-warning">
                    <i class="fa fa-exchange"></i> Transferir Itens
                </a>
                <a href="{{ route('secoes.pdf', ['unidade' => $secao->fk_unidade, 'secao' => $secao->id]) }}" class="btn btn-danger" target="_blank">
                    <i class="fa fa-file-pdf-o"></i> Gerar PDF
                </a>
            @endif
        </div>
    </section>
</div>
@endsection

