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

                    <form action="{{ route('estoque.entrada') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <!-- Produto -->
                            <div class="form-group col-md-6">
                                <label for="fk_produto">Produto <span style="color: red;">*</span>:</label>
                                <select name="fk_produto" id="fk_produto" class="form-control" required>
                                    <option value="">-- Selecione um produto --</option>
                                    @foreach($produtos as $produto)
                                        <option value="{{ $produto->id }}" data-tipo="{{ $produto->tipo_controle }}" {{ old('fk_produto') == $produto->id ? 'selected' : '' }}>
                                            {{ $produto->nome }}
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
                            <!-- Unidade (fixa para o usuário) -->
                            <input type="hidden" name="unidade" value="{{ Auth::user()->fk_unidade }}">

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

                        <div class="row" style="margin-top: 10px;" id="fotos-consumo-row">
                            <div class="form-group col-md-12">
                                <label for="fotos_upload">Fotos do Item (até 3 imagens):</label>
                                <input type="file" id="fotos_upload" name="fotos[]" class="form-control" multiple accept="image/*">
                                <small class="text-muted">Formatos aceitos: JPG, PNG, GIF (máx 5MB por imagem).</small>
                            </div>
                        </div>

                        <!-- Patrimônios (somente permanente) -->
                        <div class="row" id="patrimonios-row" style="display: none; margin-top: 20px;">
                            <div class="form-group col-md-12">
                                <label style="font-size: 18px; font-weight: bold;">
                                    <i class="fa fa-barcode"></i> Patrimônios <span style="color: red;">*</span>
                                </label>
                                <div id="patrimonios-container"></div>
                                <button type="button" id="btn-add-patrimonio" class="btn btn-sm btn-info" style="margin-top: 8px;">
                                    <i class="fa fa-plus"></i> Adicionar Patrimônio
                                </button>
                                <small class="text-muted d-block" style="margin-top: 6px;">A quantidade será igual ao número de patrimônios informados.</small>
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
        document.getElementById('fotos_upload').addEventListener('change', function() {
            if (this.files && this.files.length > 3) {
                alert('Selecione no máximo 3 imagens.');
                this.value = '';
            }
        });

        document.addEventListener('change', function(e) {
            if (e.target && e.target.classList.contains('patrimonio-fotos')) {
                if (e.target.files && e.target.files.length > 2) {
                    alert('Selecione no máximo 2 imagens por patrimônio.');
                    e.target.value = '';
                }
            }
        });

        // Dados de containers por seção vindos do backend
        const containersPorSecao = @json($todosContainers ?? []);

        // Atualiza containers quando a seção mudar
        document.getElementById('fk_secao').addEventListener('change', function() {
            const secaoId = this.value;
            const containerRow = document.getElementById('container-row');
            const containerSelect = document.getElementById('fk_item_pai');
            
            // Limpa opções anteriores
            containerSelect.innerHTML = '<option value="">-- Nenhum (Item solto na seção) --</option>';
            
            if (secaoId && containersPorSecao[secaoId]) {
                // Adiciona containers dessa seção
                containersPorSecao[secaoId].forEach(function(container) {
                    const option = document.createElement('option');
                    option.value = container.id;
                    option.textContent = container.produto ? container.produto.nome : 'Container #' + container.id;
                    containerSelect.appendChild(option);
                });
                
                // Mostra o campo de container
                containerRow.style.display = 'block';
            } else {
                // Esconde se não houver containers
                containerRow.style.display = 'none';
            }
        });

        function getProdutoTipo() {
            const select = document.getElementById('fk_produto');
            const option = select.options[select.selectedIndex];
            return option ? option.getAttribute('data-tipo') : null;
        }

        let patrimonioIndex = 0;

        function syncQuantidadeFromPatrimonios() {
            const count = document.querySelectorAll('.patrimonio-item').length;
            const quantidadeInput = document.getElementById('quantidade');
            quantidadeInput.value = count > 0 ? count : '';
        }

        function addPatrimonioRow(valor = '', observacao = '') {
            const container = document.getElementById('patrimonios-container');
            const rowId = `patrimonio-${patrimonioIndex++}`;
            const index = patrimonioIndex - 1;

            const row = document.createElement('div');
            row.className = 'patrimonio-item';
            row.id = rowId;
            row.style.marginBottom = '8px';
            row.innerHTML = `
                <div class="row">
                    <div class="form-group col-md-4">
                        <input type="text" name="patrimonios[]" class="form-control" placeholder="Patrimônio" value="${valor}" required>
                    </div>
                    <div class="form-group col-md-6">
                        <input type="text" name="patrimonios_observacoes[]" class="form-control" placeholder="Observação" value="${observacao}">
                    </div>
                    <div class="form-group col-md-6">
                        <input type="file" name="patrimonios_fotos[${index}][]" class="form-control patrimonio-fotos" multiple accept="image/*">
                        <small class="text-muted">Até 2 fotos por patrimônio.</small>
                    </div>
                    <div class="form-group col-md-2">
                        <button type="button" class="btn btn-danger btn-sm" onclick="removePatrimonioRow('${rowId}')">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;
            container.appendChild(row);
            syncQuantidadeFromPatrimonios();
        }

        function removePatrimonioRow(rowId) {
            const row = document.getElementById(rowId);
            if (row) row.remove();
            syncQuantidadeFromPatrimonios();
        }

        function reindexPatrimoniosFotos() {
            const rows = document.querySelectorAll('.patrimonio-item');
            rows.forEach(function(row, index) {
                const fileInput = row.querySelector('input.patrimonio-fotos');
                if (fileInput) {
                    fileInput.name = `patrimonios_fotos[${index}][]`;
                }
            });
        }

        function togglePatrimonios() {
            const tipo = getProdutoTipo();
            const row = document.getElementById('patrimonios-row');
            const quantidadeInput = document.getElementById('quantidade');
            const fotosConsumo = document.getElementById('fotos-consumo-row');
            if (tipo === 'permanente') {
                row.style.display = 'block';
                quantidadeInput.readOnly = true;
                if (fotosConsumo) fotosConsumo.style.display = 'none';
                if (document.querySelectorAll('.patrimonio-item').length === 0) {
                    addPatrimonioRow();
                } else {
                    syncQuantidadeFromPatrimonios();
                }
            } else {
                row.style.display = 'none';
                document.getElementById('patrimonios-container').innerHTML = '';
                quantidadeInput.readOnly = false;
                if (fotosConsumo) fotosConsumo.style.display = 'block';
            }
        }

        document.getElementById('fk_produto').addEventListener('change', togglePatrimonios);
        document.getElementById('btn-add-patrimonio').addEventListener('click', function() {
            addPatrimonioRow();
        });

        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function() {
                reindexPatrimoniosFotos();
            });
        }

        togglePatrimonios();

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
