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
                    <form action="{{ route('estoque.entrada_novoproduto') }}" method="POST" id="form-entrada-produto">
                        @csrf
                        <div class="row">
                            <!-- Unidade -->
                            <div class="form-group col-md-4">
                                <label for="unidade">Unidade:</label>
                                <input type="text" value="{{ Auth::user()->unidade->nome }}" class="form-control"
                                    disabled>
                                <input type="hidden" name="unidade" value="{{ Auth::user()->fk_unidade }}">
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
                            <!-- Campos únicos para toda a entrada -->
                            <div class="form-group col-md-2">
                                <label for="lote">Lote:</label>
                                <input type="text" name="lote" class="form-control" placeholder="Ex: LOTE123">
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
                            <div class="form-group col-md-3">
                                <label for="nota_fiscal">Número da Nota Fiscal:</label>
                                <input type="text" name="nota_fiscal" class="form-control"
                                    placeholder="Ex: 00012345">
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-4">
                                <label for="fk_produto_add">Produto:</label>
                                <select id="fk_produto_add" class="form-control select2-produto">
                                    <option value="">Selecione um Produto</option>
                                    @foreach ($produtos as $produto)
                                        <option value="{{ $produto->id }}" data-tipo="{{ $produto->tipo_controle }}">{{ $produto->nome }} -  {{ optional($produto->tamanho()->first())->tamanho ?? 'Tamanho Único' }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-2">
                                <label for="quantidade_add">Quantidade:</label>
                                <input type="number" id="quantidade_add" class="form-control" min="1"
                                    placeholder="Digite a quantidade">
                            </div>
                            <div class="form-group col-md-6" id="patrimonios-add-wrapper" style="display: none;">
                                <label for="patrimonios_add">Patrimônios (1 por linha):</label>
                                <textarea id="patrimonios_add" class="form-control" rows="2"
                                    placeholder="Ex: 1001&#10;1002&#10;1003"></textarea>
                                <small class="text-muted">A quantidade deve ser igual ao número de patrimônios informados.</small>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="observacao_add">Observações:</label>
                                <input type="text" id="observacao_add" class="form-control" placeholder="Observações">
                            </div>
                            <div class="form-group col-md-2">
                                <button type="button" class="btn btn-info" id="add-item">Adicionar Item</button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered" id="tabela-itens">
                                <thead>
                                    <tr>
                                        <th>Produto</th>
                                        <th>Quantidade</th>
                                        <th>Observações</th>
                                        <th>Patrimônios</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Linhas de itens adicionados -->
                                </tbody>
                            </table>
                        </div>
                        <div class="form-group text-right">
                            <a href="{{ route('estoque.listar') }}?nome=&categoria=&unidade={{ Auth::user()->fk_unidade }}"
                                class="btn btn-danger">
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
            let rowIndex = 0;
            $('.select2-produto').select2({
                placeholder: "Selecione um Produto",
                allowClear: true,
                width: '100%'
            });
            function getProdutoText(select) {
                return select.find('option:selected').text();
            }
            function getProdutoTipo(select) {
                return select.find('option:selected').data('tipo');
            }
            function escapeHtml(value) {
                return String(value)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }
            function addItemToTable(produtoId, produtoText, quantidade, observacao, patrimonios) {
                const patrimoniosList = patrimonios && patrimonios.length ? patrimonios.join('<br>') : '-';
                const patrimoniosRaw = patrimonios && patrimonios.length ? patrimonios.join('\n') : '';
                const patrimoniosInputs = patrimonios && patrimonios.length
                    ? `<input type="hidden" name="patrimonios_raw[]" value="${escapeHtml(patrimoniosRaw)}">`
                    : '<input type="hidden" name="patrimonios_raw[]" value="">';
                var row = `<tr>
                    <td><input type="hidden" name="fk_produto[]" value="${produtoId}">${produtoText}</td>
                    <td><input type="hidden" name="quantidade[]" value="${quantidade}">${quantidade}</td>
                    <td><input type="hidden" name="observacao[]" value="${observacao}">${observacao}</td>
                    <td>${patrimoniosInputs}${patrimoniosList}</td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm remover-item">Remover</button>
                    </td>
                </tr>`;
                $('#tabela-itens tbody').append(row);
                rowIndex++;
            }
            function togglePatrimoniosWrapper() {
                const select = $('#fk_produto_add');
                const tipo = getProdutoTipo(select);
                if (tipo === 'permanente') {
                    $('#patrimonios-add-wrapper').show();
                    $('#quantidade_add').prop('readonly', true).val('');
                } else {
                    $('#patrimonios-add-wrapper').hide();
                    $('#patrimonios_add').val('');
                    $('#quantidade_add').prop('readonly', false);
                }
            }
            function syncQuantidadeFromPatrimonios() {
                const linhas = ($('#patrimonios_add').val() || '').split('\n');
                const count = linhas.map(l => l.trim()).filter(l => l.length > 0).length;
                $('#quantidade_add').val(count > 0 ? count : '');
            }
            $('#fk_produto_add').on('change', togglePatrimoniosWrapper);
            $('#patrimonios_add').on('input', syncQuantidadeFromPatrimonios);
            $('#add-item').on('click', function(e) {
                e.preventDefault();
                var select = $('#fk_produto_add');
                var produtoId = select.val();
                var produtoText = getProdutoText(select);
                var tipo = getProdutoTipo(select);
                if (tipo === 'permanente') {
                    syncQuantidadeFromPatrimonios();
                }
                var quantidade = $('#quantidade_add').val();
                var observacao = $('#observacao_add').val();
                var patrimonios = [];
                if (tipo === 'permanente') {
                    const linhas = ($('#patrimonios_add').val() || '').split('\n');
                    patrimonios = linhas.map(l => l.trim()).filter(l => l.length > 0);
                    if (!patrimonios.length) {
                        alert('Informe os patrimônios para itens permanentes.');
                        return;
                    }
                    if (parseInt(quantidade, 10) !== patrimonios.length) {
                        alert('A quantidade deve ser igual ao número de patrimônios informados.');
                        return;
                    }
                }
                if(produtoId && quantidade) {
                    addItemToTable(produtoId, produtoText, quantidade, observacao, patrimonios);
                    select.val('');
                    $('#quantidade_add').val('');
                    $('#observacao_add').val('');
                    $('#patrimonios_add').val('');
                    select.trigger('change');
                }
            });
            $(document).on('click', '.remover-item', function() {
                $(this).closest('tr').remove();
            });
            togglePatrimoniosWrapper();
        });
    </script>
@endsection
