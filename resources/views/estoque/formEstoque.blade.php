@extends('layout/app')
@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                Cadastro de Patrimônio

            </h1>
            <ol class="breadcrumb">
                <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
                <li><a href="{{ route('patrimonios.listar') }}"><i class=""></i> Patrimônios</a></li>
                <li></i> Cadastro de Patrimônios</li>

            </ol>
        </section>

        <!-- Main content -->
        <section class="content container-fluid">

            <div class="panel" style="background-color: #3c8dbc;">
                <div class="panel-heading" style="color: white;">


                </div>
                <div class="panel-body" style="background-color: white;">
                    <form action="{{ route('patrimonio.cadastrar') }}" method="post" enctype="multipart/form-data">
                        <div class="row">

                            <div class="col-md-6">

                                @csrf

                                <div class="box box-primary">
                                    <div class="box-header">
                                        DADOS DO PATRIMÔNIO
                                    </div>
                                    <div class="box-body">
                                        <div class="form-group has-feedback col-md-6">
                                            <label class="control-label" for="">Número:</label>
                                            <input type="text" class="form-control" placeholder="" name="numero"
                                                value="" required>
                                        </div>

                                        <div class="form-group has-feedback col-md-6">
                                            <label class="control-label" for="">Ano:</label>
                                            <input type="text" class="form-control" placeholder="" name="ano"
                                                value="" required>
                                        </div>



                                        <div class="form-group has-feedback col-md-6">
                                            <label class="control-label" for="tipobem">Tipo Bem</label>
                                            <select name="tipobem" id="tipobem" class="form-control" required>
                                                <option value="">Escolha um Tipo Bem</option>
                                                @foreach ($tipobens as $tipobem)
                                                    <option value="{{ $tipobem->id }}">
                                                        {{ $tipobem->nome }}</option>
                                                @endforeach
                                            </select>
                                        </div>


                                        <div class="form-group has-feedback col-md-6">

                                            <label class="control-label" for="">Unidade</label>
                                            <select name="unidade" id="" class="form-control" required>
                                                <option value="">Selecione a Unidade</option>
                                                @foreach ($unidades as $unidade)
                                                    <option value="{{ $unidade->id }}">{{ $unidade->nome }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="form-group has-feedback col-md-6">

                                            <label class="control-label" for="">Fonte</label>
                                            <select name="fonte" id="" class="form-control" required>
                                                <option value="">Selecione a Fonte</option>
                                                @foreach ($fontes as $fonte)
                                                    <option value="{{ $fonte->id }}">{{ $fonte->nome }}</option>
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

                                    </div>
                                </div>
                                <div class="box-foote pull-right">
                                    <a href="{{ route('patrimonios.listar') }}" class="btn btn-danger"><i
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

    <script>
        $(document).ready(function() {


        });
    </script>


@endsection
