@extends('layout.app')
@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>Vincular Itens à Seção: {{ $secao->nome }}</h1>
        <a href="{{ route('secoes.index', $secao->fk_unidade) }}" class="btn btn-secondary">Voltar</a>
    </section>
    <section class="content container-fluid">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <div class="box box-primary">
            <div class="box-body">
                <form action="{{ route('secoes.vincular_itens', ['unidade' => $secao->fk_unidade, 'secao' => $secao->id]) }}" method="POST">
                    @csrf
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Quantidade disponível</th>
                                <th>Quantidade</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody id="itensTable">
                            <tr>
                                <td>
                                    <select name="itens[]" class="form-control">
                                        <option value="">Selecione o item</option>
                                        @foreach($itens as $item)
                                            <option value="{{ $item->id }}" data-qtd="{{ $item->quantidade }}">{{ $item->produto->nome }} ({{ $item->lote }})</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="qtd-disponivel"></td>
                                <td><input type="number" name="quantidades[]" class="form-control" min="1"></td>
                                <td><button type="button" class="btn btn-danger btn-sm remove-item">Remover</button></td>
                            </tr>
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-info" id="addItem">Adicionar Item</button>
                    <button type="submit" class="btn btn-success">Salvar Vínculos</button>
                </form>
            </div>
        </div>
    </section>
</div>
<script>
    document.getElementById('addItem').addEventListener('click', function() {
        var row = document.querySelector('#itensTable tr').cloneNode(true);
        row.querySelectorAll('input, select').forEach(function(el) { el.value = ''; });
        row.querySelector('.qtd-disponivel').textContent = '';
        document.getElementById('itensTable').appendChild(row);
    });
    document.getElementById('itensTable').addEventListener('click', function(e) {
        if(e.target.classList.contains('remove-item')) {
            if(document.querySelectorAll('#itensTable tr').length > 1) {
                e.target.closest('tr').remove();
            }
        }
    });
    // Atualiza quantidade disponível ao selecionar item
    document.getElementById('itensTable').addEventListener('change', function(e) {
        if(e.target.tagName === 'SELECT') {
            var selected = e.target.options[e.target.selectedIndex];
            var qtd = selected.getAttribute('data-qtd');
            var qtdCell = e.target.closest('tr').querySelector('.qtd-disponivel');
            qtdCell.textContent = qtd ? qtd : '';
        }
    });
</script>
@endsection
