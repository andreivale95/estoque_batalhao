@extends('layout/app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>Visualizar Produtos do Militar</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{ route('efetivo_produtos.listar') }}">Seleção de Militar</a></li>
            <li class="active">Visualização</li>
        </ol>
    </section>

    <section class="content container-fluid">
        <div class="panel" style="background-color: #3c8dbc;">
            <div class="panel-heading" style="color: white;">
                PRODUTOS ATRIBUÍDOS A: {{ $militar->nome }} ({{ $militar->matricula }})
            </div>

            <div class="panel-body" style="background-color: white;">
                @php
                    $temProdutos = false;
                @endphp

                @foreach ($kits as $kit)
                    @php
                        $produtosDoMilitar = $militar->produtos()
                            ->where('fk_kit', $kit->id)
                            ->get()
                            ->groupBy('nome');
                    @endphp

                    @if ($produtosDoMilitar->isNotEmpty())
                        @php $temProdutos = true; @endphp
                        <div class="box box-primary">
                            <div class="box-header with-border">
                                <h3 class="box-title">Kit: {{ $kit->nome }}</h3>
                            </div>
                            <div class="box-body">
                                <div class="row">
                                    @foreach ($produtosDoMilitar as $nomeProduto => $grupo)
                                        <div class="col-md-4">
                                            <strong>{{ $nomeProduto }}:</strong>
                                            <ul class="list-unstyled">
                                                @foreach ($grupo as $produto)
                                                    <li>{{ $produto->tamanho()->first()->tamanho ?? 'Único' }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach

                @if (! $temProdutos)
                    <div class="alert alert-info">
                        Este militar ainda não possui produtos atribuídos.
                    </div>
                @endif
            </div>

            <div class="box-footer text-right" style="padding: 15px;">
                <a href="{{ route('efetivo_produtos.listar') }}" class="btn btn-default">
                    <i class="fa fa-arrow-left"></i> Voltar
                </a>
                <a href="{{ route('efetivo_produtos.atribuir', $militar->id) }}" class="btn btn-{{ $temProdutos ? 'primary' : 'success' }}">
                    <i class="fa {{ $temProdutos ? 'fa-pencil' : 'fa-plus' }}"></i>
                    {{ $temProdutos ? 'Editar Produtos' : 'Adicionar Produtos' }}
                </a>
            </div>
        </div>
    </section>
</div>
@endsection
