@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Detalhes da Cautela</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h4>Informações da Cautela</h4>
                            <table class="table">
                                <tr>
                                    <th>Responsável:</th>
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
                                    <td>{{ date('d/m/Y', strtotime($cautela->data_cautela)) }}</td>
                                </tr>
                                <tr>
                                    <th>Data Prevista de Devolução:</th>
                                    <td>{{ date('d/m/Y', strtotime($cautela->data_prevista_devolucao)) }}</td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        @if($cautela->data_devolucao)
                                            <span class="badge badge-success">Devolvido em {{ date('d/m/Y', strtotime($cautela->data_devolucao)) }}</span>
                                        @else
                                            <span class="badge badge-warning">Em Andamento</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h4>Itens da Cautela</h4>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Seção</th>
                                        <th>Item</th>
                                        <th>Quantidade</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($cautela->produtos as $produto)
                                    <tr>
                                        <td>{{ $produto->secao->nome }}</td>
                                        <td>{{ $produto->produto->nome }}</td>
                                        <td>{{ $produto->quantidade }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="form-group mt-4">
                        <a href="{{ route('cautelas.index') }}" class="btn btn-secondary">Voltar</a>
                        @if(!$cautela->data_devolucao)
                            <a href="{{ route('cautelas.devolucao', $cautela->id) }}" class="btn btn-success">
                                <i class="fa fa-check"></i> Registrar Devolução
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection