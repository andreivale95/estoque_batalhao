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
                                <select name="unidade" class="form-control" required>
                                    <option value="">-- Selecione --</option>
                                    @foreach($unidades as $u)
                                        <option value="{{ $u->id }}" {{ old('unidade') == $u->id ? 'selected' : '' }}>
                                            {{ $u->nome }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Valor Unitário (R$)</label>
                                <input type="text" name="valor_formatado" class="form-control" 
                                    placeholder="0,00" value="{{ old('valor_formatado') }}">
                                <input type="hidden" name="valor" id="valor_limpo">
                                <small class="form-text text-muted">Digite o valor unitário do produto</small>
                            </div>

                            <div class="form-group">
                                <label>Categoria <span style="color: red;">*</span></label>
                                <select name="categoria_id" class="form-control" required>
                                    <option value="">-- Selecione --</option>
                                    @foreach($categorias as $c)
                                        <option value="{{ $c->id }}" {{ old('categoria_id') == $c->id ? 'selected' : '' }}>
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

<script>
    document.querySelector('input[name="valor_formatado"]').addEventListener('input', function(e) {
        let raw = e.target.value.replace(/\D/g, ''); // só números
        let valorCentavos = raw ? parseInt(raw, 10) : 0;

        // Atualiza o campo hidden com valor em centavos
        document.getElementById('valor_limpo').value = valorCentavos;

        // Formata para exibição
        let valor_centavos_float = valorCentavos / 100;
        e.target.value = valor_centavos_float.toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    });
</script>
@endsection
