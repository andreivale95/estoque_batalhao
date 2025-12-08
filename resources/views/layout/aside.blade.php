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
            <li class="header">MENU PRINCIPAL</li>
            
            @can('modulo', '1')
                <li>
                    <a href="{{ route('dashboard') }}">
                        <i class="fa fa-dashboard"></i> <span>Dashboard</span>
                    </a>
                </li>
            @endcan

            <!-- ESTOQUE E INVENTÁRIO -->
            @can('modulo', '4')
                <li class="treeview">
                    <a href="#">
                        <i class="fa fa-cubes"></i> <span>Estoque</span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    <ul class="treeview-menu">
                        @can('autorizacao', 5)
                            <li>
                                <a href="{{ route('estoque.listar') }}?nome=&categoria=&unidade={{ Auth::user()->fk_unidade }}">
                                    <i class="fa fa-list"></i> Inventário Geral
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('estoque.container.form') }}">
                                    <i class="fa fa-briefcase"></i> Cadastrar Container
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('movimentacoes.index') }}">
                                    <i class="fa fa-exchange"></i> Movimentações
                                </a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endcan

            <!-- CAUTELAS -->
            @can('modulo', '6')
                <li class="treeview">
                    <a href="#">
                        <i class="fa fa-handshake-o"></i> <span>Cautelas</span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    <ul class="treeview-menu">
                        <li>
                            <a href="{{ route('cautelas.create') }}">
                                <i class="fa fa-plus-circle"></i> Nova Cautela
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('cautelas.index') }}">
                                <i class="fa fa-list"></i> Listar Cautelas
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('cautelas.historico') }}">
                                <i class="fa fa-history"></i> Histórico
                            </a>
                        </li>
                    </ul>
                </li>
            @endcan

            <!-- SEÇÕES (SALAS) -->
            <li class="treeview">
                <a href="#">
                    <i class="fa fa-building"></i> <span>Seções (Salas)</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">
                    <li>
                        <a href="{{ route('secoes.index', Auth::user()->fk_unidade) }}">
                            <i class="fa fa-cog"></i> Gerenciar Minhas Seções
                        </a>
                    </li>
                    @can('modulo', '5')
                        <li class="header" style="padding-left: 15px; font-size: 11px;">VISUALIZAR SEÇÕES</li>
                        @foreach(App\Models\Secao::all() as $secao)
                            <li>
                                <a href="{{ route('secoes.show', $secao->id) }}">
                                    <i class="fa fa-folder-o"></i> {{ $secao->nome }}
                                </a>
                            </li>
                        @endforeach
                    @endcan
                </ul>
            </li>

            <!-- EFETIVO -->
            @can('modulo', '4')
                @can('autorizacao', 5)
                    <li class="treeview">
                        <a href="#">
                            <i class="fa fa-users"></i> <span>Efetivo</span>
                            <span class="pull-right-container">
                                <i class="fa fa-angle-left pull-right"></i>
                            </span>
                        </a>
                        <ul class="treeview-menu">
                            <li>
                                <a href="{{ route('efetivo_produtos.listar') }}">
                                    <i class="fa fa-list"></i> Listar Efetivo
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('saida_estoque.index') }}">
                                    <i class="fa fa-gift"></i> Entregar Kit
                                </a>
                            </li>
                        </ul>
                    </li>
                @endcan
            @endcan

            <li class="header">CONFIGURAÇÕES</li>

            <!-- CADASTROS -->
            @can('modulo', '2')
                <li class="treeview">
                    <a href="#">
                        <i class="fa fa-database"></i> <span>Cadastros</span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    <ul class="treeview-menu">
                        <li>
                            <a href="{{ route('produtos.listar', ['from' => 'parametros']) }}">
                                <i class="fa fa-cube"></i> Produtos
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('kits.listar') }}">
                                <i class="fa fa-briefcase"></i> Kits
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('categorias.listar') }}">
                                <i class="fa fa-tags"></i> Categorias
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('unidades.listar') }}">
                                <i class="fa fa-building-o"></i> Unidades
                            </a>
                        </li>
                    </ul>
                </li>
            @endcan

            <!-- SEGURANÇA -->
            @can('modulo', '3')
                <li class="treeview">
                    <a href="#">
                        <i class="fa fa-shield"></i> <span>Segurança</span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    <ul class="treeview-menu">
                        @can('autorizacao', 3)
                            <li>
                                <a href="{{ route('pf.listar') }}">
                                    <i class="fa fa-key"></i> Perfis de Acesso
                                </a>
                            </li>
                        @endcan
                        @can('autorizacao', 4)
                            <li>
                                <a href="{{ route('usi.listar') }}">
                                    <i class="fa fa-user"></i> Usuários
                                </a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endcan

            <li class="header">CONTA</li>

            <li>
                <a href="{{ route('profile.ver', Auth::user()->cpf) }}">
                    <i class="fa fa-user-circle"></i> <span>Meu Perfil</span>
                </a>
            </li>

            <li>
                <a href="{{ route('logout') }}">
                    <i class="fa fa-sign-out"></i> <span>Sair</span>
                </a>
            </li>
        </ul>
        <!-- /.sidebar-menu -->
    </section>
    <!-- /.sidebar -->
</aside>
