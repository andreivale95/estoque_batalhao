@extends('layout/app')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>Listagem de Kits</h1>
            <ol class="breadcrumb">
                <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
                <li class="active">Kits</li>
            </ol>
        </section>

        <section class="content container-fluid">
            <div class="box">
                <div class="box-header with-border">
                    <a href="{{ route('kits.criar') }}" class="btn btn-primary">
                        <i class="fa fa-plus"></i> Novo Kit
                    </a>
                </div>

                <div class="box-body">
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Nome do Kit</th>
                                <th>Unidade</th>
                                <th>Data de Criação</th>
                                <th>Disponibilidade</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($kits as $kit)
                                <tr>
                                    <td>{{ $kit->nome }}</td>
                                    <td>{{ $kit->unidade->nome ?? '-' }}</td>
                                    <td>{{ $kit->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        @if ($kit->disponivel == 'S')
                                            <span class="label label-success">Disponível</span>
                                        @else
                                            <span class="label label-danger">Indisponível</span>
                                        @endif


                                    </td>
                                    <td>
                                        {{-- Botão Editar --}}
                                        <a href="{{ route('kits.editar', $kit->id) }}" class="btn btn-warning btn-xs">
                                            <i class="fa fa-pencil"></i> Editar
                                        </a>

                                        {{-- Botão Alterar Disponibilidade --}}
                                        <form action="{{ route('kits.toggleDisponibilidade', $kit->id) }}" method="POST" style="display:inline-block;">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                class="btn btn-xs {{ $kit->disponivel == 'S' ? 'btn-default' : 'btn-success' }}">
                                                <i class="fa fa-refresh"></i>
                                                {{ $kit->disponivel == 'S' ? 'Indisponibilizar' : 'Disponibilizar' }}
                                            </button>
                                        </form>

                                        {{-- Botão Excluir --}}
                                        <form action="{{ route('kits.excluir', $kit->id) }}" method="POST" style="display:inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-xs"
                                                onclick="return confirm('Tem certeza que deseja excluir este kit?')">
                                                <i class="fa fa-trash"></i> Excluir
                                            </button>
                                        </form>
                                    </td>



                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">Nenhum kit encontrado.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="text-center">

                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
