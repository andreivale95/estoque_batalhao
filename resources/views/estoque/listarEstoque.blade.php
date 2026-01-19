@extends('layout/app')
@section('styles')
<style>
    .item-row:hover {
        background-color: #f5f5f5;
    }
    .item-row td {
        transition: background-color 0.2s;
    }
    .btn-warning {
        z-index: 2;
        position: relative;
    }
</style>
@endsection

@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>Estoque</h1>
            <ol class="breadcrumb">
                <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
                <li><a href="{{ route('estoque.listar') }}"><i class=""></i> Estoque</a></li>
            </ol>
        </section>

        <!-- Main content -->
        <section class="content container-fluid">
            <form action="{{ route('estoque.listar') }}" method="get">
                <div class="box box-primary">
                    <div class="box-header">
                        <div class="row" style="align-items: flex-end;">
                            <div class="form-group has-feedback col-md-2">
                                <label class="control-label">PRODUTO:</label>
                                <input type="text" class="form-control" name="nome" value="{{ request()->nome }}">
                            </div>
                            <div class="form-group has-feedback col-md-2">
                                <label class="control-label">CATEGORIA:</label>
                                <select name="categoria" class="form-control">
                                    <option value="">Selecione</option>
                                    @foreach ($categorias as $categoria)
                                        <option value="{{ $categoria->id }}"
                                            {{ request()->categoria == $categoria->id ? 'selected' : '' }}>
                                            {{ $categoria->nome }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group has-feedback col-md-2">
                                <label class="control-label">PATRIMÔNIO:</label>
                                <input type="text" class="form-control" name="patrimonio" value="{{ request()->patrimonio }}" placeholder="Número do patrimônio">
                            </div>
                            <div class="form-group has-feedback col-md-2"
                                style="display: flex; flex-direction: column; justify-content: flex-end;">
                                <label class="control-label">Estoque:</label>
                                <div style="display: flex; gap: 8px;">
                                    <select name="unidade" class="form-control" style="width: 70%;">
                                        <option value="">Selecione</option>
                                        @foreach ($unidades as $unidade)
                                            <option value="{{ $unidade->id }}"
                                                {{ request()->unidade == $unidade->id ? 'selected' : '' }}>
                                                {{ $unidade->nome }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button class="btn btn-primary" type="submit"
                                        style="height: 38px; align-self: flex-end;">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="form-group has-feedback col-md-3"
                                style="display: flex; flex-direction: column; justify-content: flex-end;">
                                <label class="control-label">&nbsp;</label>
                                <div style="display: flex; gap: 8px;">
                                    <a href="{{ route('produto.form') }}" class="btn btn-success"
                                        style="height: 38px;" title="Cadastrar um novo produto no sistema">
                                        <i class="fa fa-plus"></i> Cadastrar Produto
                                    </a>
                                    <a href="{{ route('estoque.entrada.form') }}" class="btn btn-info"
                                        style="height: 38px;" title="Registrar entrada de produto existente">
                                        <i class="fa fa-arrow-down"></i> Registrar Entrada
                                    </a>
                                    <a href="{{ route('saida_estoque.saida_multipla') }}" class="btn btn-warning"
                                        style="height: 38px;">
                                        <i class="fa fa-share-square"></i> Saída Múltipla
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <div class="box box-primary">
                <div class="box-body table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <!-- Coluna de seleção removida -->
                                <th>Produto</th>
                                <th>Localização</th>
                                <th>Patrimônio</th>
                                <th>Quantidade</th>
                                <th>Cautelado</th>
                                <th>Disponível</th>
                                <th>Unidade</th>
                                <th>Categoria</th>
                                <th>Estoque</th>
                                <th>Valor Médio</th>
                                <th>Subtotal</th>
                                <th>Transferência</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($itens_estoque as $estoque)
                                @php
                                    $valorUnitario = $estoque->valor ?? 0;
                                    $subtotal = $estoque->quantidade_total * $valorUnitario;
                                    
                                    // Verifica se é um container usando coluna eh_container
                                    $ehContainer = $estoque->eh_container ?? false;
                                    
                                    // Busca o primeiro item deste produto para pegar o ID correto
                                    $primeiroItem = App\Models\Itens_estoque::where('fk_produto', $estoque->id)->first();
                                    $itemId = $primeiroItem ? $primeiroItem->id : $estoque->id;
                                    
                                    // Define a rota dependendo se é container ou não
                                    $rota = $ehContainer 
                                        ? route('container.detalhes', $estoque->id)
                                        : route('estoque.produto.detalhes', $estoque->id);
                                @endphp
                                <tr class="item-row" style="cursor: pointer;" 
                                    onclick="window.location='{{ $rota }}'"
                                    title="{{ $ehContainer ? 'Clique para ver conteúdo do container' : 'Clique para ver detalhes' }}">
                                    <td>
                                        @if($ehContainer)
                                            <i class="fa fa-briefcase text-primary"></i>
                                            @php
                                                // Busca a seção do container
                                                $secaoContainer = $primeiroItem ? $primeiroItem->secao->nome : '';
                                            @endphp
                                            {{ $estoque->nome }}@if($secaoContainer) - <small class="text-muted">{{ $secaoContainer }}</small>@endif
                                        @else
                                            {{ $estoque->nome }}
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            // Busca todos os itens deste produto
                                            $itens = App\Models\Itens_estoque::where('fk_produto', $estoque->id)
                                                ->with(['secao'])
                                                ->get();
                                            
                                            // Agrupa apenas seções únicas
                                            $localizacoes = $itens->map(function($item) {
                                                return $item->secao ? $item->secao->nome : 'Sem seção';
                                            })->unique()->take(3);
                                        @endphp
                                        @if($localizacoes->count() > 0)
                                            @foreach($localizacoes as $loc)
                                                <small>{{ $loc }}</small><br>
                                            @endforeach
                                            @if($localizacoes->count() >= 3 && $itens->pluck('fk_secao')->unique()->count() > 3)
                                                <small class="text-muted">+{{ $itens->pluck('fk_secao')->unique()->count() - 3 }} mais...</small>
                                            @endif
                                        @else
                                            <small class="text-muted">-</small>
                                        @endif
                                    </td>
                                    <td>{{ $estoque->patrimonio ?? '-' }}</td>
                                    <td>
                                        @if ($estoque->quantidade_total <= 0)
                                            <span class="text-danger">Produto esgotado</span>
                                        @else
                                            {{ $estoque->quantidade_total }}
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            // Calcula total de itens cautelados para este produto
                                            $totalCautelado = App\Models\Itens_estoque::where('fk_produto', $estoque->id)
                                                ->sum('quantidade_cautelada');
                                        @endphp
                                        <span class="badge badge-warning">{{ $totalCautelado }}</span>
                                    </td>
                                    <td>
                                        @php
                                            // Calcula quantidade disponível (total - cautelado)
                                            $disponivel = $estoque->quantidade_total - $totalCautelado;
                                        @endphp
                                        @if ($disponivel <= 0)
                                            <span class="text-danger">Indisponível</span>
                                        @else
                                            <span class="text-success">{{ $disponivel }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $estoque->unidade_nome }}</td>
                                    <td>
                                        @php
                                            $categoria = App\Models\Categoria::find($estoque->categoria_id);
                                        @endphp
                                        {{ $categoria ? $categoria->nome : 'N/A' }}
                                    </td>
                                    <td>{{ $estoque->unidade_nome }}</td>
                                    <td>R$ {{ number_format($valorUnitario, 2, ',', '.') }}</td>
                                    <td>R$ {{ number_format($subtotal, 2, ',', '.') }}</td>
                                    <td>
                                        @if (Auth::user()->fk_unidade == $estoque->unidade()->first()->id)
                                            @if(!$ehContainer)
                                                <button type="button" class="btn btn-warning btn-sm" data-toggle="modal"
                                                    data-target="#modalTransferencia{{ $estoque->id }}"
                                                    onclick="event.stopPropagation();">
                                                    <i class="fa fa-exchange-alt"></i>
                                                </button>
                                            @endif
                                        @else
                                            <span class="text-muted">Acesso restrito</span>
                                        @endif
                                    </td>
                                </tr>

                                <!-- Modal de Transferência -->
                                <div class="modal fade" id="modalTransferencia{{ $estoque->id }}" tabindex="-1"
                                    role="dialog" aria-labelledby="modalTransferenciaLabel{{ $estoque->id }}"
                                    aria-hidden="true">
                                    <div class="modal-dialog modal-lg" role="document">
                                        <form action="{{ route('estoque.transferir') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="fk_produto" value="{{ $estoque->id }}">
                                            <div class="modal-content">
                                                <div class="modal-header bg-warning">
                                                    <h5 class="modal-title"><i class="fa fa-exchange-alt"></i> Transferir Item</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>

                                                <div class="modal-body">
                                                    <div class="alert alert-info">
                                                        <strong>{{ $estoque->nome }}</strong>
                                                    </div>

                                                    @php
                                                        $totalDisponivel = $estoque->quantidade_total;
                                                    @endphp
                                                    <div class="form-group">
                                                        <label><strong>Quantidade Disponível:</strong></label>
                                                        <div class="alert alert-success">
                                                            {{ $totalDisponivel }} {{ $estoque->unidade_nome }}
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="tipoTransferencia"><strong>Tipo de Transferência:</strong></label>
                                                        <select class="form-control" id="tipoTransferencia{{ $estoque->id }}" 
                                                            name="tipo_transferencia" onchange="atualizarCamposTransferencia(this, {{ $estoque->id }})" required>
                                                            <option value="">-- Selecione --</option>
                                                            <option value="secao_para_secao">De Seção para Seção</option>
                                                            <option value="secao_para_container">De Seção para Container</option>
                                                            <option value="container_para_secao">De Container para Seção</option>
                                                        </select>
                                                    </div>

                                                    <!-- Opção: Item Solto ou em Container (para transferências de seção) -->
                                                    <div class="form-group" id="localizacao_item{{ $estoque->id }}" style="display: none;">
                                                        <label for="localizacao_item_tipo"><strong>Localização do Item:</strong></label>
                                                        <select class="form-control" id="localizacao_item_tipo{{ $estoque->id }}" 
                                                            name="localizacao_item" onchange="atualizarOrigemItem(this, {{ $estoque->id }})" required>
                                                            <option value="">-- Selecione --</option>
                                                            <option value="solto">Item Solto na Seção</option>
                                                            <option value="em_container">Item Dentro de Container</option>
                                                        </select>
                                                    </div>

                                                    <!-- Origem: Seção -->
                                                    <div class="form-group" id="setor_origem{{ $estoque->id }}" style="display: none;">
                                                        <label for="secao_origem"><strong>Seção de Origem:</strong></label>
                                                        <select class="form-control" id="secao_origem{{ $estoque->id }}" 
                                                            name="secao_origem" onchange="atualizarQuantidadeDisponivel(this, {{ $estoque->id }})">
                                                            <option value="">-- Selecione --</option>
                                                            @php
                                                                $secoes = DB::table('itens_estoque')
                                                                    ->where('fk_produto', $estoque->id)
                                                                    ->whereNull('fk_item_pai')
                                                                    ->pluck('fk_secao')
                                                                    ->unique();
                                                            @endphp
                                                            @foreach($secoes as $secaoId)
                                                                @php
                                                                    $secao = DB::table('secaos')->find($secaoId);
                                                                    $qtd = DB::table('itens_estoque')
                                                                        ->where('fk_produto', $estoque->id)
                                                                        ->where('fk_secao', $secaoId)
                                                                        ->whereNull('fk_item_pai')
                                                                        ->sum('quantidade');
                                                                @endphp
                                                                <option value="{{ $secaoId }}" data-quantidade="{{ $qtd }}">
                                                                    {{ $secao->nome ?? 'Sem seção' }} ({{ $qtd }})
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <small class="text-muted" id="qtd_secao_origem{{ $estoque->id }}">Quantidade disponível: -</small>
                                                    </div>

                                                    <!-- Origem: Container -->
                                                    <div class="form-group" id="setor_container_origem{{ $estoque->id }}" style="display: none;">
                                                        <label for="container_origem"><strong>Container de Origem:</strong></label>
                                                        <select class="form-control" id="container_origem{{ $estoque->id }}" 
                                                            name="container_origem" onchange="atualizarQuantidadeDisponivel(this, {{ $estoque->id }})">
                                                            <option value="">-- Selecione --</option>
                                                            @php
                                                                // Busca containers (itens_estoque com fk_item_pai = null) que têm items do estoque atual como filhos
                                                                $containersComItem = App\Models\Itens_estoque::whereHas('itensFilhos', function($q) use ($estoque) {
                                                                    $q->where('fk_produto', $estoque->id);
                                                                })
                                                                ->with('produto')
                                                                ->get();
                                                            @endphp
                                                            @foreach($containersComItem as $itemContainer)
                                                                @php
                                                                    $qtdNoContainer = App\Models\Itens_estoque::where('fk_produto', $estoque->id)
                                                                        ->where('fk_item_pai', $itemContainer->id)
                                                                        ->sum('quantidade');
                                                                @endphp
                                                                <option value="{{ $itemContainer->id }}" data-quantidade="{{ $qtdNoContainer }}">
                                                                    {{ $itemContainer->produto->nome ?? 'Container' }} ({{ $qtdNoContainer }})
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <small class="text-muted" id="qtd_container_origem{{ $estoque->id }}">Quantidade disponível: -</small>
                                                    </div>

                                                    <!-- Destino: Seção -->
                                                    <div class="form-group" id="setor_destino{{ $estoque->id }}" style="display: none;">
                                                        <label for="secao_destino"><strong>Seção de Destino:</strong></label>
                                                        <select class="form-control" id="secao_destino{{ $estoque->id }}" name="secao_destino" required>
                                                            <option value="">-- Selecione --</option>
                                                            @foreach($categorias as $cat)
                                                                <option value="{{ $cat->id }}">{{ $cat->nome }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <!-- Destino: Container -->
                                                    <div class="form-group" id="setor_container_destino{{ $estoque->id }}" style="display: none;">
                                                        <label for="container_destino"><strong>Container de Destino:</strong></label>
                                                        <select class="form-control" id="container_destino{{ $estoque->id }}" name="container_destino" required>
                                                            <option value="">-- Selecione --</option>
                                                            @php
                                                                // Busca produtos marcados como containers que não são o produto atual
                                                                $containers = App\Models\Produto::where('eh_container', 1)
                                                                    ->where('id', '!=', $estoque->id)
                                                                    ->with(['itensEstoque' => function($q) {
                                                                        $q->whereNull('fk_item_pai');
                                                                    }])
                                                                    ->get();
                                                            @endphp
                                                            @foreach($containers as $container)
                                                                <option value="{{ $container->id }}">
                                                                    {{ $container->nome }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="quantidade{{ $estoque->id }}"><strong>Quantidade a Transferir:</strong></label>
                                                        <input type="number" id="quantidade{{ $estoque->id }}" name="quantidade" 
                                                            class="form-control" min="1" max="{{ $totalDisponivel }}" required>
                                                        <small class="text-muted">Máximo: {{ $totalDisponivel }}</small>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="observacao"><strong>Observação:</strong></label>
                                                        <textarea name="observacao" class="form-control" rows="2"
                                                            placeholder="Observações sobre a transferência (opcional)"></textarea>
                                                    </div>
                                                </div>

                                                <div class="modal-footer">
                                                    <button type="submit" class="btn btn-success">
                                                        <i class="fa fa-check"></i> Confirmar Transferência
                                                    </button>
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                                        <i class="fa fa-times"></i> Cancelar
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </tbody>
                    </table>
                      {{ $itens_estoque->links() }}
                </div>

                <!-- Removido botão duplicado de saída múltipla -->
            </div>

            {{--
            <!-- Modal de Saída Múltipla -->
            <div class="modal fade" id="modalSaidaMultipla" tabindex="-1" role="dialog"
                aria-labelledby="modalSaidaMultiplaLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <form id="modal-saida-form" action="{{ route('estoque.saidaMultiplos') }}" method="POST">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title">Confirmar Saída de Produtos</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <p>Confirme as informações de saída para os produtos selecionados:</p>
                                <div id="produtosSelecionadosContainer"></div>
                                <div class="form-group">
                                    <label for="militar">Militar:</label>
                                    <select name="militar" class="form-control" required>
                                        <option value="">Selecione o militar</option>
                                        @foreach ($militares as $militar)
                                            <option value="{{ $militar->id }}">{{ $militar->nome }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="observacao">Observação:</label>
                                    <textarea name="observacao" class="form-control" rows="3" placeholder="Observações sobre a saída (opcional)"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-success">Confirmar Saída</button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            --}}
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectAllCheckbox = document.getElementById('select-all');
            const itemCheckboxes = document.querySelectorAll('.select-item');
            const openModalButton = document.getElementById('open-modal-saida');
            const produtosSelecionadosContainer = document.getElementById('produtosSelecionadosContainer');
            const STORAGE_KEY = 'itensEstoqueSelecionados';

            // Função para obter seleção do localStorage
            function getSelecionados() {
                return JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}');
            }
            // Função para salvar seleção no localStorage
            function setSelecionados(obj) {
                localStorage.setItem(STORAGE_KEY, JSON.stringify(obj));
            }

            // Restaurar seleção ao carregar página
            const selecionados = getSelecionados();
            itemCheckboxes.forEach(checkbox => {
                const estoqueId = checkbox.name.match(/\d+/)[0];
                if (selecionados[estoqueId]) {
                    checkbox.checked = true;
                }
            });
            toggleOpenModalButton();

            selectAllCheckbox.addEventListener('change', function() {
                itemCheckboxes.forEach(checkbox => {
                    const estoqueId = checkbox.name.match(/\d+/)[0];
                    const row = checkbox.closest('tr');
                    const produtoNome = row.querySelector('td:nth-child(2)').innerText.trim();
                    const quantidadeDisponivel = row.querySelector('td:nth-child(3)').innerText.trim();
                    checkbox.checked = selectAllCheckbox.checked;
                    if (checkbox.checked) {
                        selecionados[estoqueId] = {
                            nome: produtoNome,
                            quantidade: quantidadeDisponivel
                        };
                    } else {
                        delete selecionados[estoqueId];
                    }
                });
                setSelecionados(selecionados);
                toggleOpenModalButton();
            });

            itemCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const estoqueId = checkbox.name.match(/\d+/)[0];
                    const row = checkbox.closest('tr');
                    const produtoNome = row.querySelector('td:nth-child(2)').innerText.trim();
                    const quantidadeDisponivel = row.querySelector('td:nth-child(3)').innerText.trim();
                    if (checkbox.checked) {
                        selecionados[estoqueId] = {
                            nome: produtoNome,
                            quantidade: quantidadeDisponivel
                        };
                    } else {
                        delete selecionados[estoqueId];
                    }
                    setSelecionados(selecionados);
                    toggleOpenModalButton();
                });
            });

            function toggleOpenModalButton() {
                const anyChecked = Object.keys(getSelecionados()).length > 0;
                openModalButton.disabled = !anyChecked;
            }

            openModalButton.addEventListener('click', function() {
                produtosSelecionadosContainer.innerHTML = '';
                const selecionadosAtual = getSelecionados();
                Object.entries(selecionadosAtual).forEach(([estoqueId, info]) => {
                    const html = `
                        <div class=\"form-group\">
                            <label>${info.nome} (Disponível: ${info.quantidade})</label>
                            <input type=\"number\" name=\"produtos[${estoqueId}][quantidade]\" class=\"form-control\" min=\"1\" max=\"${info.quantidade}\" required>
                        </div>
                    `;
                    produtosSelecionadosContainer.insertAdjacentHTML('beforeend', html);
                });
                $('#modalSaidaMultipla').modal('show');
            });

            // Limpar seleção ao submeter saída múltipla
            document.getElementById('modal-saida-form').addEventListener('submit', function() {
                localStorage.removeItem(STORAGE_KEY);
            });
        });
    </script>
    <!-- Modal para detalhes por seção -->
    <div class="modal fade" id="modalDetalhesSecao" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Detalhes por Seção</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Seção</th>
                                <th>Quantidade</th>
                            </tr>
                        </thead>
                        <tbody id="detalhesSecaoBody">
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Impedir que o clique no botão de transferência abra os detalhes
    $('.btn-warning').click(function(e) {
        e.stopPropagation();
    });

    // Função para ver detalhes da seção
    function verDetalhesSecao(produtoId) {
        $.get(`/api/produtos/${produtoId}/detalhes-secao`, function(data) {
            let tbody = $('#detalhesSecaoBody');
            tbody.empty();
            
            data.forEach(function(item) {
                tbody.append(`
                    <tr>
                        <td>${item.secao_nome || 'Sem seção'}</td>
                        <td>${item.quantidade}</td>
                    </tr>
                `);
            });
            
            $('#modalDetalhesSecao').modal('show');
        });
    }

    // Adicionar evento de clique nas linhas
    $('.item-row').click(function() {
        const produtoId = $(this).data('produto-id');
        verDetalhesSecao(produtoId);
    });

    // Função para atualizar campos de transferência
    window.atualizarCamposTransferencia = function(select, estoqueId) {
        const tipo = select.value;
        const setorOrigem = document.getElementById(`setor_origem${estoqueId}`);
        const setorContainerOrigem = document.getElementById(`setor_container_origem${estoqueId}`);
        const setorDestino = document.getElementById(`setor_destino${estoqueId}`);
        const setorContainerDestino = document.getElementById(`setor_container_destino${estoqueId}`);
        const localizacaoItem = document.getElementById(`localizacao_item${estoqueId}`);
        
        // Esconder todos
        setorOrigem.style.display = 'none';
        setorContainerOrigem.style.display = 'none';
        setorDestino.style.display = 'none';
        setorContainerDestino.style.display = 'none';
        localizacaoItem.style.display = 'none';
        
        // Mostrar conforme tipo
        switch(tipo) {
            case 'secao_para_secao':
                localizacaoItem.style.display = 'block';
                setorDestino.style.display = 'block';
                document.getElementById(`secao_destino${estoqueId}`).setAttribute('name', 'secao_destino');
                break;
            case 'secao_para_container':
                localizacaoItem.style.display = 'block';
                setorContainerDestino.style.display = 'block';
                break;
            case 'container_para_secao':
                setorContainerOrigem.style.display = 'block';
                setorDestino.style.display = 'block';
                document.getElementById(`secao_destino${estoqueId}`).setAttribute('name', 'secao_destino');
                break;
        }
    };

    // Função para atualizar origem do item (solto ou em container)
    window.atualizarOrigemItem = function(select, estoqueId) {
        const localizacao = select.value;
        const setorOrigem = document.getElementById(`setor_origem${estoqueId}`);
        const setorContainerOrigem = document.getElementById(`setor_container_origem${estoqueId}`);
        
        if (localizacao === 'solto') {
            setorOrigem.style.display = 'block';
            setorContainerOrigem.style.display = 'none';
        } else if (localizacao === 'em_container') {
            setorOrigem.style.display = 'none';
            setorContainerOrigem.style.display = 'block';
        } else {
            setorOrigem.style.display = 'none';
            setorContainerOrigem.style.display = 'none';
        }
    };

    // Função para atualizar quantidade disponível
    window.atualizarQuantidadeDisponivel = function(select, estoqueId) {
        const quantidade = select.options[select.selectedIndex].getAttribute('data-quantidade');
        const tipo = document.getElementById(`tipoTransferencia${estoqueId}`).value;
        
        let label;
        if (tipo.includes('secao')) {
            label = document.getElementById(`qtd_secao_origem${estoqueId}`);
        } else {
            label = document.getElementById(`qtd_container_origem${estoqueId}`);
        }
        
        if (label && quantidade) {
            label.textContent = `Quantidade disponível: ${quantidade}`;
            document.getElementById(`quantidade${estoqueId}`).setAttribute('max', quantidade);
        }
    };
});
</script>
@endpush

```
