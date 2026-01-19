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

        .comprovante-preview h3 {
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
