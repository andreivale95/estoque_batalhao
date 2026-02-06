@extends('layout.app')
@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>Detalhes da Movimentação</h1>
    </section>
    <section class="content container-fluid">
        <div class="box box-primary">
            <div class="box-body">
                <table class="table table-bordered table-striped">
                    <tr><th>Data</th><td>{{ \Carbon\Carbon::parse($movimentacao->data_movimentacao)->format('d/m/Y H:i:s') }}</td></tr>
                    <tr><th>Produto</th><td>{{ $movimentacao->produto->nome ?? '-' }}</td></tr>
                    <tr><th>Tipo</th><td>{{ $movimentacao->tipo_movimentacao }}</td></tr>
                    <tr><th>Fornecedor</th><td>{{ $movimentacao->fornecedor }}</td></tr>
                    <tr><th>Nota Fiscal</th><td>{{ $movimentacao->nota_fiscal }}</td></tr>
                    <tr><th>Quantidade</th><td>{{ $movimentacao->quantidade }}</td></tr>
                    <tr><th>Valor Unitário</th><td>{{ number_format($movimentacao->valor_unitario, 2, ',', '.') }}</td></tr>
                    <tr><th>Valor Total</th><td>{{ number_format($movimentacao->valor_total, 2, ',', '.') }}</td></tr>
                    <tr><th>Unidade</th><td>{{ $movimentacao->unidade->nome ?? '-' }}</td></tr>
                    <tr><th>Origem</th><td>{{ $movimentacao->origem->nome ?? '-' }}</td></tr>
                    <tr><th>Destino</th><td>{{ $movimentacao->destino->nome ?? '-' }}</td></tr>
                    <tr><th>Responsável</th><td>{{ $movimentacao->responsavel }}</td></tr>
                    <tr><th>Militar</th><td>{{ $movimentacao->militar }}</td></tr>
                    <tr><th>Setor</th><td>{{ $movimentacao->setor }}</td></tr>
                    <tr><th>Proc. SEI</th><td>{{ $movimentacao->sei }}</td></tr>
                    <tr><th>Data TRP</th><td>{{ $movimentacao->data_trp ? \Carbon\Carbon::parse($movimentacao->data_trp)->format('d/m/Y') : '-' }}</td></tr>
                    <tr><th>Fonte</th><td>{{ $movimentacao->fonte }}</td></tr>
                    <tr><th>Observação</th><td>{{ $movimentacao->observacao }}</td></tr>
                </table>

                <hr>
                <h4>Imagens do Item</h4>
                @php
                    $fotosProduto = $movimentacao->produto ? $movimentacao->produto->fotos->sortBy('ordem') : collect();
                @endphp
                @if($fotosProduto->count() > 0)
                    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                        @foreach($fotosProduto as $foto)
                            <a href="{{ $foto->url }}" target="_blank" title="Abrir imagem">
                                <img src="{{ $foto->url }}" alt="Foto do item" style="width: 120px; height: 120px; object-fit: cover; border: 1px solid #ddd; border-radius: 4px;">
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted">Nenhuma imagem cadastrada para este item.</p>
                @endif
                <a href="{{ route('movimentacoes.index') }}" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Voltar</a>
            </div>
        </div>
    </section>
</div>
@endsection
