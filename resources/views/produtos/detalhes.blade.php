@extends('layout.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>Detalhes do Produto</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{ route('estoque.listar') }}">Inventário</a></li>
            <li class="active">Detalhes</li>
        </ol>
    </section>

    <section class="content container-fluid">
        <div class="box box-primary">
            <div class="box-body">
                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-md-8">
                        <h3>{{ $produto->nome }}</h3>
                        <p><strong>Descrição:</strong> {{ $produto->descricao ?? '-' }}</p>
                        <p><strong>Marca:</strong> {{ $produto->marca ?? '-' }}</p>
                        <p><strong>Quantidade total:</strong> {{ $quantidadeTotal }}</p>
                    </div>
                    <div class="col-md-4" style="text-align: center;">
                        @php
                            $fotoProduto = $produto->fotos->sortBy('ordem')->first();
                        @endphp
                        <div style="border: 1px solid #ddd; padding: 10px; border-radius: 4px; background-color: #f9f9f9;">
                            <h5 style="margin-top: 0;">Foto do Produto</h5>
                            @if($fotoProduto)
                                <a href="{{ $fotoProduto->url }}" target="_blank" title="Abrir imagem em tamanho real">
                                    <img src="{{ $fotoProduto->url }}" alt="Foto do produto" style="max-width: 200px; max-height: 200px; object-fit: contain;">
                                </a>
                            @else
                                <div style="width: 200px; height: 200px; margin: 0 auto; border: 1px dashed #ccc; display: flex; align-items: center; justify-content: center; color: #888;">
                                    Sem foto
                                </div>
                            @endif

                            <form action="{{ route('estoque.produto.foto', $produto->id) }}" method="POST" enctype="multipart/form-data" style="margin-top: 10px; text-align: left;" id="form-foto-produto">
                                @csrf
                                <div class="form-group" style="margin-bottom: 8px;">
                                    <input type="file" name="foto" accept="image/*" class="form-control" id="foto-input" required>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fa fa-upload"></i> Atualizar Foto
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <hr>
                <h4>Localização dos Itens</h4>
                <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
                    @if(($produto->tipo_controle ?? '') === 'permanente')
                        @php
                            $secoesByPatrimonio = [];
                            foreach($itensPatrimoniais as $item) {
                                $secaoId = $item->fk_secao;
                                $secaoNome = $item->secao->nome ?? 'Sem seção';
                                if (!isset($secoesByPatrimonio[$secaoId])) {
                                    $secoesByPatrimonio[$secaoId] = [
                                        'nome' => $secaoNome,
                                        'itens' => []
                                    ];
                                }
                                $secoesByPatrimonio[$secaoId]['itens'][] = $item;
                            }
                        @endphp

                        @forelse($secoesByPatrimonio as $secaoId => $secao)
                            <div class="panel panel-default">
                                <div class="panel-heading" role="tab" id="heading-{{ $secaoId }}">
                                    <h4 class="panel-title">
                                        <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse-{{ $secaoId }}" aria-expanded="false" aria-controls="collapse-{{ $secaoId }}">
                                            <i class="fa fa-folder"></i> Seção: {{ $secao['nome'] }}
                                            <span class="badge bg-blue" style="margin-left: 10px;">{{ count($secao['itens']) }} {{ count($secao['itens']) == 1 ? 'item' : 'itens' }}</span>
                                        </a>
                                    </h4>
                                </div>
                                <div id="collapse-{{ $secaoId }}" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading-{{ $secaoId }}">
                                    <div class="panel-body">
                                        <div class="list-group">
                                            @foreach($secao['itens'] as $item)
                                                <div class="list-group-item">
                                                    <p style="margin: 4px 0;">
                                                        <strong>Patrimônio:</strong> {{ $item->patrimonio }}
                                                    </p>
                                                    <p style="margin: 4px 0; color: #666; font-size: 12px;">
                                                        Série: {{ $item->serie ?? '-' }} | Condição: {{ ucfirst($item->condicao ?? 'bom') }}
                                                        | Status: {{ $item->quantidade_cautelada > 0 ? 'Cautelado' : 'Disponível' }}
                                                    </p>
                                                    @if($item->observacao)
                                                        <p style="margin: 4px 0; color: #666; font-size: 12px;">
                                                            Observação: {{ $item->observacao }}
                                                        </p>
                                                    @endif
                                                    @if(isset($item->fotos) && $item->fotos->count() > 0)
                                                        <div style="margin-top: 6px; display: flex; gap: 6px; flex-wrap: wrap;">
                                                            @foreach($item->fotos as $foto)
                                                                <a href="{{ $foto->url }}" target="_blank" title="Abrir imagem">
                                                                    <img src="{{ $foto->url }}" alt="Foto do patrimônio" style="width: 80px; height: 80px; object-fit: cover; border: 1px solid #ddd; border-radius: 4px;">
                                                                </a>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal de Transferência de Patrimônios -->
                                <div class="modal fade" id="modalTransferenciaPatrimonios{{ $secaoId }}" tabindex="-1"
                                    role="dialog" aria-hidden="true">
                                    <div class="modal-dialog modal-lg" role="document">
                                        <form action="{{ route('estoque.transferir.patrimonios') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="fk_produto" value="{{ $produto->id }}">
                                            <div class="modal-content">
                                                <div class="modal-header bg-warning">
                                                    <h5 class="modal-title"><i class="fa fa-exchange-alt"></i> Transferir Patrimônios</h5>
                                                    <button type="button" class="close" data-dismiss="modal">
                                                        <span>&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <p><strong>Produto:</strong> {{ $produto->nome }}</p>
                                                    <p><strong>Seção de origem:</strong> {{ $secao['nome'] }}</p>
                                                    
                                                    <hr>
                                                    
                                                    <h6><i class="fa fa-barcode"></i> Selecione os patrimônios para transferir:</h6>
                                                    <div style="border: 1px solid #ddd; padding: 10px; border-radius: 4px; max-height: 300px; overflow-y: auto;">
                                                        @foreach($secao['itens'] as $item)
                                                            <div style="margin: 8px 0;">
                                                                <label style="display: flex; align-items: center; cursor: pointer; margin: 0;">
                                                                    <input type="checkbox" name="patrimonio_ids[]" value="{{ $item->id }}" style="margin-right: 8px;">
                                                                    <span>
                                                                        <strong>{{ $item->patrimonio }}</strong> 
                                                                        (Série: {{ $item->serie ?? '-' }}, Condição: {{ ucfirst($item->condicao ?? 'bom') }})
                                                                    </span>
                                                                </label>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                    
                                                    <hr>
                                                    
                                                    <div class="form-group">
                                                        <label for="fk_secao_destino"><strong>Seção de Destino:</strong></label>
                                                        <select class="form-control" name="fk_secao_destino" required>
                                                            <option value="">-- Selecione a seção de destino --</option>
                                                            @foreach($todasSecoes as $s)
                                                                @if($s->id !== $secaoId)
                                                                    <option value="{{ $s->id }}">{{ $s->nome }}</option>
                                                                @endif
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                                        Cancelar
                                                    </button>
                                                    <button type="submit" class="btn btn-success" onclick="return validarTransferenciaPatrimonios{{ $secaoId }}()">
                                                        <i class="fa fa-check"></i> Confirmar Transferência
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <script>
                                function validarTransferenciaPatrimonios{{ $secaoId }}() {
                                    var checkboxes = document.querySelectorAll('#modalTransferenciaPatrimonios{{ $secaoId }} input[name="patrimonio_ids[]"]:checked');
                                    if (checkboxes.length === 0) {
                                        alert('Selecione pelo menos um patrimônio para transferir.');
                                        return false;
                                    }
                                    return true;
                                }
                                </script>
                            </div>
                        @empty
                            <div class="alert alert-info">
                                Nenhum item permanente cadastrado.
                            </div>
                        @endforelse
                    @else
                        @php
                            $secoesByItem = [];
                            foreach($todosOsItens as $item) {
                                $secaoId = $item->fk_secao;
                                $secaoNome = $item->secao->nome ?? '-';
                                if (!isset($secoesByItem[$secaoId])) {
                                    $secoesByItem[$secaoId] = [
                                        'nome' => $secaoNome,
                                        'itensRaiz' => [],
                                        'itensEmContainers' => [],
                                        'todosItens' => []
                                    ];
                                }
                                $secoesByItem[$secaoId]['todosItens'][] = $item;
                                if(is_null($item->fk_item_pai)) {
                                    $secoesByItem[$secaoId]['itensRaiz'][] = $item;
                                } else {
                                    $secoesByItem[$secaoId]['itensEmContainers'][] = $item;
                                }
                            }
                        @endphp

                        @forelse($secoesByItem as $secaoId => $secao)
                            <div class="panel panel-default">
                                <div class="panel-heading" role="tab" id="heading-{{ $secaoId }}">
                                    <h4 class="panel-title">
                                        <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse-{{ $secaoId }}" aria-expanded="false" aria-controls="collapse-{{ $secaoId }}">
                                            <i class="fa fa-folder"></i> Seção: {{ $secao['nome'] }}
                                            <span class="badge bg-blue" style="margin-left: 10px;">{{ count($secao['todosItens']) }} {{ count($secao['todosItens']) == 1 ? 'item' : 'itens' }}</span>
                                        </a>
                                    </h4>
                                </div>
                                <div id="collapse-{{ $secaoId }}" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading-{{ $secaoId }}">
                                    <div class="panel-body">
                                        @php
                                            $gruposSoltos = [];
                                            foreach($secao['itensRaiz'] as $item) {
                                                if(!$item->isContainer()) {
                                                    $chave = $item->fk_produto . '|' . ($item->lote ?? '');
                                                    if (!isset($gruposSoltos[$chave])) {
                                                        $gruposSoltos[$chave] = [
                                                            'item_exemplo' => $item,
                                                            'quantidade' => 0,
                                                            'quantidade_itens' => 0,
                                                            'lote' => $item->lote
                                                        ];
                                                    }
                                                    $gruposSoltos[$chave]['quantidade'] += $item->quantidade;
                                                    $gruposSoltos[$chave]['quantidade_itens']++;
                                                }
                                            }
                                        @endphp

                                        @if(count($gruposSoltos) > 0)
                                            <div>
                                                <h5 style="color: #2b7a78; border-bottom: 2px solid #2b7a78; padding-bottom: 5px; margin-bottom: 10px;">
                                                    <i class="fa fa-cubes"></i> Itens Soltos
                                                </h5>
                                                <div class="list-group">
                                                    @forelse($gruposSoltos as $grupo)
                                                        <div class="list-group-item" style="margin-bottom: 10px;">
                                                            <p style="margin: 5px 0;">
                                                                <i class="fa fa-cube"></i> <strong>{{ $grupo['item_exemplo']->produto->nome ?? 'Sem Nome' }}</strong>
                                                            </p>
                                                            <p style="margin: 5px 0; color: #666;">
                                                                Quantidade por item: <span class="badge bg-green">{{ $grupo['quantidade'] }}</span>
                                                                @if($grupo['quantidade_itens'] > 1)
                                                                    | Total de registros: <span class="badge bg-blue">{{ $grupo['quantidade_itens'] }}</span>
                                                                @endif
                                                                @if($grupo['lote'])
                                                                    | Lote: <span class="badge">{{ $grupo['lote'] }}</span>
                                                                @endif
                                                            </p>
                                                        </div>
                                                    @empty
                                                        <p style="color: #999; font-style: italic;">Nenhum item solto nesta seção</p>
                                                    @endforelse
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Containers com seus itens --}}
                                        @php
                                            $containersComItens = [];
                                            foreach($secao['itensRaiz'] as $item) {
                                                if($item->isContainer()) {
                                                    $containersComItens[$item->id] = $item;
                                                }
                                            }
                                        @endphp

                                        @if(count($containersComItens) > 0)
                                            <div>
                                                <h5 style="color: #0066cc; border-bottom: 2px solid #0066cc; padding-bottom: 5px; margin-bottom: 10px;">
                                                    <i class="fa fa-briefcase"></i> Containers/Bolsas
                                                </h5>
                                                @foreach($containersComItens as $container)
                                                    <div style="margin-bottom: 15px; padding: 10px; background-color: #f0f8ff; border-left: 3px solid #0066cc; border-radius: 3px;">
                                                        <h6 style="margin: 0 0 8px 0; color: #0066cc;">
                                                            <i class="fa fa-briefcase"></i> {{ $container->produto->nome ?? 'Container' }}
                                                            <span class="badge bg-primary" style="margin-left: 5px;">{{ $container->itensFilhos->count() }} item(ns)</span>
                                                        </h6>
                                                        
                                                        @if($container->itensFilhos->count() > 0)
                                                            <div style="margin-left: 15px; border-left: 2px solid #999; padding-left: 10px;">
                                                                @foreach($container->itensFilhos as $filho)
                                                                    <div style="margin: 6px 0; padding: 6px; background-color: #ffffff; border-radius: 2px;">
                                                                        <p style="margin: 3px 0;">
                                                                            <i class="fa fa-arrow-right text-success"></i> 
                                                                            <strong>{{ $filho->produto->nome ?? 'Sem Nome' }}</strong>
                                                                        </p>
                                                                        <p style="margin: 3px 0; color: #666; font-size: 12px;">
                                                                            Quantidade: <span class="badge bg-green">{{ $filho->quantidade }}</span>
                                                                        </p>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @else
                                                            <p style="color: #999; font-style: italic; margin: 5px 0; margin-left: 15px;">Container vazio</p>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif

                                        {{-- Itens dentro de containers (fk_item_pai != null) --}}
                                        @if(count($secao['itensEmContainers']) > 0)
                                            <div style="margin-top: 20px;">
                                                <h5 style="color: #cc7700; border-bottom: 2px solid #cc7700; padding-bottom: 5px; margin-bottom: 10px;">
                                                    <i class="fa fa-sitemap"></i> Itens Dentro de Containers
                                                </h5>
                                                @foreach($secao['itensEmContainers'] as $item)
                                                    @php
                                                        $nomePai = $item->itemPai ? $item->itemPai->produto->nome : 'Container desconhecido';
                                                    @endphp
                                                    <div style="margin-bottom: 10px; padding: 8px; background-color: #fff8e6; border-left: 3px solid #cc7700; border-radius: 3px;">
                                                        <p style="margin: 0 0 5px 0;">
                                                            <i class="fa fa-folder-open text-warning"></i>
                                                            <strong>Dentro de:</strong> <span style="color: #cc7700; font-weight: bold;">{{ $nomePai }}</span>
                                                        </p>
                                                        <p style="margin: 3px 0; color: #666;">
                                                            <i class="fa fa-cube"></i> <strong>{{ $item->produto->nome ?? 'Sem Nome' }}</strong>
                                                        </p>
                                                        <p style="margin: 3px 0; color: #666; font-size: 12px;">
                                                            Quantidade: <span class="badge bg-green">{{ $item->quantidade }}</span>
                                                            @if($item->lote)
                                                                | Lote: <span class="badge">{{ $item->lote }}</span>
                                                            @endif
                                                        </p>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif

                                        {{-- Mensagem se seção vazia --}}
                                        @if(count($secao['itensRaiz']) == 0 && count($secao['itensEmContainers']) == 0)
                                            <div class="alert alert-info">
                                                Nenhum item nesta seção
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="alert alert-info">
                                Nenhuma seção com itens deste produto.
                            </div>
                        @endforelse
                    @endif
                </div>

                <hr>
                <h4>Quantidade por Seção</h4>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Seção</th>
                            <th>Quantidade</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($detalhesSecao as $d)
                            <tr>
                                <td>{{ $d['secao_nome'] }}</td>
                                <td>{{ $d['quantidade'] }}</td>
                                <td>
                                    @if(($produto->tipo_controle ?? '') === 'permanente')
                                        <button type="button" class="btn btn-sm btn-warning" data-toggle="modal"
                                            data-target="#modalTransferenciaPatrimonios{{ $d['secao_id'] }}"
                                            title="Transferir patrimônios desta seção para outra">
                                            <i class="fa fa-exchange-alt"></i>
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-sm btn-warning" data-toggle="modal"
                                            data-target="#modalTransferencia{{ $d['secao_id'] }}"
                                            title="Transferir para outra seção">
                                            <i class="fa fa-exchange-alt"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>

                            <!-- Modal de Transferência entre Seções -->
                            @if(($produto->tipo_controle ?? '') !== 'permanente')
                                <div class="modal fade" id="modalTransferencia{{ $d['secao_id'] }}" tabindex="-1"
                                    role="dialog" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <form action="{{ route('estoque.transferir.secoes') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="fk_produto" value="{{ $produto->id }}">
                                            <input type="hidden" name="fk_secao_origem" value="{{ $d['secao_id'] }}">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Transferir para Outra Seção</h5>
                                                    <button type="button" class="close" data-dismiss="modal">
                                                        <span>&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <p><strong>Produto:</strong> {{ $produto->nome }}</p>
                                                    <p><strong>Seção origem:</strong> {{ $d['secao_nome'] }}</p>
                                                    <p><strong>Disponível:</strong> {{ $d['quantidade'] }}</p>

                                                    <div class="form-group">
                                                        <label for="fk_secao_destino">Seção Destino:</label>
                                                        <select class="form-control" name="fk_secao_destino" required>
                                                            <option value="">-- Selecione --</option>
                                                            @foreach($todasSecoes as $s)
                                                                @if($s->id !== $d['secao_id'])
                                                                    <option value="{{ $s->id }}">{{ $s->nome }}</option>
                                                                @endif
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="quantidade">Quantidade:</label>
                                                        <input type="number" name="quantidade" class="form-control"
                                                            min="1" max="{{ $d['quantidade'] }}" required>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="observacao">Observação:</label>
                                                        <textarea name="observacao" class="form-control" rows="2"></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                                        Cancelar
                                                    </button>
                                                    <button type="submit" class="btn btn-primary">
                                                        Transferir
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            @endif
                        @empty
                            <tr>
                                <td colspan="3">Nenhuma seção vinculada a esse produto.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <a href="{{ route('estoque.listar') }}" class="btn btn-default">Voltar</a>
            </div>
        </div>
    </section>
