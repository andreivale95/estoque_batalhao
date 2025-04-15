@extends('layout/app')
@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                Cadastro de Kit
                <small></small>

            </h1>
            <ol class="breadcrumb">
                <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
                <li><a href="{{ route('estoque.listar') }}"><i class=""></i> Estoque</a></li>
                <li></i> Cadastro de Kit</li>

            </ol>
        </section>

        <!-- Main content -->
        <section class="content container-fluid">

            <div class="panel" style="background-color: #3c8dbc;">
                <div class="panel-heading" style="color: white;">


                </div>
                <div class="panel-body" style="background-color: white;">
                    <form action="{{ route('kits.salvar') }}" method="POST">
                        @csrf



                        <div class="form-group">
                            <label for="nome">Nome do Kit:</label>
                            <input type="text" name="nome" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="descricao">Descrição do Kit (Opcional):</label>
                            <input type="text" name="descricao" class="form-control" required>
                        </div>




                        <button type="submit" class="btn btn-primary">Criar Kit</button>
                    </form>


                </div>

            </div>

        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->



@endsection
