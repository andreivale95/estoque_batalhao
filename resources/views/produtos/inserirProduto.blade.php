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
@endsection
