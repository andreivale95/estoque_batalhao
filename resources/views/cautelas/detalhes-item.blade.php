@extends('layout.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fas fa-box"></i> Detalhes - {{ $produto->nome }}
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{route('dashboard')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{route('cautelas.index')}}">Cautelas</a></li>
            <li><a href="{{route('cautelas.por-item')}}">Por Item</a></li>
            <li class="active">{{ $produto->nome }}</li>
        </ol>
    </section>

    <section class="content container-fluid">
        <!-- Abas de visualização -->
        <div class="row">
            <div class="col-md-12">
                <ul class="nav nav-tabs" role="tablist" style="margin-bottom: 20px;">
                    <li role="presentation" style="margin-right: 10px;">
                        <a href="{{ route('cautelas.index') }}" 
                           class="btn btn-sm" 
                           style="background-color: #6c757d; color: white; border: 1px solid #5a6268;">
                            <i class="fa fa-file-text"></i> Por Cautela
                        </a>
                    </li>
                    <li role="presentation" class="active" style="margin-right: 10px;">
                        <a href="{{ route('cautelas.por-item') }}" 
                           class="btn btn-sm" 
                           style="background-color: #0073cc; color: white; border: 1px solid #0056b3;">
                            <i class="fa fa-box"></i> Por Item
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Card de resumo -->
        <div class="row">
            <div class="col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-yellow"><i class="fa fa-cube"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Quantidade Total Cautelada</span>
                        <span class="info-box-number">{{ $quantidadeTotalCautelada }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-blue"><i class="fa fa-users"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Quantas Pessoas Possuem</span>
                        <span class="info-box-number">{{ $cautelas->count() }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-green"><i class="fa fa-tag"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Categoria</span>
                        <span class="info-box-number">{{ $produto->categoria->nome ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabela de quem possui -->
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-list"></i> Quem Possui Este Item em Cautela
                        </h3>
                    </div>
                    <div class="box-body">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th width="25%">Responsável</th>
                                    <th width="20%">Cautela Nº</th>
                                    <th width="15%" class="text-center">Quantidade Cautelada</th>
                                    <th width="15%" class="text-center">Quantidade Devolvida</th>
                                    <th width="15%" class="text-center">Saldo</th>
                                    <th width="10%" class="text-center">Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($cautelas as $cp)
                                    @php
                                        $saldo = $cp->quantidade - (int)($cp->quantidade_devolvida ?? 0);
                                    @endphp
                                    <tr>
                                        <td>
                                            <strong>{{ $cp->cautela->nome_responsavel ?? 'N/A' }}</strong>
                                            @if($cp->cautela && $cp->cautela->responsavel_unidade)
                                                <br/>
                                                <small class="text-muted">{{ $cp->cautela->responsavel_unidade }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('cautelas.show', $cp->cautela_id) }}" 
                                               class="text-decoration-none">
                                                #{{ $cp->cautela->numero ?? $cp->cautela_id }}
                                            </a>
                                            <br/>
                                            <small class="text-muted">
                                                {{ $cp->cautela->data_cautela ? $cp->cautela->data_cautela->format('d/m/Y') : 'N/A' }}
                                            </small>
                                        </td>
                                        <td class="text-center">
                                            <span class="label label-warning">
                                                {{ $cp->quantidade }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="label label-success">
                                                {{ $cp->quantidade_devolvida ?? 0 }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @if($saldo > 0)
                                                <span class="label label-danger">{{ $saldo }}</span>
                                            @else
                                                <span class="label label-default">0</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('cautelas.show', $cp->cautela_id) }}" 
                                               class="btn btn-xs btn-info" 
                                               title="Ver cautela">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            Nenhuma cautela ativa para este produto
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botão voltar -->
        <div class="row">
            <div class="col-md-12">
                <a href="{{ route('cautelas.por-item') }}" class="btn btn-secondary">
                    <i class="fa fa-arrow-left"></i> Voltar à Lista
                </a>
            </div>
        </div>
    </section>
</div>
@endsection
