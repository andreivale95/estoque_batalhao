@extends('layout/app')
@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>Estoque</h1>
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
                                <label class="control-label">CATEGORIA:</label>
                                <select name="categoria" class="form-control">
                                    <option value="">Selecione</option>
                                    @foreach ($categorias as $categoria)
                                        <option value="{{ $categoria->id }}"
                                            {{ request()->categoria == $categoria->id ? 'selected' : '' }}>
                                            {{ $categoria->nome }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group has-feedback col-md-2">
                                <label class="control-label">Estoque:</label>
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

                            <div class="form-group has-feedback col-md-1 pull-right">
                                <label class="control-label">&nbsp;</label>
                                <button class="btn btn-primary form-control">
                                    <i class="fa fa-search"></i> Pesquisar
                                </button>
                            </div>

                            <!-- Botão para inserir novo produto -->
                            <div class="form-group has-feedback col-md-2 pull-right">
                                <label class="control-label">&nbsp;</label>
                                <a href="{{ route('produtoinserir.form') }}" class="btn btn-success form-control">
                                    <i class="fa fa-plus"></i> Inserir Produto
                                </a>
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
                                <th><input type="checkbox" id="select-all"></th>
                                <th>Produto</th>
                                <th>Quantidade</th>
                                <th>Unidade</th>
                                <th>Categoria</th>
                                <th>Estoque</th>
                                <th>Valor Unitário</th>
                                <th>Subtotal</th>
                                <th>Ações</th>
                                <th>Transferência</th>
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
                                        @if (Auth::user()->fk_unidade == $estoque->unidade()->first()->id)
                                            <input type="checkbox" class="select-item"
                                                name="produtos[{{ $estoque->id }}][selecionado]">
                                        @else
                                            <input type="checkbox" class="select-item"
                                                name="produtos[{{ $estoque->id }}][selecionado]"disabled>
                                        @endif

                                    </td>
                                    <td>
                                        <a href="{{ route('produto.ver', $estoque->fk_produto) }}">
                                            {{ $estoque->produto()->first()->nome }} -
                                            {{ optional($estoque->produto()->first()?->tamanho()->first())->tamanho ?? 'Tamanho Único' }}
                                        </a>
                                    </td>
                                    <td>
                                        @if ($estoque->quantidade <= 0)
                                            <span class="text-danger">Produto esgotado</span>
                                        @else
                                            {{ $estoque->quantidade }}
                                        @endif
                                    </td>
                                    <td>{{ $estoque->produto->unidade }}</td>
                                    <td>{{ $estoque->produto->categoria->nome }}</td>
                                    <td>{{ $estoque->unidade()->first()->nome }}</td>
                                    <td>R$ {{ number_format($valorUnitario, 2, ',', '.') }}</td>
                                    <td>R$ {{ number_format($subtotal, 2, ',', '.') }}</td>
                                    <td>
                                        @if (Auth::user()->fk_unidade == $estoque->unidade()->first()->id)
                                            <a class="btn btn-primary" href="{{ route('entrada.form', $estoque->id) }}"
                                                style="color: white">
                                                <i class="fa fa-plus"></i>
                                            </a>
                                            <a class="btn btn-danger" href="{{ route('saida.form', $estoque->id) }}"
                                                style="color: white">
                                                <i class="fa fa-minus"></i>
                                            </a>
                                        @else
                                            <span class="text-muted">Acesso restrito</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if (Auth::user()->fk_unidade == $estoque->unidade()->first()->id)
                                            <button type="button" class="btn btn-warning" data-toggle="modal"
                                                data-target="#modalTransferencia{{ $estoque->id }}">
                                                <i class="fa fa-exchange-alt"></i>
                                            </button>
                                        @else
                                            <span class="text-muted">Acesso restrito</span>
                                        @endif
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
                                                    <p><strong>Unidade atual:</strong>
                                                        {{ $estoque->unidade()->first()->nome ?? 'Não definida' }}</p>

                                                    <div class="form-group">
                                                        <label for="nova_unidade">Nova Unidade:</label>
                                                        <select class="form-control" name="nova_unidade" required>
                                                            <option value="">Selecione</option>
                                                            @foreach ($unidades as $unidade)
                                                                @if ($unidade->id != $estoque->fk_unidade)
                                                                    <option value="{{ $unidade->id }}">
                                                                        {{ $unidade->nome }}</option>
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
                                                    <button type="submit" class="btn btn-success">Confirmar
                                                        Transferência</button>
                                                    <button type="button" class="btn btn-secondary"
                                                        data-dismiss="modal">Cancelar</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="box-footer">
                    <button type="button" class="btn btn-success" id="open-modal-saida" disabled>
                        <i class="fa fa-check"></i> Saída Múltipla
                    </button>
                </div>
            </div>

            <!-- Modal de Saída Múltipla -->
            <div class="modal fade" id="modalSaidaMultipla" tabindex="-1" role="dialog"
                aria-labelledby="modalSaidaMultiplaLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <form id="modal-saida-form" action="{{ route('estoque.saidaMultiplos') }}" method="POST">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title">Confirmar Saída de Produtos</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <p>Confirme as informações de saída para os produtos selecionados:</p>
                                <div id="produtosSelecionadosContainer"></div>
                                <div class="form-group">
                                    <label for="militar">Militar:</label>
                                    <select name="militar" class="form-control" required>
                                        <option value="">Selecione o militar</option>
                                        @foreach ($militares as $militar)
                                            <option value="{{ $militar->id }}">{{ $militar->nome }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="data_saida">Data de Saída:</label>
                                    <input type="date" name="data_saida" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="observacao">Observação:</label>
                                    <textarea name="observacao" class="form-control" rows="3" placeholder="Observações sobre a saída (opcional)"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-success">Confirmar Saída</button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectAllCheckbox = document.getElementById('select-all');
            const itemCheckboxes = document.querySelectorAll('.select-item');
            const openModalButton = document.getElementById('open-modal-saida');
            const produtosSelecionadosContainer = document.getElementById('produtosSelecionadosContainer');

            selectAllCheckbox.addEventListener('change', function() {
                itemCheckboxes.forEach(checkbox => {
                    checkbox.checked = selectAllCheckbox.checked;
                });
                toggleOpenModalButton();
            });

            itemCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', toggleOpenModalButton);
            });

            function toggleOpenModalButton() {
                const anyChecked = Array.from(itemCheckboxes).some(checkbox => checkbox.checked);
                openModalButton.disabled = !anyChecked;
            }

            openModalButton.addEventListener('click', function() {
                produtosSelecionadosContainer.innerHTML = '';
                itemCheckboxes.forEach(checkbox => {
                    if (checkbox.checked) {
                        const row = checkbox.closest('tr');
                        const produtoNome = row.querySelector('td:nth-child(2)').innerText.trim();
                        const estoqueId = checkbox.name.match(/\d+/)[0];
                        const quantidadeDisponivel = row.querySelector('td:nth-child(3)').innerText
                            .trim();

                        const html = `
                            <div class="form-group">
                                <label>${produtoNome} (Disponível: ${quantidadeDisponivel})</label>
                                <input type="number" name="produtos[${estoqueId}][quantidade]" class="form-control" min="1" max="${quantidadeDisponivel}" required>
                            </div>
                        `;
                        produtosSelecionadosContainer.insertAdjacentHTML('beforeend', html);
                    }
                });
                $('#modalSaidaMultipla').modal('show');
            });
        });
    </script>
@endsection
