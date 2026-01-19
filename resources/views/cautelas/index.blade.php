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
                                    <button type="button" class="btn btn-sm btn-warning preview-comprovante-btn" 
                                        data-cautela-id="{{ $cautela->id }}" title="Visualizar Comprovante">
                                        <i class="fa fa-search"></i>
                                    </button>
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

    /* Modal Styles */
    .modal-preview {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.6);
        overflow: auto;
    }

    .modal-preview-content {
        background-color: #fff;
        margin: 30px auto;
        padding: 0;
        width: 95%;
        max-width: 1000px;
        height: 85vh;
        display: flex;
        flex-direction: column;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
        border-radius: 4px;
        overflow: hidden;
    }

    .modal-preview-header {
        background: linear-gradient(135deg, #3c8dbc 0%, #2c6a8f 100%);
        color: white;
        padding: 15px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-shrink: 0;
    }

    .modal-preview-header h3 {
        margin: 0;
        font-size: 18px;
    }

    .modal-preview-close {
        background: none;
        border: none;
        color: white;
        font-size: 28px;
        cursor: pointer;
        padding: 0;
        line-height: 1;
        transition: opacity 0.2s;
    }

    .modal-preview-close:hover {
        opacity: 0.7;
    }

    .modal-preview-body {
        flex: 1;
        overflow-y: auto;
        padding: 0;
        background: white;
    }

    .modal-preview-footer {
        border-top: 1px solid #ddd;
        padding: 15px 20px;
        text-align: right;
        background-color: #f8f9fa;
        flex-shrink: 0;
    }

    .modal-preview-footer .btn {
        margin-left: 10px;
    }

    /* Scrollbar customization */
    .modal-preview-body::-webkit-scrollbar {
        width: 8px;
    }

    .modal-preview-body::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .modal-preview-body::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }

    .modal-preview-body::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
</style>

<!-- Modal para Preview do Comprovante -->
<div id="modal-preview-comprovante" class="modal-preview">
    <div class="modal-preview-content">
        <div class="modal-preview-header">
            <h3><i class="fa fa-file-pdf-o"></i> Visualizar Comprovante</h3>
            <button id="btn-close-modal" class="modal-preview-close" title="Fechar">&times;</button>
        </div>
        <div class="modal-preview-body" id="comprovante-content">
            <!-- Conteúdo carregado via AJAX -->
        </div>
        <div class="modal-preview-footer">
            <button id="btn-download-pdf" class="btn btn-danger" onclick="downloadPDF()">
                <i class="fa fa-download"></i> Baixar PDF
            </button>
            <button class="btn btn-default" onclick="document.getElementById('modal-preview-comprovante').style.display='none'">
                Fechar
            </button>
        </div>
    </div>
</div>

<script>
    function downloadPDF() {
        const modal = document.getElementById('modal-preview-comprovante');
        const cautelaId = modal.getAttribute('data-cautela-id');
        const downloadUrl = '{{ route("cautelas.pdf", ":id") }}'.replace(':id', cautelaId);
        window.open(downloadUrl, '_blank');
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Handler para botão de preview do comprovante
        document.querySelectorAll('.preview-comprovante-btn').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const cautelaId = this.getAttribute('data-cautela-id');
                const modal = document.getElementById('modal-preview-comprovante');
                const previewUrl = '{{ route("cautelas.preview", ":id") }}'.replace(':id', cautelaId);
                
                // Carregar o conteúdo via fetch
                fetch(previewUrl)
                    .then(response => response.text())
                    .then(html => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const comprovante = doc.querySelector('.comprovante-preview') || doc.body;
                        
                        if (comprovante) {
                            document.getElementById('comprovante-content').innerHTML = comprovante.innerHTML;
                            // Store the cautela ID for download
                            modal.setAttribute('data-cautela-id', cautelaId);
                            modal.style.display = 'block';
                        }
                    })
                    .catch(error => {
                        alert('Erro ao carregar comprovante: ' + error);
                        console.error('Error:', error);
                    });
            });
        });

        // Handler para fechar o modal
        document.getElementById('btn-close-modal').addEventListener('click', function() {
            document.getElementById('modal-preview-comprovante').style.display = 'none';
        });

        // Fechar modal ao clicar fora
        window.addEventListener('click', function(event) {
            const modal = document.getElementById('modal-preview-comprovante');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        });
    });
</script>
@endsection