@extends('layout/app')
@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                Entrada <b> {{ $produto->produto()->first()->nome ?? '' }} -
                    {{ optional($produto->produto()->first()?->tamanho()->first())->tamanho ?? 'Tamanho Único' }}

                </b> no Estoque. <br>
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
                                    value="{{ $produto->produto()->first()->nome ?? '' }} -   {{ optional($produto->produto()->first()?->tamanho()->first())->tamanho ?? 'Tamanho Único' }}"
                                    disabled>
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
                            <div class="form-group col-md-4">
                                <label for="data_trp">Data TRP:</label>
                                <input type="date" name="data_trp" class="form-control">
                            </div>
                            <!-- Quantidade -->
                            <div class="form-group col-md-2">
                                <label for="quantidade">Quantidade:</label>
                                <input type="number" name="quantidade" class="form-control" required min="1"
                                    placeholder="Digite a quantidade">
                            </div>


                            <div class="form-group has-feedback col-md-6">
                                <label class="control-label" for="valor">Preço Unitário (R$):</label>
                                <input type="text" class="form-control" placeholder="0,00" name="valor_formatado"
                                    id="valor" required>
                                <input type="hidden" name="valor" id="valor_limpo">
                            </div>

                        </div>



                        <!-- Fornecedor -->
                        <div class="form-group col-md-3">
                            <label for="fornecedor">Fornecedor:</label>
                            <input type="text" name="fornecedor" class="form-control" placeholder="Nome do Fornecedor">
                        </div>
                        <div class="form-group col-md-3">
                            <label for="sei">Número do Processo SEI:</label>
                            <input type="text" name="sei" class="form-control" placeholder="Número do Processo SEI">
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
                            <input type="text" name="observacao" class="form-control" placeholder="Nome do Fornecedor">
                        </div>





                </div>
            </div>

            <!-- Botões -->
            <div class="form-group text-right">
                <a href="{{ route('estoque.listar') }}?nome=&categoria=&unidade={{ Auth::user()->fk_unidade }}"
                    class="btn btn-danger">
                    Cancelar <i class="fa fa-arrow-left"></i>
                </a>
                <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Cadastrar</button>
            </div>
            </form>


    </div>

    </section>
    <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    <script>
        document.getElementById('valor').addEventListener('input', function(e) {
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
