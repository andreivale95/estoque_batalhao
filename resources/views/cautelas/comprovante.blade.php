<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Comprovante de Cautela #{{ $cautela->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
        }
    </style>
</head>
<body>
    @include('cautelas.comprovante-content')
</body>
</html>
