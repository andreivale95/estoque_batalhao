@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Cabeçalho -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="mb-0">
                    <i class="fas fa-boxes"></i> Estoque Unificado
                </h1>
                <a href="{{ route('estoque.listar') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Volta para Estoque
                </a>
            </div>
            <p class="text-muted mt-2">Visualização unificada de itens de consumo e permanente</p>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('estoque.unificado') }}" class="row g-3 align-items-end">
                <!-- Busca -->
                <div class="col-md-4">
                    <label for="search" class="form-label">
                        <i class="fas fa-search"></i> Buscar Item
                    </label>
                          <input type="text" class="form-control" id="search" name="search" 
                              placeholder="Nome do item..." 
                           value="{{ $filtros['search'] ?? '' }}">
                </div>

                <!-- Tipo -->
                <div class="col-md-2">
                    <label for="tipo" class="form-label">Tipo</label>
                    <select class="form-select" id="tipo" name="tipo">
                        <option value="">Todos</option>
                        <option value="consumo" {{ ($filtros['tipo'] ?? '') === 'consumo' ? 'selected' : '' }}>
                            Consumo
                        </option>
                        <option value="permanente" {{ ($filtros['tipo'] ?? '') === 'permanente' ? 'selected' : '' }}>
                            Permanente
                        </option>
                    </select>
                </div>

                <!-- Categoria -->
                <div class="col-md-3">
                    <label for="categoria" class="form-label">Categoria</label>
                    <select class="form-select" id="categoria" name="categoria">
                        <option value="">Todas</option>
                        @foreach($categorias as $cat)
                            <option value="{{ $cat->id }}" 
                                    {{ ($filtros['fk_categoria'] ?? '') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Seção -->
                <div class="col-md-3">
                    <label for="secao" class="form-label">Seção</label>
                    <select class="form-select" id="secao" name="secao">
                        <option value="">Todas</option>
                        @foreach($secoes as $sec)
                            <option value="{{ $sec->id }}" 
                                    {{ ($filtros['fk_secao'] ?? '') == $sec->id ? 'selected' : '' }}>
                                {{ $sec->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Botões -->
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>
                    <a href="{{ route('estoque.unificado') }}" class="btn btn-secondary">
                        <i class="fas fa-redo"></i> Limpar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabela -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 5%;">
                            <i class="fas fa-cube"></i>
                        </th>
                        <th style="width: 25%;">Item</th>
                        <th style="width: 15%;">Seção</th>
                        <th style="width: 10%;" class="text-center">Cautelado</th>
                        <th style="width: 10%;" class="text-center">Disponível</th>
                        <th style="width: 10%;" class="text-center">Total</th>
                        <th style="width: 10%;">Unidade</th>
                        <th style="width: 10%;">Categoria</th>
                        <th style="width: 15%;" class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($itens as $item)
                        <tr>
                            <!-- Badge de Tipo -->
                            <td>
                                @if($item->tipo === 'consumo')
                                    <span class="badge bg-success" title="Consumo">
                                        <i class="fas fa-layer-group"></i>
                                    </span>
                                @else
                                    <span class="badge bg-info" title="Permanente">
                                        <i class="fas fa-shield-alt"></i>
                                    </span>
                                @endif
                            </td>

                            <!-- Item (Nome do Produto) -->
                            <td>
                                <div class="fw-bold">{{ $item->nome_produto }}</div>
                                @if($item->serie)
                                    <small class="text-muted d-block">Série: {{ $item->serie }}</small>
                                @endif
                            </td>

                            <!-- Seção -->
                            <td>
                                @if($item->secao_nome)
                                    <span class="badge bg-light text-dark">{{ $item->secao_nome }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>

                            <!-- Cautelado -->
                            <td class="text-center">
                                @if($item->quantidade_cautelada > 0)
                                    <span class="badge bg-warning text-dark">
                                        {{ $item->quantidade_cautelada }}
                                    </span>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                            </td>

                            <!-- Disponível -->
                            <td class="text-center">
                                @if($item->tipo === 'consumo')
                                    @if($item->disponivel > 0)
                                        <span class="badge bg-success">{{ $item->disponivel }}</span>
                                    @else
                                        <span class="text-muted">0</span>
                                    @endif
                                @else
                                    @if($item->disponivel > 0)
                                        <span class="badge bg-success">1</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Cautelado</span>
                                    @endif
                                @endif
                            </td>

                            <!-- Total -->
                            <td class="text-center">
                                @if($item->tipo === 'consumo')
                                    <span class="fw-bold">{{ $item->quantidade_total }}</span>
                                @else
                                    <span class="fw-bold">1</span>
                                @endif
                            </td>

                            <!-- Unidade -->
                            <td>
                                @if($item->unidade)
                                    <span class="badge bg-secondary">{{ $item->unidade }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>

                            <!-- Categoria -->
                            <td>
                                @if($item->categoria_nome)
                                    <span class="badge bg-light text-dark">{{ $item->categoria_nome }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>

                            <!-- Ações -->
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    @if($item->tipo === 'consumo')
                                        <!-- Entrada Consumo -->
                                        <a href="{{ route('entrada.form', ['id' => $item->id]) }}" 
                                           class="btn btn-outline-primary" title="Entrada">
                                            <i class="fas fa-arrow-down"></i>
                                        </a>
                                        
                                        <!-- Saída Consumo -->
                                        <a href="{{ route('saida.form', ['id' => $item->id]) }}" 
                                           class="btn btn-outline-danger" title="Saída">
                                            <i class="fas fa-arrow-up"></i>
                                        </a>
                                    @else
                                        <!-- Ver Detalhes Permanente -->
                                        <a href="{{ route('estoque.produto.detalhes', ['id' => $item->fk_produto]) }}" 
                                           class="btn btn-outline-info" title="Ver Detalhes">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    @endif

                                    <!-- Ver Produto -->
                                    <a href="{{ route('produto.ver', ['id' => $item->fk_produto]) }}" 
                                       class="btn btn-outline-secondary" title="Ver Produto">
                                        <i class="fas fa-info-circle"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fa-2x mb-3 d-block"></i>
                                Nenhum item encontrado
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginação -->
        @if($itens->total() > 0)
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Mostrando <strong>{{ ($itens->currentPage() - 1) * $itens->perPage() + 1 }}</strong> 
                        até <strong>{{ min($itens->currentPage() * $itens->perPage(), $itens->total()) }}</strong> 
                        de <strong>{{ $itens->total() }}</strong> itens
                    </div>
                    {{ $itens->links() }}
                </div>
            </div>
        @endif
    </div>
</div>

<style>
    .table-hover tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.02);
    }
    
    .badge {
        font-size: 0.85rem;
        font-weight: 500;
    }
    
    code {
        background-color: #f8f9fa;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 0.85rem;
    }
</style>
@endsection
