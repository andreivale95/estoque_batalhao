@extends('layout.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Devolução de Cautela #{{ $cautela->id }}
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{route('dashboard')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{ route('cautelas.index') }}">Cautelas</a></li>
            <li><a href="{{ route('cautelas.show', $cautela->id) }}">Cautela #{{ $cautela->id }}</a></li>
            <li class="active">Devolução</li>
        </ol>
    </section>

    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-8">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Informações da Cautela</h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Responsável:</strong> {{ $cautela->nome_responsavel }}</p>
                                <p><strong>Instituição:</strong> {{ $cautela->instituicao }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Data da Cautela:</strong> {{ $cautela->data_cautela->format('d/m/Y') }}</p>
                                <p><strong>Data Prevista:</strong> {{ $cautela->data_prevista_devolucao->format('d/m/Y') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <form action="{{ route('cautelas.processDevolucao', $cautela->id) }}" method="POST">
                    @csrf
                    <div class="box box-success">
                        <div class="box-header with-border">
                            <h3 class="box-title">Itens para Devolução</h3>
                        </div>
                        <div class="box-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Produto</th>
                                            <th>Tipo</th>
                                            <th>Patrimônio</th>
                                            <th>Seção</th>
                                            <th>Qtd Cautelada</th>
                                            <th>Qtd Devolvida</th>
                                            <th>Qtd Pendente</th>
                                            <th>Devolver Agora</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($cautela->produtos as $item)
                                        @php
                                            $pendente = $item->quantidadePendente();
                                        @endphp
                                        @if($pendente > 0)
                                        <tr>
                                            <td>{{ $item->produto->nome }}</td>
                                            <td>
                                                @if($item->iten_patrimonial_id)
                                                    <span class="label label-info">Permanente</span>
                                                @else
                                                    <span class="label label-success">Consumo</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($item->iten_patrimonial_id)
                                                    {{ $item->itenPatrimonial->patrimonio ?? '-' }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                @if($item->iten_patrimonial_id)
                                                    {{ $item->itenPatrimonial->secao->nome ?? 'Sem seção' }}
                                                @else
                                                    {{ $item->estoque->secao->nome ?? 'Sem seção' }}
                                                @endif
                                            </td>
                                            <td>{{ $item->quantidade }}</td>
                                            <td>{{ $item->quantidade_devolvida }}</td>
                                            <td><strong>{{ $pendente }}</strong></td>
                                            <td>
                                                <input type="hidden" name="itens[]" value="{{ $item->id }}">
                                                <input type="number" 
                                                       name="quantidades[]" 
                                                       class="form-control" 
                                                       min="0" 
                                                       max="{{ $pendente }}" 
                                                       value="{{ $pendente }}" 
                                                       @if($item->iten_patrimonial_id) readonly @endif
                                                       style="width: 100px;">
                                            </td>
                                        </tr>
                                        @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            @if($cautela->produtos->sum(function($item) { return $item->quantidadePendente(); }) == 0)
                            <div class="alert alert-success">
                                <i class="fa fa-check-circle"></i> Todos os itens foram devolvidos!
                            </div>
                            @endif
                        </div>
                        <div class="box-footer">
                            <a href="{{ route('cautelas.show', $cautela->id) }}" class="btn btn-default">
                                <i class="fa fa-arrow-left"></i> Cancelar
                            </a>
                            @if($cautela->produtos->sum(function($item) { return $item->quantidadePendente(); }) > 0)
                            <button type="submit" class="btn btn-success pull-right">
                                <i class="fa fa-check"></i> Registrar Devolução
                            </button>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
@endsection
