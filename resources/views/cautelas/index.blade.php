@extends('layout.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Cautelas
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{route('dashboard')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">Cautelas</li>
        </ol>
    </section>

    <section class="content container-fluid">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Lista de Cautelas</h3>
                <div class="box-tools pull-right">
                    <a href="{{ route('cautelas.create') }}" class="btn btn-success">
                        <i class="fa fa-plus"></i> Nova Cautela
                    </a>
                </div>
            </div>
            <div class="box-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Responsável</th>
                                <th>Telefone</th>
                                <th>Instituição</th>
                                <th>Data Cautela</th>
                                <th>Data Prevista Devolução</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cautelas as $cautela)
                            @php
                                $totalPendente = $cautela->produtos->sum(function($item) { 
                                    return $item->quantidadePendente(); 
                                });
                            @endphp
                            <tr>
                                <td>{{ $cautela->id }}</td>
                                <td>{{ $cautela->nome_responsavel }}</td>
                                <td>{{ $cautela->telefone }}</td>
                                <td>{{ $cautela->instituicao }}</td>
                                <td>{{ $cautela->data_cautela->format('d/m/Y') }}</td>
                                <td>{{ $cautela->data_prevista_devolucao->format('d/m/Y') }}</td>
                                <td>
                                    @if($totalPendente == 0)
                                        <span class="label label-success">Devolvido</span>
                                    @else
                                        <span class="label label-warning">Pendente</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('cautelas.show', $cautela->id) }}" class="btn btn-sm btn-info" title="Ver Detalhes">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    @if($totalPendente > 0)
                                    <a href="{{ route('cautelas.devolucao', $cautela->id) }}" class="btn btn-sm btn-success" title="Registrar Devolução">
                                        <i class="fa fa-check"></i>
                                    </a>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center">Nenhuma cautela cadastrada.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection