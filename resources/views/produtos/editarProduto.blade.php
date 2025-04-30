@extends('layout/app')
@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                {{ $produto->nome }} - {{ optional($produto->tamanho()->first())->tamanho ?? 'Tamanho Único' }}
            </h1>

            <ol class="breadcrumb">
                <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
                <li><a href="{{ route('produtos.listar') }}">Patrimônios</a></li>
                <li><a href="{{ route('produto.ver', $produto->id) }}">{{ $produto->descricao }}</a></li>
            </ol>
        </section>

        <!-- Main content -->
        <section class="content container-fluid">
            <div class="panel" style="background-color: #3c8dbc;">
                <div class="panel-heading" style="color: white;">
                    DADOS DO PRODUTO
                </div>
                <div class="panel-body" style="background-color: white;">
                    <form action="{{ route('produto.atualizar', $produto->id) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="box box-primary">
                                    <div class="box-header">
                                        @if (session('path'))
                                            <img src="{{ asset('storage/' . session('path')) }}" alt="Imagem do Produto">
                                        @endif
                                    </div>
                                    <div class="box-body">
                                        <div class="form-group has-feedback col-md-6">
                                            <label for="">Nome:</label>
                                            <input type="text" class="form-control" name="nome"
                                                value="{{ $produto->nome }}">
                                        </div>
                                        <div class="form-group has-feedback col-md-6">
                                            <label for="">Marca:</label>
                                            <input type="text" class="form-control" name="marca"
                                                value="{{ $produto->marca }}">
                                        </div>
                                        <div class="form-group has-feedback col-md-6">
                                            <label for="">Descrição:</label>
                                            <input type="text" class="form-control" name="descricao"
                                                value="{{ $produto->descricao }}">
                                        </div>
                                        @php
                                            $valorSelecionado = old('unidade', $produto->unidade ?? '');
                                        @endphp

                                        <div class="form-group has-feedback col-md-6">
                                            <label class="control-label" for="unidade">Unidade</label>
                                            <select name="unidade" id="unidade" class="form-control" required>
                                                <option value="">Escolha</option>
                                                <option value="UN" {{ $valorSelecionado == 'UN' ? 'selected' : '' }}>UN
                                                </option>
                                                <option value="CX" {{ $valorSelecionado == 'CX' ? 'selected' : '' }}>CX
                                                </option>
                                                <option value="PCT" {{ $valorSelecionado == 'PCT' ? 'selected' : '' }}>
                                                    PCT</option>
                                            </select>
                                        </div>

                                        <div class="form-group has-feedback col-md-6">
                                            <label for="tipoproduto">Categoria:</label>
                                            <select class="form-control" name="categoria">
                                                @foreach ($categorias as $categoria)
                                                    <option value="{{ $categoria->id }}"
                                                        {{ $produto->fk_categoria == $categoria->id ? 'selected' : '' }}>
                                                        {{ $categoria->nome }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>


                                        <div class="form-group has-feedback col-md-6">
                                            <label for="">Condição:</label>
                                            <select class="form-control" name="fk_condicao">
                                                @foreach ($condicoes as $condicao)
                                                    <option value="{{ $condicao->id }}"
                                                        {{ $produto->fk_condicao == $condicao->id ? 'selected' : '' }}>
                                                        {{ $condicao->condicao }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group has-feedback col-md-6">
                                            <label for="">Kit</label>
                                            <select name="fk_kit" class="form-control">
                                                <option value="">Selecione</option>
                                                @if ($kit)
                                                    @foreach ($kits as $kitt)
                                                        <option value="{{ $kitt->id }}"
                                                            {{ isset($kit) && $kit->id == $kitt->id ? 'selected' : '' }}>
                                                            {{ $kitt->nome }}
                                                        </option>
                                                    @endforeach
                                                @else
                                                    @foreach ($kits as $kitt)
                                                        <option value="{{ $kitt->id }}">{{ $kitt->nome }}</option>
                                                    @endforeach
                                                @endif

                                            </select>
                                        </div>



                                        <div class="form-group has-feedback col-md-6">
                                            <label class="control-label" for="tamanho">Tamanho</label>
                                            <select name="tamanho" class="form-control">
                                                <option value="">Selecione</option>

                                                @if ($tamanhos && count($tamanhos) > 0)
                                                    @foreach ($tamanhos as $tamanho)
                                                        <option value="{{ $tamanho->id }}"
                                                            {{ isset($produto) && $produto->tamanho == $tamanho->id ? 'selected' : '' }}>
                                                            {{ $tamanho->tamanho }}
                                                        </option>
                                                    @endforeach
                                                @else
                                                    <option disabled selected>Nenhum tamanho disponível</option>
                                                @endif

                                            </select>
                                        </div>

                                        <div class="form-group has-feedback col-md-6">
                                            <label class="control-label" for="valor">Valor (R$):</label>
                                            <input type="text" class="form-control" placeholder="0,00"
                                                name="valor_formatado" id="valor" required
                                                value="{{ number_format((float) $produto->valor, 2, ',', '.') }}">
                                            <input type="hidden" name="valor" id="valor_limpo">
                                        </div>
                                    </div>
                                </div>
                                <div class="box-footer pull-right">
                                    <a href="{{ route('produtos.listar') }}" class="btn btn-danger">
                                        <i class="fa fa-arrow-left"></i> Voltar
                                    </a>

                                    <button type="submit" class="btn btn-success">
                                        <i class="fa fa-save"></i> Salvar
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="box box-primary">
                                    <div class="box-header">
                                        IMAGEM DO PRODUTO
                                    </div>
                                    <div class="box-body text-center">
                                        <img src="{{ asset('/storage/' . $produto->imagem) }}" alt="Imagem do Produto"
                                            class="img-responsive" style="max-width: 100%;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
    </div>

    <script>
        document.getElementById('valor').addEventListener('input', function(e) {
            let raw = e.target.value.replace(/\D/g, ''); // só números
            let valorCentavos = raw ? parseInt(raw, 10) : 0;

            // Atualiza o campo hidden com valor em centavos
            document.getElementById('valor_limpo').value = valorCentavos;

            // Atualiza o campo visível formatado com vírgula e ponto
            let valorFormatado = (valorCentavos / 100).toFixed(2)
                .replace('.', ',')
                .replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            e.target.value = valorFormatado;
        });
    </script>


    <style>
        .imagem-redimensionada {
            width: 500px;
            height: auto;
        }
    </style>
    </section>
    <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->
@endsection
