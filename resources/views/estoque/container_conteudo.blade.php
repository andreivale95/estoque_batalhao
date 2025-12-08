@extends('layout/app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-briefcase"></i> Conteúdo do Container
            <small>{{ $container->produto->nome ?? 'Container' }}</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{ route('estoque.listar') }}">Inventário</a></li>
            <li class="active">Container</li>
        </ol>
    </section>

    <section class="content container-fluid">
        <!-- Informações do Container -->
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-info-circle"></i> Informações do Container</h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-3">
                        <strong>Nome:</strong><br>
                        {{ $container->produto->nome ?? '-' }}
                    </div>
                    <div class="col-md-3">
                        <strong>Seção:</strong><br>
                        {{ $container->secao->nome ?? '-' }}
                    </div>
                    <div class="col-md-3">
                        <strong>Unidade:</strong><br>
                        {{ $container->unidade->nome ?? '-' }}
                    </div>
                    <div class="col-md-3">
                        <strong>Quantidade de Containers:</strong><br>
                        {{ $container->quantidade }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Resumo -->
        <div class="row">
            <div class="col-md-6">
                <div class="info-box bg-aqua">
                    <span class="info-box-icon"><i class="fa fa-cubes"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Tipos de Itens Diferentes</span>
                        <span class="info-box-number">{{ $quantidadeItens }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="info-box bg-green">
                    <span class="info-box-icon"><i class="fa fa-archive"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Quantidade Total de Itens</span>
                        <span class="info-box-number">{{ $quantidadeTotalItens }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de Itens Dentro do Container -->
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-list"></i> Itens Armazenados</h3>
            </div>
            <div class="box-body">
                @if($itensFilhos->count() > 0)
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>Categoria</th>
                                <th>Quantidade</th>
                                <th>Patrimônio</th>
                                <th>Valor Unitário</th>
                                <th>Subtotal</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($itensFilhos as $item)
                                <tr>
                                    <td>
                                        <i class="fa fa-arrow-right text-primary"></i>
                                        <strong>{{ $item->produto->nome ?? '-' }}</strong>
                                    </td>
                                    <td>{{ $item->produto->categoria->nome ?? '-' }}</td>
                                    <td>
                                        <span class="badge bg-blue">{{ $item->quantidade }}</span>
                                    </td>
                                    <td>{{ $item->produto->patrimonio ?? '-' }}</td>
                                    <td>R$ {{ number_format($item->valor_unitario ?? 0, 2, ',', '.') }}</td>
                                    <td>
                                        R$ {{ number_format(($item->valor_unitario ?? 0) * $item->quantidade, 2, ',', '.') }}
                                    </td>
                                    <td>
                                        <a href="{{ route('estoque.produto.detalhes', $item->fk_produto) }}" 
                                           class="btn btn-xs btn-info" 
                                           title="Ver detalhes do produto">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-light-blue">
                                <td colspan="5" class="text-right"><strong>Total Geral:</strong></td>
                                <td colspan="2">
                                    <strong>
                                        R$ {{ number_format($itensFilhos->sum(function($item) {
                                            return ($item->valor_unitario ?? 0) * $item->quantidade;
                                        }), 2, ',', '.') }}
                                    </strong>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                @else
                    <div class="alert alert-warning">
                        <i class="fa fa-exclamation-triangle"></i>
                        <strong>Container vazio!</strong> Não há itens armazenados dentro deste container.
                    </div>
                @endif
            </div>
            <div class="box-footer">
                <a href="{{ route('estoque.listar') }}" class="btn btn-default">
                    <i class="fa fa-arrow-left"></i> Voltar ao Inventário
                </a>
                @if($itensFilhos->count() > 0)
                    <button class="btn btn-primary" onclick="window.print()">
                        <i class="fa fa-print"></i> Imprimir
                    </button>
                @endif
            </div>
        </div>
    </section>
</div>

@section('styles')
<style>
    @media print {
        .content-header, .breadcrumb, .box-footer, .main-sidebar, .main-header {
            display: none !important;
        }
    }
</style>
@endsection
@endsection
