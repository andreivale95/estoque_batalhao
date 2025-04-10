@extends('layout/app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>Atribuir Produtos ao Militar</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{ route('efetivo_produtos.listar') }}">Seleção de Militar</a></li>
            <li class="active">Atribuição de Produtos</li>
        </ol>
    </section>

    <section class="content container-fluid">
        <div class="panel" style="background-color: #3c8dbc;">
            <div class="panel-heading" style="color: white;">
                PRODUTOS DISPONÍVEIS PARA: {{ $militar->nome }} ({{ $militar->matricula }})
            </div>

            <div class="panel-body" style="background-color: white;">
                <form action="{{ route('efetivo_produtos.salvar') }}" method="POST">
                    @csrf
                    <input type="hidden" name="militar_id" value="{{ $militar->id }}">

                    <div class="row">
                        <!-- Coluna Kits e Produtos -->
                        <div class="col-md-12">
                            @foreach ($kits as $kit)
                                @if ($kit->disponivel === 'S')
                                    <div class="box box-primary">
                                        <div class="box-header with-border">
                                            <h3 class="box-title">Kit: {{ $kit->nome }}</h3>
                                        </div>
                                        <div class="box-body">
                                            @php
                                                $produtosAgrupados = $kit->produtos->groupBy('nome');
                                            @endphp

                                            @foreach ($produtosAgrupados as $nomeProduto => $grupo)
                                                <div class="form-group col-md-4">
                                                    <label>{{ $nomeProduto }}</label>
                                                    <select name="produtos[{{ $kit->id }}][{{ $nomeProduto }}]"
                                                        class="form-control">
                                                        <option value="">-- Selecionar Tamanho --</option>
                                                        @foreach ($grupo as $produto)
                                                        <option value="{{ $produto->id }}"
                                                            {{ in_array($produto->id, $produtosSelecionados) ? 'selected' : '' }}>
                                                            {{ $produto->tamanho ?? 'Único' }}
                                                        </option>

                                                        @endforeach
                                                    </select>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    <div class="box-footer text-right">
                        <a href="{{ route('efetivo_produtos.listar') }}" class="btn btn-danger">
                            <i class="fa fa-arrow-left"></i> Voltar
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="fa fa-save"></i> Salvar Produtos
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
@endsection
