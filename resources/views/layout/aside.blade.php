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
                    <a href="#"><i class="fa fa-expeditedssl"></i> <span>Parâmetros</span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    <ul class="treeview-menu">
                        @can('autorizacao', 3)
                            <li><a href="{{ route('produtos.listar') }}">Listar Produtos</a></li>
                        @endcan


                        @can('autorizacao', 3)
                            <li><a href="{{ route('kits.listar') }}">Listar Kits</a></li>
                        @endcan
                        @can('autorizacao', 3)
                            <li><a href="{{ route('unidades.listar') }}">Listar Unidades</a></li>
                        @endcan

                    </ul>
                </li>
            @endcan


            @can('modulo', '3')
                <li class="treeview">
                    <a href="#"><i class="fa fa-expeditedssl"></i> <span>Segurança</span>
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
                    <a href="#"><i class="fa fa-cab"></i> <span>Registros</span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    <ul class="treeview-menu">
                        @can('autorizacao', 5)
                            <li><a href="{{ route('estoque.listar') }}">Estoque</a></li>
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




            @can('modulo', '5')
                <li class="treeview">
                    <a href="#"><i class="fa fa-bar-chart"></i> <span>Relatórios</span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    <ul class="treeview-menu">

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

        </ul>
        <!-- /.sidebar-menu -->
    </section>
    <!-- /.sidebar -->
</aside>
