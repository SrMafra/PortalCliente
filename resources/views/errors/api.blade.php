<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ config('app.name', 'Portal do Cliente') }} - Erro de Conexão</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    
    <style>
        html, body {
            height: 100%;
        }
        
        body {
            display: flex;
            align-items: center;
            padding-top: 40px;
            padding-bottom: 40px;
            background-color: #f5f5f5;
        }
        
        .error-container {
            width: 100%;
            max-width: 600px;
            padding: 15px;
            margin: auto;
            text-align: center;
        }
        
        .error-icon {
            font-size: 4rem;
            color: #dc3545;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="card shadow">
            <div class="card-body p-5">
                <i class="fas fa-exclamation-triangle error-icon"></i>
                
                <h1 class="h3 mb-3">Erro de Conexão</h1>
                
                <div class="alert alert-danger">
                    <p class="mb-0">{{ $exception->getMessage() }}</p>
                </div>
                
                <p class="text-muted mb-4">
                    Não foi possível estabelecer conexão com o sistema ERP. Por favor, tente novamente mais tarde ou entre em contato com o suporte técnico.
                </p>
                
                <div class="d-grid gap-2">
                    <a href="{{ route('login') }}" class="btn btn-primary">
                        <i class="fas fa-arrow-left me-2"></i> Voltar para a página de login
                    </a>
                    
                    <button class="btn btn-outline-secondary" onclick="window.location.reload()">
                        <i class="fas fa-sync-alt me-2"></i> Tentar novamente
                    </button>
                </div>
                
                <p class="mt-4">
                    Se o problema persistir, entre em contato com o suporte técnico:
                    <br>
                    <a href="mailto:suporte@example.com">suporte@example.com</a> | 
                    <a href="tel:+551199999999">(11) 9999-9999</a>
                </p>
            </div>
        </div>
        
        <p class="mt-5 mb-3 text-muted">&copy; {{ date('Y') }} {{ config('app.name', 'Portal do Cliente') }}</p>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>