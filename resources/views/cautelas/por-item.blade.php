@extends('layout.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fas fa-box"></i> Cautelas por Item
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{route('dashboard')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{route('cautelas.index')}}">Cautelas</a></li>
            <li class="active">Por Item</li>
        </ol>
    </section>

    <section class="content container-fluid">
        <!-- Abas de visualização -->
        <div class="row">
            <div class="col-md-12">
                <ul class="nav nav-tabs" role="tablist" style="margin-bottom: 20px;">
                    <li role="presentation" style="margin-right: 10px;">
                        <a href="{{ route('cautelas.index') }}" 
                           class="btn btn-sm" 
                           style="background-color: #6c757d; color: white; border: 1px solid #5a6268;">
                            <i class="fa fa-file-text"></i> Por Cautela
                        </a>
                    </li>
                    <li role="presentation" class="active" style="margin-right: 10px;">
                        <a href="{{ route('cautelas.por-item') }}" 
                           class="btn btn-sm" 
                           style="background-color: #0073cc; color: white; border: 1px solid #0056b3;">
                            <i class="fa fa-box"></i> Por Item
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                @if($produtos && $produtos->count() > 0)
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">
                                <i class="fa fa-list"></i> Itens em Cautela ({{ $produtos->count() }})
                            </h3>
                        </div>
                        <div class="box-body">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th width="40%">Produto</th>
                                        <th width="15%" class="text-center">Quantidade Cautelada</th>
                                        <th width="15%" class="text-center">Quantidade Devolvida</th>
                                        <th width="15%" class="text-center">Quantos Possuem</th>
                                        <th width="15%" class="text-center">Ação</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($produtos as $produto)
                                        <tr>
                                            <td>
                                                <strong>{{ $produto['nome'] }}</strong>
                                            </td>
                                            <td class="text-center">
                                                <span class="label label-warning">
                                                    {{ $produto['quantidade_cautelada'] }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="label label-success">
                                                    {{ $produto['quantidade_devolvida'] ?? 0 }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="label label-info">
                                                    {{ $produto['quantidade_pessoas'] }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('cautelas.detalhes-item', $produto['id']) }}" 
                                                   class="btn btn-xs btn-info" 
                                                   title="Ver detalhes">
                                                    <i class="fa fa-eye"></i> Detalhes
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="box box-info">
                        <div class="box-body">
                            <div class="alert alert-info alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <h4><i class="icon fa fa-info"></i> Atenção!</h4>
                                Nenhum item em cautela no momento.
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>
</div>
@endsection
