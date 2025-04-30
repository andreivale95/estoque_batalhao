@extends('layout/app')
@section('content')
    <div class="content-wrapper">

        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                Produtos Cadastrados

            </h1>
            <ol class="breadcrumb">
                <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
                <li><a href="{{ route('estoque.listar') }}"><i class=""></i> Estoque</a></li>
            </ol>
        </section>

        <!-- Main content -->
        <section class="content container-fluid">

            <form action="{{ route('produtos.listar') }}" method="get">
                <div class="box box-primary">
                    <div class="box-header">
                        <div class="row">
                            <div class="form-group has-feedback col-md-2">
                                <label class="control-label" for="">PRODUTO:</label>
                                <input type="text" class="form-control" name="nome" value="{{ request()->nome }}">
                            </div>

                            <div class="form-group has-feedback col-md-2">
                                <label class="control-label" for="">CATEGORIA:</label>
                                <select name="categoria" class="form-control">
                                    <option value="">Selecione</option>
                                    @foreach ($categorias as $categoria)
                                        <option value="{{ $categoria->nome }}"
                                            {{ request()->categoria == $categoria->nome ? 'selected' : '' }}>
                                            {{ $categoria->nome }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group has-feedback col-md-2">
                                <label class="control-label" for="">MARCA:</label>
                                <select name="marca" class="form-control">
                                    <option value="">Selecione</option>
                                    @foreach ($todasMarcas as $marca)
                                        <option value="{{ $marca }}"
                                            {{ request()->marca == $marca ? 'selected' : '' }}>
                                            {{ $marca }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group has-feedback col-md-1 pull-right">
                                <label class="control-label" for="">&nbsp;</label>
                                <button class="btn btn-primary form-control"><i class="fa fa-search"></i> Pesquisar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            <div class="box-header with-border">
                <a href="{{ route('produto.form') }}" class="btn btn-primary">
                    <i class="fa fa-plus"></i> Novo Produto
                </a>
            </div>

            <div class="box box-primary">

                <div class="box-body table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>

                                <th>PRODUTO</th>
                                <th>UNIDADE</th>
                                <th>DESCRICAO</th>
                                <th>MARCA</th>
                                <th>VALOR</th>

                                <th>CATEGORIA</th>
                            </tr>
                        </thead>
                        <tbody>

                            @foreach ($produtos as $produto)
                                <tr>
                                    <td>
                                        <a href="{{ route('produto.ver', $produto->id) }}">
                                            {{ $produto->nome }} -
                                            {{ optional($produto->tamanho()->first())->tamanho ?? 'Sem tamanho' }}
                                        </a>
                                    </td>
                                    <td>
                                        {{ $produto->unidade }}
                                    </td>

                                    <td>{{ $produto->descricao }}</td>
                                    <td>{{ $produto->marca }}</td>
                                    <td>{{ $produto->valor }}</td>

                                    <td>{{ $produto->categoria->nome }}</td>



                                    <td> <a class="btn btn-warning" href="{{ route('produto.editar', $produto->id) }}"
                                            style="color: white">
                                            <i class="fa fa-edit"></i></a>

                                        <a class="btn btn-primary" href="{{ route('produto.ver', $produto->id) }}"
                                            style="color: white">
                                            <i class="fa fa-television"></i></a>
                                    </td>


                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="box-footer">




                    {{ $produtos->appends([
                            'nome' => request()->get('nome'),
                            'categoria' => request()->get('categoria'),
                            'marca' => request()->get('marca'),
                        ])->links() }}
                </div>
            </div>

        </section>
        <!-- /.content -->
    </div>


    <style>
        .imagem-redimensionada {
            width: 75px;
            /* Define a largura */
            height: auto;
            /* Mantém a proporção */
        }

        .btn-person {
    </style>

    <!-- /.content-wrapper -->
@endsection
