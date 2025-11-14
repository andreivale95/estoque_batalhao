@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Cautelas</h3>
                    <div class="card-tools">
                        <a href="{{ route('cautelas.create') }}" class="btn btn-primary">Nova Cautela</a>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Responsável</th>
                                <th>Instituição</th>
                                <th>Data Cautela</th>
                                <th>Data Prevista Devolução</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cautelas as $cautela)
                            <tr>
                                <td>{{ $cautela->id }}</td>
                                <td>{{ $cautela->nome_responsavel }}</td>
                                <td>{{ $cautela->instituicao }}</td>
                                <td>{{ date('d/m/Y', strtotime($cautela->data_cautela)) }}</td>
                                <td>{{ date('d/m/Y', strtotime($cautela->data_prevista_devolucao)) }}</td>
                                <td>
                                    @if($cautela->data_devolucao)
                                        <span class="badge badge-success">Devolvido</span>
                                    @else
                                        <span class="badge badge-warning">Em Andamento</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('cautelas.show', $cautela->id) }}" class="btn btn-sm btn-info">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    @if(!$cautela->data_devolucao)
                                        <a href="{{ route('cautelas.devolucao', $cautela->id) }}" class="btn btn-sm btn-success">
                                            <i class="fa fa-check"></i> Devolver
                                        </a>
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
@endsection