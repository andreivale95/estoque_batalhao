@extends('layout/app')
@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                Usuários Internos
            </h1>
            <ol class="breadcrumb">
                <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
                <li><a href="{{ route('usi.listar') }}"><i class=""></i> Usuários Internos</a></li>
            </ol>
        </section>

        <!-- Main content -->
        <section class="content container-fluid">

            <div class="box box-primary">

                <div class="box-header with-border">

                    <form action="{{ route('usi.listar') }}" method="get">
                        @csrf
                        <div class="row">
                            <div class="form-group col-md-3 col-sm-6">
                                <label>Pesquisar</label>
                                <input type="text" class="form-control" name="search" value="{{ request()->search }}"
                                    placeholder="Nome, Sobrenome, CPF, Email">
                            </div>

                            <div class="form-group col-md-2 col-sm-6">
                                <label>Perfil</label>
                                <select name="perfil" class="form-control">
                                    <option value="">Selecione</option>
                                    @foreach ($perfis as $perfil)
                                        <option value="{{ $perfil->id_perfil }}"
                                            {{ $perfil->id_perfil == request()->perfil ? 'selected' : '' }}>
                                            {{ $perfil->nome }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="group" style="margin-top: 25px">

                                <div class="col-md-3 pull-right">
                                    <a href="{{ route('usi.listar') }}" class="btn btn-danger pull-right"
                                        style="margin-left: 10px;"><i class="fa fa-eraser"></i> Limpar</a>
                                    <button type="submit" class=" btn btn-primary pull-right"> <i class="fa fa-search"></i>
                                        Buscar</button>
                                </div>

                            </div>
                        </div>
                    </form>
                </div>
                <div class="box-body table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>NOME</th>
                                <th>PERFIL</th>
                                <th>CPF</th>
                                <th>SOBRENOME</th>
                                <th>TELEFONE</th>
                                <th>E-MAIL</th>
                                <th>UNIDADE</th>
                                <th>STATUS</th>
                            </tr>
                        </thead>
                        <tbody>

                            @foreach ($users as $user)
                                <tr>
                                    <td><a href="{{ route('usi.ver', $user->cpf) }}">{{ $user->nome }}</a></td>
                                    <td>{{ $user->perfil()->get()->first()->nome }}</td>
                                    <td>{{ $user->cpf }}</td>
                                    <td>{{ $user->sobrenome }}</td>
                                    <td>{{ $user->telefone }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->unidade()->get()->first()->nome }}</td>
                                    @if ($user->status == 's')
                                        <td>ATIVO</td>
                                    @else
                                        <td>INATIVO</td>
                                    @endif
                                    <td>
                                        <a href="{{ route('usi.ver', $user->cpf) }}" class="btn btn-success"><i class="fa fa-desktop"></i></a>
                                        <a href="{{ route('usi.editar', $user->cpf) }}" class="btn btn-warning"><i class="fa fa-edit"></i></a>
                                        <form action="{{ route('usi.excluir', $user->cpf) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Tem certeza que deseja excluir este usuário?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger"><i class="fa fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

                    </table>


                </div>
                <div class="box-footer">
                    <div class="btn-person">
                        <a href="{{ route('usi.form') }}" type="button" class="btn btn-primary add pull-right"><i
                                class="fa fa-plus"></i>
                        </a>
                    </div>
                    {{ $users->links() }}



                </div>
            </div>

        </section>
        <!-- /.content -->
    </div>

    <style>
        .btn-person {
            position: fixed;
            right: 2%;
            bottom: 5%;
            border-radius: 50%;
            z-index: 1000;
        }

        .add {
            box-shadow: 3px 3px 10px 3px rgba(23, 22, 0, 0.5);
            border-radius: 50px;
            font-size: 20px
        }

        .add:hover {
            zoom: 135%;
        }

        @media (max-width:767px) {
            .btn-person {
                position: fixed;
                right: 7%;
                bottom: 3%;
                border-radius: 50%;
                z-index: 1000;
            }
        }
    </style>
    <!-- /.content-wrapper -->
@endsection
