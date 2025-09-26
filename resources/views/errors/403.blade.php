@extends('layout.app')
@section('title', 'Acesso Negado')
@section('content')
<div class="container text-center mt-5">
    <div class="card shadow-lg p-4" style="max-width: 500px; margin: auto;">
        <h1 class="display-4 text-danger"><i class="fas fa-ban"></i> 403</h1>
        <h2 class="mb-3">Acesso não autorizado</h2>
        <p class="lead">Você não possui permissões para acessar esta página.<br>
        Entre em contato com o administrador do sistema se acredita que isso é um erro.</p>
        <hr>
        <a href="{{ route('logout') }}" class="btn btn-outline-primary"><i class="fas fa-sign-out-alt"></i> Trocar de usuário</a>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary ml-2"><i class="fas fa-home"></i> Ir para o início</a>
    </div>
</div>
@endsection
