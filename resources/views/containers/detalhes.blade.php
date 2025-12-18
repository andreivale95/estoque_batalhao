@extends('layout.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>Detalhes do Container
            <small>{{ $produto->nome }}</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{ route('estoque.listar') }}"><i class="fa fa-box"></i> Estoque</a></li>
            <li class="active">{{ $produto->nome }}</li>
        </ol>
    </section>

    <section class="content container-fluid">
        <div class="row">
            <!-- Informações Básicas do Container -->
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-briefcase"></i> Informações do Container</h3>
                    </div>
                    <div class="box-body">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Nome:</strong></td>
                                <td>{{ $produto->nome }}</td>
                            </tr>
                            <tr>
                                <td><strong>Patrimônio:</strong></td>
                                <td>{{ $produto->patrimonio ?? '-' }}</td>
                            </tr>
                            @if($container)
                                <tr>
                                    <td><strong>Tipo:</strong></td>
                                    <td>{{ $container->tipo ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Material:</strong></td>
                                    <td>{{ $container->material ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Cor:</strong></td>
                                    <td>{{ $container->cor ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Capacidade Máxima:</strong></td>
                                    <td>
                                        @if($container->capacidade_maxima)
                                            {{ $container->capacidade_maxima }} {{ $container->unidade_capacidade }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Compartimentos:</strong></td>
                                    <td>{{ $container->compartimentos ?? 0 }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Número de Série:</strong></td>
                                    <td>{{ $container->numero_serie ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        @php
                                            $statusColor = [
                                                'ativo' => 'success',
                                                'danificado' => 'danger',
                                                'em_reparo' => 'warning',
                                                'inativo' => 'muted'
                                            ];
                                        @endphp
                                        <span class="label label-{{ $statusColor[$container->status] ?? 'default' }}">
                                            {{ ucfirst(str_replace('_', ' ', $container->status)) }}
                                        </span>
                                    </td>
                                </tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>

            <!-- Itens no Container -->
            <div class="col-md-6">
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-list"></i> Itens no Container</h3>
                    </div>
                    <div class="box-body">
                        @if($itensNoContainer->count() > 0)
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Produto</th>
                                        <th>Quantidade</th>
                                        <th>Ação</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($itensNoContainer as $item)
                                        <tr>
                                            <td>{{ $item->produto->nome }}</td>
                                            <td>{{ $item->quantidade }}</td>
                                            <td>
                                                <a href="{{ route('estoque.produto.detalhes', $item->produto->id) }}" 
                                                    class="btn btn-xs btn-info">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="alert alert-info">
                                <i class="fa fa-info-circle"></i> Este container está vazio.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Descrição Adicional -->
        @if($container && $container->descricao_adicional)
            <div class="row">
                <div class="col-md-12">
                    <div class="box box-info">
                        <div class="box-header with-border">
                            <h3 class="box-title"><i class="fa fa-sticky-note"></i> Descrição Adicional</h3>
                        </div>
                        <div class="box-body">
                            {{ $container->descricao_adicional }}
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Ações -->
        <div class="row">
            <div class="col-md-12">
                <a href="{{ route('estoque.listar') }}" class="btn btn-default">
                    <i class="fa fa-arrow-left"></i> Voltar
                </a>
                <button class="btn btn-primary" data-toggle="modal" data-target="#editModal">
                    <i class="fa fa-edit"></i> Editar Container
                </button>
            </div>
        </div>
    </section>
</div>

<!-- Modal de Edição -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Dados do Container</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            @if($container)
                <form action="{{ route('container.atualizar', $container->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Tipo</label>
                            <input type="text" name="tipo" class="form-control" value="{{ $container->tipo }}">
                        </div>
                        <div class="form-group">
                            <label>Material</label>
                            <input type="text" name="material" class="form-control" value="{{ $container->material }}">
                        </div>
                        <div class="form-group">
                            <label>Cor</label>
                            <input type="text" name="cor" class="form-control" value="{{ $container->cor }}">
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Capacidade Máxima</label>
                                    <input type="number" step="0.01" name="capacidade_maxima" class="form-control" 
                                        value="{{ $container->capacidade_maxima }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Unidade</label>
                                    <select name="unidade_capacidade" class="form-control">
                                        <option value="kg" {{ $container->unidade_capacidade == 'kg' ? 'selected' : '' }}>kg</option>
                                        <option value="un" {{ $container->unidade_capacidade == 'un' ? 'selected' : '' }}>unidades</option>
                                        <option value="l" {{ $container->unidade_capacidade == 'l' ? 'selected' : '' }}>litros</option>
                                        <option value="m3" {{ $container->unidade_capacidade == 'm3' ? 'selected' : '' }}>m³</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Compartimentos</label>
                            <input type="number" name="compartimentos" class="form-control" value="{{ $container->compartimentos }}">
                        </div>
                        <div class="form-group">
                            <label>Número de Série</label>
                            <input type="text" name="numero_serie" class="form-control" value="{{ $container->numero_serie }}">
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="ativo" {{ $container->status == 'ativo' ? 'selected' : '' }}>Ativo</option>
                                <option value="danificado" {{ $container->status == 'danificado' ? 'selected' : '' }}>Danificado</option>
                                <option value="em_reparo" {{ $container->status == 'em_reparo' ? 'selected' : '' }}>Em Reparo</option>
                                <option value="inativo" {{ $container->status == 'inativo' ? 'selected' : '' }}>Inativo</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Descrição Adicional</label>
                            <textarea name="descricao_adicional" class="form-control" rows="3">{{ $container->descricao_adicional }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
