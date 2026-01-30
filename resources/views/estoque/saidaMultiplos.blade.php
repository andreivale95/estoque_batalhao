@extends('layout/app')
@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>Saída Múltipla de Produtos</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{ route('estoque.listar') }}">Inventário</a></li>
            <li class="active">Saída Múltipla</li>
        </ol>
    </section>
    <section class="content container-fluid">
        <div class="panel panel-default">
            <div class="panel-heading bg-warning text-white">
                <h3 class="panel-title">Cadastro de Saída Múltipla</h3>
            </div>
            <div class="panel-body" style="background-color: white;">
                <form action="{{ route('estoque.saidaMultiplos') }}" method="POST" id="form-saida-multipla">
                    @csrf
                    <div class="row">
                        <div class="form-group col-md-4">
                            <label for="unidade">Unidade:</label>
                            <input type="text" value="{{ Auth::user()->unidade->nome }}" class="form-control" disabled>
                            <input type="hidden" name="unidade" value="{{ Auth::user()->fk_unidade }}">
                        </div>
                        <div class="form-group col-md-4">
                            <label for="data_saida">Data de Saída:</label>
                            <input type="date" name="data_saida" class="form-control" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="militar">Militar:</label>
                            <select name="militar" class="form-control" required>
                                <option value="">Selecione o militar</option>
                                @foreach ($militares as $militar)
                                    <option value="{{ $militar->id }}">{{ $militar->nome }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="motivo">Motivo da baixa:</label>
                            <select name="motivo" class="form-control" required>
                                <option value="">Selecione o motivo</option>
                                <option value="Consumo">Consumo</option>
                                <option value="Defeito">Defeito</option>
                                <option value="Quebrado">Quebrado</option>
                                <option value="Outro">Outro</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="observacao">Observações:</label>
                            <input type="text" name="observacao" class="form-control" placeholder="Observações gerais">
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-4">
                            <label for="fk_produto_add">Produto:</label>
                            <select id="fk_produto_add" class="form-control select2-produto">
                                <option value="">Selecione um Produto</option>
                                @foreach ($itens_estoque as $item)
                                    <option value="{{ $item['id'] }}" 
                                            data-total="{{ $item['quantidade_total'] }}"
                                            data-tipo="{{ $item['tipo_controle'] ?? 'consumo' }}">
                                        {{ $item['nome'] }}
                                        @if(($item['tipo_controle'] ?? 'consumo') === 'permanente')
                                            <span class="badge bg-info">Permanente</span>
                                        @else
                                            <span class="badge bg-success">Consumo</span>
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="fk_secao_add">Seção (origem):</label>
                            <select id="fk_secao_add" class="form-control">
                                <option value="">Selecione a seção</option>
                                {{-- opções populadas via JS quando escolher o produto --}}
                            </select>
                        </div>
                        <div class="form-group col-md-2">
                            <label for="qtd_disponivel">Qtd. disponível:</label>
                            <input type="text" id="qtd_disponivel" class="form-control" readonly>
                        </div>
                        <div class="form-group col-md-2">
                            <label for="secao_display">Seção:</label>
                            <input type="text" id="secao_display" class="form-control" readonly>
                        </div>
                    </div>
                    
                    <!-- Campo para itens de consumo -->
                    <div class="row" id="campos_consumo" style="display: none;">
                        <div class="form-group col-md-2">
                            <label for="quantidade_add">Quantidade:</label>
                            <input type="number" id="quantidade_add" class="form-control" min="1" placeholder="Digite a quantidade">
                        </div>
                        <div class="form-group col-md-2">
                            <button type="button" class="btn btn-warning" id="add-item">Adicionar Item</button>
                        </div>
                    </div>
                    
                    <!-- Campo para itens permanentes -->
                    <div id="campos_permanente" style="display: none;">
                        <div class="row">
                            <div class="col-md-12">
                                <label><strong>Selecione os patrimônios para dar saída:</strong></label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div id="lista_patrimonios" class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                    <table class="table table-sm table-bordered">
                                        <thead>
                                            <tr>
                                                <th width="50">
                                                    <input type="checkbox" id="select_all_patrimonios">
                                                </th>
                                                <th>Patrimônio</th>
                                                <th>Série</th>
                                                <th>Condição</th>
                                                <th>Observação</th>
                                            </tr>
                                        </thead>
                                        <tbody id="patrimonios_tbody">
                                            <!-- Preenchido via JavaScript -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-12">
                                <button type="button" class="btn btn-warning" id="add-item-permanente">Adicionar Patrimônios Selecionados</button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered" id="tabela-itens">
                            <thead>
                                <tr>
                                    <th>Tipo</th>
                                    <th>Produto</th>
                                    <th>Seção</th>
                                    <th>Quantidade</th>
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
                        <a href="{{ route('estoque.listar') }}?nome=&categoria=&unidade={{ Auth::user()->fk_unidade }}" class="btn btn-danger">
                            Cancelar <i class="fa fa-arrow-left"></i>
                        </a>
                        <button type="submit" class="btn btn-warning"><i class="fa fa-share-square"></i> Confirmar Saída Múltipla</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
<script>
$(document).ready(function() {
    $('.select2-produto').select2({
        placeholder: "Selecione um Produto",
        allowClear: true,
        width: '100%'
    });
    
    var currentTipo = 'consumo';
    var currentPatrimonios = [];
    
    function getProdutoText(select) {
        return select.find('option:selected').text();
    }
    
    function addItemToTable(produtoId, produtoText, quantidade, secao, tipo, patrimonios = []) {
        var tipoDisplay = tipo === 'permanente' ? '<span class="badge bg-info"><i class="fa fa-shield"></i></span>' : '<span class="badge bg-success"><i class="fa fa-layer-group"></i></span>';
        var patrimoniosDisplay = '-';
        var patrimoniosInput = '';
        
        if (tipo === 'permanente' && patrimonios.length > 0) {
            // Exibe os números de patrimônio
            var numeros = patrimonios.map(p => p.numero || p).join(', ');
            patrimoniosDisplay = numeros;
            
            // Envia os IDs para o backend
            patrimoniosInput = patrimonios.map(p => {
                var id = p.id || p;
                return `<input type="hidden" name="patrimonios[]" value="${id}">`;
            }).join('');
        }
        
        var row = `<tr>
            <td>${tipoDisplay}<input type="hidden" name="tipo[]" value="${tipo}"></td>
            <td><input type="hidden" name="fk_produto[]" value="${produtoId}">${produtoText}</td>
            <td><input type="hidden" name="secao[]" value="${secao}">${secao}</td>
            <td><input type="hidden" name="quantidade[]" value="${quantidade}">${quantidade}</td>
            <td>${patrimoniosDisplay}${patrimoniosInput}</td>
            <td>
                <button type="button" class="btn btn-danger btn-sm remover-item">Remover</button>
            </td>
        </tr>`;
        $('#tabela-itens tbody').append(row);
    }
    
    // Adicionar item de consumo
    $('#add-item').on('click', function(e) {
        e.preventDefault();
        var produtoSelect = $('#fk_produto_add');
        var produtoId = produtoSelect.val();
        var produtoText = produtoSelect.find('option:selected').text().trim();
        var secaoSelect = $('#fk_secao_add');
        var estoqueId = secaoSelect.val();
        var secaoText = secaoSelect.find('option:selected').text().replace(/\s*\(Disponível:.*\)\s*/i, '').trim();
        var quantidade = parseInt($('#quantidade_add').val() || 0, 10);

        if (!produtoId) {
            alert('Selecione um produto.');
            return;
        }
        if (!estoqueId) {
            alert('Selecione a seção de origem.');
            return;
        }
        if (!quantidade || quantidade <= 0) {
            alert('Informe uma quantidade válida.');
            return;
        }

        var available = 0;
        if (typeof sectionsMap !== 'undefined' && sectionsMap[produtoId]) {
            for (var i = 0; i < sectionsMap[produtoId].length; i++) {
                if (sectionsMap[produtoId][i].estoque_id == estoqueId && sectionsMap[produtoId][i].tipo === 'consumo') {
                    available = sectionsMap[produtoId][i].quantidade;
                    break;
                }
            }
        }
        if (quantidade > available) {
            alert('Quantidade informada excede o disponível na seção selecionada (disponível: ' + available + ').');
            return;
        }

        addItemToTable(estoqueId, produtoText, quantidade, secaoText, 'consumo', []);
        limparCampos();
    });
    
    // Adicionar itens permanentes
    $('#add-item-permanente').on('click', function(e) {
        e.preventDefault();
        var produtoSelect = $('#fk_produto_add');
        var produtoId = produtoSelect.val();
        var produtoText = produtoSelect.find('option:selected').text().trim();
        var secaoSelect = $('#fk_secao_add');
        var secaoText = secaoSelect.find('option:selected').text().replace(/\s*\(Disponível:.*\)\s*/i, '').trim();
        
        var patrimoniosInfo = [];
        $('#patrimonios_tbody input[type="checkbox"]:checked').each(function() {
            patrimoniosInfo.push({
                id: $(this).data('id'),
                numero: $(this).val()
            });
        });
        
        if (patrimoniosInfo.length === 0) {
            alert('Selecione pelo menos um patrimônio.');
            return;
        }
        
        addItemToTable(produtoId, produtoText, patrimoniosInfo.length, secaoText, 'permanente', patrimoniosInfo);
        limparCampos();
    });
    
    function limparCampos() {
        $('#fk_produto_add').val('').trigger('change');
        $('#quantidade_add').val('');
        $('#secao_display').val('');
        $('#fk_secao_add').html('<option value="">Selecione a seção</option>');
        $('#patrimonios_tbody').html('');
        $('#campos_consumo').hide();
        $('#campos_permanente').hide();
    }
    
    $(document).on('click', '.remover-item', function() {
        $(this).closest('tr').remove();
    });
    
    $('#form-saida-multipla').on('submit', function(e) {
        var temItens = $('#tabela-itens tbody tr').length > 0;
        var dataSaida = $('input[name="data_saida"]').val();
        var militar = $('select[name="militar"]').val();
        var motivo = $('select[name="motivo"]').val();
        if (!temItens) {
            alert('Adicione pelo menos um item à lista!');
            e.preventDefault();
            return false;
        }
        if (!dataSaida || !militar || !motivo) {
            alert('Preencha todos os campos obrigatórios!');
            e.preventDefault();
            return false;
        }
    });
    
    var sectionsMap = {!! json_encode($sectionsMap ?? []) !!};

    $('#fk_produto_add').on('change', function() {
        var produtoId = $(this).val();
        var produtoOption = $(this).find('option:selected');
        var tipo = produtoOption.data('tipo') || 'consumo';
        currentTipo = tipo;
        
        var secaoSelect = $('#fk_secao_add');
        secaoSelect.html('<option value="">Selecione a seção</option>');
        $('#qtd_disponivel').val('');
        $('#secao_display').val('');
        $('#patrimonios_tbody').html('');
        $('#campos_consumo').hide();
        $('#campos_permanente').hide();
        
        if (!produtoId) return;

        var sections = sectionsMap[produtoId] || [];
        var total = 0;
        
        for (var i = 0; i < sections.length; i++) {
            var s = sections[i];
            var optionText = s.secao_nome + ' (Disponível: ' + s.quantidade + ')';
            secaoSelect.append('<option value="' + s.estoque_id + '" data-qty="' + s.quantidade + '" data-tipo="' + s.tipo + '">' + optionText + '</option>');
            total += s.quantidade;
        }
        $('#qtd_disponivel').val(total > 0 ? total : '');
    });

    $('#fk_secao_add').on('change', function() {
        var estoqueId = $(this).val();
        var prodId = $('#fk_produto_add').val();
        
        if (!estoqueId || !prodId) {
            $('#secao_display').val('');
            $('#qtd_disponivel').val('');
            $('#campos_consumo').hide();
            $('#campos_permanente').hide();
            return;
        }
        
        var sections = sectionsMap[prodId] || [];
        for (var i = 0; i < sections.length; i++) {
            if (sections[i].estoque_id == estoqueId) {
                $('#secao_display').val(sections[i].secao_nome || '');
                $('#qtd_disponivel').val(sections[i].quantidade);
                
                if (sections[i].tipo === 'permanente') {
                    // Mostrar campos de patrimônio
                    $('#campos_permanente').show();
                    $('#campos_consumo').hide();
                    
                    // Preencher tabela de patrimônios
                    var tbody = $('#patrimonios_tbody');
                    tbody.html('');
                    var patrimonios = sections[i].patrimonios || [];
                    for (var j = 0; j < patrimonios.length; j++) {
                        var p = patrimonios[j];
                        var row = `<tr>
                            <td><input type="checkbox" class="patrimonio-check" value="${p.patrimonio}" data-id="${p.id}"></td>
                            <td>${p.patrimonio}</td>
                            <td>${p.serie || '-'}</td>
                            <td>${p.condicao || '-'}</td>
                            <td>${p.observacao || '-'}</td>
                        </tr>`;
                        tbody.append(row);
                    }
                } else {
                    // Mostrar campos de quantidade
                    $('#campos_consumo').show();
                    $('#campos_permanente').hide();
                }
                break;
            }
        }
    });
    
    // Selecionar/desselecionar todos os patrimônios
    $('#select_all_patrimonios').on('change', function() {
        $('.patrimonio-check').prop('checked', $(this).is(':checked'));
    });
});
</script>
@endsection