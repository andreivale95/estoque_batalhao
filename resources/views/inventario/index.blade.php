@extends('layout.app')
@section('content')
<div class="container" style="background: #fff; padding: 24px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
    <h1>Inventário</h1>
    <a href="{{ route('inventario.cadastrar') }}" class="btn btn-success mb-3">Cadastrar Novo Tipo de Item</a>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Item</th>
                <th>Categoria</th>
                <th>Quantidade</th>
                <th>Unidade</th>
            </tr>
        </thead>
        <tbody>
            {{-- Aqui será exibida a lista de itens do inventário --}}
            @foreach($itens as $item)
                <tr>
                    <td>{{ $item->nome }}</td>
                    <td>{{ $item->categoria->nome ?? '-' }}</td>
                    <td>{{ $item->quantidade }}</td>
                    <td>{{ $item->unidade->nome ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
