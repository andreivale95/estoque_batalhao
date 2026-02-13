@extends('layout.app')
@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-folder"></i> Itens da Seção: {{ $secao->nome }}
            <small>{{ $totalItensSecao }} {{ $totalItensSecao == 1 ? 'item' : 'itens' }}</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{ route('secoes.index', $secao->fk_unidade) }}">Seções</a></li>
            <li class="active">{{ $secao->nome }}</li>
        </ol>
    </section>

    <section class="content container-fluid">
        <style>
            .dragging {
                opacity: 0.5;
                background-color: #ffffcc !important;
            }
            .drag-handle {
                cursor: move;
                color: #999;
                width: 30px;
                text-align: center;
            }
            .drag-handle:hover {
                color: #333;
            }
            .selected-row {
                background-color: #d9edf7 !important;
            }
            .drag-ghost {
                background-color: #5bc0de;
                color: white;
                padding: 5px 10px;
                border-radius: 3px;
                position: fixed;
                pointer-events: none;
                z-index: 9999;
            }
        </style>
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <!-- Tabela de Itens -->
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-cubes"></i> Lista de Itens</h3>
                @if($consumoAgrupado->count() > 0 || $itensPatrimoniais->count() > 0)
                    <div class="pull-right">
                        <button id="salvarOrdem" class="btn btn-success btn-sm">
                            <i class="fa fa-save"></i> Salvar Ordem
                        </button>
                    </div>
                @endif
            </div>
            <div class="box-body table-responsive">
                @if($consumoAgrupado->count() > 0 || $itensPatrimoniais->count() > 0)
                    <table class="table table-striped table-hover" id="tabelaItens">
                        <thead class="bg-primary" style="color:white;">
                            <tr>
                                <th style="width:30px;"><input type="checkbox" id="selectAll"></th>
                                <th style="width:30px;"></th>
                                <th>Item</th>
                                <th>Patrimônio</th>
                                <th>Descrição</th>
                                <th>Quantidade</th>
                            </tr>
                        </thead>
                        <tbody id="sortableBody">
                            @foreach($consumoAgrupado as $dados)
                                <tr draggable="true" data-tipo="consumo" data-id="{{ $dados['produto']->id }}">
                                    <td><input type="checkbox" class="row-checkbox"></td>
                                    <td class="drag-handle"><i class="fa fa-bars"></i></td>
                                    <td>{{ $dados['produto']->nome ?? 'Sem Nome' }}</td>
                                    <td>-</td>
                                    <td>{{ $dados['produto']->descricao ?? '-' }}</td>
                                    <td>{{ $dados['quantidade'] }}</td>
                                </tr>
                            @endforeach
                            @foreach($itensPatrimoniais as $patrimonio)
                                <tr draggable="true" data-tipo="patrimonial" data-id="{{ $patrimonio->id }}">
                                    <td><input type="checkbox" class="row-checkbox"></td>
                                    <td class="drag-handle"><i class="fa fa-bars"></i></td>
                                    <td>{{ $patrimonio->produto->nome ?? 'Sem Nome' }}</td>
                                    <td>{{ $patrimonio->patrimonio ?? '-' }}</td>
                                    <td>{{ $patrimonio->produto->descricao ?? '-' }}</td>
                                    <td>1</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i> Nenhum item vinculado a esta seção.
                    </div>
                @endif
            </div>
        </div>

        <!-- Botões de Ação -->
        <div style="margin-top: 15px;">
            <a href="{{ route('secoes.index', $secao->fk_unidade) }}" class="btn btn-default">
                <i class="fa fa-arrow-left"></i> Voltar
            </a>
            @if($itensConsumo->count() > 0 || $itensPatrimoniais->count() > 0)
                <a href="{{ route('secoes.transferir_lote_form', ['unidade' => $secao->fk_unidade, 'secao' => $secao->id]) }}" class="btn btn-warning">
                    <i class="fa fa-exchange"></i> Transferir Itens
                </a>
                <a href="{{ route('secoes.pdf', ['unidade' => $secao->fk_unidade, 'secao' => $secao->id]) }}" class="btn btn-danger" target="_blank">
                    <i class="fa fa-file-pdf-o"></i> Gerar PDF
                </a>
            @endif
        </div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tbody = document.getElementById('sortableBody');
    if (!tbody) return;

    let draggedElements = [];
    let ghostElement = null;

    // Select All functionality
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.row-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = this.checked;
            updateRowSelection(cb.closest('tr'), this.checked);
        });
    });

    // Individual checkbox selection
    document.querySelectorAll('.row-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function(e) {
            e.stopPropagation();
            updateRowSelection(this.closest('tr'), this.checked);
            updateSelectAllState();
        });
    });

    function updateRowSelection(row, selected) {
        if (selected) {
            row.classList.add('selected-row');
        } else {
            row.classList.remove('selected-row');
        }
    }

    function updateSelectAllState() {
        const checkboxes = document.querySelectorAll('.row-checkbox');
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        document.getElementById('selectAll').checked = allChecked;
    }

    tbody.addEventListener('dragstart', function(e) {
        if (e.target.tagName === 'TR') {
            const selectedCheckboxes = document.querySelectorAll('.row-checkbox:checked');
            const draggedRow = e.target;
            
            if (selectedCheckboxes.length > 0) {
                // Drag multiple selected items
                draggedElements = Array.from(selectedCheckboxes).map(cb => cb.closest('tr'));
                
                // If dragged row is not selected, only drag that one
                if (!draggedRow.querySelector('.row-checkbox').checked) {
                    draggedElements = [draggedRow];
                }
            } else {
                // Drag single item
                draggedElements = [draggedRow];
            }

            // Create ghost element
            ghostElement = document.createElement('div');
            ghostElement.className = 'drag-ghost';
            ghostElement.textContent = draggedElements.length > 1 
                ? `Movendo ${draggedElements.length} itens` 
                : 'Movendo 1 item';
            document.body.appendChild(ghostElement);

            draggedElements.forEach(el => el.classList.add('dragging'));
            
            // Set ghost position
            ghostElement.style.left = e.clientX + 10 + 'px';
            ghostElement.style.top = e.clientY + 10 + 'px';
        }
    });

    tbody.addEventListener('drag', function(e) {
        if (ghostElement) {
            ghostElement.style.left = e.clientX + 10 + 'px';
            ghostElement.style.top = e.clientY + 10 + 'px';
        }
    });

    tbody.addEventListener('dragend', function(e) {
        if (e.target.tagName === 'TR') {
            draggedElements.forEach(el => el.classList.remove('dragging'));
            if (ghostElement) {
                document.body.removeChild(ghostElement);
                ghostElement = null;
            }
            draggedElements = [];
        }
    });

    tbody.addEventListener('dragover', function(e) {
        e.preventDefault();
        if (draggedElements.length === 0) return;

        const afterElement = getDragAfterElement(tbody, e.clientY);
        const firstDraggedElement = draggedElements[0];

        if (afterElement == null) {
            // Move all selected elements to the end
            draggedElements.forEach(el => {
                tbody.appendChild(el);
            });
        } else {
            // Move all selected elements before afterElement
            draggedElements.forEach(el => {
                tbody.insertBefore(el, afterElement);
            });
        }
    });

    function getDragAfterElement(container, y) {
        const draggableElements = [...container.querySelectorAll('tr:not(.dragging)')];
        return draggableElements.reduce((closest, child) => {
            const box = child.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;
            if (offset < 0 && offset > closest.offset) {
                return { offset: offset, element: child };
            } else {
                return closest;
            }
        }, { offset: Number.NEGATIVE_INFINITY }).element;
    }

    document.getElementById('salvarOrdem').addEventListener('click', function() {
        const linhas = tbody.querySelectorAll('tr');
        const ordens = [];
        linhas.forEach((linha, index) => {
            ordens.push({
                tipo: linha.getAttribute('data-tipo'),
                id: linha.getAttribute('data-id'),
                ordem: index + 1
            });
        });

        fetch('{{ route('secoes.reordenar', ['unidade' => $secao->fk_unidade, 'secao' => $secao->id]) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ ordens: ordens })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Ordem salva com sucesso!');
                // Clear selections
                document.querySelectorAll('.row-checkbox').forEach(cb => {
                    cb.checked = false;
                    updateRowSelection(cb.closest('tr'), false);
                });
                document.getElementById('selectAll').checked = false;
            } else {
                alert('Erro ao salvar ordem: ' + (data.message || 'Desconhecido'));
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao salvar ordem.');
        });
    });
});
</script>
@endsection

