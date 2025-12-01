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

    <!-- DEBUG -->
    <div class="alert alert-info" style="margin: 20px;">
        <strong>DEBUG:</strong><br>
        Unidade do usuário: {{ Auth::user()->fk_unidade }}<br>
        Total de produtos disponíveis: {{ count($itens_estoque) }}<br>
        SectionsMap keys: {{ json_encode(array_keys($sectionsMap ?? [])) }}<br>
        <details>
            <summary>Ver sectionsMap completo</summary>
            <pre>{{ json_encode($sectionsMap ?? [], JSON_PRETTY_PRINT) }}</pre>
        </details>
        <details>
            <summary>Ver itens_estoque</summary>
            <pre>{{ json_encode($itens_estoque, JSON_PRETTY_PRINT) }}</pre>
        </details>
    </div>
    <!-- FIM DEBUG -->

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
                                    <option value="{{ $item['id'] }}" data-total="{{ $item['quantidade_total'] }}">{{ $item['nome'] }}</option>
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
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Quantidade</label>
                            <input type="number" id="quantidade_add" class="form-control" min="1" placeholder="0">
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <button type="button" class="btn btn-success" id="addItem">
                            <i class="fa fa-plus"></i> Adicionar Item
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered" id="tabela-itens">
                        <thead>
                            <tr>
                                <th>Produto</th>
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
    // Máscara para o campo de telefone
    $('#telefone').mask('(00) 00000-0000');

    // Mapa de seções por produto passado do controller
    var sectionsMap = @json($sectionsMap ?? []);
    console.log('sectionsMap (renderizado do @@json):', sectionsMap);
    console.log('Type de sectionsMap:', typeof sectionsMap);
    console.log('Chaves de sectionsMap:', Object.keys(sectionsMap));

    // Inicializar Select2 (protege caso o plugin não esteja carregado)
    if ($.fn && $.fn.select2) {
        $('#fk_produto_add').select2({
            placeholder: "Selecione um Produto",
            allowClear: true,
            width: '100%'
        });
    } else {
        console.warn('Select2 não está disponível nesta página.');
    }
    console.log('sectionsMap carregado:', sectionsMap);

    // Ao selecionar produto, popula seções onde esse produto existe
    // Usa 'select2:select' para compatibilidade com Select2
    $('#fk_produto_add').on('change select2:select', function() {
        var produtoId = $(this).val();
        console.log('=== EVENTO CHANGE DISPARADO ===');
        console.log('Produto selecionado:', produtoId);
        console.log('Type:', typeof produtoId);
        
        var secaoSelect = $('#fk_secao_add');
        secaoSelect.html('<option value="">Selecione a seção</option>');
        $('#qtd_disponivel').val('');
        
        if (!produtoId) {
            console.log('Nenhum produto selecionado, retornando');
            return;
        }

        // Busca seções usando String como chave (controller converte keys para string)
        var produtoIdStr = String(produtoId);
        console.log('Buscando sectionsMap[' + produtoIdStr + ']');
        var sections = sectionsMap[produtoIdStr] || [];
        console.log('Seções encontradas:', sections);
        console.log('Quantidade de seções:', sections.length);
        
        if (sections.length === 0) {
            secaoSelect.append('<option value="">Nenhuma seção com este produto</option>');
            console.warn('Produto sem estoque em nenhuma seção');
            return;
        }

        // Popula dropdown com seções onde o produto existe
        sections.forEach(function(s) {
            var optionText = s.secao_nome + ' (Qtd: ' + s.quantidade + ')';
            console.log('Adicionando opção:', optionText, 'value:', s.estoque_id);
            secaoSelect.append('<option value="' + s.estoque_id + '" data-qty="' + s.quantidade + '">' + optionText + '</option>');
        });
        
        console.log('Total de seções adicionadas ao select:', sections.length);
        console.log('HTML do select após adição:', secaoSelect.html());
    });

    // Ao selecionar seção, exibe quantidade disponível
    $('#fk_secao_add').on('change', function() {
        var estoqueId = $(this).val();
        var prodId = $('#fk_produto_add').val();
        if (!estoqueId || !prodId) {
            $('#qtd_disponivel').val('');
            return;
        }
        var sections = sectionsMap[prodId] || [];
        for (var i = 0; i < sections.length; i++) {
            if (sections[i].estoque_id == estoqueId) {
                $('#qtd_disponivel').val(sections[i].quantidade);
                break;
            }
        }
    });

    // Função para adicionar item à tabela
    function addItemToTable(produtoId, produtoText, secaoId, secaoText, quantidade) {
        var row = `<tr>
            <td><input type="hidden" name="produtos[]" value="${produtoId}">${produtoText}</td>
            <td><input type="hidden" name="secoes[]" value="${secaoId}">${secaoText}</td>
            <td><input type="hidden" name="quantidades[]" value="${quantidade}">${quantidade}</td>
            <td>
                <button type="button" class="btn btn-danger btn-sm remover-item">
                    <i class="fa fa-trash"></i>
                </button>
            </td>
        </tr>`;
        $('#tabela-itens tbody').append(row);
    }

    // Adicionar item
    $('#addItem').click(function() {
        var produtoId = $('#fk_produto_add').val();
        var produtoText = $('#fk_produto_add').find('option:selected').text();
        var secaoId = $('#fk_secao_add').val();
        var secaoText = $('#fk_secao_add').find('option:selected').text();
        var quantidade = parseInt($('#quantidade_add').val() || 0, 10);

        if (!produtoId) {
            alert('Selecione um produto.');
            return;
        }
        if (!secaoId) {
            alert('Selecione a seção de origem.');
            return;
        }
        if (!quantidade || quantidade <= 0) {
            alert('Informe uma quantidade válida.');
            return;
        }

        // Verifica quantidade disponível
        var available = 0;
        if (sectionsMap[produtoId]) {
            for (var i = 0; i < sectionsMap[produtoId].length; i++) {
                if (sectionsMap[produtoId][i].estoque_id == secaoId) {
                    available = sectionsMap[produtoId][i].quantidade;
                    break;
                }
            }
        }
        if (quantidade > available) {
            alert('Quantidade informada excede o disponível (disponível: ' + available + ').');
            return;
        }

        addItemToTable(produtoId, produtoText, secaoId, secaoText, quantidade);
        
        // Limpa campos
        $('#fk_produto_add').val('').trigger('change');
        $('#quantidade_add').val('');
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