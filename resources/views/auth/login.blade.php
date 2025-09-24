<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>SISALMOX - Login</title>
    <link rel="shortcut icon" href="{{ env('APP_URL') . '/assets/img/logo.png' }}" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <!-- Custom CSSs -->
    <style>
        body {
            background-image: url("{{ env('APP_URL') . '/assets/img/logo.jpg' }}");
            background-size: 100% 100%;
            background-position: center;
            background-repeat: no-repeat;
            height: 100%;
            min-height: 100vh;
            width: 100%;
        }

        .login-container {
            background-color: rgba(100, 92, 92, 0.5);
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            max-width: 1000px;
            width: 100%;
            height: 500px;
            /* Ajusta a largura com base na tela */
            max-width: 480px;
            max-height: 480px;
            margin: 0 auto;
            margin-top: -3vh; /* Ajusta o posicionamento superiodr */
        }

        .login-logo a {
            color: #ffffff;
            font-size: 1.8rem;
            font-weight: bold;
            text-decoration: none;
        }

        .btn-primary {
            background-color: #007bff;
            border: none;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .text-center {
            color: #ffffff;
        }

        /* Responsividade para telas menores */
        @media (max-width: 768px) {
            .login-container {
                width: 80%; /* Ajusta a largura para 80% da tela */
                margin-top: 15vh; /* Ajusta o espaçamento superior */
            }
        }

        @media (max-width: 576px) {
            .login-container {
                width: 90%; /* Ajusta a largura para 90% da tela */
                margin-top: 20vh; /* Ajusta o espaçamento superior */
            }
        }
    </style>
</head>

<body>
    <div class="container-fluid d-flex justify-content-center align-items-center" style="height: 100vh;">
        <div class="login-container">
            <!-- Logo -->
            <div class="login-logo text-center mb-4">
                <img src="{{ env('APP_URL') . '/assets/img/LOGOS.png' }}" alt="Logo SIGEV" class="mb-3" style="max-width: 300px;"><br>
                <a href="#"><b>SISALMOX</b></a>
            </div>

            <!-- Message -->
            <p class="text-center mb-4">Entre para iniciar sua sessão</p>

            <!-- Login Form -->
            <form action="{{ route('login') }}" method="POST">
                @csrf
                <!-- CPF Input -->
                <div class="mb-3">
                    <label for="cpf" style="color:#ffffff" class="form-label">CPF</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input id="cpf" name="cpf" type="text" class="form-control" placeholder="Digite seu CPF" required>
                    </div>
                </div>

                <!-- Password Input -->
                <div class="mb-3">
                    <label for="password" style="color:#ffffff" class="form-label">Senha</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" name="password" id="password" class="form-control" placeholder="Digite sua senha" required autocomplete="new-password">
                    </div>
                </div>

                <!-- Login Button -->
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Entrar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Input Mask -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.8/jquery.inputmask.min.js"></script>
    <script>
        $(document).ready(function() {
            $("#cpf").inputmask("999.999.999-99");
        });
    </script>
</body>

</html>
