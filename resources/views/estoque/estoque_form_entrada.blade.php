@extends('layout/app')
@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                Entrada <b> {{ $produto->produto()->first()->nome ?? '' }} - {{$produto->produto()->first()->tamanho()->first()->tamanho}} </b> no Estoque. <br>
                <small>Unidade: {{ $produto->unidade()->first()->nome }}</small>

            </h1>
            <ol class="breadcrumb">
                <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
                <li><a href="{{ route('estoque.listar') }}"><i class=""></i> Estoque</a></li>
                <li></i>Entrada de Produtos</li>

            </ol>
        </section>

        <!-- Main content -->
        <section class="content container-fluid">

            <div class="panel" style="background-color: #3c8dbc;">
                <div class="panel-heading" style="color: white;">


                </div>
                <div class="panel-body" style="background-color: white;">
                    <form action="{{ route('estoque.entrada') }}" method="POST">
                        @csrf

                        <div class="row">
                            <!-- Produto -->

                            <div class="form-group col-md-4">
                                <label for="fk_produto">Produto:</label>
                                <input type="text" class="form-control"
                                    value="{{ $produto->produto()->first()->nome ?? '' }} - {{$produto->produto()->first()->tamanho()->first()->tamanho}}" disabled>
                            </div>
                            <div>
                                <!-- Campo oculto com o ID do produto (será enviado no form) -->
                                <input type="hidden" name="fk_produto" value="{{ $produto->fk_produto }}">

                            </div>
                            <div>
                                <!-- Campo oculto com o ID do produto (será enviado no form) -->
                                <input type="hidden" name="unidade" value="{{ $produto->unidade }}">

                            </div>

                            <!-- Lote -->
                            <div class="form-group col-md-4">
                                <label for="lote">Lote:</label>
                                <input type="text" name="lote" class="form-control" placeholder="Ex: LOTE123">
                            </div>
                               <!-- Data de Entrada -->
                               <div class="form-group col-md-4">
                                <label for="data_entrada">Data de Entrada:</label>
                                <input type="date" name="data_entrada" class="form-control" required>
                            </div>
                            <!-- Quantidade -->
                            <div class="form-group col-md-2">
                                <label for="quantidade">Quantidade:</label>
                                <input type="number" name="quantidade" class="form-control" required min="1"
                                    placeholder="Digite a quantidade">
                            </div>

                            <!-- Fornecedor -->
                            <div class="form-group col-md-3">
                                <label for="fornecedor">Fornecedor:</label>
                                <input type="text" name="fornecedor" class="form-control"
                                    placeholder="Nome do Fornecedor">
                            </div>

                            <!-- Nota Fiscal -->
                            <div class="form-group col-md-3">
                                <label for="nota_fiscal">Número da Nota Fiscal:</label>
                                <input type="text" name="nota_fiscal" class="form-control" placeholder="Ex: 00012345">
                            </div>

                            <!-- Observações -->
                            <div class="form-group col-md-12">
                                <label for="fornecedor">Observações:</label>
                                <input type="text" name="observacao" class="form-control"
                                    placeholder="Nome do Fornecedor">
                            </div>





                        </div>

                        <!-- Botões -->
                        <div class="form-group text-right">
                            <a href="{{ route('estoque.listar') }}" class="btn btn-danger"><i class="fa fa-arrow-left"></i>
                                Cancelar</a>
                            <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Cadastrar</button>
                        </div>
                    </form>
                </div>

            </div>

        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    <script>
        $(document).ready(function() {});


        document.getElementById('valor').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = (parseInt(value, 10) / 100).toLocaleString('pt-BR', {
                minimumFractionDigits: 2
            });
            e.target.value = value !== "NaN" ? value : "";
        });
    </script>
@endsection
