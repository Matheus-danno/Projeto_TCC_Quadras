<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastre seu Estabelecimento - {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    @livewireStyles
    <style>
        body.tela-cadastro-dono {
            background-color: #FAFAFA;
        }
        .cadastro-dono-card {
            max-width: 980px;
            width: 100%;
            border: 1px solid var(--cor-principal);
            border-radius: 24px;
            padding: 2rem 2.25rem;
        }
        .cadastro-dono-titulo {
            color: var(--cor-principal);
            font-weight: 700;
            margin-bottom: 0.25rem;
        }
        .cadastro-dono-subtitulo {
            color: #989898;
            font-size: 0.9rem;
            padding-bottom: 1.25rem;
            margin-bottom: 1.25rem;
            border-bottom: 1px solid #EEEEEE;
        }
        .cadastro-label {
            font-size: 0.95rem;
            color: #333333;
            margin-bottom: 0.4rem;
            display: block;
        }
        .cadastro-input, .cadastro-select {
            border-radius: 50rem;
            border: 1px solid #DDE1E5;
            padding: 0.6rem 1.2rem;
            width: 100%;
            font-size: 0.95rem;
        }
        .cadastro-input:focus, .cadastro-select:focus {
            outline: none;
            border-color: var(--cor-principal);
            box-shadow: 0 0 0 0.15rem rgba(255, 125, 20, 0.15);
        }
        .cadastro-field {
            margin-bottom: 1.25rem;
        }
        .btn-cadastro-cancelar {
            border: 1px solid var(--cor-principal);
            color: var(--cor-principal);
            background: #fff;
            border-radius: 50rem;
            font-weight: 600;
            padding: 0.65rem;
        }
        .btn-cadastro-criar {
            background: var(--cor-principal);
            color: #fff;
            border: none;
            border-radius: 50rem;
            font-weight: 600;
            padding: 0.65rem;
        }
        .btn-cadastro-criar:hover { background: #e67e00; color: #fff; }
    </style>
</head>
<body class="tela-cadastro-dono">
    <div class="container d-flex justify-content-center align-items-center py-5" style="min-height: 100vh;">
        <livewire:auth.registrar-dono />
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @livewireScripts
</body>
</html>
