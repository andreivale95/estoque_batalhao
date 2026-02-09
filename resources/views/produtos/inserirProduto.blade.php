@extends('layout.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>Cadastrar Novo Produto</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{ route('estoque.listar') }}"><i class=""></i> Estoque</a></li>
            <li class="active">Cadastrar Produto</li>
        </ol>
    </section>

    <section class="content container-fluid">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Novo Produto</h3>
                <p class="text-muted">Preencha os dados do produto. Para adicionar quantidade, use "Registrar Entrada".</p>
            </div>
            <div class="box-body">
                @if(session('warning'))
                    <div class="alert alert-warning">{{ session('warning') }}</div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('produto.cadastrar') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nome <span style="color: red;">*</span></label>
                                <input type="text" name="nome" class="form-control" value="{{ old('nome') }}" required>
                            </div>

                            <div class="form-group">
                                <label>Descrição</label>
                                <textarea name="descricao" class="form-control" rows="3">{{ old('descricao') }}</textarea>
                            </div>

                            <div class="form-group">
                                <label>Marca</label>
                                <input type="text" name="marca" class="form-control" value="{{ old('marca') }}">
                            </div>

                            <div class="form-group">
                                <label>Tamanho</label>
                                <select name="tamanho" class="form-control">
                                    <option value="">Selecione</option>
                                    @foreach($tamanhos as $tamanho)
                                        <option value="{{ $tamanho->id }}" {{ old('tamanho') == $tamanho->id ? 'selected' : '' }}>
                                            {{ $tamanho->tamanho }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Unidade <span style="color: red;">*</span></label>
                                <input type="hidden" name="unidade" value="{{ Auth::user()->fk_unidade }}">
                                <input type="text" class="form-control" value="{{ $unidadeUsuario->nome ?? 'Unidade não encontrada' }}" disabled>
                            </div>

                            {{-- valor unitário removido do cadastro do produto --}}

                            <div class="form-group">
                                <label>Categoria <span style="color: red;">*</span></label>
                                <select name="categoria" class="form-control" required>
                                    <option value="">-- Selecione --</option>
                                    @foreach($categorias as $c)
                                        <option value="{{ $c->id }}" {{ old('categoria') == $c->id ? 'selected' : '' }}>
                                            {{ $c->nome }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Tipo de Controle <span style="color: red;">*</span></label>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="tipo_controle" value="consumo" 
                                            {{ old('tipo_controle', 'consumo') == 'consumo' ? 'checked' : '' }}>
                                        <span>Consumo (quantidade agregada)</span>
                                    </label>
                                </div>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="tipo_controle" value="permanente" 
                                            {{ old('tipo_controle') == 'permanente' ? 'checked' : '' }}>
                                        <span>Permanente/Patrimonial (itens individuais)</span>
                                    </label>
                                </div>
                                <small class="text-muted d-block" style="margin-top: 5px;">
                                    <strong>Consumo:</strong> Produtos compráveis em quantidade (pilhas, papel, luvas)<br>
                                    <strong>Permanente:</strong> Bens numerados individualmente (rádios, armas, EPIs). O patrimônio será informado na entrada de estoque.
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Seção de dados adicionais do container -->
                    <div id="container-fields" style="display: none;">
                        <!-- Container removido - use a seção de Containers no menu Estoque -->
                    </div>

                    <!-- Campo de Foto do Produto -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Foto do Produto</label>
                                <input type="file" name="foto" class="form-control" accept="image/*" id="foto-input">
                                <small class="text-muted">Formatos aceitos: JPG, PNG, GIF (máx 5MB).</small>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save"></i> Cadastrar Produto
                        </button>
                        <a href="{{ route('estoque.listar') }}" class="btn btn-default">
                            <i class="fa fa-arrow-left"></i> Voltar
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="alert alert-info">
            <strong><i class="fa fa-info-circle"></i> Como usar:</strong>
            <ul>
                <li><strong>Consumo:</strong> Após cadastrar, use "Registrar Entrada" para adicionar quantidade ao estoque</li>
                <li><strong>Permanente:</strong> Após cadastrar, faça a entrada no estoque informando o patrimônio de cada item</li>
            </ul>
        </div>
    </section>
</div>

<script>
    document.querySelector('input[name="foto"]').addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            // Valida tamanho (máx 5MB)
            if (file.size > 5 * 1024 * 1024) {
                alert('A imagem não pode exceder 5MB.');
                this.value = '';
                return;
            }
            // Valida tipo
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!allowedTypes.includes(file.type)) {
                alert('Formato de arquivo não permitido. Use JPG, PNG ou GIF.');
                this.value = '';
            }
        }
    });
</script>

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

            input.addEventListener('change', function () {
                var files = input.files;
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
