@extends('layout/app')

@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>Entrada de Novo Produto no Estoque</h1>
            <ol class="breadcrumb">
                <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
                <li><a href="{{ route('estoque.listar') }}">Estoque</a></li>
                <li class="active">Entrada de Produtos</li>
            </ol>
        </section>

        <!-- Main content -->
        <section class="content container-fluid">
            <div class="panel panel-default">
                <div class="panel-heading bg-primary text-white">
                    <h3 class="panel-title">Cadastro de Entrada</h3>
                </div>
                <div class="panel-body" style="background-color: white;">
                    <form action="{{ route('estoque.entrada_novoproduto') }}" method="POST">
                        @csrf

                        <div class="row">
                            <!-- Unidade -->
                            <div class="form-group col-md-4">
                                <label for="unidade">Unidade:</label>
                                <input type="text" value="{{ Auth::user()->unidade->nome }}" class="form-control" disabled>
                                <input type="hidden" name="unidade" value="{{ Auth::user()->fk_unidade }}">
                            </div>

                            <!-- Produto -->
                            <div class="form-group col-md-4">
                                <label for="fk_produto">Produto:</label>
                                <select name="fk_produto" class="form-control select2-produto" required>
                                    <option value="">Selecione um Produto</option>
                                    @foreach ($produtos as $produto)
                                        <option value="{{ $produto->id }}">{{ $produto->nome }} -  {{ optional($produto->tamanho()->first())->tamanho ?? 'Tamanho Único' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>



                            <!-- Data de Entrada -->
                            <div class="form-group col-md-4">
                                <label for="data_entrada">Data de Entrada:</label>
                                <input type="date" name="data_entrada" class="form-control" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="data_trp">Data TRP:</label>
                                <input type="date" name="data_trp" class="form-control">
                            </div>

                            <!-- Lote -->
                            <div class="form-group col-md-4">
                                <label for="lote">Lote:</label>
                                <input type="text" name="lote" class="form-control" placeholder="Ex: LOTE123">
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
                            <div class="form-group col-md-3">
                                <label for="sei">Número do Processo SEI:</label>
                                <input type="text" name="sei" class="form-control"
                                    placeholder="Número do Processo SEI">
                            </div>

                            <!-- Nota Fiscal -->
                            <div class="form-group col-md-3">
                                <label for="nota_fiscal">Número da Nota Fiscal:</label>
                                <input type="text" name="nota_fiscal" class="form-control" placeholder="Ex: 00012345">
                            </div>
                            <div class="form-group col-md-3">
                                <label for="fonte">Fonte:</label>
                                <input type="text" name="fonte" class="form-control" list="fontes" placeholder="">
                                <datalist id="fontes">
                                    <option value="SENASP">
                                    <option value="SEJUSP">
                                    <option value="VINCI">
                                    <option value="100">
                                    <option value="700">
                                    <option value="DOAÇÃO">
                                    <option value="FUNDO A FUNDO">
                                    <option value="OUTROS">
                                </datalist>
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
                            <a href="{{ route('estoque.listar') }}?nome=&categoria=&unidade={{ Auth::user()->fk_unidade }}" class="btn btn-danger">
                                Cancelar <i class="fa fa-arrow-left"></i>
                            </a>
                            <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Cadastrar</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>

    <!-- Scripts -->


    <script>
        $(document).ready(function() {
            $('.select2-produto').select2({
                placeholder: "Selecione um Produto",
                allowClear: true,
                width: '100%'
            });
        });
    </script>
@endsection
