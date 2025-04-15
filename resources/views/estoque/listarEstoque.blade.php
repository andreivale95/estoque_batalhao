@extends('layout/app')
@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                Estoque

            </h1>
            <ol class="breadcrumb">
                <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
                <li><a href="{{ route('estoque.listar') }}"><i class=""></i> Estoque</a></li>
            </ol>
        </section>
        <!-- Main content -->
        <section class="content container-fluid">

            <form action="{{ route('estoque.listar') }}" method="get">
                <div class="box box-primary">
                    <div class="box-header">
                        <div class="row">
                            <div class="form-group has-feedback col-md-2">
                                <label class="control-label">PRODUTO:</label>
                                <input type="text" class="form-control" name="nome" value="{{ request()->nome }}">
                            </div>

                            <div class="form-group has-feedback col-md-2">
                                <label class="control-label">TIPO DO PRODUTO:</label>
                                <select name="tipoproduto" class="form-control">
                                    <option value="">Selecione</option>
                                    @foreach ($tipoprodutos as $tipoproduto)
                                        <option value="{{ $tipoproduto->id }}"
                                            {{ request()->tipoproduto == $tipoproduto->id ? 'selected' : '' }}>
                                            {{ $tipoproduto->nome }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            @if (Auth::user()->fk_unidade == 14)
                                <div class="form-group has-feedback col-md-2">
                                    <label class="control-label">UNIDADE:</label>
                                    <select name="unidade" class="form-control">
                                        <option value="">Selecione</option>
                                        @foreach ($unidades as $unidade)
                                            <option value="{{ $unidade->id }}"
                                                {{ request()->unidade == $unidade->id ? 'selected' : '' }}>
                                                {{ $unidade->nome }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <div class="form-group has-feedback col-md-1 pull-right">
                                <label class="control-label">&nbsp;</label>
                                <button class="btn btn-primary form-control">
                                    <i class="fa fa-search"></i> Pesquisar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <div class="box box-primary">

                <div class="box-body table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>Quantidade</th>
                                <th>Tipo</th>
                                <th>Categoria</th>
                                <th>Unidade</th>
                                <th>Valor Unitário</th>
                                <th>Subtotal</th>
                                <th>Ações</th>
                                <th>Transferir</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($itens_estoque as $estoque)
                                @php
                                    $valorUnitario = $estoque->produto->valor ?? 0;
                                    $subtotal = $estoque->quantidade * $valorUnitario;
                                @endphp
                                <tr>
                                    <td>
                                        <a href="{{ route('produto.ver', $estoque->fk_produto) }}">
                                            {{ $estoque->produto()->first()->nome }} -  {{ $estoque->produto()->first()->tamanho()->first()->tamanho }}
                                        </a>
                                    </td>
                                    <td>{{ $estoque->quantidade }}</td>
                                    <td>{{ $estoque->produto->tipoProduto->nome }}</td>
                                    <td>{{ $estoque->produto->tipoProduto->categoria->nome }}</td>
                                    <td>{{ $estoque->unidade()->first()->nome }}</td>
                                    <td>R$ {{ number_format($valorUnitario, 2, ',', '.') }}</td>
                                    <td>R$ {{ number_format($subtotal, 2, ',', '.') }}</td>
                                    <td>
                                        <a class="btn btn-primary" href="{{ route('entrada.form', $estoque->id) }}"
                                            style="color: white">
                                            <i class="fa fa-plus"></i>
                                        </a>
                                        <a class="btn btn-danger" href="{{ route('saida.form', $estoque->id) }}"
                                            style="color: white">
                                            <i class="fa fa-minus"></i>
                                        </a>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-warning" data-toggle="modal"
                                            data-target="#modalTransferencia{{ $estoque->id }}">
                                            <i class="fa fa-exchange-alt"></i>
                                        </button>
                                    </td>
                                </tr>

                                <!-- Modal de Transferência -->
                                <div class="modal fade" id="modalTransferencia{{ $estoque->id }}" tabindex="-1"
                                    role="dialog" aria-labelledby="modalTransferenciaLabel{{ $estoque->id }}"
                                    aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <form action="{{ route('estoque.transferir') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="estoque_id" value="{{ $estoque->id }}">
                                            <input type="hidden" name="unidade_atual" value="{{ $estoque->unidade }}">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Transferir Produto</h5>
                                                    <button type="button" class="close" data-dismiss="modal"
                                                        aria-label="Fechar">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>

                                                <div class="modal-body">
                                                    <p><strong>Produto:</strong> {{ $estoque->produto->nome }}</p>
                                                    <p><strong>Unidade atual:</strong> {{ $estoque->unidade()->first()->nome ?? 'Não definida' }}</p>

                                                    <div class="form-group">
                                                        <label for="nova_unidade">Nova Unidade:</label>
                                                        <select class="form-control" name="nova_unidade" required>
                                                            <option value="">Selecione</option>
                                                            @foreach ($unidades as $unidade)
                                                                @if ($unidade->id != $estoque->fk_unidade)
                                                                    <option value="{{ $unidade->id }}">{{ $unidade->nome }}</option>
                                                                @endif
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="quantidade">Quantidade:</label>
                                                        <input type="number" name="quantidade" class="form-control"
                                                            min="1" max="{{ $estoque->quantidade }}" required>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="observacao">Observação:</label>
                                                        <textarea name="observacao" class="form-control" rows="3"
                                                            placeholder="Observações sobre a transferência (opcional)"></textarea>
                                                    </div>
                                                </div>

                                                <div class="modal-footer">
                                                    <button type="submit" class="btn btn-success">Confirmar Transferência</button>
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="6" class="text-right"><strong>Total Geral:</strong></td>
                                <td><strong>R$ {{ number_format($totalGeral, 2, ',', '.') }}</strong></td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>

                    </table>
                </div>



                <div class="box-footer">


                    <div class="btn-person">
                        <a href="{{ route('produtoinserir.form') }}" class="btn btn-primary add pull-right">
                            <i class="fa fa-plus"></i> Inserir novo produto
                        </a>
                    </div>




                    {{ $itens_estoque->appends([
                            'nome' => request()->get('nome'),
                            'tipoproduto' => request()->get('tipoproduto'),
                            'unidade' => request()->get('unidade'),
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
            position: fixed;
            right: 4%;
            bottom: 5%;
            border-radius: 50%;
            z-index: 1000;
        }

        .btn-person2 {
            position: fixed;
            right: 1%;
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

        .remove {
            box-shadow: 3px 3px 10px 3px rgba(23, 22, 0, 0.5);
            border-radius: 50px;
            font-size: 20px
        }

        .remove:hover {
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

            @media (max-width:767px) {
                .btn-person2 {
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
