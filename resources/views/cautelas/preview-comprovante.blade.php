<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: white;
            color: #333;
        }

        .comprovante-preview {
            padding: 20px;
        }

        .comprovante-preview .container {
            max-width: 800px;
            margin: 0 auto;
        }

        .comprovante-preview .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #3c8dbc;
            padding-bottom: 15px;
        }

        .comprovante-preview .header h1 {
            margin: 0;
            color: #3c8dbc;
            font-size: 28px;
        }

        .comprovante-preview .header p {
            margin: 5px 0;
            color: #666;
        }

        .comprovante-preview .info-section {
            margin: 20px 0;
            display: flex;
            justify-content: space-between;
        }

        .comprovante-preview .info-column {
            flex: 1;
            margin-right: 20px;
        }

        .comprovante-preview .info-column:last-child {
            margin-right: 0;
        }

        .comprovante-preview .info-label {
            font-weight: bold;
            color: #3c8dbc;
            font-size: 12px;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .comprovante-preview .info-value {
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
            margin-bottom: 15px;
            font-size: 14px;
        }

        .comprovante-preview .table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
        }

        .comprovante-preview .table thead {
            background-color: #3c8dbc;
            color: white;
        }

        .comprovante-preview .table th,
        .comprovante-preview .table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        .comprovante-preview .table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .comprovante-preview .table td {
            font-size: 13px;
        }

        .comprovante-preview .resumo {
            margin: 30px 0;
            padding: 15px;
            background-color: #f0f0f0;
            border-left: 4px solid #3c8dbc;
        }

        .comprovante-preview .resumo-item {
            display: flex;
            justify-content: space-between;
            margin: 8px 0;
            font-size: 14px;
        }

        .comprovante-preview .resumo-item.total {
            font-weight: bold;
            border-top: 2px solid #3c8dbc;
            padding-top: 10px;
            margin-top: 10px;
            color: #3c8dbc;
        }

        .comprovante-preview .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 12px;
            color: #666;
        }

        .comprovante-preview .assinatura {
            margin-top: 50px;
            display: flex;
            justify-content: space-around;
        }

        .comprovante-preview .assinatura-item {
            text-align: center;
        }

        .comprovante-preview .assinatura-linha {
            border-top: 1px solid #333;
            width: 150px;
            margin-top: 30px;
        }

        .comprovante-preview .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }

        .comprovante-preview .status-pendente {
            background-color: #fff3cd;
            color: #856404;
        }

        .comprovante-preview .status-devolvido {
            background-color: #d4edda;
            color: #155724;
        }

        h3 {
            color: #3c8dbc;
            border-bottom: 2px solid #3c8dbc;
            padding-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="comprovante-preview">
        @include('cautelas.comprovante-content')
    </div>
</body>
</html>