</div>

    <div class="modal fade" id="modalCropFotoProduto" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title"><i class="fa fa-crop"></i> Recortar foto</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div style="width: 100%; max-height: 60vh; overflow: hidden;">
                        <img id="cropperImage" src="" alt="Prévia" style="max-width: 100%; display: block;">
                    </div>
                    <small class="text-muted">Arraste para ajustar o enquadramento. Proporção fixa 1:1.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" id="btnCropConfirmar">
                        <i class="fa fa-check"></i> Aplicar recorte
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" />
@endpush

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
    <script>
        (function () {
            var input = document.getElementById('foto-input');
            var image = document.getElementById('cropperImage');
            var cropper = null;
            var modalId = '#modalCropFotoProduto';

            if (!input || !image) {
                return;
            }

            input.addEventListener('change', function (e) {
                var files = e.target.files;
                if (!files || !files.length) {
                    return;
                }

                var file = files[0];
                var reader = new FileReader();
                reader.onload = function (event) {
                    image.src = event.target.result;
                    $(modalId).modal('show');
                };
                reader.readAsDataURL(file);
            });

            $(modalId).on('shown.bs.modal', function () {
                if (cropper) {
                    cropper.destroy();
                }
                cropper = new Cropper(image, {
                    aspectRatio: 1,
                    viewMode: 1,
                    autoCropArea: 1,
                    responsive: true,
                    background: false,
                });
            });

            $(modalId).on('hidden.bs.modal', function () {
                if (cropper) {
                    cropper.destroy();
                    cropper = null;
                }
            });

            document.getElementById('btnCropConfirmar').addEventListener('click', function () {
                if (!cropper) {
                    $(modalId).modal('hide');
                    return;
                }

                var canvas = cropper.getCroppedCanvas({
                    width: 500,
                    height: 500,
                    imageSmoothingQuality: 'high'
                });

                canvas.toBlob(function (blob) {
                    if (!blob) {
                        $(modalId).modal('hide');
                        return;
                    }

                    var originalName = input.files[0] ? input.files[0].name : 'foto.jpg';
                    var file = new File([blob], originalName, { type: blob.type });
                    var dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    input.files = dataTransfer.files;
                    $(modalId).modal('hide');
                }, 'image/jpeg', 0.9);
            });
        })();
    </script>
@endpush
