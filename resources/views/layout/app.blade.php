<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>SISALMOX</title>
    <link rel="shortcut icon" href="{{ env('APP_URL') . '/assets/img/logo.png' }}" type="">
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!--<link rel="stylesheet" href="AdminLTE/bower_components/bootstrap/dist/css/bootstrap.min.css">-->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
        integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <!-- Font Awesome -->
    <!--<link rel="stylesheet" href="AdminLTE/bower_components/font-awesome/css/font-awesome.min.css">-->


    <link rel="stylesheet" href="{{ asset('fontawesome/css/all.min.css') }}" />







    <!-- Ionicons -->
    <!--<link rel="stylesheet" href="AdminLTE/bower_components/Ionicons/css/ionicons.min.css">-->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/7.1.2/collection/components/icon/icon.min.css"
        integrity="sha512-hauda6NeibaMNqxY3e7YDWWgP6cXKVAeaG/H26S5mvR0IFubczGFSNQCF9LEip/+Kt7Asuv1hYbrTGovJ+Qv9g=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Theme style -->
    <!--<link rel="stylesheet" href="AdminLTE/dist/css/AdminLTE.min.css">-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/2.4.4/css/AdminLTE.css"
        integrity="sha512-DFBKH7Bog2gSkSC3ZyTJIdm5caMi23AUpIMwIUtydEHU4LA4dr8LVc2CKHVqf0hj7lxXMvIPJN8Wxff8dvGRKA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!--<link rel="stylesheet" href="AdminLTE/dist/css/skins/_all-skins.min.css">-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/2.4.4/css/skins/_all-skins.min.css"
        integrity="sha512-D231SkmJ+61oWzyBS0Htmce/w1NLwUVtMSA05ceaprOG4ZAszxnScjexIQwdAr4bZ4NRNdSHH1qXwu1GwEVnvA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Google Font -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">




    <!-- jQuery 3 -->
    <!--<script src="AdminLTE/bower_components/jquery/dist/jquery.min.js"></script>-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js"
        integrity="sha512-bnIvzh6FU75ZKxp0GXLH9bewza/OIw6dLVh9ICg0gogclmYGguQJWl8U30WpbsGTqbIiAwxTsbe76DErLq5EDQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/3.3.11/jquery.inputmask.bundle.min.js"
        integrity="sha512-J1L8ODHEJNJVTzXzQ0upzmDuYY7J1Uk9okiVZYQIrNz/Hd+nXAAnzFiyf2WaOcBB7K8rRSc5a917I7gCYMQ/yg=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fastclick/0.6.0/fastclick.min.js"
        integrity="sha512-oljyd1wg75alHReTpDvNIQ4Yj1wZwGxxZhJhId3vr2dKY+26/r/wmMrImwDgin03+7wxyhX+adOQB/2BTvO5tQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>




</head>

<body class="hold-transition skin-purple sidebar-mini" data-host="{{ env('APP_URL') }}">
    <div class="wrapper">

        @include('layout/header')
        @include('layout/aside')
        @include('layout/alerts')

        <!-- Content Wrapper. Contains page content -->

        @yield('content')
        <!-- Main Footer -->


        <!-- Control Sidebar -->
        @include('layout/control-aside')
        <!-- /.control-sidebar -->

    </div>
    <!-- ./wrapper -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"
        integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous">
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/2.4.4/js/adminlte.min.js"
        integrity="sha512-lHukWUJ6D45LP6NdqvuyDJndgUxRQaRbEexN1BOXYcwwQDpxtferNb186wGHJ9S10anCkfU5qvWXZitlJxa2KA=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/2.4.4/js/demo.js"
        integrity="sha512-Tup3vXtETjvY2NhNjzVpgu1d1rj40+i/FIcLU14q0vcnr1WoOkeDvuOF++dwUgzcJR29W4DxT6k7J/S9bJ2nqA=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script type="text/javascript">
        $("input[id*='cnpj']").inputmask({
            mask: ['99.999.999/9999-99'],
            keepStatic: true
        });
        $("input[id*='cpf']").inputmask({
            mask: ['999.999.999-99'],
            keepStatic: true
        });
        $("input[id*='telefone']").inputmask({
            mask: ['(99) 99999-9999'],
            keepStatic: true
        });
        $("input[id*='cep']").inputmask({
            mask: ['99.999-999'],
            keepStatic: true
        });

        $(document).ready(function() {
            setTimeout(() => {
                $('#alert-box').show(1000);
            }, 500);
            setTimeout(() => {
                $('#alert-box').hide(1000);
            }, 5000);

        });
    </script>
</body>

</html>
