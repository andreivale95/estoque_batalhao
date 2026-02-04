@extends('layout.app')
@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>Transferir Itens da Seção: {{ $secao->nome }}</h1>
        <a href="{{ route('secoes.ver', ['unidade' => $secao->fk_unidade, 'secao' => $secao->id]) }}" class="btn btn-secondary">Voltar</a>
    </section>
    <section class="content container-fluid">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        <div class="box box-primary">
            <div class="box-body">
                <form action="{{ route('secoes.transferir_lote', ['unidade' => $secao->fk_unidade, 'secao' => $secao->id]) }}" method="POST">
                    @csrf
                    
                    <div class="form-group">
                        <label for="nova_secao">Transferir para seção: <span style="color: red;">*</span></label>
                        <select name="nova_secao" id="nova_secao" class="form-control" required>
                            <option value="">Selecione a seção de destino</option>
                            @foreach($todasSecoes as $s)
                                <option value="{{ $s->id }}" {{ $s->id == $secao->id ? 'disabled' : '' }}>
                                    {{ $s->nome }} {{ $s->id == $secao->id ? '(Atual)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <hr>

                    <!-- Itens de Consumo -->
                    <h4><i class="fa fa-box"></i> Itens de Consumo</h4>
                    @if($itens->count() > 0)
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Lote</th>
                                    <th>Quantidade disponível</th>
                                    <th>Quantidade a transferir</th>
                                    <th>Ação</th>
                                </tr>
                            </thead>
                            <tbody id="itensTable">
                                <tr>
                                    <td>
                                        <select name="item_id[]" class="form-control">
                                            <option value="">Selecione o item</option>
                                            @foreach($itens as $item)
                                                <option value="{{ $item->id }}" data-qtd="{{ $item->quantidade }}">
                                                    {{ $item->produto->nome }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="lote-info"></td>
                                    <td class="qtd-disponivel"></td>
                                    <td><input type="number" name="quantidade_transferir[]" class="form-control" min="1"></td>
                                    <td><button type="button" class="btn btn-danger btn-sm remove-item">Remover</button></td>
                                </tr>
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-info btn-sm" id="addItem"><i class="fa fa-plus"></i> Adicionar Item</button>
                    @else
                        <p class="text-muted">Nenhum item de consumo disponível nesta seção.</p>
                    @endif

                    <hr style="margin-top: 20px;">

                    <!-- Itens Patrimoniais -->
                    <h4><i class="fa fa-barcode"></i> Itens Patrimoniais</h4>
                    @if($itensPatrimoniais->count() > 0)
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th width="50px">
                                        <input type="checkbox" id="selectAllPatrimonios">
                                    </th>
                                    <th>Produto</th>
                                    <th>Patrimônio</th>
                                    <th>Série</th>
                                    <th>Condição</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($itensPatrimoniais as $pat)
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="patrimonio_id[]" value="{{ $pat->id }}" class="patrimonio-checkbox">
                                        </td>
                                        <td>{{ $pat->produto->nome }}</td>
                                        <td><strong>{{ $pat->patrimonio }}</strong></td>
                                        <td>{{ $pat->serie ?? '-' }}</td>
                                        <td>{{ $pat->condicao ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-muted">Nenhum item patrimonial disponível nesta seção.</p>
                    @endif

                    <div class="form-group text-right" style="margin-top: 20px;">
                        <a href="{{ route('secoes.ver', ['unidade' => $secao->fk_unidade, 'secao' => $secao->id]) }}" class="btn btn-default">
                            <i class="fa fa-arrow-left"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-warning">
                            <i class="fa fa-exchange"></i> Transferir Selecionados
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
</div>
<script>
    // Adicionar item de consumo
    document.getElementById('addItem').addEventListener('click', function() {
        var row = document.querySelector('#itensTable tr').cloneNode(true);
        row.querySelectorAll('input, select').forEach(function(el) { el.value = ''; });
        row.querySelector('.qtd-disponivel').textContent = '';
        row.querySelector('.lote-info').textContent = '';
        document.getElementById('itensTable').appendChild(row);
    });
    
    // Remover item
    document.getElementById('itensTable').addEventListener('click', function(e) {
        if(e.target.classList.contains('remove-item')) {
            if(document.querySelectorAll('#itensTable tr').length > 1) {
                e.target.closest('tr').remove();
            } else {
                alert('Você deve manter pelo menos uma linha.');
            }
        }
    });
    
    // Atualizar quantidade disponível ao selecionar item
    document.getElementById('itensTable').addEventListener('change', function(e) {
        if(e.target.tagName === 'SELECT' && e.target.name === 'item_id[]') {
            var selected = e.target.options[e.target.selectedIndex];
            var qtd = selected.getAttribute('data-qtd');
            var row = e.target.closest('tr');
            var qtdCell = row.querySelector('.qtd-disponivel');
            var loteCell = row.querySelector('.lote-info');
            
            qtdCell.textContent = qtd ? qtd : '';
            
            // Extrair lote do texto da opção se existir
            var texto = selected.textContent;
            var loteMatch = texto.match(/\(([^)]+)\)$/);
            loteCell.textContent = loteMatch ? loteMatch[1] : '-';
        }
    });
    
    // Selecionar/desselecionar todos os patrimônios
    document.getElementById('selectAllPatrimonios').addEventListener('change', function() {
        var checkboxes = document.querySelectorAll('.patrimonio-checkbox');
        checkboxes.forEach(function(checkbox) {
            checkbox.checked = this.checked;
        }.bind(this));
    });
    
    // Validação antes de enviar
    document.querySelector('form').addEventListener('submit', function(e) {
        var novaSecao = document.getElementById('nova_secao').value;
        if (!novaSecao) {
            e.preventDefault();
            alert('Selecione uma seção de destino!');
            return false;
        }
        
        // Verificar se pelo menos um item foi selecionado
        var itensConsumo = document.querySelectorAll('select[name="item_id[]"]');
        var hasConsumo = Array.from(itensConsumo).some(function(select) {
            return select.value !== '';
        });
        
        var patrimonios = document.querySelectorAll('.patrimonio-checkbox:checked');
        var hasPatrimonio = patrimonios.length > 0;
        
        if (!hasConsumo && !hasPatrimonio) {
            e.preventDefault();
            alert('Selecione pelo menos um item de consumo ou patrimônio para transferir!');
            return false;
        }
        
        return true;
    });
</script>
@endsection
