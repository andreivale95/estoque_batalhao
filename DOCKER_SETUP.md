# 🐳 GUIA COMPLETO - SISALMOX COM DOCKER DESKTOP

## 📋 Pré-requisitos

### 1. **Windows (com WSL2)**
- ✅ Windows 10 Pro/Enterprise ou Windows 11
- ✅ Docker Desktop instalado e rodando
- ✅ WSL2 habilitado
- ✅ Git instalado

### 2. **Verificar Instalação do Docker**

```bash
docker --version
docker-compose --version
```

Deve retornar versões (ex: Docker 24.0.0, Docker Compose 2.20.0)

---

## 🚀 SETUP RÁPIDO (5 minutos)

### **Passo 1: Clone do Repositório**

Se ainda não tiver clonado:
```bash
git clone <seu_repositorio> sisalmox
cd sisalmox
```

### **Passo 2: Preparar Variáveis de Ambiente**

```bash
# Copiar arquivo de configuração
cp .env.docker .env

# Gerar chave da aplicação
docker-compose run --rm app php artisan key:generate
```

### **Passo 3: Iniciar Containers**

```bash
# Subir todos os serviços
docker-compose up -d

# Aguardar 30 segundos para MySQL inicializar
# Verificar status
docker-compose ps
```

**Saída esperada:**
```
NAME                 STATUS              PORTS
sisalmox_app         Up 2 minutes        0.0.0.0:80->80/tcp
sisalmox_mysql       Up 2 minutes        0.0.0.0:3306->3306/tcp
sisalmox_phpmyadmin  Up 2 minutes        0.0.0.0:8080->80/tcp
sisalmox_redis       Up 2 minutes        0.0.0.0:6379->6379/tcp
```

### **Passo 4: Configurar Banco de Dados**

```bash
# Entrar no container PHP
docker-compose exec app bash

# Dentro do container:

# Executar migrações
php artisan migrate

# Popular banco com dados iniciais (seeders)
php artisan db:seed

# Limpar cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Sair do container
exit
```

### **Passo 5: Acessar Aplicação**

Abra no navegador:
- 🌐 **Aplicação**: http://localhost
- 🛠️ **PhpMyAdmin**: http://localhost:8080

---

## 🔑 CREDENCIAIS PADRÃO

### **Banco de Dados MySQL**
```
Host: localhost
Porta: 3306
Usuário: sisalmox_user
Senha: sisalmox_password
Banco: sisalmox_db_local
```

### **PhpMyAdmin**
```
Usuário: sisalmox_user
Senha: sisalmox_password
```

### **Laravel (se houver seeder)**
Verifique em `database/seeders/` os dados padrão

---

## 📦 ESTRUTURA DOS SERVIÇOS

### **1. Aplicação (APP)**
- **Imagem**: `php:8.3-apache`
- **Container**: `sisalmox_app`
- **Porta**: `80`
- **Volume**: Projeto inteiro mapeado em `/app`

### **2. Banco de Dados (MYSQL)**
- **Imagem**: `mysql:8.0`
- **Container**: `sisalmox_mysql`
- **Porta**: `3306`
- **Volume Persistente**: `mysql_data`
- **Healthcheck**: Verifica conexão a cada 20 segundos

### **3. PHPMyAdmin**
- **Imagem**: `phpmyadmin:latest`
- **Container**: `sisalmox_phpmyadmin`
- **Porta**: `8080`
- **Uso**: Gerenciar banco de dados via UI

### **4. Redis (Cache)**
- **Imagem**: `redis:7-alpine`
- **Container**: `sisalmox_redis`
- **Porta**: `6379`
- **Volume Persistente**: `redis_data`

---

## 🛠️ COMANDOS ÚTEIS

### **Iniciar/Parar Containers**

```bash
# Iniciar todos os serviços
docker-compose up -d

# Parar todos os serviços
docker-compose down

# Parar e remover volumes (CUIDADO!)
docker-compose down -v

# Reiniciar serviços
docker-compose restart

# Reiniciar um serviço específico
docker-compose restart app
```

### **Visualizar Logs**

```bash
# Logs de todos os serviços
docker-compose logs

# Logs em tempo real
docker-compose logs -f

# Logs de um serviço específico
docker-compose logs -f app

# Logs do MySQL
docker-compose logs -f mysql
```

### **Acessar Containers**

