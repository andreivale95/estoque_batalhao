@extends('layout/app')

@section('content')
    <div class="content-wrapper">
        <!-- Content Header -->
        <section class="content-header">
            <h1>Registrar Entrada de Produto</h1>
            <ol class="breadcrumb">
                <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
                <li><a href="{{ route('estoque.listar') }}"><i class=""></i> Estoque</a></li>
                <li class="active">Entrada</li>
            </ol>
        </section>

        <!-- Main content -->
        <section class="content container-fluid">
            <div class="panel" style="background-color: #3c8dbc;">
                <div class="panel-heading" style="color: white;">
                    <h3 class="panel-title">Adicionar Quantidade ao Estoque</h3>
                </div>
                <div class="panel-body" style="background-color: white;">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('estoque.entrada') }}" method="POST">
                        @csrf

                        <div class="row">
                            <!-- Produto -->
                            <div class="form-group col-md-6">
                                <label for="fk_produto">Produto <span style="color: red;">*</span>:</label>
                                <select name="fk_produto" id="fk_produto" class="form-control" required>
                                    <option value="">-- Selecione um produto --</option>
                                    @foreach($produtos as $produto)
                                        <option value="{{ $produto->id }}" {{ old('fk_produto') == $produto->id ? 'selected' : '' }}>
                                            {{ $produto->nome }} ({{ $produto->unidade }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Seção -->
                            <div class="form-group col-md-6">
                                <label for="fk_secao">Seção <span style="color: red;">*</span>:</label>
                                <select name="fk_secao" id="fk_secao" class="form-control" required>
                                    <option value="">-- Selecione a Seção --</option>
                                    @foreach($secoes as $secao)
                                        <option value="{{ $secao->id }}" {{ old('fk_secao') == $secao->id ? 'selected' : '' }}>
                                            {{ $secao->nome }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Unidade -->
                            <div class="form-group col-md-4">
                                <label for="unidade">Unidade <span style="color: red;">*</span>:</label>
                                @if($isAdmin)
                                    <select name="unidade" id="unidade" class="form-control" required>
                                        <option value="">-- Selecione a Unidade --</option>
                                        @foreach($unidades as $unidade)
                                            <option value="{{ $unidade->id }}" {{ old('unidade', Auth::user()->fk_unidade) == $unidade->id ? 'selected' : '' }}>
                                                {{ $unidade->nome }}
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    <input type="hidden" name="unidade" value="{{ Auth::user()->fk_unidade }}">
                                    <input type="text" class="form-control" value="{{ $unidadeUsuario->nome ?? 'Unidade não encontrada' }}" disabled>
                                @endif
                            </div>

                            <!-- Data de Entrada -->
                            <div class="form-group col-md-4">
                                <label for="data_entrada">Data de Entrada <span style="color: red;">*</span>:</label>
                                <input type="date" name="data_entrada" id="data_entrada" class="form-control" 
                                    value="{{ old('data_entrada', now()->format('Y-m-d')) }}" required>
                            </div>

                            <!-- Lote -->
                            <div class="form-group col-md-4">
                                <label for="lote">Lote:</label>
                                <input type="text" name="lote" id="lote" class="form-control" 
                                    placeholder="Ex: LOTE123" value="{{ old('lote') }}">
                            </div>
                        </div>

                        <div class="row">
                            <!-- Quantidade -->
                            <div class="form-group col-md-3">
                                <label for="quantidade">Quantidade <span style="color: red;">*</span>:</label>
                                <input type="number" name="quantidade" id="quantidade" class="form-control" 
                                    min="1" placeholder="Digite a quantidade" value="{{ old('quantidade') }}" required>
                            </div>

                            <!-- Valor Unitário -->
                            <div class="form-group col-md-3">
                                <label for="valor">Preço Unitário (R$) <span style="color: red;">*</span>:</label>
                                <input type="text" name="valor_formatado" id="valor" class="form-control" 
                                    placeholder="0,00" value="{{ old('valor_formatado') }}" required>
                                <input type="hidden" name="valor" id="valor_limpo">
                            </div>

                            <!-- Fornecedor -->
                            <div class="form-group col-md-3">
                                <label for="fornecedor">Fornecedor:</label>
                                <input type="text" name="fornecedor" id="fornecedor" class="form-control" 
                                    placeholder="Nome do Fornecedor" value="{{ old('fornecedor') }}">
                            </div>

                            <!-- Data TRP -->
                            <div class="form-group col-md-3">
                                <label for="data_trp">Data TRP:</label>
                                <input type="date" name="data_trp" id="data_trp" class="form-control" 
                                    value="{{ old('data_trp') }}">
                            </div>
                        </div>

                        <div class="row">
                            <!-- Nota Fiscal -->
                            <div class="form-group col-md-3">
                                <label for="nota_fiscal">Número da Nota Fiscal:</label>
                                <input type="text" name="nota_fiscal" id="nota_fiscal" class="form-control" 
                                    placeholder="Ex: 00012345" value="{{ old('nota_fiscal') }}">
                            </div>

                            <!-- SEI -->
                            <div class="form-group col-md-3">
                                <label for="sei">Número do Processo SEI:</label>
                                <input type="text" name="sei" id="sei" class="form-control" 
                                    placeholder="Número do Processo SEI" value="{{ old('sei') }}">
                            </div>

                            <!-- Fonte -->
                            <div class="form-group col-md-3">
                                <label for="fonte">Fonte:</label>
                                <input type="text" name="fonte" id="fonte" class="form-control" 
                                    list="fontes" placeholder="" value="{{ old('fonte') }}">
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
                            <div class="form-group col-md-3">
                                <label for="observacao">Observações:</label>
                                <input type="text" name="observacao" id="observacao" class="form-control" 
                                    placeholder="Observações sobre a entrada" value="{{ old('observacao') }}">
                            </div>
                        </div>

                        <!-- Botões -->
                        <div class="form-group text-right">
                            <a href="{{ route('estoque.listar') }}" class="btn btn-danger">
                                Cancelar <i class="fa fa-arrow-left"></i>
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fa fa-save"></i> Registrar Entrada
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>

    <script>
        document.getElementById('valor').addEventListener('input', function(e) {
            let raw = e.target.value.replace(/\D/g, ''); // só números
            let valorCentavos = raw ? parseInt(raw, 10) : 0;

            // Atualiza o campo hidden com valor em centavos
            document.getElementById('valor_limpo').value = valorCentavos;

            // Formata para exibição
            let valor_centavos_float = valorCentavos / 100;
            e.target.value = valor_centavos_float.toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        });
    </script>
@endsection
