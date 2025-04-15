@extends('layout/app')
@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                Saída <b> {{ $produto->produto()->first()->nome ?? '' }} - {{$produto->produto()->first()->tamanho()->first()->tamanho}} </b> no Estoque. <br>
                <small>Unidade: {{ $produto->unidade()->first()->nome }}</small>

            </h1>
            <ol class="breadcrumb">
                <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
                <li><a href="{{ route('estoque.listar') }}"><i class=""></i> Estoque</a></li>
                <li></i>Saída de Produtos</li>

            </ol>
        </section>

        <!-- Main content -->
        <section class="content container-fluid">

            <div class="panel" style="background-color: #e61212;">
                <div class="panel-heading" style="color: white;">


                </div>
                <div class="panel-body" style="background-color: white;">
                    <form action="{{ route('estoque.saida') }}" method="POST">
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


                               <!-- Data de Saída -->
                               <div class="form-group col-md-4">
                                <label for="data_entrada">Data de Saída:</label>
                                <input type="datetime-local" name="data_saida" class="form-control" required>

                            </div>
                            <!-- Quantidade -->
                            <div class="form-group col-md-4">
                                <label for="quantidade">Quantidade:</label>
                                <input type="number" name="quantidade" class="form-control" required min="1"
                                    placeholder="Digite a quantidade">
                            </div>


                            <div class="form-group col-md-12">
                                <label for="militar">Entregue para o Militar:</label>
                                <select name="militar" class="form-control select2" required>
                                    <option value="">Selecione </option>
                                    @foreach($militares as $militar)
                                        <option value="{{ $militar->id }}">{{ $militar->nome }}</option>
                                    @endforeach
                                </select>
                            </div>



                            <!-- Observações -->
                            <div class="form-group col-md-12">
                                <label for="fornecedor">Observações:</label>
                                <input type="text" name="observacao" class="form-control"
                                    placeholder="Detalhes da saída do produto">
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

<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "Selecione um Militar",
            allowClear: true,
            width: '100%'
        });
    });
</script>
@endsection