```bash
# Terminal do container PHP
docker-compose exec app bash

# Terminal do MySQL
docker-compose exec mysql bash

# Dentro do MySQL:
mysql -u sisalmox_user -p sisalmox_db_local
```

### **Executar Comandos Artisan**

```bash
# Sem entrar no container
docker-compose exec app php artisan migrate
docker-compose exec app php artisan db:seed
docker-compose exec app php artisan cache:clear

# Com entrada do container
docker-compose exec app bash
php artisan <comando>
exit
```

---

## 🔧 CONFIGURAÇÃO AVANÇADA

### **Personalizar Variáveis de Ambiente**

Editar arquivo `.env` na raiz do projeto:

```env
# Porta da aplicação
APP_PORT=80

# Banco de dados
DB_HOST=mysql
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha

# PhpMyAdmin
PHPMYADMIN_PORT=8080

# MySQL
MYSQL_PORT=3306
MYSQL_ROOT_PASSWORD=sua_senha_root

# Redis
REDIS_PORT=6379
```

### **Aumentar Limite de Upload**

Editar `Dockerfile`:

```dockerfile
# Adicionar após as extensões PHP:
ENV PHP_UPLOAD_MAX_FILESIZE=300M
ENV PHP_POST_MAX_SIZE=300M

RUN echo "upload_max_filesize = 300M" >> /usr/local/etc/php/conf.d/uploads.ini
RUN echo "post_max_size = 300M" >> /usr/local/etc/php/conf.d/uploads.ini
```

Depois reconstruir:
```bash
docker-compose up -d --build
```

### **Mapear Portas Diferentes**

No `.env`:
```env
APP_PORT=8000          # http://localhost:8000
MYSQL_PORT=3307        # localhost:3307
PHPMYADMIN_PORT=8081   # http://localhost:8081
REDIS_PORT=6380        # localhost:6380
```

### **Usar PostgreSQL em vez de MySQL**

Criar arquivo `docker-compose.postgres.yml`:

```yaml
version: '3.8'
services:
  db:
    image: postgres:15-alpine
    environment:
      POSTGRES_DB: sisalmox_db
      POSTGRES_USER: sisalmox_user
      POSTGRES_PASSWORD: sisalmox_password
    ports:
      - "5432:5432"
    volumes:
      - postgres_data:/var/lib/postgresql/data
  
  adminer:
    image: adminer
    ports:
      - "8080:8080"

volumes:
  postgres_data:
```

Execute com:
```bash
docker-compose -f docker-compose.postgres.yml up -d
```

---

## 🔄 FLUXO DE DESENVOLVIMENTO

### **1. Fazer Alterações no Código**

```bash
# Seus arquivos estão sincronizados em tempo real
# via volume mounting
```

### **2. Instalar Novas Dependências PHP**

```bash
docker-compose exec app composer require package/name
```

### **3. Instalar Novas Dependências npm**

```bash
docker-compose exec app npm install package-name
docker-compose exec app npm run build
```

### **4. Executar Migrações**

```bash
docker-compose exec app php artisan migrate
```

### **5. Criar Novos Modelos/Controllers**

```bash
docker-compose exec app php artisan make:model NomeModelo -mcr
```

---

## 🐛 SOLUÇÃO DE PROBLEMAS

### **Problema 1: "Connection refused" no MySQL**

**Solução:**
```bash
# Verificar se MySQL está rodando
docker-compose ps

# Se não estiver, reiniciar
docker-compose restart mysql

# Aguardar 30 segundos e tentar novamente
docker-compose exec app php artisan tinker
# Dentro do tinker: exit
```

### **Problema 2: "Port 80 already in use"**

**Solução:**
```bash
# Mudar porta no .env
APP_PORT=8000

# Reconstruir
docker-compose down
docker-compose up -d
```

### **Problema 3: "Docker daemon not running"**

**Solução:**
- Abrir Docker Desktop
- Verificar se WSL2 está habilitado
- Reiniciar computador

### **Problema 4: "Permission denied" nos arquivos**

**Solução:**
```bash
# Dar permissão correta
docker-compose exec app chown -R www-data:www-data /app/storage /app/bootstrap/cache
```

### **Problema 5: Mudanças no código não aparecem**

**Solução:**
```bash
# Limpar cache Laravel
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan view:clear

# Verificar volume mounting
docker inspect sisalmox_app | grep -A 20 "Mounts"
```

