@extends('layout/app')
@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                Inserir Novo Produto no Estoque

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
                    <form action="{{ route('estoque.entrada_novoproduto') }}" method="POST">
                        @csrf

                        <div class="row">

                             <!-- Unidade -->
                             <div class="form-group col-md-6">
                                <label for="unidade">Unidade:</label>
                                <select name="unidade" class="form-control" required>
                                    <option value="">Estoque da Unidade</option>
                                    @foreach ($unidades as $unidade)
                                        <option value="{{ $unidade->id }}">{{ $unidade->nome }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Produto -->
                            <div class="form-group col-md-6">
                                <label for="fk_produto">Produto:</label>
                                <select name="fk_produto" class="form-control" required>
                                    <option value="">Selecione um Produto</option>
                                    @foreach ($produtos as $produto)
                                        <option value="{{ $produto->id }}">{{ $produto->nome }} - {{ $produto->tamanho }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <!-- Quantidade -->
                            <div class="form-group col-md-6">
                                <label for="quantidade">Quantidade:</label>
                                <input type="number" name="quantidade" class="form-control" required min="1"
                                    placeholder="Digite a quantidade">
                            </div>

                            <!-- Data de Entrada -->
                            <div class="form-group col-md-6">
                                <label for="data_entrada">Data de Entrada:</label>
                                <input type="date" name="data_entrada" class="form-control" required>
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
