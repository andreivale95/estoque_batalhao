@extends('layout.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Detalhes da Cautela #{{ $cautela->id }}
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{route('dashboard')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{ route('cautelas.index') }}">Cautelas</a></li>
            <li class="active">Detalhes</li>
        </ol>
    </section>

    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Informações da Cautela</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-warning btn-sm preview-comprovante-btn" 
                                data-cautela-id="{{ $cautela->id }}" title="Visualizar Comprovante">
                                <i class="fa fa-search"></i> Visualizar Comprovante
                            </button>
                            <a href="{{ route('cautelas.pdf', $cautela->id) }}" class="btn btn-danger btn-sm" target="_blank">
                                <i class="fa fa-file-pdf-o"></i> Baixar PDF
                            </a>
                        </div>
                    </div>
                    <div class="box-body">
                        <table class="table table-borderless">
                            <tr>
                                <th style="width: 200px;">Responsável:</th>
                                <td>{{ $cautela->nome_responsavel }}</td>
                            </tr>
                            <tr>
                                <th>Telefone:</th>
                                <td>{{ $cautela->telefone }}</td>
                            </tr>
                            <tr>
                                <th>Instituição:</th>
                                <td>{{ $cautela->instituicao }}</td>
                            </tr>
                            <tr>
                                <th>Responsável da Unidade:</th>
                                <td>{{ $cautela->responsavel_unidade ?? '(não informado)' }}</td>
                            </tr>
                            <tr>
                                <th>Data da Cautela:</th>
                                <td>{{ $cautela->data_cautela->format('d/m/Y') }}</td>
                            </tr>
                            <tr>
                                <th>Data Prevista de Devolução:</th>
                                <td>{{ $cautela->data_prevista_devolucao->format('d/m/Y') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">Itens da Cautela</h3>
                    </div>
                    <div class="box-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Produto</th>
                                        <th>Seção</th>
                                        <th>Qtd Cautelada</th>
                                        <th>Qtd Devolvida</th>
                                        <th>Qtd Pendente</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($cautela->produtos as $item)
                                    <tr>
                                        <td>{{ $item->produto->nome }}</td>
                                        <td>{{ $item->estoque->secao->nome ?? 'Sem seção' }}</td>
                                        <td>{{ $item->quantidade }}</td>
                                        <td>{{ $item->quantidade_devolvida }}</td>
                                        <td>{{ $item->quantidadePendente() }}</td>
                                        <td>
                                            @if($item->isDevolvido())
                                                <span class="label label-success">Devolvido</span>
                                                @if($item->data_devolucao)
                                                <br><small>{{ $item->data_devolucao->format('d/m/Y') }}</small>
                                                @endif
                                            @else
                                                <span class="label label-warning">Pendente</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <a href="{{ route('cautelas.index') }}" class="btn btn-default">
                    <i class="fa fa-arrow-left"></i> Voltar
                </a>
                @if($cautela->produtos->sum(function($item) { return $item->quantidadePendente(); }) > 0)
                <a href="{{ route('cautelas.devolucao', $cautela->id) }}" class="btn btn-success">
                    <i class="fa fa-check"></i> Registrar Devolução
                </a>
                @else
                <span class="label label-success" style="font-size: 14px; padding: 8px 12px;">
                    <i class="fa fa-check-circle"></i> Todos os itens foram devolvidos
                </span>
                @endif
            </div>
        </div>
    </section>
</div>

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

<style>
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
                
                fetch(previewUrl)
                    .then(response => response.text())
                    .then(html => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const comprovante = doc.querySelector('.comprovante-preview') || doc.body;
                        
                        if (comprovante) {
                            document.getElementById('comprovante-content').innerHTML = comprovante.innerHTML;
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
        if (document.getElementById('btn-close-modal')) {
            document.getElementById('btn-close-modal').addEventListener('click', function() {
                document.getElementById('modal-preview-comprovante').style.display = 'none';
            });
        }

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