### **Problema 6: Banco de dados vazio após reiniciar**

**Solução:**
```bash
# Verificar se volume persiste
docker volume ls | grep mysql

# Se não estiver persistindo, rodar migrações/seeds
docker-compose exec app php artisan migrate
docker-compose exec app php artisan db:seed
```

---

## 📊 MONITORAMENTO

### **Verificar Recursos Usados**

```bash
# Ver uso de CPU/Memória
docker stats

# Clicar Ctrl+C para sair
```

### **Ver Detalhes do Container**

```bash
docker inspect sisalmox_app
docker inspect sisalmox_mysql
```

### **Verificar Erros de Inicialização**

```bash
docker-compose logs app --tail 50
docker-compose logs mysql --tail 50
```

---

## 🔐 SEGURANÇA

### **Mudanças Obrigatórias antes de PRODUÇÃO**

1. **Gerar nova chave APP_KEY**
   ```bash
   docker-compose exec app php artisan key:generate
   ```

2. **Mudar senhas padrão no `.env`**
   ```env
   DB_PASSWORD=SenhaForte123!@#
   MYSQL_ROOT_PASSWORD=SenhaRoot456!@#
   ```

3. **Desabilitar APP_DEBUG em produção**
   ```env
   APP_DEBUG=false
   APP_ENV=production
   ```

4. **Usar HTTPS**
   - Adicionar certificado SSL
   - Redirecionar HTTP para HTTPS

5. **Variáveis sensíveis**
   - Nunca commitar `.env` 
   - Usar `.env.example` para documentar

---

## 📈 PERFORMANCE

### **Otimizações Recomendadas**

```bash
# 1. Cache de configuração
docker-compose exec app php artisan config:cache

# 2. Cache de rotas
docker-compose exec app php artisan route:cache

# 3. Cache de views
docker-compose exec app php artisan view:cache

# 4. Otimizar autoloader
docker-compose exec app composer dump-autoload --optimize
```

### **Aumentar Recursos do Docker**

1. Abrir Docker Desktop Settings
2. Ir em "Resources"
3. Aumentar:
   - CPUs: 4+
   - Memory: 8GB+
   - Swap: 2GB

---

## 🚀 DEPLOY PARA PRODUÇÃO

### **Opção 1: Railway.app**

```bash
# Autenticação
railway login

# Ligar projeto
railway link

# Deploy
railway up
```

### **Opção 2: Render.com**

```bash
# Criar serviço web
# URL: seu_repositorio.git
# Build command: composer install && npm run build
# Start command: php artisan migrate && apache2-foreground
```

### **Opção 3: Servidor Próprio**

1. Instalar Docker no servidor
2. Clonar repositório
3. Configurar variáveis de produção
4. Usar Nginx com reverse proxy
5. Ativar HTTPS com Let's Encrypt

---

## 📝 CHECKLIST DE DEPLOY

- [ ] Variáveis de ambiente configuradas
- [ ] Banco de dados migrado
- [ ] APP_KEY gerado
- [ ] Permissões de arquivo corretas
- [ ] Cache limpo
- [ ] HTTPS ativo
- [ ] Backups configurados
- [ ] Logs centralizados
- [ ] Monitoramento ativo

---

## 🔗 REFERÊNCIAS

- [Docker Documentation](https://docs.docker.com/)
- [Docker Compose Documentation](https://docs.docker.com/compose/)
- [Laravel Docker Setup](https://laravel.com/docs/deployment#docker)
- [MySQL Docker Image](https://hub.docker.com/_/mysql)
- [PHP Docker Image](https://hub.docker.com/_/php)

---

## 💡 DICAS EXTRAS

### **Backup do Banco de Dados**

```bash
# Fazer backup
docker-compose exec mysql mysqldump -u sisalmox_user -p sisalmox_db_local > backup.sql

# Restaurar backup
docker-compose exec -T mysql mysql -u sisalmox_user -p sisalmox_db_local < backup.sql
```

### **Limpar Espaço em Disco**

```bash
# Remover imagens não usadas
docker image prune -a

# Remover volumes não usados
docker volume prune

# Remover containers parados
docker container prune
```

### **Atualizar Imagens**

```bash
# Puxar versões mais recentes
docker-compose pull

# Reconstruir
docker-compose up -d --build
```

---

**Última Atualização**: 2026
**Versão**: 1.0
**Status**: ✅ Pronto para Produção

