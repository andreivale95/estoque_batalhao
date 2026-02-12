@extends('layout/app')
@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                Entrada de Itens no Estoque
                <small>Unidade: {{ $unidadeUsuario->nome ?? 'Unidade Padrão' }}</small>
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
                    <form action="{{ route('estoque.entrada') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- Configurações Gerais -->
                        <div class="row">
                            @if($isAdmin)
                                <!-- Unidade editável para admin -->
                                <div class="form-group col-md-4">
                                    <label for="unidade">Unidade:</label>
                                    <select name="unidade" id="unidade" class="form-control" required>
                                        <option value="">-- Selecione a Unidade --</option>
                                        @php
                                            $unidades = \App\Models\Unidade::all();
                                        @endphp
                                        @foreach($unidades as $unidade)
                                            <option value="{{ $unidade->id }}" {{ $produto->unidade == $unidade->id ? 'selected' : '' }}>
                                                {{ $unidade->nome }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @else
                                <!-- Unidade fixa para não-admin -->
                                <div class="form-group col-md-4">
                                    <label for="unidade">Unidade:</label>
                                    <input type="hidden" name="unidade" value="{{ $produto->unidade }}">
                                    <input type="text" class="form-control" 
                                        value="{{ $unidadeUsuario->nome ?? 'Unidade não encontrada' }}" disabled>
                                </div>
                            @endif

                            <!-- Fornecedor -->
                            <div class="form-group col-md-4">
                                <label for="fornecedor">Fornecedor:</label>
                                <input type="text" name="fornecedor" class="form-control" placeholder="Nome do Fornecedor">
                            </div>

                            <!-- Nota Fiscal -->
                            <div class="form-group col-md-4">
                                <label for="nota_fiscal">Número da Nota Fiscal:</label>
                                <input type="text" name="nota_fiscal" class="form-control" placeholder="Ex: 00012345">
                            </div>

                            <!-- Número do Processo SEI -->
                            <div class="form-group col-md-4">
                                <label for="sei">Número do Processo SEI:</label>
                                <input type="text" name="sei" class="form-control" placeholder="Número do Processo SEI">
                            </div>

                            <!-- Fonte -->
                            <div class="form-group col-md-4">
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

                            <!-- Observações Gerais -->
                            <div class="form-group col-md-12">
                                <label for="observacao">Observações Gerais:</label>
                                <input type="text" name="observacao" class="form-control" placeholder="Observações">
                            </div>
                        </div>

                        <hr>
                        <h4>Adicionar Itens</h4>

                        <!-- Seletor de Tipo -->
                        <div class="alert alert-info">
                            <strong>Tipo de Entrada:</strong>
                            <div style="margin-top: 10px;">
                                <label style="margin-right: 30px;">
                                    <input type="radio" name="tipo_entrada" value="consumo" class="tipo-entrada-radio" checked> 
                                    <strong>Itens de Consumo</strong>
                                </label>
                                <label>
                                    <input type="radio" name="tipo_entrada" value="permanente" class="tipo-entrada-radio"> 
                                    <strong>Itens Permanentes (Patrimôniados)</strong>
                                </label>
                            </div>
                        </div>

                        <p style="color: #d9534f; font-weight: bold;">
                            ⚠️ Selecione o tipo antes de adicionar itens. Não é permitido misturar tipos na mesma entrada.
                        </p>

                        <!-- Tabela de itens -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="itensTable">
                                <thead style="background-color: #3c8dbc; color: white;">
                                    <tr>
                                        <th width="15%">Produto</th>
                                        <th width="8%">Quantidade</th>
                                        <th width="10%">Patrimônios</th>
                                        <th width="10%">Valor Unit.</th>
                                        <th width="10%">Data Entrada</th>
                                        <th width="10%">Seção</th>
                                        <th width="10%">Lote</th>
                                        <th width="8%">Ação</th>
                                    </tr>
                                </thead>
                                <tbody id="itensBody">
                                </tbody>
                            </table>
                        </div>

                        <button type="button" class="btn btn-primary" id="addItemBtn">
                            <i class="fa fa-plus"></i> Adicionar Item
                        </button>

                        <hr>

                        <!-- Botões de Ação -->
                        <div class="form-group text-right">
                            <a href="{{ route('estoque.listar') }}?nome=&categoria=&unidade={{ Auth::user()->fk_unidade }}"
                                class="btn btn-danger">
                                Cancelar <i class="fa fa-arrow-left"></i>
                            </a>
                            <button type="submit" class="btn btn-success" id="submitBtn" disabled>
                                <i class="fa fa-save"></i> Cadastrar Itens
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->
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
        const secoes = @json($secoes);
        const produtos = @json(\App\Models\Produto::all());
        let tipoEntradaSelecionado = 'consumo';
        let itemCount = 0;

        // Detectar mudança de tipo de entrada
        document.querySelectorAll('.tipo-entrada-radio').forEach(radio => {
            radio.addEventListener('change', function() {
                tipoEntradaSelecionado = this.value;
                
                // Se houver itens, avisar
                const temItens = document.querySelectorAll('.item-row').length > 0;
                if (temItens) {
                    alert('Todos os itens serão removidos ao mudar o tipo de entrada.');
                    document.getElementById('itensBody').innerHTML = '';
                    atualizarSubmitBtn();
                }
            });
        });

        // Filtrar produtos por tipo
        function obterProdutosFiltrados() {
            return produtos.filter(p => p.tipo_controle === tipoEntradaSelecionado);
        }

        // Adicionar novo item
        document.getElementById('addItemBtn').addEventListener('click', function() {
            const produtosFiltrados = obterProdutosFiltrados();
            
            if (produtosFiltrados.length === 0) {
                const tipoNome = tipoEntradaSelecionado === 'permanente' ? 'Itens Permanentes' : 'Itens de Consumo';
                alert(`Não há ${tipoNome} cadastrados no sistema.`);
                return;
            }

            const row = document.createElement('tr');
            row.className = 'item-row';
            row.innerHTML = `
                <td>
                    <select name="produtos[]" class="form-control produto-select" required>
                        <option value="">-- Selecione --</option>
                        ${produtosFiltrados.map(p => `<option value="${p.id}" data-tipo="${p.tipo_controle}">${p.nome}</option>`).join('')}
                    </select>
                </td>
                <td>
                    <input type="number" name="quantidades[]" class="form-control quantidade-input" required min="1" value="1">
                </td>
                <td>
                    <div class="patrimonios-cell" style="display:${tipoEntradaSelecionado === 'permanente' ? 'block' : 'none'};">
                        <input type="text" name="patrimonios[]" class="form-control patrimonios-input" placeholder="Separar por vírgula">
                        <small class="text-muted">Ex: 001,002,003</small>
                    </div>
                </td>
                <td>
                    <input type="text" name="valores[]" class="form-control valor-input" placeholder="0,00">
                    <input type="hidden" name="valores_centavos[]" class="valor-centavos">
                </td>
                <td>
                    <input type="date" name="datas_entrada[]" class="form-control" required>
                </td>
                <td>
                    <select name="secoes[]" class="form-control" required>
                        <option value="">-- Selecione --</option>
                        ${secoes.map(s => `<option value="${s.id}">${s.nome}</option>`).join('')}
                    </select>
                </td>
                <td>
                    <input type="text" name="lotes[]" class="form-control" placeholder="Lote">
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger remove-item">
                        <i class="fa fa-trash"></i>
                    </button>
                </td>
            `;

            document.getElementById('itensBody').appendChild(row);
            
            // Forçar data atual
            const dataInputs = row.querySelectorAll('input[type="date"]');
            dataInputs.forEach(input => {
                if (!input.value) {
                    const today = new Date().toISOString().split('T')[0];
                    input.value = today;
                }
            });

            // Detectar mudança de produto
            const produtoSelect = row.querySelector('.produto-select');
            produtoSelect.addEventListener('change', function() {
                const tipo = this.options[this.selectedIndex]?.dataset?.tipo;
                const quantidadeInput = row.querySelector('.quantidade-input');
                
                if (tipo === 'permanente') {
                    quantidadeInput.value = 1;
                    quantidadeInput.disabled = true;
                } else {
                    quantidadeInput.disabled = false;
                }
            });

            // Formatar valores quando digitados
            row.querySelector('.valor-input').addEventListener('input', formatarValor);

            // Remove button
            row.querySelector('.remove-item').addEventListener('click', function() {
                row.remove();
                atualizarSubmitBtn();
            });

            atualizarSubmitBtn();
        });

        // Formatar valor em moeda
        function formatarValor(e) {
            let raw = e.target.value.replace(/\D/g, '');
            let valorCentavos = raw ? parseInt(raw, 10) : 0;

            e.target.nextElementSibling.value = valorCentavos;

            let valorFormatado = (valorCentavos / 100).toFixed(2)
                .replace('.', ',')
                .replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            e.target.value = valorFormatado;
        }

        // Atualizar botão submit
        function atualizarSubmitBtn() {
            const temItens = document.querySelectorAll('.item-row').length > 0;
            document.getElementById('submitBtn').disabled = !temItens;
        }

        // Validar formulário antes de enviar
        document.querySelector('form').addEventListener('submit', function(e) {
            const linhas = document.querySelectorAll('.item-row');
            
            if (linhas.length === 0) {
                e.preventDefault();
                alert('Adicione pelo menos um item!');
                return false;
            }

            let valido = true;
            linhas.forEach((linha, idx) => {
                const produto = linha.querySelector('.produto-select').value;
                const quantidade = linha.querySelector('.quantidade-input').value;
                const secao = linha.querySelector('select[name="secoes[]"]').value;
                const dataEntrada = linha.querySelector('input[name="datas_entrada[]"]').value;
                const valor = linha.querySelector('.valor-centavos').value;
                const tipo = linha.querySelector('.produto-select').options[linha.querySelector('.produto-select').selectedIndex]?.dataset?.tipo;

                if (!produto || !quantidade || !secao || !dataEntrada) {
                    alert(`Linha ${idx + 1}: Preencha todos os campos obrigatórios`);
                    valido = false;
                    return;
                }

                if (!valor || parseInt(valor) === 0) {
                    alert(`Linha ${idx + 1}: O valor unitário não pode ser zero`);
                    valido = false;
                    return;
                }

                if (tipo === 'permanente') {
                    const patrimonios = linha.querySelector('.patrimonios-input').value.trim();
                    if (!patrimonios) {
                        alert(`Linha ${idx + 1}: Informe os patrimônios separados por vírgula`);
                        valido = false;
                        return;
                    }
                }
            });

            if (!valido) {
                e.preventDefault();
            }
        });
    </script>
@endsection
