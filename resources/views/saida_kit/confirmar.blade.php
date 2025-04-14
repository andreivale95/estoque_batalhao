@extends('layout/app')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>Saída de Produtos</h1>
            <ol class="breadcrumb">
                <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
                <li><a href="{{ route('saida_estoque.index') }}">Saída de Produtos</a></li>
                <li class="active">Confirmar Saída</li>
            </ol>
        </section>

        <section class="content container-fluid">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Confirmação da Saída - Kit "{{ $kit->nome }}"</h3>
                </div>
                <div class="box-body">
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Militar:</strong> {{ $militar->nome }} ({{ $militar->matricula }})
                        </div>
                        <div class="col-md-4"><strong>Unidade:</strong> {{ $militar->unidade->nome ?? '-' }}</div>
                        <div class="col-md-4"><strong>Posto/Graduação:</strong> {{ $militar->posto_graduacao }}</div>
                    </div>

                    <h4>Produtos do Kit</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="bg-primary text-white">
                                <tr>
                                    <th>Produto</th>
                                    <th>Tamanho</th>
                                    <th>Quantidade</th>
                                    <th>Disponível</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($itens as $item)
                                    <tr class="{{ $item['disponivel'] === 'Não' ? 'bg-warning' : '' }}">
                                        <td>{{ $item['produto']->nome }}</td>
                                        <td>{{ $item['tamanho'] }}</td>
                                        <td>{{ $item['quantidade'] }}</td>
                                        <td>
                                            @if ($item['disponivel'] === 'Sim')
                                                <span class="label label-success">Sim</span>
                                            @else
                                                <span class="label label-danger">Não</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">Nenhum item encontrado no kit.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <form action="{{ route('saida_estoque.confirmar_saida') }}" method="POST">
                    @csrf
                    <input type="hidden" name="militar_id" value="{{ $militar->id }}">
                    <input type="hidden" name="kit_id" value="{{ $kit->id }}">

                    <div class="box-footer text-right">
                        <a href="{{ route('saida_estoque.index') }}" class="btn btn-default">
                            <i class="fa fa-arrow-left"></i> Voltar
                        </a>

                        @if ($temProdutoDisponivel)
                            <button type="submit" class="btn btn-success"
                                onclick="return confirm('Deseja realmente dar saída desses produtos para o militar?')">
                                <i class="fa fa-check"></i> Confirmar Saída
                            </button>
                        @else
                            <button type="button" class="btn btn-danger" disabled>
                                <i class="fa fa-times"></i> Sem produtos disponíveis
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        </section>
    </div>
@endsection
