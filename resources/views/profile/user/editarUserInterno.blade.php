@extends('layout/app')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Dados do Usuário

        </h1>
        <ol class="breadcrumb">
            <li><a href="{{route('dashboard')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{ route('usi.listar') }}"><i class=""></i> Usuários Internos</a></li>
            <li><a href="{{ route('usi.ver', $user->cpf) }}"><i class=""></i> {{$user->nome}}</a></li>


        </ol>
    </section>

    <!-- Main content -->
    <section class="content container-fluid">

        <div class="panel" style="background-color: #f39c12;">
            <div class="panel-heading" style="color: white;">
                {{$user->nome}}
            </div>
            <div class="panel-body" style="background-color: white;">
                <form action="{{ route('usi.atualizar', $user->cpf) }}" method="post" class="row">
                    @csrf()

                    <div class="col-md-6">
                        <div class="box box-warning">
                            <div class="box-header">
                                DADOS DO USUÁRIO
                            </div>
                            <div class="panel-body">
                                <div class="form-group has-feedback">
                                    <label class="control-label" for="">Nome:</label>

                                    <input type="text" class="form-control" placeholder="" name="nome" value="{{$user->nome}}">

                                </div>
                                <div class="form-group has-feedback">
                                    <label class="control-label" for="">Sobrenome:</label>

                                    <input type="text" class="form-control" placeholder="" name="sobrenome" value="{{$user->sobrenome}}">

                                </div>
                                <div class="form-group has-feedback">
                                    <label class="control-label" for="">CPF:</label>

                                    <input id="cpf" type="text" class="form-control" placeholder="" name="cpf" value="{{$user->cpf}}">

                                </div>
                                <div class="form-group has-feedback">
                                    <label class="control-label" for="">Telefone:</label>
                                    <div class="input-group">
                                        <div class="input-group-addon">
                                            <i class="fa fa-phone"></i>
                                        </div>

                                        <input id="telefone" type="text" class="form-control" placeholder="" name="telefone" value="{{$user->telefone}}">

                                    </div>
                                </div>
                                <div class="form-group has-feedback">
                                    <label class="control-label" id="email" name="email" type="email">Email:</label>
                                    <div class="input-group">
                                        <div class="input-group-addon">
                                            <i class="fa fa-envelope"></i>
                                        </div>

                                        <input type="text" class="form-control" placeholder="" name="email" value="{{$user->email}}">

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="box box-warning">
                            <div class="box-header">
                                SEGURANÇA
                            </div>
                            <div class="panel-body">

                                <div class="form-group has-feedback">
                                    <label class="control-label" for="">PERFIL DE ACESSO:</label>

                                    <select name="perfil" class="form-control">

                                        @foreach ($perfis as $perfil)
                                        <option value="{{ $perfil->id_perfil }}" {{ ( $perfil->id_perfil == $user->fk_perfil) ? 'selected' : '' }}> {{ $perfil->nome }} </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group has-feedback">
                                    <label class="control-label" for="">UNIDADE DE LOTAÇÃO:</label>

                                    <select name="unidade" class="form-control">

                                        @foreach ($unidades as $unidade)
                                        <option value="{{ $unidade->id }}" {{ ( $unidade->id == $user->fk_unidade) ? 'selected' : '' }}> {{ $unidade->nome }} </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group has-feedback">
                                    <label class="control-label" for="">STATUS:</label>
                                    <select name="status" class="form-control">

                                        @if ($user->status == 's')
                                        <option value="s" selected>Ativo</option>
                                        <option value="n">Inativo</option>
                                        @else
                                        <option value="s">Ativo</option>
                                        <option value="n" selected>Inativo</option>
                                        @endif
                                    </select>

                                </div>

                            </div>

                        </div>

                        <a href="{{route('usi.ver', $user->cpf)}}" class="btn btn-danger"><i class="fa fa-close"></i> Cancelar</a>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i>
                            Salvar
                        </button>

                </form>

            </div>

        </div>

    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

@endsection
