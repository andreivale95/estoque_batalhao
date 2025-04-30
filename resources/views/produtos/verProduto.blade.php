@extends('layout/app')
@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">

            <h1>
                {{ $produto->nome }} -
                {{ optional($produto->tamanho()->first())->tamanho ?? 'Tamanho Único' }}
            </a>
            </h1>

            <ol class="breadcrumb">
                <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
                <li><a href="{{ route('produtos.listar') }}"><i class=""></i> Patrimônios</a></li>
                <li><a href="{{ route('produto.ver', $produto->id) }}"><i class=""></i>
                        {{ $produto->descricao }}</a></li>
            </ol>
        </section>

        <!-- Main content -->
        <section class="content container-fluid">

            <div class="panel" style="background-color: #3c8dbc;">
                <div class="panel-heading" style="color: white;">
                    DADOS DO PRODUTO
                </div>
                <div class="panel-body" style="background-color: white;">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="box box-primary">
                                <div class="box-header">
                                    @if (session('path'))
                                        <img src="{{ asset('storage/' . session('path')) }}" alt="Imagem do Produto">
                                    @endif
                                </div>
                                <div class="box-body">
                                    <div class="form-group has-feedback col-md-6">
                                        <label class="control-label" for="">Nome:</label>
                                        <input type="text" class="form-control" name="nome"
                                            value="{{ $produto->nome }} -  {{ optional($produto->tamanho()->first())->tamanho ?? 'Tamanho Único' }}"
                                            disabled>


                                        </a>
                                    </div>
                                    <div class="form-group has-feedback col-md-6">
                                        <label class="control-label" for="">Marca:</label>
                                        <input type="text" class="form-control" name="marca"
                                            value="{{ $produto->marca }}" disabled>
                                    </div>
                                    <div class="form-group has-feedback col-md-6">
                                        <label class="control-label" for="">Descrição:</label>
                                        <input type="text" class="form-control" name="descricao"
                                            value="{{ $produto->descricao }}" disabled>
                                    </div>

                                    <div class="form-group has-feedback col-md-6">
                                        <label class="control-label" for="">Unidade:</label>
                                        <input type="text" class="form-control" name="unidade"
                                            value="{{ $produto->unidade }}" disabled>
                                    </div>
                                    <div class="form-group has-feedback col-md-6">
                                        <label class="control-label" for="categoria">Categoria:</label>
                                        <select class="form-control" disabled>
                                            @foreach ($categorias as $categoria)
                                                <option {{ $produto->fk_categoria == $categoria->id ? 'selected' : '' }}>
                                                    {{ $categoria->nome }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group has-feedback col-md-6">
                                        <label class="control-label" for="">Condição:</label>
                                        <select class="form-control" disabled>
                                            @foreach ($condicoes as $condicao)
                                                <option value="{{ $condicao->id }}"
                                                    {{ $produto->fk_condicao == $condicao->id ? 'selected' : '' }}>
                                                    {{ $condicao->condicao }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group has-feedback col-md-6">

                                        <label class="control-label" for="">Kit</label>
                                        <select name="kit" id="" class="form-control" disabled>
                                            <option value="">Selecione</option>
                                            @if ($kits->isNotEmpty())
                                                @foreach ($kits as $kitt)
                                                    <option value="{{ $kitt->id }}"
                                                        {{ isset($kit) && $kit->id == $kitt->id ? 'selected' : '' }}>
                                                        {{ $kitt->nome }}
                                                    </option>
                                                @endforeach
                                            @endif

                                        </select>
                                    </div>

                                    <div class="form-group has-feedback col-md-6">
                                        <label class="control-label" for="tamanho">Tamanho</label>
                                        <select name="tamanho" class="form-control" disabled>
                                            <option value="">Selecione</option>

                                            @if ($tamanhos && count($tamanhos) > 0)
                                                @foreach ($tamanhos as $tamanho)
                                                    <option value="{{ $tamanho->id }}"
                                                        {{ isset($produto) && $produto->tamanho == $tamanho->id ? 'selected' : '' }}>
                                                        {{ $tamanho->tamanho }}
                                                    </option>
                                                @endforeach
                                            @else
                                                <option disabled selected>Nenhum tamanho disponível</option>
                                            @endif

                                        </select>
                                    </div>

                                    <div class="form-group has-feedback col-md-6">
                                        <label class="control-label" for="valor">Valor (R$):</label>
                                        <input type="text" class="form-control" name="valor"
                                            value="{{ number_format((float) $produto->valor, 2, ',', '.') }}" disabled>

                                    </div>
                                </div>
                            </div>
                            <div class="box-footer pull-right">
                                <a href="{{ route('produtos.listar') }}" class="btn btn-danger">
                                    <i class="fa fa-arrow-left"></i> Voltar
                                </a>

                                <a class="btn btn-warning" href="{{ route('produto.editar', $produto->id) }}"
                                    style="color: white;">
                                    <i class="fa fa-edit"></i> Editar
                                </a>

                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="box box-primary">
                                <div class="box-header">
                                    IMAGEM DO PRODUTO
                                </div>
                                <div class="box-body text-center">
                                    <img src="{{ asset('/storage/' . $produto->imagem) }}" alt="Imagem do Produto"
                                        class="img-responsive" style="max-width: 100%;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

    </div>
    </div>

    <script>
        document.getElementById("tipo").addEventListener("change", function() {
            var tipoSelecionado = this.value;
            var campoOutros = document.getElementById("campoOutros");
            var camposTransferencia = document.getElementById("camposTransferencia");

            if (tipoSelecionado === "outros") {
                campoOutros.style.display = "block";
                camposTransferencia.style.display = "none";
            } else {
                campoOutros.style.display = "none";
                camposTransferencia.style.display = "block";
            }
        });
    </script>


    <style>
        .imagem-redimensionada {
            width: 500px;
            /* Define a largura */
            height: auto;
            /* Mantém a proporção */
        }
    </style>




    </section>
    <!-- /.content -->
    </div>

    <!-- /.content-wrapper -->
@endsection
