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

                <form action="{{ route('produto.cadastrar') }}" method="POST">
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
                                <input type="text" name="tamanho" class="form-control" value="{{ old('tamanho') }}">
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
                                <label>Patrimônio</label>
                                <input type="text" name="patrimonio" class="form-control" 
                                    placeholder="Número do patrimônio" value="{{ old('patrimonio') }}">
                            </div>

                            <div class="form-group">
                                <label>
                                    <input type="checkbox" name="eh_container" value="1" {{ old('eh_container') ? 'checked' : '' }}>
                                    <span style="margin-left: 5px;">Este é um Container/Bolsa/Prateleira?</span>
                                </label>
                                <small class="text-muted d-block">Marque se este produto serve como container para outros itens</small>
                            </div>
                        </div>
                    </div>

                    <!-- Seção de dados adicionais do container -->
                    <div id="container-fields" style="display: none;">
                        <div class="box box-warning" style="margin-top: 20px;">
                            <div class="box-header with-border">
                                <h3 class="box-title">Dados do Container</h3>
                            </div>
                            <div class="box-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Tipo de Container</label>
                                            <input type="text" name="container_tipo" class="form-control" 
                                                placeholder="Ex: Bolsa, Prateleira, Caixa, Armário" value="{{ old('container_tipo') }}">
                                            <small class="text-muted">Bolsa, Prateleira, Caixa, Armário, etc.</small>
                                        </div>

                                        <div class="form-group">
                                            <label>Material</label>
                                            <input type="text" name="container_material" class="form-control" 
                                                placeholder="Ex: Plástico, Metal, Madeira, Tecido" value="{{ old('container_material') }}">
                                            <small class="text-muted">Plástico, Metal, Madeira, Tecido, etc.</small>
                                        </div>

                                        <div class="form-group">
                                            <label>Cor</label>
                                            <input type="text" name="container_cor" class="form-control" 
                                                placeholder="Cor do container" value="{{ old('container_cor') }}">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Capacidade Máxima</label>
                                            <div class="input-group">
                                                <input type="number" step="0.01" name="container_capacidade" class="form-control" 
                                                    placeholder="Ex: 50" value="{{ old('container_capacidade') }}">
                                                <span class="input-group-addon">
                                                    <select name="container_unidade" class="form-control" style="border: none;">
                                                        <option value="kg">kg</option>
                                                        <option value="un">unidades</option>
                                                        <option value="l">litros</option>
                                                        <option value="m3">m³</option>
                                                    </select>
                                                </span>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label>Compartimentos</label>
                                            <input type="number" name="container_compartimentos" class="form-control" 
                                                placeholder="0" min="0" value="{{ old('container_compartimentos', 0) }}">
                                        </div>

                                        <div class="form-group">
                                            <label>Número de Série</label>
                                            <input type="text" name="container_numero_serie" class="form-control" 
                                                placeholder="Opcional" value="{{ old('container_numero_serie') }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Descrição Adicional</label>
                                    <textarea name="container_descricao" class="form-control" rows="3" 
                                        placeholder="Informações adicionais sobre o container...">{{ old('container_descricao') }}</textarea>
                                </div>
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
            <strong><i class="fa fa-info-circle"></i> Próximas etapas:</strong>
            <ol>
                <li>Clique em "Cadastrar Produto" para salvar este novo item no catálogo</li>
                <li>Após cadastrar, use "Registrar Entrada" para adicionar quantidade ao estoque</li>
            </ol>
        </div>
    </section>
</div>

{{-- script de formatação de valor removido (campo valor eliminado) --}}

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkboxContainer = document.querySelector('input[name="eh_container"]');
        const containerFields = document.getElementById('container-fields');
        
        // Mostrar/esconder campos ao mudar checkbox
        checkboxContainer.addEventListener('change', function() {
            containerFields.style.display = this.checked ? 'block' : 'none';
        });
        
        // Se o checkbox estava marcado (old data), mostrar campos
        if (checkboxContainer.checked) {
            containerFields.style.display = 'block';
        }
    });
</script>
@endsection
