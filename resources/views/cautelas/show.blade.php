@extends('layout.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Detalhes da Cautela #{{ $cautela->id }}
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{route('dashboard')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{ route('cautelas.index') }}">Cautelas</a></li>
            <li class="active">Detalhes</li>
        </ol>
    </section>

    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Informações da Cautela</h3>
                        <div class="box-tools pull-right">
                            <a href="{{ route('cautelas.pdf', $cautela->id) }}" class="btn btn-danger btn-sm" target="_blank">
                                <i class="fa fa-file-pdf-o"></i> Baixar Comprovante (PDF)
                            </a>
                        </div>
                    </div>
                    <div class="box-body">
                        <table class="table table-borderless">
                            <tr>
                                <th style="width: 200px;">Responsável:</th>
                                <td>{{ $cautela->nome_responsavel }}</td>
                            </tr>
                            <tr>
                                <th>Telefone:</th>
                                <td>{{ $cautela->telefone }}</td>
                            </tr>
                            <tr>
                                <th>Instituição:</th>
                                <td>{{ $cautela->instituicao }}</td>
                            </tr>
                            <tr>
                                <th>Data da Cautela:</th>
                                <td>{{ $cautela->data_cautela->format('d/m/Y') }}</td>
                            </tr>
                            <tr>
                                <th>Data Prevista de Devolução:</th>
                                <td>{{ $cautela->data_prevista_devolucao->format('d/m/Y') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">Itens da Cautela</h3>
                    </div>
                    <div class="box-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Produto</th>
                                        <th>Seção</th>
                                        <th>Qtd Cautelada</th>
                                        <th>Qtd Devolvida</th>
                                        <th>Qtd Pendente</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($cautela->produtos as $item)
                                    <tr>
                                        <td>{{ $item->produto->nome }}</td>
                                        <td>{{ $item->estoque->secao->nome ?? 'Sem seção' }}</td>
                                        <td>{{ $item->quantidade }}</td>
                                        <td>{{ $item->quantidade_devolvida }}</td>
                                        <td>{{ $item->quantidadePendente() }}</td>
                                        <td>
                                            @if($item->isDevolvido())
                                                <span class="label label-success">Devolvido</span>
                                                @if($item->data_devolucao)
                                                <br><small>{{ $item->data_devolucao->format('d/m/Y') }}</small>
                                                @endif
                                            @else
                                                <span class="label label-warning">Pendente</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <a href="{{ route('cautelas.index') }}" class="btn btn-default">
                    <i class="fa fa-arrow-left"></i> Voltar
                </a>
                @if($cautela->produtos->sum(function($item) { return $item->quantidadePendente(); }) > 0)
                <a href="{{ route('cautelas.devolucao', $cautela->id) }}" class="btn btn-success">
                    <i class="fa fa-check"></i> Registrar Devolução
                </a>
                @else
                <span class="label label-success" style="font-size: 14px; padding: 8px 12px;">
                    <i class="fa fa-check-circle"></i> Todos os itens foram devolvidos
                </span>
                @endif
            </div>
        </div>
    </section>
</div>
@endsection