@extends('layout.app')

@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Cadastrar Cautela
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{route('dashboard')}}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{ route('cautelas.index') }}">Cautelas</a></li>
            <li class="active">Nova Cautela</li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content container-fluid">
        <div class="box box-primary">
            <div class="box-body">
                <form action="{{ route('cautelas.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="nome_responsavel">Nome do Responsável</label>
            <input type="text" name="nome_responsavel" id="nome_responsavel" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="telefone">Telefone</label>
            <input type="text" name="telefone" id="telefone" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="instituicao">Instituição/Unidade</label>
            <input type="text" name="instituicao" id="instituicao" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="data_cautela">Data da Cautela</label>
            <input type="date" name="data_cautela" id="data_cautela" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="data_prevista_devolucao">Data Prevista de Devolução</label>
            <input type="date" name="data_prevista_devolucao" id="data_prevista_devolucao" class="form-control" required>
        </div>

        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">Itens da Cautela</h3>
            </div>
            <div class="box-body">
                <div id="itemList">
                    <div class="item-row">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Seção</label>
                                    <select name="secoes[]" class="form-control secao-select" required>
                                        <option value="">Selecione a Seção</option>
                                        @if($secoes->isEmpty())
                                            <option value="" disabled>Nenhuma seção cadastrada</option>
                                        @else
                                            @foreach($secoes as $secao)
                                                <option value="{{ $secao->id }}">{{ $secao->nome }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <!-- Debug Info -->
                                    @if(config('app.debug'))
                                        <small class="text-muted">
                                            Total de seções: {{ $secoes->count() }}
                                        </small>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Item</label>
                                    <select name="items[]" class="form-control item-select" required disabled>
                                        <option value="">Selecione o Item</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Quantidade</label>
                                    <input type="number" name="quantidades[]" class="form-control quantidade-input" min="1" required>
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="button" class="btn btn-danger btn-block remove-item">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <button type="button" class="btn btn-success" id="addItem">
                            <i class="fa fa-plus"></i> Adicionar Item
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Salvar</button>
    </form>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Máscara para o campo de telefone
    $('#telefone').mask('(00) 00000-0000');

    // Função para carregar itens de uma seção
    function carregarItens(secaoId, itemSelect) {
        if (secaoId) {
            $.get(`/api/secoes/${secaoId}/items`, function(data) {
                itemSelect.prop('disabled', false);
                itemSelect.empty().append('<option value="">Selecione o Item</option>');
                data.forEach(function(item) {
                    itemSelect.append(`<option value="${item.id}">${item.nome} (Disponível: ${item.quantidade})</option>`);
                });
            });
        } else {
            itemSelect.prop('disabled', true);
            itemSelect.empty().append('<option value="">Selecione o Item</option>');
        }
    }

    // Quando uma seção é selecionada
    $(document).on('change', '.secao-select', function() {
        var itemSelect = $(this).closest('.item-row').find('.item-select');
        carregarItens($(this).val(), itemSelect);
    });

    // Adicionar novo item
    $('#addItem').click(function() {
        var newItem = $('.item-row:first').clone();
        newItem.find('select').val('');
        newItem.find('input').val('');
        newItem.find('.item-select').prop('disabled', true);
        $('#itemList').append(newItem);
    });

    // Remover item
    $(document).on('click', '.remove-item', function() {
        if ($('.item-row').length > 1) {
            $(this).closest('.item-row').remove();
        }
    });

    // Validar quantidade disponível
    $(document).on('change', '.item-select', function() {
        var option = $(this).find('option:selected');
        var quantidadeInput = $(this).closest('.item-row').find('.quantidade-input');
        if (option.length) {
            var matches = option.text().match(/Disponível: (\d+)/);
            if (matches) {
                var disponivel = parseInt(matches[1]);
                quantidadeInput.attr('max', disponivel);
            }
        }
    });
});
</script>
@endpush