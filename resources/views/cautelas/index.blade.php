@extends('layout.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Cautelas
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{route('dashboard')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">Cautelas</li>
        </ol>
    </section>

    <section class="content container-fluid">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Lista de Cautelas</h3>
                <div class="box-tools pull-right">
                    <a href="{{ route('cautelas.create') }}" class="btn btn-success">
                        <i class="fa fa-plus"></i> Nova Cautela
                    </a>
                </div>
            </div>
            
            <!-- Filtros -->
            <div class="box-body" style="border-bottom: 1px solid #ddd; padding-bottom: 15px;">
                <form method="GET" action="{{ route('cautelas.index') }}" class="form-inline" role="search">
                    <div class="form-group" style="margin-right: 15px;">
                        <label for="responsavel">Responsável:</label>
                        <input type="text" id="responsavel" name="responsavel" class="form-control" 
                            placeholder="Nome do responsável" value="{{ $filtros['responsavel'] ?? '' }}">
                    </div>
                    
                    <div class="form-group" style="margin-right: 15px;">
                        <label for="data_inicio">Data Início:</label>
                        <input type="date" id="data_inicio" name="data_inicio" class="form-control" 
                            value="{{ $filtros['data_inicio'] ?? '' }}">
                    </div>
                    
                    <div class="form-group" style="margin-right: 15px;">
                        <label for="data_fim">Data Fim:</label>
                        <input type="date" id="data_fim" name="data_fim" class="form-control" 
                            value="{{ $filtros['data_fim'] ?? '' }}">
                    </div>
                    
                    <div class="form-group" style="margin-right: 15px;">
                        <label for="status">Status:</label>
                        <select id="status" name="status" class="form-control">
                            <option value="">-- Todos --</option>
                            <option value="pendente" {{ $filtros['status'] === 'pendente' ? 'selected' : '' }}>Pendente</option>
                            <option value="concluido" {{ $filtros['status'] === 'concluido' ? 'selected' : '' }}>Concluído</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-search"></i> Filtrar
                        </button>
                        <a href="{{ route('cautelas.index') }}" class="btn btn-default">
                            <i class="fa fa-times"></i> Limpar
                        </a>
                    </div>
                </form>
            </div>
            
            <div class="box-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th width="30"></th>
                                <th>ID</th>
                                <th>Responsável</th>
                                <th>Telefone</th>
                                <th>Instituição</th>
                                <th>Data Cautela</th>
                                <th>Data Prevista Devolução</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cautelas as $cautela)
                            @php
                                $totalPendente = $cautela->produtos->sum(function($item) { 
                                    return $item->quantidadePendente(); 
                                });
                            @endphp
                            <tr class="cautela-row" data-cautela-id="{{ $cautela->id }}">
                                <td class="expand-toggle" style="cursor: pointer; text-align: center;">
                                    <i class="fa fa-plus"></i>
                                </td>
                                <td>{{ $cautela->id }}</td>
                                <td>{{ $cautela->nome_responsavel }}</td>
                                <td>{{ $cautela->telefone }}</td>
                                <td>{{ $cautela->instituicao }}</td>
                                <td>{{ $cautela->data_cautela->format('d/m/Y') }}</td>
                                <td>{{ $cautela->data_prevista_devolucao->format('d/m/Y') }}</td>
                                <td>
                                    @if($totalPendente == 0)
                                        <span class="label label-success">Devolvido</span>
                                    @else
                                        <span class="label label-warning">Pendente</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('cautelas.show', $cautela->id) }}" class="btn btn-sm btn-info" title="Ver Detalhes">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    @if($totalPendente > 0)
                                    <a href="{{ route('cautelas.devolucao', $cautela->id) }}" class="btn btn-sm btn-success" title="Registrar Devolução">
                                        <i class="fa fa-check"></i>
                                    </a>
                                    @endif
                                </td>
                            </tr>
                            <!-- Linha de detalhe (inicialmente oculta) -->
                            <tr class="cautela-detail-row" id="detail-{{ $cautela->id }}" style="display: none;">
                                <td colspan="9">
                                    <div class="cautela-items" style="padding: 15px; background-color: #f9f9f9;">
                                        <h5 style="margin-top: 0; margin-bottom: 10px;">Itens Cautelados</h5>
                                        @if($cautela->produtos->count() > 0)
                                            <table class="table table-sm table-condensed" style="margin-bottom: 0;">
                                                <thead>
                                                    <tr style="background-color: #e8e8e8;">
                                                        <th>Produto</th>
                                                        <th>Quantidade</th>
                                                        <th>Devolvida</th>
                                                        <th>Pendente</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($cautela->produtos as $item)
                                                    <tr>
                                                        <td>
                                                            <strong>{{ $item->produto->nome ?? 'Produto Desconhecido' }}</strong>
                                                        </td>
                                                        <td>{{ $item->quantidade }}</td>
                                                        <td>{{ $item->quantidade_devolvida }}</td>
                                                        <td>{{ $item->quantidadePendente() }}</td>
                                                        <td>
                                                            @if($item->isDevolvido())
                                                                <span class="label label-success">Devolvido</span>
                                                            @else
                                                                <span class="label label-warning">Pendente</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        @else
                                            <p class="text-muted">Nenhum item cautelado.</p>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center">Nenhuma cautela cadastrada.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Expandir/Colapsar ao clicar na linha
    document.querySelectorAll('.expand-toggle').forEach(function(toggle) {
        toggle.addEventListener('click', function(e) {
            e.stopPropagation();
            const row = this.closest('.cautela-row');
            const cautelaId = row.getAttribute('data-cautela-id');
            const detailRow = document.getElementById('detail-' + cautelaId);
            const icon = this.querySelector('i');
            
            if (detailRow.style.display === 'none') {
                detailRow.style.display = 'table-row';
                icon.classList.remove('fa-plus');
                icon.classList.add('fa-minus');
            } else {
                detailRow.style.display = 'none';
                icon.classList.remove('fa-minus');
                icon.classList.add('fa-plus');
            }
        });
    });
});
</script>

<style>
    .expand-toggle {
        user-select: none;
    }
    
    .expand-toggle:hover {
        background-color: #f5f5f5;
    }
    
    .cautela-items {
        animation: slideDown 0.3s ease-out;
    }
    
    @keyframes slideDown {
        from {
            opacity: 0;
            max-height: 0;
        }
        to {
            opacity: 1;
            max-height: 500px;
        }
    }
</style>
@endsection