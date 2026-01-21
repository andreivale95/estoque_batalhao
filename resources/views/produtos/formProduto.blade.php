@extends('layout/app')
@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                Cadastro de Produto
            </h1>
            <ol class="breadcrumb">
                <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
                <li><a href="{{ route('estoque.listar') }}"><i class=""></i> Estoque</a></li>
                <li></i> Cadastro de Produto</li>
            </ol>
        </section>
        <!-- Main content -->
        <section class="content container-fluid">

            <div class="panel" style="background-color: #3c8dbc;">
                <div class="panel-heading" style="color: white;">


                </div>
                <div class="panel-body" style="background-color: white;">
                    <form action="{{ route('produto.cadastrar') }}" method="post" enctype="multipart/form-data">
                        <div class="row">

                            <div class="col-md-6">

                                @csrf

                                <div class="box box-primary">
                                    <div class="box-header">
                                        DADOS DO PRODUTO
                                    </div>
                                    <div class="box-body">
                                        <div class="form-group has-feedback col-md-6">
                                            <label class="control-label" for="">Nome:</label>
                                            <input type="text" class="form-control" placeholder="" name="nome"
                                                value="" required>
                                        </div>
                                        <div class="form-group has-feedback col-md-6">
                                            <label class="control-label" for="">Marca:</label>
                                            <input type="text" class="form-control" placeholder="" name="marca"
                                                value="" required>
                                        </div>
                                        

                                        <div class="form-group has-feedback col-md-6">
                                            <label class="control-label" for="">Descrição:</label>
                                            <input type="text" class="form-control" placeholder="" name="descricao"
                                                value="" required>
                                        </div>
                                        <div class="form-group has-feedback col-md-6">
                                            <label class="control-label" for="unidade">Unidade</label>
                                            <input type="hidden" name="unidade" id="unidade" value="{{ Auth::user()->fk_unidade }}">
                                            <input type="text" class="form-control" value="{{ $unidadeUsuario->nome ?? 'Unidade não encontrada' }}" disabled>
                                        </div>



                                        <div class="form-group has-feedback col-md-6">
                                            <label class="control-label" for="categoria">Categoria</label>
                                            <select name="categoria" id="categoria" class="form-control" required>
                                                <option value="">Escolha</option>
                                                @foreach ($categorias as $categoria)
                                                    <option value="{{ $categoria->id }}"
                                                        data-tipo="{{ $categoria->tipo_tamanho }}">
                                                        {{ $categoria->nome }}
                                                    </option>
                                                @endforeach
                                            </select>

                                        </div>

                                        <div class="form-group has-feedback col-md-6">
                                            <label class="control-label" for="fk_secao">Seção padrão</label>
                                            <select name="fk_secao" id="fk_secao" class="form-control">
                                                <option value="">Nenhuma</option>
                                                @foreach($secoes as $secao)
                                                    <option value="{{ $secao->id }}">{{ $secao->nome }}</option>
                                                @endforeach
                                            </select>
                                        </div>


                                        <div class="form-group has-feedback col-md-6">

                                            <label class="control-label" for="">Condição</label>
                                            <select name="condicao" id="" class="form-control" required>
                                                <option value="">Selecione</option>
                                                @foreach ($condicoes as $condicao)
                                                    <option value="{{ $condicao->id }}">{{ $condicao->condicao }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="form-group has-feedback col-md-6">

                                            <label class="control-label" for="">Kit (se houver)</label>
                                            <select name="kit" id="" class="form-control">
                                                <option value="">Selecione</option>
                                                @foreach ($kits as $kit)
                                                    <option value="{{ $kit->id }}">{{ $kit->nome }}</option>
                                                @endforeach
                                            </select>
                                        </div>


                                        <div class="form-group has-feedback col-md-6">
                                            <label class="control-label" for="tamanho">Tamanho (Se houver)</label>
                                            <select name="tamanho" id="tamanho" class="form-control">
                                                <option value="">Selecione</option>
                                                @foreach ($tamanhos as $tamanho)
                                                    <option value="{{ $tamanho->id }}"
                                                        data-tipo="{{ $tamanho->tipo_tamanho }}">
                                                        {{ $tamanho->tamanho }}
                                                    </option>
                                                @endforeach
                                            </select>

                                        </div>

                                        {{-- valor removido: agora o valor unitário é gerenciado apenas via entradas no estoque --}}

                                        <div class="form-group has-feedback col-md-6">
                                            <label class="control-label" for="patrimonio">Patrimônio (se houver)</label>
                                            <input type="text" class="form-control" name="patrimonio" value="{{ old('patrimonio', $produto->patrimonio ?? '') }}">
                                        </div>
                                </div>
                                <div class="box-foote pull-right">
                                    <a href="{{ route('produtos.listar') }}" class="btn btn-danger"><i
                                            class="fa fa-close"></i>
                                        Cancelar</a>
                                    <button class="btn btn-success"><i class="fa fa-save"></i> Cadastrar</button>
                                </div>
                            </div>

                    </form>
                </div>

            </div>

        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    {{-- valor removed script --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const categoriaSelect = document.getElementById('categoria');
            const tamanhoSelect = document.getElementById('tamanho');

            const todasOpcoesTamanho = Array.from(tamanhoSelect.options);

            categoriaSelect.addEventListener('change', function() {
                const tipoSelecionado = categoriaSelect.options[categoriaSelect.selectedIndex].dataset.tipo;

                // Limpa opções exceto a primeira
                tamanhoSelect.innerHTML = '<option value="">Selecione</option>';

                // Filtra e adiciona apenas os tamanhos compatíveis
                todasOpcoesTamanho.forEach(option => {
                    if (!option.dataset.tipo || option.dataset.tipo === tipoSelecionado) {
                        tamanhoSelect.appendChild(option);
                    }
                });
            });
        });
    </script>
@endsection
