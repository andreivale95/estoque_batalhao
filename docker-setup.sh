#!/bin/bash
# 🚀 Script de Setup Rápido - SISALMOX com Docker
# Uso: bash docker-setup.sh

set -e

echo "================================"
echo "🐳 SISALMOX - Docker Setup"
echo "================================"
echo ""

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Função para print com cor
print_status() {
    echo -e "${BLUE}→${NC} $1"
}

print_success() {
    echo -e "${GREEN}✓${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

print_error() {
    echo -e "${RED}✗${NC} $1"
}

# Verificar se Docker está instalado
print_status "Verificando instalação do Docker..."
if ! command -v docker &> /dev/null; then
    print_error "Docker não está instalado!"
    echo "Baixe em: https://www.docker.com/products/docker-desktop"
    exit 1
fi
print_success "Docker encontrado: $(docker --version)"

# Verificar se Docker Compose está instalado
print_status "Verificando Docker Compose..."
if ! command -v docker-compose &> /dev/null; then
    print_error "Docker Compose não está instalado!"
    exit 1
fi
print_success "Docker Compose encontrado: $(docker-compose --version)"

# Copiar arquivo de ambiente
print_status "Preparando arquivo de ambiente..."
if [ ! -f .env ]; then
    if [ -f .env.docker ]; then
        cp .env.docker .env
        print_success ".env criado a partir de .env.docker"
    else
        print_error ".env.docker não encontrado!"
        exit 1
    fi
else
    print_warning ".env já existe, pulando..."
fi

# Gerar chave de aplicação
print_status "Gerando chave da aplicação..."
docker-compose run --rm app php artisan key:generate 2>/dev/null || true
print_success "Chave gerada"

# Parar containers antigos
print_status "Parando containers antigos..."
docker-compose down --remove-orphans 2>/dev/null || true

# Iniciar containers
print_status "Iniciando containers..."
docker-compose up -d

# Aguardar MySQL estar pronto
print_status "Aguardando banco de dados estar pronto..."
for i in {1..30}; do
    if docker-compose exec -T mysql mysqladmin ping -h localhost 2>/dev/null | grep -q "mysqld is alive"; then
        print_success "MySQL está pronto!"
        break
    fi
    echo -n "."
    sleep 1
done

echo ""

# Verificar status
print_status "Verificando status dos containers..."
docker-compose ps

echo ""

# Executar migrações
print_status "Executando migrações do banco de dados..."
docker-compose exec -T app php artisan migrate --force 2>/dev/null

# Limpar cache
print_status "Limpando cache..."
docker-compose exec -T app php artisan cache:clear 2>/dev/null
docker-compose exec -T app php artisan config:clear 2>/dev/null
docker-compose exec -T app php artisan view:clear 2>/dev/null

print_success "Cache limpo"

# Mostrar informações finais
echo ""
echo "================================"
echo "✅ Setup Concluído!"
echo "================================"
echo ""
echo -e "${GREEN}URLs de Acesso:${NC}"
echo "  🌐 Aplicação:   ${BLUE}http://localhost${NC}"
echo "  🛠️  PhpMyAdmin:  ${BLUE}http://localhost:8080${NC}"
echo ""
echo -e "${GREEN}Credenciais Banco de Dados:${NC}"
echo "  Host:      mysql"
echo "  Usuário:   sisalmox_user"
echo "  Senha:     sisalmox_password"
echo "  Banco:     sisalmox_db_local"
echo ""
echo -e "${GREEN}Comandos Úteis:${NC}"
echo "  Parar:              ${BLUE}docker-compose down${NC}"
echo "  Logs:               ${BLUE}docker-compose logs -f${NC}"
echo "  Terminal PHP:       ${BLUE}docker-compose exec app bash${NC}"
echo "  Artisan:            ${BLUE}docker-compose exec app php artisan${NC}"
echo ""
print_status "Para mais informações, veja: DOCKER_SETUP.md"
echo ""
