@extends('layout.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>Histórico de Movimentações</h1>
    </section>

    <section class="content container-fluid">
        <form method="GET" action="{{ route('movimentacoes.index') }}" class="mb-4">
            <div class="row">
                <div class="col-md-2">
                    <label>Produto</label>
                    <select name="produto" class="form-control select2">
                        <option value="">Todos</option>
                        @foreach ($produtos as $produto)
                        <option value="{{ $produto->id }}"
                            {{ request('produto') == $produto->id ? 'selected' : '' }}>
                            {{ $produto->nome }} -
                            {{ optional($produto->tamanho()->first())->tamanho ?? 'Tamanho Único' }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-1">
                    <label>Tipo</label>
                    <select name="tipo" class="form-control">
                        <option value="">Todos</option>
                        <option value="entrada" {{ request('tipo') == 'entrada' ? 'selected' : '' }}>Entrada</option>
                        <option value="saida" {{ request('tipo') == 'saida' ? 'selected' : '' }}>Saída</option>
                        <option value="transferencia" {{ request('tipo') == 'transferencia' ? 'selected' : '' }}>
                            Transferência</option>
                        <option value="saida_kit" {{ request('tipo') == 'saida_kit' ? 'selected' : '' }}>Saída Kit
                        </option>
                        <option value="Saida_manual_multipla"
                            {{ request('tipo') == 'Saida_manual_multipla' ? 'selected' : '' }}>Saída Múltipla</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label>Data Inicial</label>
                    <input type="date" name="data_inicio" class="form-control" value="{{ request('data_inicio') }}">
                </div>

                <div class="col-md-2">
                    <label>Data Final</label>
                    <input type="date" name="data_fim" class="form-control" value="{{ request('data_fim') }}">
                </div>

                <div class="col-md-2">
                    <label>Militar</label>
                    <input type="text" name="militar" class="form-control" value="{{ request('militar') }}">
                </div>

                <div class="col-md-2">
                    <label>Fonte</label>
                    <input type="text" name="fonte" class="form-control" value="{{ request('fonte') }}">
                </div>

                <div class="col-md-2">
                    <label>Estoque</label>
                    <input type="text" name="estoque" class="form-control" value="{{ request('estoque') }}">
                </div>

                <div class="col-md-2">
                    <label>Proc. SEI</label>
                    <input type="text" name="sei" class="form-control" value="{{ request('sei') }}">
                </div>

                <div class="col-md-1">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-primary btn-block">Filtrar</button>
                </div>
                <div class="col-md-1">
                    <label>&nbsp;</label>
                    <a href="{{ route('movimentacoes.index') }}" class="btn btn-default btn-block" id="btnLimparFiltro">Limpar filtro</a>
                </div>
            </div>
        </form>
        <br>

        <div class="row mb-3">
            <div class="col-md-12">
                <button type="button" class="btn btn-default" id="configMenuPlain" style="display:inline-block;">
                    Configurações de Colunas
                </button>
                <div id="configMenuPanel" class="well well-sm" style="display:none;margin-top:8px;max-height:300px;overflow-y:auto;">
                    <button type="button" class="btn btn-sm btn-primary mb-2" id="selectAllCols">Selecionar todos</button>
                    <label class="checkbox-inline"><input type="checkbox" class="toggle-col" data-col="data" checked> Data</label>
                    <label class="checkbox-inline"><input type="checkbox" class="toggle-col" data-col="produto" checked> Produto</label>
                    <label class="checkbox-inline"><input type="checkbox" class="toggle-col" data-col="tipo" checked> Tipo</label>
                    <label class="checkbox-inline"><input type="checkbox" class="toggle-col" data-col="fornecedor" checked> Fornecedor</label>
                    <label class="checkbox-inline"><input type="checkbox" class="toggle-col" data-col="nota_fiscal" checked> N.F.</label>
                    <label class="checkbox-inline"><input type="checkbox" class="toggle-col" data-col="quantidade" checked> Qtd.</label>
                    <label class="checkbox-inline"><input type="checkbox" class="toggle-col" data-col="valor_unitario"> V. Unitário</label>
                    <label class="checkbox-inline"><input type="checkbox" class="toggle-col" data-col="valor_total"> V. Total</label>
                    <label class="checkbox-inline"><input type="checkbox" class="toggle-col" data-col="unidade" checked> Unidade</label>
                    <label class="checkbox-inline"><input type="checkbox" class="toggle-col" data-col="responsavel" checked> Responsável</label>
                    <label class="checkbox-inline"><input type="checkbox" class="toggle-col" data-col="estoque" checked> Estoque</label>
                    <label class="checkbox-inline"><input type="checkbox" class="toggle-col" data-col="origem" checked> Origem</label>
                    <label class="checkbox-inline"><input type="checkbox" class="toggle-col" data-col="destino" checked> Destino</label>
                    <label class="checkbox-inline"><input type="checkbox" class="toggle-col" data-col="militar" checked> Militar</label>
                    <label class="checkbox-inline"><input type="checkbox" class="toggle-col" data-col="setor" checked> Setor</label>
                    <label class="checkbox-inline"><input type="checkbox" class="toggle-col" data-col="sei"> Proc. SEI</label>
                    <label class="checkbox-inline"><input type="checkbox" class="toggle-col" data-col="data_trp"> D. TRP</label>
                    <label class="checkbox-inline"><input type="checkbox" class="toggle-col" data-col="fonte" checked> Fonte</label>
                    <label class="checkbox-inline"><input type="checkbox" class="toggle-col" data-col="observacao" checked> Observação</label>
                </div>
            </div>
        </div>

        <table class="table table-bordered table-hover">
            <thead class="bg-primary" style="color:white;">
                <tr>
                    <th class="col-data">Data</th>
                    <th class="col-produto">Produto</th>
                    <th class="col-tipo">Tipo</th>
                    <th class="col-fornecedor">Fornecedor</th>
                    <th class="col-nota_fiscal">N.F.</th>
                    <th class="col-quantidade">Qtd.</th>
                    <th class="col-valor_unitario" style="display:none;">V. Unitário</th>
                    <th class="col-valor_total" style="display:none;">V. Total</th>
                    <th class="col-unidade">Unidade</th>
                    <th class="col-responsavel">Responsável</th>
                    <th class="col-estoque">Estoque</th>
                    <th class="col-origem">Origem</th>
                    <th class="col-destino">Destino</th>
                    <th class="col-militar">Militar</th>
                    <th class="col-setor">Setor</th>
                    <th class="col-sei" style="display:none;">Proc. SEI</th>
                    <th class="col-data_trp" style="display:none;">D. TRP</th>
                    <th class="col-fonte">Fonte</th>
                    <th class="col-observacao">Observação</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($movimentacoes as $m)
                <tr>
                    <td class="col-data">{{ \Carbon\Carbon::parse($m->data_movimentacao)->format('d/m/Y H:i:s') }}</td>
                    <td class="col-produto">{{ $m->produto->nome ?? '—' }}</td>
                    <td class="col-tipo">
                        {{ $m->tipo_movimentacao }}
                        @if(($m->movimentacao_origem_id ?? null) || str_contains($m->observacao ?? '', 'Desfeito movimentação'))
                            <span class="label label-danger" style="margin-left:6px;">Desfeita</span>
                        @endif
                    </td>
                    <td class="col-fornecedor">{{ $m->fornecedor }}</td>
                    <td class="col-nota_fiscal">{{ $m->nota_fiscal }}</td>
                    <td class="col-quantidade">{{ $m->quantidade }}</td>
                    <td class="col-valor_unitario" style="display:none;">{{ number_format($m->valor_unitario, 2, ',', '.') }}</td>
                    <td class="col-valor_total" style="display:none;">{{ number_format($m->valor_total, 2, ',', '.') }}</td>
                    <td class="col-unidade">{{ $m->produto->unidade ?? '-' }}</td>
                    <td class="col-responsavel">{{ $m->responsavel }}</td>
                    <td class="col-estoque">{{ $m->unidade->nome ?? '-' }}</td>
                    <td class="col-origem">{{ $m->origem->nome ?? '-' }}</td>
                    <td class="col-destino">{{ $m->destino->nome ?? '-' }}</td>
                    <td class="col-militar">{{ $m->militar }}</td>
                    <td class="col-setor">{{ $m->setor }}</td>
                    <td class="col-sei" style="display:none;">{{ $m->sei }}</td>
                    <td class="col-data_trp" style="display:none;">{{ $m->data_trp ? \Carbon\Carbon::parse($m->data_trp)->format('d/m/Y') : '-' }}</td>
                    <td class="col-fonte"><span class="badge badge-info">{{ $m->fonte ?? '-' }}</span></td>
                    <td class="col-observacao">{{ $m->observacao ?? '-' }}</td>
                    <td>
                        <form action="{{ route('movimentacao.desfazer', $m->id) }}" method="POST" class="form-desfazer" style="display:inline-block;">
                            @csrf
                            @method('PUT')
                            <button type="button" class="btn btn-warning btn-sm btn-confirm-desfazer" data-id="{{ $m->id }}" title="Desfazer movimentação">
                                Desfazer
                            </button>
                        </form>
                        <a href="{{ route('movimentacao.ver', $m->id) }}" class="btn btn-info btn-sm" title="Ver mais informações">
                            <i class="fa fa-info-circle"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="20" class="text-center">Nenhuma movimentação encontrada.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{ $movimentacoes->links() }}
    </section>
</div>

<!-- Modal de confirmação -->
<div class="modal fade" id="modalConfirmDesfazer" tabindex="-1" role="dialog" aria-labelledby="modalConfirmDesfazerLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalConfirmDesfazerLabel">Confirmar Desfazer Movimentação</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        Tem certeza que deseja desfazer esta movimentação? Esta ação não pode ser desfeita.
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-warning" id="btnModalConfirmarDesfazer">Desfazer</button>
      </div>
    </div>
  </div>
</div>

<script>
    let formDesfazerSelecionado = null;
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "Selecione um Produto",
            allowClear: true,
            width: '100%'
        });

        // Modal de confirmação para desfazer
        $('.btn-confirm-desfazer').on('click', function(e) {
            e.preventDefault();
            formDesfazerSelecionado = $(this).closest('form');
            $('#modalConfirmDesfazer').modal('show');
        });
        $('#btnModalConfirmarDesfazer').on('click', function() {
            if (formDesfazerSelecionado) {
                formDesfazerSelecionado.submit();
            }
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        // Toggle panel for column configuration
        var configMenuBtn = document.getElementById('configMenuPlain');
        var configMenuPanel = document.getElementById('configMenuPanel');
        if (configMenuBtn && configMenuPanel) {
            configMenuBtn.addEventListener('click', function() {
                var isOpen = configMenuPanel.style.display !== 'none';
                configMenuPanel.style.display = isOpen ? 'none' : 'block';
            });
        }

        // Carregar configurações salvas
        let savedCols = JSON.parse(localStorage.getItem('movCols')) || {};
        document.querySelectorAll('.toggle-col').forEach(function(checkbox) {
            var col = checkbox.getAttribute('data-col');
            if (savedCols[col] === false) {
                checkbox.checked = false;
                document.querySelectorAll('.col-' + col).forEach(function(cell) {
                    cell.style.display = 'none';
                });
            } else {
                checkbox.checked = true;
                document.querySelectorAll('.col-' + col).forEach(function(cell) {
                    cell.style.display = '';
                });
            }
            checkbox.addEventListener('change', function() {
                var show = this.checked;
                savedCols[col] = show;
                localStorage.setItem('movCols', JSON.stringify(savedCols));
                document.querySelectorAll('.col-' + col).forEach(function(cell) {
                    cell.style.display = show ? '' : 'none';
                });
            });
        });
        // Botão selecionar todos
        document.getElementById('selectAllCols').addEventListener('click', function() {
            document.querySelectorAll('.toggle-col').forEach(function(checkbox) {
                checkbox.checked = true;
                var col = checkbox.getAttribute('data-col');
                savedCols[col] = true;
                document.querySelectorAll('.col-' + col).forEach(function(cell) {
                    cell.style.display = '';
                });
            });
            localStorage.setItem('movCols', JSON.stringify(savedCols));
        });
    });
</script>
@endsection