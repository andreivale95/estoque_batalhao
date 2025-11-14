<!-- Left side column. contains the logo and sidebar -->
<aside class="main-sidebar">

    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">

        <!-- Sidebar user panel (optional) -->
        <div class="user-panel">
            <div class="pull-left image">
                <img src="{{ env('APP_URL') . '/privace/images/' . Auth::user()->image }}" class="img-circle"
                    alt="User Image">
            </div>
            <div class="pull-left info">
                <p>{{ Auth::user()->nome }}</p>
                <!-- Status -->
                <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
            </div>
        </div>

        <!-- search form (Optional) -->
        <form action="" method="get" class="sidebar-form">
            <div class="input-group">
                <input type="text" class="form-control" name="busca" value="{{ request()->busca }}"
                    placeholder="Pesquise">
                <span class="input-group-btn">
                    <button type="submit" name="search" id="search-btn" class="btn btn-flat"><i
                            class="fa fa-search"></i>
                    </button>
                </span>
            </div>
        </form>
        <!-- /.search form -->
        <!-- Sidebar Menu -->
        <ul class="sidebar-menu" data-widget="tree">
            <li class="header">HEADER</li>
            @can('modulo', '1')
                <li class="active">
                    <a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> <span>Dashboard</span></a>
                </li>
            @endcan
            @can('modulo', '2')
                <li class="treeview">
                    <a href="#"><i class="fa fa-cogs"></i> <span>Parâmetros</span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    <ul class="treeview-menu">
                        @can('modulo', '2')
                            <li><a href="{{ route('produtos.listar') }}">Listar Produtos</a></li>
                            <li><a href="{{ route('kits.listar') }}">Listar Kits</a></li>
                            <li><a href="{{ route('categorias.listar') }}">Listar Categorias</a></li>
                            <li><a href="{{ route('unidades.listar') }}">Listar Unidades</a></li>
                        @endcan
                    </ul>
                </li>
            @endcan
            @can('modulo', '3')
                <li class="treeview">
                    <a href="#"><i class="fa fa-shield"></i> <span>Segurança</span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    <ul class="treeview-menu">
                        @can('autorizacao', 3)
                            <li><a href="{{ route('pf.listar') }}">Perfis de Acesso</a></li>
                        @endcan
                        @can('autorizacao', 4)
                            <li><a href="{{ route('usi.listar') }}">Usuários</a></li>
                        @endcan

                    </ul>
                </li>
            @endcan
            @can('modulo', '4')
                <li class="treeview">
                    <a href="#"><i class="fa fa-clipboard"></i> <span>Registros</span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    <ul class="treeview-menu">
                        @can('autorizacao', 5)
                            <li> <a href="{{ route('estoque.listar') }}?nome=&categoria=&unidade={{ Auth::user()->fk_unidade }}"
                                    class="small-box-footer">
                                    Inventário
                                </a></li>
                        @endcan
                        @can('autorizacao', 5)
                            <li><a href="{{ route('efetivo_produtos.listar') }}">Listar Efetivo</a></li>
                        @endcan
                        @can('autorizacao', 5)
                            <li><a href="{{ route('saida_estoque.index') }}">Entregar Kit</a></li>
                        @endcan


                    </ul>
                </li>
            @endcan
            @can('modulo', '4')
                <li class="treeview">
                    <a href="#"><i class="fa fa-random"></i> <span>Movimentações</span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    <ul class="treeview-menu">
                        @can('autorizacao', 5)
                            <li><a href="{{ route('movimentacoes.index') }}">Movimentações</a></li>
                        @endcan
                    </ul>
                </li>

            @endcan
            <li class="">
                <a href="{{ route('profile.ver', Auth::user()->cpf) }}"><i class="fa fa-user-secret"></i> <span>Dados
                        Pessoais</span></a>

            </li>

            <li class="">
                <a href="{{ route('logout') }}"><i class="glyphicon glyphicon-log-out"></i> <span>Sair</span></a>
            </li>
            <li class="treeview">
                <a href="#"><i class="fa fa-building"></i> <span>Seções (Salas)</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">
                    <li><a href="{{ route('secoes.index', Auth::user()->fk_unidade) }}">Gerenciar Seções da Minha Unidade</a>
                    </li>
                </ul>
            </li>
            @can('modulo', '6')
                <li class="treeview">
                    <a href="#">
                        <i class="fa fa-archive"></i> <span>Cautelas</span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    <ul class="treeview-menu">
                        <li><a href="{{ route('cautelas.index') }}"><i class="fa fa-list"></i> Listar Cautelas</a></li>
                        <li><a href="{{ route('cautelas.create') }}"><i class="fa fa-plus"></i> Nova Cautela</a></li>
                        <li><a href="{{ route('cautelas.historico') }}"><i class="fa fa-history"></i> Histórico</a></li>
                    </ul>
                </li>
            @endcan
            @can('modulo', '5')
                <li class="treeview">
                    <a href="#">
                        <i class="fa fa-building"></i> <span>Seções</span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    <ul class="treeview-menu">
                        @foreach(App\Models\Secao::all() as $secao)
                            <li><a href="{{ route('secoes.show', $secao->id) }}">{{ $secao->nome }}</a></li>
                        @endforeach
                    </ul>
                </li>
            @endcan
        </ul>
        <!-- /.sidebar-menu -->
    </section>
    <!-- /.sidebar -->
</aside>
