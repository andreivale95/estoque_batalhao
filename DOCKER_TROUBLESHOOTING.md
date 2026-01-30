# 🐛 Docker Troubleshooting Rápido

## ❌ Erros Comuns e Soluções

### 1. **"Connection refused" - MySQL**

**Erro:**
```
SQLSTATE[HY000] [2002] Connection refused
```

**Solução:**
```bash
# Verificar status
docker-compose ps

# Se MySQL não está UP, aguardar:
sleep 30

# Tentar novamente
docker-compose restart mysql

# Executar migração
docker-compose exec app php artisan migrate --force
```

---

### 2. **Porta 80 em Uso**

**Erro:**
```
Error response from daemon: Ports are not available
```

**Solução Rápida:**
```bash
# Editar .env
APP_PORT=8000

# Reiniciar
docker-compose down
docker-compose up -d

# Acessar em http://localhost:8000
```

**Solução Permanente (Windows):**
```powershell
# Ver o que está usando porta 80
netstat -ano | findstr :80

# Matar processo (substitua PID)
taskkill /PID <PID> /F
```

---

### 3. **Docker Daemon Não Está Rodando**

**Erro:**
```
Cannot connect to Docker daemon
```

**Solução:**
1. Abrir **Docker Desktop**
2. Verificar se está rodando (ícone na bandeja)
3. Se não iniciar, reiniciar computador
4. WSL2 deve estar habilitado (Windows)

---

### 4. **Permissão Negada em Arquivos**

**Erro:**
```
Permission denied while trying to connect to Docker daemon socket
```

**Solução (Linux/Mac):**
```bash
sudo usermod -aG docker $USER
newgrp docker
```

**Solução (Windows):**
- Executar PowerShell como **Administrador**
- Rodar docker-setup.ps1 novamente

---

### 5. **Mudanças no Código Não Aparecem**

**Problema:** Arquivo foi editado mas aplicação não reflete

**Solução:**
```bash
# Limpar cache Laravel
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan view:clear

# Para CSS/JS (Vite)
docker-compose exec app npm run dev
```

---

### 6. **Banco de Dados Vazio Após Reiniciar**

**Problema:** Dados sumiram depois de `docker-compose down`

**Solução:**
```bash
# Verificar volume
docker volume ls | grep mysql

# Se volume não existe, dados foram perdidos
# Restaurar backup:
docker-compose exec -T mysql mysql -u sisalmox_user -psissalmox_password sisalmox_db_local < backup.sql
```

---

### 7. **"Out of Memory" - Container Mata**

**Erro:** Container para sem motivo aparente

**Solução:**
1. Abrir Docker Desktop → Settings
2. Ir em **Resources**
3. Aumentar:
   - **CPUs**: 4 ou mais
   - **Memory**: 8GB ou mais
   - **Swap**: 2GB

```bash
# Depois reiniciar
docker-compose down
docker-compose up -d
```

---

### 8. **Aplicação Lenta**

**Problema:** Site muito lentinho

**Solução:**
```bash
# Aumentar recursos (veja #7 acima)

# Otimizar Laravel
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache

# Otimizar composer
docker-compose exec app composer dump-autoload --optimize

# Verificar logs
docker-compose logs -f app
```

---

### 9. **Build Falha com Erro Estranho**

**Solução:**
```bash
# Limpar tudo
docker-compose down -v

# Remover imagens antigas
docker image prune -a -f

# Reconstruir
docker-compose up -d --build
```

---

### 10. **MySQL Recusa Conexão com Caracteres Especiais**

**Erro:** UTF-8 encoding problems

**Solução:**
```bash
# Já está configurado em docker-compose.yml
# Se ainda tiver problema:

docker-compose exec mysql mysql -u sisalmox_user -p sisalmox_db_local

# Dentro do MySQL:
ALTER DATABASE sisalmox_db_local CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE sua_tabela CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

---

## 🔍 Debug Avançado

### Ver Logs Detalhados
```bash
# Todos os serviços
docker-compose logs

# Apenas erros
docker-compose logs --tail 50 app
docker-compose logs --tail 50 mysql

# Tempo real
docker-compose logs -f --tail 100
```

### Inspecionar Container
```bash
# Detalhes do container
docker inspect sisalmox_app | grep -A 20 "Mounts"

# Ver variáveis de ambiente
docker inspect sisalmox_app | grep "Env" -A 50

# Ver portas
docker inspect sisalmox_app | grep "PortBindings"
```

### Acessar Container Interativamente
```bash
# Terminal bash
docker-compose exec app bash

# MySQL interativo
docker-compose exec mysql mysql -u sisalmox_user -p

# Redis CLI
docker-compose exec redis redis-cli
```

---

## 📊 Monitoramento

### Ver Uso de Recursos
```bash
# Tempo real
docker stats

# Uma vez
docker ps --format "table {{.Names}}\t{{.CPUPerc}}\t{{.MemUsage}}"
```

### Health Check
```bash
# Verificar status
docker-compose ps

# Mais detalhes
docker-compose exec mysql mysqladmin ping
docker-compose exec redis redis-cli ping
```

---

## 🧹 Limpeza

### Remover Dados (CUIDADO!)
```bash
# Parar e remover tudo
docker-compose down -v

# Remover apenas imagens
docker-compose down
docker image rm sisalmox_app

# Limpar volumes não usados
docker volume prune
```

### Backup Antes de Limpar
```bash
# Fazer backup
docker-compose exec mysql mysqldump -u sisalmox_user -p sisalmox_db_local > backup_$(date +%Y%m%d_%H%M%S).sql

# Depois pode limpar
docker-compose down -v
```

---

## ✅ Checklist de Verificação

- [ ] Docker Desktop aberto e rodando
- [ ] WSL2 habilitado (Windows)
- [ ] Arquivo `.env` existe
- [ ] `docker-compose up -d` foi executado
- [ ] Aguardou 30+ segundos
- [ ] `docker-compose ps` mostra todos UP
- [ ] Consegue acessar http://localhost
- [ ] PhpMyAdmin funciona em http://localhost:8080
- [ ] Pode fazer login com as credenciais

---

## 🆘 Ainda com Problema?

```bash
# Gerar informações de debug
docker-compose logs > debug.log
docker ps -a > containers.log
docker volume ls > volumes.log

# Compartilhar esses arquivos para suporte
```

---

## 💡 Dicas de Ouro

1. **Sempre fazer backup antes de `down -v`**
2. **Aumentar RAM do Docker se estiver lento**
3. **Usar `--build` se mudar Dockerfile**
4. **Limpar cache regularmente**
5. **Monitorar logs em tempo real**

---

**Última atualização**: 2026
