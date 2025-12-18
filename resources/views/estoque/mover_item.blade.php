@extends('layout/app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-exchange"></i> Mover Item
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{ route('estoque.listar') }}">Estoque</a></li>
            <li class="active">Mover Item</li>
        </ol>
    </section>

    <section class="content container-fluid">
        @if ($errors->any())
            <div class="alert alert-danger">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <h4><i class="icon fa fa-ban"></i> Erro!</h4>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <h4><i class="icon fa fa-check"></i> Sucesso!</h4>
                {{ session('success') }}
            </div>
        @endif

        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-info-circle"></i> Informações do Item</h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Produto:</strong> {{ $item->produto->nome }}</p>
                        <p><strong>Quantidade:</strong> {{ $item->quantidade }} {{ $item->unidade }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Seção:</strong> {{ $item->secao->nome }}</p>
                        @if($item->itemPai)
                            <p><strong>Localização atual:</strong> {{ $item->itemPai->produto->nome }}</p>
                        @else
                            <p><strong>Localização atual:</strong> Seção (solto)</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-exchange"></i> Mover Para</h3>
            </div>
            <form action="{{ route('estoque.item.mover', $item->id) }}" method="POST">
                @csrf
                <div class="box-body">
                    <div class="form-group">
                        <label><strong>Destino:</strong></label>
                        <div style="margin-top: 10px;">
                            <div class="radio">
                                <label>
                                    <input type="radio" name="destino" id="destino_secao" value="secao" checked onchange="toggleContainer()">
                                    Seção (remover de container)
                                </label>
                            </div>
                            <div class="radio">
                                <label>
                                    <input type="radio" name="destino" id="destino_container" value="container" onchange="toggleContainer()">
                                    Outro Container
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group" id="container_section" style="display: none;">
                        <label for="fk_item_pai"><strong>Selecione o Container</strong></label>
                        <select name="fk_item_pai" id="fk_item_pai" class="form-control">
                            <option value="">-- Selecione um container --</option>
                            @forelse($containers as $container)
                                <option value="{{ $container['id'] }}">{{ $container['nome'] }}</option>
                            @empty
                                <option value="" disabled>Nenhum container disponível nesta seção</option>
                            @endforelse
                        </select>
                    </div>
                </div>

                <div class="box-footer">
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-check"></i> Mover Item
                    </button>
                    <a href="javascript:history.back()" class="btn btn-default">
                        <i class="fa fa-arrow-left"></i> Voltar
                    </a>
                </div>
            </form>
        </div>
    </section>
</div>

<script>
function toggleContainer() {
    const destino = document.querySelector('input[name="destino"]:checked').value;
    const containerSection = document.getElementById('container_section');
    const fk_item_pai = document.getElementById('fk_item_pai');
    
    if (destino === 'container') {
        containerSection.style.display = 'block';
        fk_item_pai.setAttribute('required', 'required');
    } else {
        containerSection.style.display = 'none';
        fk_item_pai.removeAttribute('required');
        fk_item_pai.value = '';
    }
}
</script>
@endsection
