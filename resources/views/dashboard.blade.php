@extends('layout/app')
@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->

        <section class="content-header">
            <h1>

                {{ Auth::user()->unidade()->first()->nome }}

            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            </ol>

        </section>
        <!-- Main content -->
        <section class="content container-fluid">
            <div class="row">
                <div class="col-lg-3 col-xs-6">
                    <!-- small box -->
                    <div class="small-box bg-blue">
                        <div class="inner">
                            <h3>{{ $tudo }}</h3>
                            @if (Auth::user()->fk_unidade == 14)
                                <p>Estoque CBMAC</p>
                            @else
                                <p>Estoque da Unidade</p>
                            @endif
                        </div>
                        <div class="icon">
                            <i class="ion ion-bag"></i>
                        </div>
                        <a href="{{ route('estoque.listar', ['nome' => '', 'categoria' => '', 'unidade' => 14]) }}" class="small-box-footer">
                            Mais Informações <i class="fa fa-arrow-circle-right"></i>
                        </a>

                    </div>
                </div>
                
                <div class="col-lg-3 col-xs-6">
                    <!-- small box -->
                    <div class="small-box bg-red">
                        <div class="inner">
                            <h3>{{ $cautelasPendentes }}</h3>
                            <p>Cautelas Pendentes</p>
                            <p style="margin-top: 6px; font-size: 12px;">
                                Vencidas: <strong>{{ $cautelasVencidas }}</strong>
                            </p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-clipboard"></i>
                        </div>
                        <a href="{{ route('cautelas.index') }}" class="small-box-footer">
                            Mais Informações <i class="fa fa-arrow-circle-right"></i>
                        </a>

                    </div>
                </div>

        </section>
    </div>
    <!-- /.content-wrapper -->
@endsection
