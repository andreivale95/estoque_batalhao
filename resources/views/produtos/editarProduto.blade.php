@extends('layout/app')
@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>Editar Produto - {{ $produto->nome }}</h1>
            <ol class="breadcrumb">
                <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
                <li><a href="{{ route('produtos.listar') }}">Produtos</a></li>
                <li class="active">Editar Produto</li>
            </ol>
        </section>

        <section class="content container-fluid">
            <div class="panel" style="background-color: #3c8dbc;">
                <div class="panel-heading" style="color: white;">
                    DADOS DO PRODUTO
                </div>
                <div class="panel-body" style="background-color: white;">
                    <form action="{{ route('produto.atualizar', $produto->id) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">

                                <div class="box-body">
                                    <div class="form-group col-md-6">
                                        <label>Nome:</label>
                                        <input type="text" class="form-control" name="nome"
                                            value="{{ $produto->nome }}" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Marca:</label>
                                        <input type="text" class="form-control" name="marca"
                                            value="{{ $produto->marca }}" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Descrição:</label>
                                        <input type="text" class="form-control" name="descricao"
                                            value="{{ $produto->descricao }}" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Tipo Produto:</label>
                                        <select name="tipoproduto" class="form-control" required>
                                            <option value="">Escolha um Tipo</option>
                                            @foreach ($tipoprodutos as $tipoproduto)
                                                <option value="{{ $tipoproduto->id }}"
                                                    {{ $produto->fk_tipo_produto == $tipoproduto->id ? 'selected' : '' }}>
                                                    {{ $tipoproduto->nome }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Condição:</label>
                                        <select name="condicao" class="form-control" required>
                                            <option value="">Selecione</option>
                                            @foreach ($condicoes as $condicao)
                                                <option value="{{ $condicao->id }}"
                                                    {{ $produto->fk_condicao == $condicao->id ? 'selected' : '' }}>
                                                    {{ $condicao->condicao }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Valor (R$):</label>
                                        <input type="text" class="form-control" name="valor_formatado" id="valor"
                                            value="{{ number_format((float) $produto->valor, 2, ',', '.') }}" required>
                                        <input type="hidden" name="valor" id="valor_limpo"
                                            value="{{ intval($produto->valor * 100) }}">
                                    </div>
                                </div>


                                <div class="box-footer pull-right">
                                    <a href="{{ route('produto.ver', $produto->id) }}" class="btn btn-danger"><i
                                            class="fa fa-close"></i> Cancelar</a>
                                    <button type="submit" class="btn btn-success"><i class="fa fa-save"></i>
                                        Atualizar</button>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="box box-primary">
                                    <div class="box-header">Imagem Atual</div>
                                    <div class="box-body text-center">
                                        @if ($produto->imagem)
                                            <img src="{{ asset('/storage/' . $produto->imagem) }}" class="img-responsive"
                                                style="max-width: 100%;" alt="Imagem do Produto">
                                        @else
                                            <p>Sem imagem cadastrada.</p>
                                        @endif
                                        <input type="file" name="imagem" class="form-control mt-2">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>

    <script>
        document.getElementById('valor').addEventListener('input', function(e) {
            let raw = e.target.value.replace(/\D/g, '');
            let valorCentavos = raw ? parseInt(raw, 10) : 0;
            document.getElementById('valor_limpo').value = valorCentavos;

            let valorFormatado = (valorCentavos / 100).toFixed(2)
                .replace('.', ',')
                .replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            e.target.value = valorFormatado;
        });
    </script>
@endsection
