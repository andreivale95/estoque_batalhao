@extends('layout.app')
@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>Seções da Unidade: {{ $unidade->nome }}</h1>
        <a href="{{ route('secoes.create', $unidade->id) }}" class="btn btn-success">Nova Seção</a>
    </section>
    <section class="content container-fluid">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <div class="box box-primary">
            <div class="box-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Nome da Seção</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($unidade->secoes as $secao)
                        <tr>
                            <td>{{ $secao->nome }}</td>
                            <td>
                                <a href="{{ route('secoes.edit', [$unidade->id, $secao->id]) }}" class="btn btn-primary btn-sm">Editar</a>
                                <form action="{{ route('secoes.destroy', [$unidade->id, $secao->id]) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Deseja realmente excluir esta seção?')">Excluir</button>
                                </form>
                                <a href="{{ route('secoes.ver', ['unidade' => $unidade->id, 'secao' => $secao->id]) }}" class="btn btn-secondary btn-sm">Ver Seção</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="2" class="text-center">Nenhuma seção cadastrada.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>
@endsection
