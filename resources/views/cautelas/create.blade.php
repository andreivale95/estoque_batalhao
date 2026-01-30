@extends('layout.app')

@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Cadastrar Cautela
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{route('dashboard')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{ route('cautelas.index') }}">Cautelas</a></li>
            <li class="active">Nova Cautela</li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content container-fluid">
        <div class="box box-primary">
            <div class="box-body">
                <form action="{{ route('cautelas.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="nome_responsavel">Nome do Responsável</label>
            <input type="text" name="nome_responsavel" id="nome_responsavel" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="telefone">Telefone</label>
            <input type="text" name="telefone" id="telefone" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="instituicao">Instituição/Unidade</label>
            <input type="text" name="instituicao" id="instituicao" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="data_cautela">Data da Cautela</label>
            <input type="date" name="data_cautela" id="data_cautela" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="data_prevista_devolucao">Data Prevista de Devolução</label>
            <input type="date" name="data_prevista_devolucao" id="data_prevista_devolucao" class="form-control" required>
        </div>

        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">Itens da Cautela</h3>
            </div>
            <div class="box-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Produto</label>
                            <select id="fk_produto_add" class="form-control select2-produto">
                                <option value="">Selecione um Produto</option>
                                @foreach ($itens_estoque as $item)
                                    <option value="{{ $item['id'] }}"
                                            data-total="{{ $item['quantidade_total'] }}"
                                            data-tipo="{{ $item['tipo_controle'] ?? 'consumo' }}">
                                        {{ $item['nome'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Seção (origem)</label>
                            <select id="fk_secao_add" class="form-control">
                                <option value="">Selecione a seção</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Disponível</label>
                            <input type="text" id="qtd_disponivel" class="form-control" readonly>
                        </div>
                    </div>
                </div>
                <div class="row mb-3" id="campos_consumo" style="display: none;">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Quantidade</label>
                            <input type="number" id="quantidade_add" class="form-control" min="1" placeholder="0">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <button type="button" class="btn btn-success" id="addItem">
                            <i class="fa fa-plus"></i> Adicionar Item
                        </button>
                    </div>
                </div>
                <div id="campos_permanente" style="display: none;">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label><strong>Selecione os patrimônios:</strong></label>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                <table class="table table-bordered table-sm">
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
                                        <!-- Itens preenchidos via JS -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <button type="button" class="btn btn-success" id="addItemPermanente">
                                <i class="fa fa-plus"></i> Adicionar Patrimônios
                            </button>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered" id="tabela-itens">
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Produto</th>
                                <th>Patrimônio</th>
                                <th>Seção</th>
                                <th>Quantidade</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Itens adicionados via JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Salvar</button>
    </form>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Mapa de seções por produto passado do controller
    var sectionsMap = @json($sectionsMap ?? []);

    function limparCampos() {
        $('#fk_produto_add').val('');
        $('#fk_secao_add').html('<option value="">Selecione a seção</option>');
        $('#quantidade_add').val('');
        $('#qtd_disponivel').val('');
        $('#patrimonios_tbody').html('');
        $('#campos_consumo').hide();
        $('#campos_permanente').hide();
    }

    // Ao selecionar produto, popula seções onde esse produto existe
    $('#fk_produto_add').on('change', function() {
        var produtoId = String($(this).val());
        var tipo = $(this).find('option:selected').data('tipo') || 'consumo';
        
        var $secaoSelect = $('#fk_secao_add');
        $secaoSelect.empty();
        $secaoSelect.append('<option value="">Selecione a seção</option>');
        $('#qtd_disponivel').val('');
        $('#patrimonios_tbody').html('');
        $('#campos_consumo').hide();
        $('#campos_permanente').hide();
        
        if (!produtoId) {
            return;
        }

        var sections = sectionsMap[produtoId];
        
        if (!sections || sections.length === 0) {
            $secaoSelect.append('<option value="">Nenhuma seção disponível</option>');
            return;
        }

        // Adiciona cada seção ao select
        $.each(sections, function(index, secao) {
            var optionText = secao.secao_nome + ' (Qtd: ' + secao.quantidade + ')';
            var option = $('<option></option>')
                .attr('value', secao.estoque_id)
                .attr('data-qty', secao.quantidade)
                .attr('data-tipo', secao.tipo || tipo)
                .text(optionText);
            $secaoSelect.append(option);
        });
    });

    // Ao selecionar seção, exibe quantidade disponível
    $('#fk_secao_add').on('change', function() {
        var qty = $(this).find('option:selected').data('qty');
        $('#qtd_disponivel').val(qty || '');

        var tipo = $(this).find('option:selected').data('tipo') || 'consumo';
        var produtoId = String($('#fk_produto_add').val());

        if (!produtoId || !$(this).val()) {
            $('#campos_consumo').hide();
            $('#campos_permanente').hide();
            return;
        }

        if (tipo === 'permanente') {
            $('#campos_consumo').hide();
            $('#campos_permanente').show();

            var sections = sectionsMap[produtoId] || [];
            var tbody = $('#patrimonios_tbody');
            tbody.html('');
            var selectedSectionId = $(this).val();

            for (var i = 0; i < sections.length; i++) {
                if (String(sections[i].estoque_id) === String(selectedSectionId)) {
                    var patrimonios = sections[i].patrimonios || [];
                    for (var j = 0; j < patrimonios.length; j++) {
                        var p = patrimonios[j];
                        var row = '<tr>' +
                            '<td><input type="checkbox" class="patrimonio-check" value="' + p.patrimonio + '" data-id="' + p.id + '"></td>' +
                            '<td>' + (p.patrimonio || '-') + '</td>' +
                            '<td>' + (p.serie || '-') + '</td>' +
                            '<td>' + (p.condicao || '-') + '</td>' +
                            '<td>' + (p.observacao || '-') + '</td>' +
                        '</tr>';
                        tbody.append(row);
                    }
                    break;
                }
            }
        } else {
            $('#campos_permanente').hide();
            $('#campos_consumo').show();
        }
    });

    // Máscara para telefone
    if ($.fn.mask) {
        $('#telefone').mask('(00) 00000-0000');
    }

    // Adicionar item à tabela
    $(document).on('click', '#addItem', function(e) {
        e.preventDefault();
        var produtoId = $('#fk_produto_add').val();
        var produtoText = $('#fk_produto_add option:selected').text();
        var estoqueId = $('#fk_secao_add').val();
        var secaoText = $('#fk_secao_add option:selected').text().replace(/\s*\(Qtd:.*\)\s*/i, '').trim();
        var quantidade = parseInt($('#quantidade_add').val() || 0);
        var disponivel = parseInt($('#qtd_disponivel').val() || 0);

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
        if (quantidade > disponivel) {
            alert('Quantidade informada (' + quantidade + ') excede o disponível (' + disponivel + ').');
            return;
        }
        var row = '<tr>' +
            '<td><span class="label label-success">Consumo</span><input type="hidden" name="tipos[]" value="consumo"></td>' +
            '<td><input type="hidden" name="produtos[]" value="' + produtoId + '">' + produtoText + '</td>' +
            '<td>-<input type="hidden" name="patrimonios[]" value=""></td>' +
            '<td><input type="hidden" name="secoes[]" value="' + estoqueId + '">' + secaoText + '</td>' +
            '<td><input type="hidden" name="quantidades[]" value="' + quantidade + '">' + quantidade + '</td>' +
            '<td><button type="button" class="btn btn-danger btn-sm remover-item"><i class="fa fa-trash"></i></button></td>' +
            '</tr>';
        
        $('#tabela-itens tbody').append(row);
        
        limparCampos();
    });

    // Adicionar itens permanentes
    $(document).on('click', '#addItemPermanente', function(e) {
        e.preventDefault();
        var produtoId = $('#fk_produto_add').val();
        var produtoText = $('#fk_produto_add option:selected').text();
        var secaoText = $('#fk_secao_add option:selected').text().replace(/\s*\(Qtd:.*\)\s*/i, '').trim();

        if (!produtoId) {
            alert('Selecione um produto.');
            return;
        }

        var selecionados = [];
        $('#patrimonios_tbody input[type="checkbox"]:checked').each(function() {
            selecionados.push({
                id: $(this).data('id'),
                numero: $(this).val()
            });
        });

        if (selecionados.length === 0) {
            alert('Selecione pelo menos um patrimônio.');
            return;
        }

        selecionados.forEach(function(p) {
            var row = '<tr>' +
                '<td><span class="label label-info">Permanente</span><input type="hidden" name="tipos[]" value="permanente"></td>' +
                '<td><input type="hidden" name="produtos[]" value="' + produtoId + '">' + produtoText + '</td>' +
                '<td>' + p.numero + '<input type="hidden" name="patrimonios[]" value="' + p.id + '"></td>' +
                '<td><input type="hidden" name="secoes[]" value="">' + secaoText + '</td>' +
                '<td><input type="hidden" name="quantidades[]" value="1">1</td>' +
                '<td><button type="button" class="btn btn-danger btn-sm remover-item"><i class="fa fa-trash"></i></button></td>' +
                '</tr>';
            $('#tabela-itens tbody').append(row);
        });

        limparCampos();
    });

    // Selecionar/desselecionar todos os patrimônios
    $('#select_all_patrimonios').on('change', function() {
        $('.patrimonio-check').prop('checked', $(this).is(':checked'));
    });

    // Remover item
    $(document).on('click', '.remover-item', function() {
        $(this).closest('tr').remove();
    });

    // Validar antes de submeter
    $('form').on('submit', function(e) {
        var temItens = $('#tabela-itens tbody tr').length > 0;
        if (!temItens) {
            alert('Adicione pelo menos um item à cautela!');
            e.preventDefault();
            return false;
        }
    });
});
</script>
@endpush