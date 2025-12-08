@extends('layout/app')
@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                Cadastrar Container/Bolsa/Prateleira
                <small>Registre containers para organizar itens dentro deles</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
                <li><a href="{{ route('estoque.listar') }}"><i class=""></i> Estoque</a></li>
                <li class="active">Cadastrar Container</li>
            </ol>
        </section>

        <!-- Main content -->
        <section class="content container-fluid">
            <div class="panel" style="background-color: #3c8dbc;">
                <div class="panel-heading" style="color: white;">
                    <i class="fa fa-briefcase"></i> Novo Container
                </div>
                <div class="panel-body" style="background-color: white;">
                    <form action="{{ route('estoque.container.salvar') }}" method="POST">
                        @csrf

                        <div class="row">
                            <!-- Nome do Container -->
                            <div class="form-group col-md-6">
                                <label for="nome_container">Nome do Container/Bolsa/Prateleira: <span style="color: red;">*</span></label>
                                <input type="text" 
                                       class="form-control" 
                                       id="nome_container" 
                                       name="nome_container" 
                                       placeholder="Ex: Bolsa Verde, Prateleira A1, Mochila Tática"
                                       value="{{ old('nome_container') }}" 
                                       required>
                                <small class="text-muted">Digite um nome descritivo para identificar o container</small>
                            </div>

                            <!-- Quantidade -->
                            <div class="form-group col-md-3">
                                <label for="quantidade">Quantidade: <span style="color: red;">*</span></label>
                                <input type="number" 
                                       class="form-control" 
                                       id="quantidade" 
                                       name="quantidade" 
                                       min="1" 
                                       value="{{ old('quantidade', 1) }}" 
                                       required>
                                <small class="text-muted">Número de containers deste tipo</small>
                            </div>

                            <!-- Categoria -->
                            <div class="form-group col-md-3">
                                <label for="fk_categoria">Categoria: <span style="color: red;">*</span></label>
                                <select class="form-control select2" id="fk_categoria" name="fk_categoria" required>
                                    <option value="">-- Selecione --</option>
                                    @foreach($categorias as $cat)
                                        <option value="{{ $cat->id }}" {{ old('fk_categoria') == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->nome }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Categoria do container</small>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Seção -->
                            <div class="form-group col-md-6">
                                <label for="fk_secao">Seção: <span style="color: red;">*</span></label>
                                <select class="form-control select2" id="fk_secao" name="fk_secao" required>
                                    <option value="">-- Selecione uma Seção --</option>
                                    @foreach($secoes as $secao)
                                        <option value="{{ $secao->id }}" {{ old('fk_secao') == $secao->id ? 'selected' : '' }}>
                                            {{ $secao->nome }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Onde o container ficará localizado</small>
                            </div>

                            @if($isAdmin)
                                <!-- Unidade editável para admin -->
                                <div class="form-group col-md-6">
                                    <label for="unidade">Unidade: <span style="color: red;">*</span></label>
                                    <select class="form-control select2" id="unidade" name="unidade" required>
                                        <option value="">-- Selecione --</option>
                                        @foreach(\App\Models\Unidade::all() as $un)
                                            <option value="{{ $un->id }}" {{ old('unidade', $unidadeUsuario->id) == $un->id ? 'selected' : '' }}>
                                                {{ $un->nome }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @else
                                <!-- Unidade fixa para não-admin -->
                                <div class="form-group col-md-6">
                                    <label for="unidade_display">Unidade:</label>
                                    <input type="text" class="form-control" value="{{ $unidadeUsuario->nome }}" disabled>
                                    <input type="hidden" name="unidade" value="{{ $unidadeUsuario->id }}">
                                </div>
                            @endif
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <i class="fa fa-info-circle"></i> 
                                    <strong>Informação:</strong> Após cadastrar o container, você poderá adicionar itens dentro dele através do formulário de entrada de estoque.
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-save"></i> Cadastrar Container
                                </button>
                                <a href="{{ route('estoque.listar') }}" class="btn btn-default">
                                    <i class="fa fa-arrow-left"></i> Cancelar
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Inicializa Select2
        $('.select2').select2({
            placeholder: "Selecione uma opção",
            allowClear: true
        });
    });
</script>
@endsection
