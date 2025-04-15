@extends('layout/app')
@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                Cadastro de Produto
            </h1>
            <ol class="breadcrumb">
                <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
                <li><a href="{{ route('estoque.listar') }}"><i class=""></i> Estoque</a></li>
                <li></i> Cadastro de Produto</li>
            </ol>
        </section>
        <!-- Main content -->
        <section class="content container-fluid">

            <div class="panel" style="background-color: #3c8dbc;">
                <div class="panel-heading" style="color: white;">


                </div>
                <div class="panel-body" style="background-color: white;">
                    <form action="{{ route('produto.cadastrar') }}" method="post" enctype="multipart/form-data">
                        <div class="row">

                            <div class="col-md-6">

                                @csrf

                                <div class="box box-primary">
                                    <div class="box-header">
                                        DADOS DO PRODUTO
                                    </div>
                                    <div class="box-body">
                                        <div class="form-group has-feedback col-md-6">
                                            <label class="control-label" for="">Nome:</label>
                                            <input type="text" class="form-control" placeholder="" name="nome"
                                                value="" required>
                                        </div>
                                        <div class="form-group has-feedback col-md-6">
                                            <label class="control-label" for="">Marca:</label>
                                            <input type="text" class="form-control" placeholder="" name="marca"
                                                value="" required>
                                        </div>

                                        <div class="form-group has-feedback col-md-6">
                                            <label class="control-label" for="">Descrição:</label>
                                            <input type="text" class="form-control" placeholder="" name="descricao"
                                                value="" required>
                                        </div>



                                        <div class="form-group has-feedback col-md-6">
                                            <label class="control-label" for="tipoproduto">Tipo Produto</label>
                                            <select name="tipoproduto" id="tipoproduto" class="form-control" required>
                                                <option value="">Escolha um Tipo</option>
                                                @foreach ($tipoprodutos as $tipoproduto)
                                                    <option value="{{ $tipoproduto->id }}">
                                                        {{ $tipoproduto->nome }}</option>
                                                @endforeach
                                            </select>
                                        </div>


                                        <div class="form-group has-feedback col-md-6">

                                            <label class="control-label" for="">Condição</label>
                                            <select name="condicao" id="" class="form-control" required>
                                                <option value="">Selecione</option>
                                                @foreach ($condicoes as $condicao)
                                                    <option value="{{ $condicao->id }}">{{ $condicao->condicao }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="form-group has-feedback col-md-6">

                                            <label class="control-label" for="">Kit (se houver)</label>
                                            <select name="kit" id="" class="form-control">
                                                <option value="">Selecione</option>
                                                @foreach ($kits as $kit)
                                                    <option value="{{ $kit->id }}">{{ $kit->nome }}</option>
                                                @endforeach
                                            </select>
                                        </div>


                                        <div class="form-group has-feedback col-md-6">
                                            <label class="control-label" for="tamanho">Tamanho (Se houver)</label>
                                            <select name="tamanho" class="form-control">
                                                <option value="">Selecione</option>
                                                @foreach ($tamanhos as $tamanho)
                                                    <option value="{{ $tamanho->id }}">

                                                        {{ $tamanho->tamanho }}
                                                    </option>

                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="form-group has-feedback col-md-6">
                                            <label class="control-label" for="valor">Valor (R$):</label>
                                            <input type="text" class="form-control" placeholder="0,00" name="valor_formatado" id="valor" required>
                                            <input type="hidden" name="valor" id="valor_limpo">
                                        </div>


                                    </div>
                                </div>
                                <div class="box-foote pull-right">
                                    <a href="{{ route('produtos.listar') }}" class="btn btn-danger"><i
                                            class="fa fa-close"></i>
                                        Cancelar</a>
                                    <button class="btn btn-success"><i class="fa fa-save"></i> Cadastrar</button>
                                </div>
                            </div>

                    </form>
                </div>

            </div>

        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    <script>
        document.getElementById('valor').addEventListener('input', function (e) {
            let raw = e.target.value.replace(/\D/g, ''); // só números
            let valorCentavos = raw ? parseInt(raw, 10) : 0;

            // Atualiza o campo hidden com valor em centavos
            document.getElementById('valor_limpo').value = valorCentavos;

            // Atualiza o campo visível formatado com vírgula e ponto
            let valorFormatado = (valorCentavos / 100).toFixed(2)
                .replace('.', ',')
                .replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            e.target.value = valorFormatado;
        });
    </script>


@endsection
