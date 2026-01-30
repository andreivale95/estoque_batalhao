# 🚀 GUIA PASSO A PASSO - Hospedar SISALMOX no Docker Desktop

## 📌 Pré-requisitos (5 min)

### Verificar Instalações

```powershell
# 1. Verificar Docker
docker --version
# Deve retornar: Docker version X.X.X

# 2. Verificar Docker Compose
docker-compose --version
# Deve retornar: Docker Compose version X.X.X

# 3. Verificar WSL2 (Windows)
wsl --version
# Deve retornar: WSL version X.X.X
```

Se algum comando não funcionar:
- 📥 Baixar [Docker Desktop](https://www.docker.com/products/docker-desktop)
- 📥 Instalar [WSL2](https://docs.microsoft.com/en-us/windows/wsl/install)

---

## 🎯 PASSO 1: Preparar Arquivos (2 min)

### 1.1 Abrir Terminal

**Windows:**
- Pressionar `Win + R`
- Digitar `powershell`
- Pressionar Enter

**Linux/Mac:**
- Abrir Terminal

### 1.2 Navegar para Projeto

```bash
cd C:\laragon\www\sisalmox
# ou
cd /caminho/do/sisalmox
```

### 1.3 Verificar Arquivos Docker

```bash
# Verificar se arquivos existem
ls docker-compose.yml
ls docker-setup.ps1
ls DOCKER_*.md
```

---

## 🔑 PASSO 2: Executar Setup Automático (3 min)

### 2.1 Rodar Script (Windows)

```powershell
powershell -ExecutionPolicy Bypass -File docker-setup.ps1
```

**O que vai acontecer:**
1. ✅ Verificar Docker instalado
2. ✅ Criar arquivo `.env`
3. ✅ Gerar chave de aplicação
4. ✅ Iniciar containers
5. ✅ Executar migrações
6. ✅ Limpar cache

### 2.2 Rodar Script (Linux/Mac)

```bash
bash docker-setup.sh
```

### 2.3 Verificar Status

```bash
docker-compose ps
```

**Saída esperada:**
```
NAME                  STATUS       PORTS
sisalmox_app          Up 2 minutes 0.0.0.0:80->80/tcp
sisalmox_mysql        Up 2 minutes 0.0.0.0:3306->3306/tcp
sisalmox_phpmyadmin   Up 2 minutes 0.0.0.0:8080->80/tcp
sisalmox_redis        Up 2 minutes 0.0.0.0:6379->6379/tcp
sisalmox_mailhog      Up 2 minutes 0.0.0.0:1025-1025/tcp, 0.0.0.0:8025->8025/tcp
```

---

## 🌐 PASSO 3: Acessar Aplicação (1 min)

### 3.1 Abrir no Navegador

| Serviço | Link | Notas |
|---------|------|-------|
| **SISALMOX** | http://localhost | App principal |
| **PhpMyAdmin** | http://localhost:8080 | Gerenciar BD |
| **Mailhog** | http://localhost:8025 | Testes de email |

### 3.2 Fazer Login

```
Usuário: (verifique database/seeders/)
Senha: (verifique database/seeders/)
```

Se não houver dados padrão:
```bash
docker-compose exec app php artisan db:seed
```

---

## 🔧 PASSO 4: Primeira Execução (5 min)

### 4.1 Acessar Terminal do Container

```bash
docker-compose exec app bash
```

### 4.2 Dentro do Container - Comandos Úteis

```bash
# Ver status
php artisan tinker
# Type: DB::connection()->getPdo() // Deve funcionar
# Type: exit

# Ver rotas
php artisan route:list

# Ver migrações
php artisan migrate:status

# Criar novo user (opcional)
php artisan tinker
User::create(['email' => 'test@example.com', 'password' => bcrypt('password')])
exit
```

### 4.3 Sair do Container

```bash
exit
```

---

## 📊 PASSO 5: Verificar Banco de Dados (5 min)

### 5.1 Abrir PhpMyAdmin

- Navegador: http://localhost:8080

### 5.2 Login PhpMyAdmin

```
Servidor: mysql
Usuário: sisalmox_user
Senha: sisalmox_password
```

### 5.3 Explorar Banco

- Menu esquerdo: `sisalmox_db_local`
- Ver tabelas: `Tabelas`
- Testar dados: `SELECT * FROM users;`

---

## 💾 PASSO 6: Fazer Backup (2 min)

### 6.1 Backup do Banco

```bash
docker-compose exec -T mysql mysqldump -u sisalmox_user -psissalmox_password sisalmox_db_local > backup_inicial.sql
```

### 6.2 Backup dos Arquivos

```bash
# Copiar pasta do projeto
# Windows: Ctrl+C na pasta sisalmox
# Linux/Mac: cp -r sisalmox sisalmox_backup
```

---

## 🔨 PASSO 7: Desenvolvimento (Contínuo)

### 7.1 Fazer Mudanças no Código

```bash
# Seus arquivos estão em:
C:\laragon\www\sisalmox\app
C:\laragon\www\sisalmox\resources
C:\laragon\www\sisalmox\routes
```

**Mudanças aparecem em tempo real!**

### 7.2 Instalar Novo Pacote PHP

```bash
docker-compose exec app composer require package/name
```

### 7.3 Instalar Novo Pacote npm

```bash
docker-compose exec app npm install package-name
docker-compose exec app npm run build
```

### 7.4 Criar Nova Migração

```bash
docker-compose exec app php artisan make:migration criar_tabela
```

---

## 🛑 PASSO 8: Parar Containers (1 min)

### 8.1 Parar Sem Deletar Dados

```bash
docker-compose stop
```

### 8.2 Reiniciar

```bash
docker-compose start
```

### 8.3 Parar e Remover (CUIDADO!)

```bash
docker-compose down
# Dados persistem em volumes!

# Remover TUDO (incluindo dados)
docker-compose down -v
```

---

## ⚙️ PASSO 9: Personalizar (Opcional)

### 9.1 Mudar Portas

Editar `.env`:
```env
APP_PORT=8000
MYSQL_PORT=3307
PHPMYADMIN_PORT=8081
```

Depois:
```bash
docker-compose down
docker-compose up -d
```

### 9.2 Aumentar Limite de Upload

Editar `Dockerfile`:
```dockerfile
RUN echo "upload_max_filesize = 300M" >> /usr/local/etc/php/conf.d/uploads.ini
```

Depois:
```bash
docker-compose up -d --build
```

### 9.3 Adicionar Novo Serviço

Editar `docker-compose.yml` e adicionar serviço, depois:
```bash
docker-compose up -d
```

---

## 🐛 PASSO 10: Solucionar Problemas

### 10.1 Verificar Logs

```bash
# Todos os logs
docker-compose logs

# Logs de um serviço
docker-compose logs app
docker-compose logs mysql

# Logs em tempo real
docker-compose logs -f app
```

### 10.2 Problemas Comuns

| Problema | Comando | Resultado |
|----------|---------|-----------|
| MySQL não conecta | `docker-compose restart mysql` | Reinicia BD |
| Cache desatualizado | `docker-compose exec app php artisan cache:clear` | Limpa cache |
| Porta em uso | Mudar `APP_PORT` no `.env` | Nova porta |
| Lentidão | Aumentar RAM Docker | Mais recursos |

### 10.3 Ler Documentação

```bash
# Se tiver dúvidas, ler:
cat DOCKER_TROUBLESHOOTING.md

# Para configurações avançadas:
cat DOCKER_SETUP.md
```

---

## 🚀 PASSO 11: Deploy para Produção

### 11.1 Servidores Recomendados

- **Railway.app** - Fácil, gratuito
- **Render.com** - Confiável, grátis
- **DigitalOcean** - VPS, Docker-ready
- **AWS/Azure** - Enterprise

### 11.2 Antes de Fazer Deploy

```bash
# 1. Testar em produção local
export APP_ENV=production
export APP_DEBUG=false
docker-compose -f docker-compose.prod.yml up -d

# 2. Fazer backup
docker-compose exec -T mysql mysqldump -u sisalmox_user -p sisalmox_db_local > backup_prod.sql

# 3. Teste de carga
# ... seu teste ...

# 4. Se OK, fazer deploy
```

### 11.3 Comando de Deploy (Railway)

```bash
npm install -g railway
railway login
railway link
railway up
```

---

## 📝 Checklist Final

- [ ] Docker Desktop instalado e rodando
- [ ] Executou script de setup
- [ ] Todos containers estão UP
- [ ] Acessa http://localhost
- [ ] PhpMyAdmin funciona
- [ ] Banco de dados tem dados
- [ ] Fez backup inicial
- [ ] Consegue fazer mudanças no código
- [ ] Logs aparecem com `docker-compose logs -f`
- [ ] Leu `DOCKER_SETUP.md` para aprender mais

---

## 🎓 Próximas Lições

### Após este guia, leia:

1. **DOCKER_README.md** - Resumo de comandos (10 min)
2. **DOCKER_SETUP.md** - Guia completo (30 min)
3. **DOCKER_TROUBLESHOOTING.md** - Solução de problemas (20 min)

---

## 💡 Dicas de Produtividade

### 1. Alias de Comandos

```bash
# Adicionar ao perfil (.bashrc ou .ps1)
alias dart='docker-compose exec app'
alias ddb='docker-compose exec -T mysql mysql'

# Uso:
dart php artisan migrate
ddb < backup.sql
```

### 2. Abrir Múltiplos Terminais

```bash
# Terminal 1 - Logs
docker-compose logs -f app

# Terminal 2 - Trabalho normal
cd sisalmox
docker-compose exec app bash
```

### 3. Monitorar Recursos

```bash
# Terminal 3
docker stats
```

---

## 📞 Suporte

### Se algo não funcionar:

1. Checar `DOCKER_TROUBLESHOOTING.md`
2. Verificar logs: `docker-compose logs -f`
3. Tentar restart: `docker-compose restart`
4. Limpar cache: `docker-compose down -v && docker-compose up -d`

---

## 🎉 Conclusão

✅ Você agora tem SISALMOX rodando em Docker!

### Próximos passos sugeridos:

- Explorar a aplicação
- Fazer mudanças no código
- Criar novos containers
- Aprender Docker mais a fundo
- Deploy para produção

---

**Parabéns! 🎊**

Seu SISALMOX está pronto para desenvolvimento em Docker.

Para dúvidas, consulte os arquivos de documentação.

---

**Criado em**: 2026
**Versão**: 1.0
**Status**: ✅ Pronto para Usar
