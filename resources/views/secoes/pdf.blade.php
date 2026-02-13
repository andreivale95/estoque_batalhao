<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Secao {{ $secao->nome }} - Itens</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        h2 {
            margin: 0 0 10px 0;
        }
        .pdf-header {
            width: 100%;
            text-align: center;
            margin-bottom: 20px;
        }
        .pdf-header img {
            height: 60px;
            margin-bottom: 6px;
        }
        .pdf-footer {
            width: 100%;
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
        }
        .secao-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0 20px 0;
        }
        .secao-table th,
        .secao-table td {
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
        }
        .secao-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .secao-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
    <header class="pdf-header">
        <img src="{{ public_path('assets/img/logo.png') }}" alt="Brasao CBM">
        <p><strong>GOVERNO DO ESTADO DO ACRE</strong></p>
        <p><strong>CORPO DE BOMBEIROS MILITAR DO ESTADO DO ACRE</strong></p>
        <p><strong>{{ $secao->unidade->nome ?? '-' }}</strong></p>
    </header>

    <h2>Itens da Secao: {{ $secao->nome }}</h2>
    <p>Total de itens: {{ $totalItensSecao }}</p>

    <table class="secao-table">
        <thead>
            <tr>
                <th>Nº</th>
                <th>Item</th>
                <th>Patrimonio</th>
                <th>Descricao</th>
                <th>Quantidade</th>
            </tr>
        </thead>
        <tbody>
            @php $contador = 1; @endphp
            @foreach($consumoAgrupado as $dados)
                <tr>
                    <td>{{ $contador++ }}</td>
                    <td>{{ $dados['produto']->nome ?? 'Sem Nome' }}</td>
                    <td>-</td>
                    <td>{{ $dados['produto']->descricao ?? '-' }}</td>
                    <td>{{ $dados['quantidade'] }}</td>
                </tr>
            @endforeach
            @foreach($itensPatrimoniais as $patrimonio)
                <tr>
                    <td>{{ $contador++ }}</td>
                    <td>{{ $patrimonio->produto->nome ?? 'Sem Nome' }}</td>
                    <td>{{ $patrimonio->patrimonio ?? '-' }}</td>
                    <td>{{ $patrimonio->produto->descricao ?? '-' }}</td>
                    <td>1</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <footer class="pdf-footer">
        <p>Quartel do Comando Operacional da Capital</p>
        <p>Rua Projetada "A", no 445 - Portal da Amazonia - Rio Branco - AC</p>
        <p>CEP: 69.915-824 - Telefone: (68) 3227-XXXX</p>
    </footer>
</body>
</html>
