@extends('layout/app')
@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>Recibo</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{ route('estoque.listar') }}"><i class=""></i> Estoque</a></li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content container-fluid">
        <div class="container" style="max-width: 700px; margin-top: 10px;  background: #fff; padding: 30px;">
            <h2 class="text-center">Recibo de Entrega de Itens</h2>
            <p><strong>Militar:</strong> {{ $militar }}</p>
            <p><strong>Data:</strong> {{ \Carbon\Carbon::parse($data)->format('d/m/Y') }}</p>
            <hr>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Quantidade</th>
                        <th>Unidade</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($itens as $item)
                    <tr>
                        <td>{{ $item->produto->nome ?? $item->fk_produto }}</td>
                        <td>{{ $item->quantidade }}</td>
                        <td>{{ $item->produto->unidade ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <br><br>
            <div style="text-align:center;">
                ___________________________________________<br>
                Assinatura do Recebedor
            </div>
            <br>
            <div class="text-center no-print">
                <button class="btn btn-primary" onclick="window.print()">Imprimir Recibo</button>
                <a href="{{ route('estoque.listar') }}" class="btn btn-secondary">Voltar</a>
            </div>
        </div>
    </section>
</div>
<style>
    @media print {
        .no-print {
            display: none !important;
        }
    }
</style>
@endsection