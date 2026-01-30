# 🚀 Script de Setup Rápido - SISALMOX com Docker (Windows)
# Uso: powershell -ExecutionPolicy Bypass -File docker-setup.ps1

# Cores para output
function Write-Status {
    param([string]$Message)
    Write-Host "→ $Message" -ForegroundColor Blue
}

function Write-Success {
    param([string]$Message)
    Write-Host "✓ $Message" -ForegroundColor Green
}

function Write-Warning {
    param([string]$Message)
    Write-Host "⚠ $Message" -ForegroundColor Yellow
}

function Write-Error-Custom {
    param([string]$Message)
    Write-Host "✗ $Message" -ForegroundColor Red
}

Write-Host "================================" -ForegroundColor Cyan
Write-Host "🐳 SISALMOX - Docker Setup (Windows)" -ForegroundColor Cyan
Write-Host "================================" -ForegroundColor Cyan
Write-Host ""

# Verificar se Docker está instalado
Write-Status "Verificando instalação do Docker..."
try {
    $dockerVersion = docker --version
    Write-Success "Docker encontrado: $dockerVersion"
} catch {
    Write-Error-Custom "Docker não está instalado!"
    Write-Host "Baixe em: https://www.docker.com/products/docker-desktop" -ForegroundColor Yellow
    exit 1
}

# Verificar se Docker Compose está instalado
Write-Status "Verificando Docker Compose..."
try {
    $composeVersion = docker-compose --version
    Write-Success "Docker Compose encontrado: $composeVersion"
} catch {
    Write-Error-Custom "Docker Compose não está instalado!"
    exit 1
}

# Verificar se Docker Desktop está rodando
Write-Status "Verificando se Docker Desktop está rodando..."
try {
    docker ps > $null 2>&1
    Write-Success "Docker está rodando!"
} catch {
    Write-Error-Custom "Docker Desktop não está rodando!"
    Write-Host "Abra Docker Desktop e tente novamente" -ForegroundColor Yellow
    exit 1
}

# Copiar arquivo de ambiente
Write-Status "Preparando arquivo de ambiente..."
if (-not (Test-Path ".env")) {
    if (Test-Path ".env.docker") {
        Copy-Item ".env.docker" ".env"
        Write-Success ".env criado a partir de .env.docker"
    } else {
        Write-Error-Custom ".env.docker não encontrado!"
        exit 1
    }
} else {
    Write-Warning ".env já existe, pulando..."
}

# Gerar chave de aplicação
Write-Status "Gerando chave da aplicação..."
docker-compose run --rm app php artisan key:generate 2>$null
Write-Success "Chave gerada"

# Parar containers antigos
Write-Status "Parando containers antigos..."
docker-compose down --remove-orphans 2>$null

# Iniciar containers
Write-Status "Iniciando containers..."
Write-Host "Aguarde enquanto os containers estão sendo criados..." -ForegroundColor Yellow
docker-compose up -d

# Aguardar MySQL estar pronto
Write-Status "Aguardando banco de dados estar pronto..."
$attempt = 0
$maxAttempts = 30

while ($attempt -lt $maxAttempts) {
    try {
        $mysqlCheck = docker-compose exec -T mysql mysqladmin ping -h localhost 2>$null
        if ($mysqlCheck -like "*mysqld is alive*") {
            Write-Success "MySQL está pronto!"
            break
        }
    } catch {
        # Continue tentando
    }
    
    Write-Host "." -NoNewline
    Start-Sleep -Seconds 1
    $attempt++
}

Write-Host ""

# Verificar status
Write-Status "Verificando status dos containers..."
docker-compose ps

Write-Host ""

# Executar migrações
Write-Status "Executando migrações do banco de dados..."
docker-compose exec -T app php artisan migrate --force 2>$null

# Limpar cache
Write-Status "Limpando cache..."
docker-compose exec -T app php artisan cache:clear 2>$null
docker-compose exec -T app php artisan config:clear 2>$null
docker-compose exec -T app php artisan view:clear 2>$null

Write-Success "Cache limpo"

# Mostrar informações finais
Write-Host ""
Write-Host "================================" -ForegroundColor Green
Write-Host "✅ Setup Concluído!" -ForegroundColor Green
Write-Host "================================" -ForegroundColor Green
Write-Host ""

Write-Host "URLs de Acesso:" -ForegroundColor Green
Write-Host "  🌐 Aplicação:   http://localhost" -ForegroundColor Blue
Write-Host "  🛠️  PhpMyAdmin:  http://localhost:8080" -ForegroundColor Blue
Write-Host ""

Write-Host "Credenciais Banco de Dados:" -ForegroundColor Green
Write-Host "  Host:      mysql"
Write-Host "  Usuário:   sisalmox_user"
Write-Host "  Senha:     sisalmox_password"
Write-Host "  Banco:     sisalmox_db_local"
Write-Host ""

Write-Host "Comandos Úteis:" -ForegroundColor Green
Write-Host "  Parar:              docker-compose down" -ForegroundColor Blue
Write-Host "  Logs:               docker-compose logs -f" -ForegroundColor Blue
Write-Host "  Terminal PHP:       docker-compose exec app bash" -ForegroundColor Blue
Write-Host "  Artisan:            docker-compose exec app php artisan" -ForegroundColor Blue
Write-Host ""

Write-Status "Para mais informações, veja: DOCKER_SETUP.md"
Write-Host ""

# Perguntar se quer abrir a aplicação
$openApp = Read-Host "Deseja abrir http://localhost no navegador? (S/N)"
if ($openApp -eq "S" -or $openApp -eq "s") {
    Start-Process "http://localhost"
}
