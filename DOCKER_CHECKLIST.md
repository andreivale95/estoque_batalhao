# ✅ CHECKLIST INTERATIVO - Docker Setup SISALMOX

## 🎯 FASE 1: PRÉ-REQUISITOS (5 min)

### Hardware
- [ ] Computador com 8GB+ RAM
- [ ] 4+ núcleos de CPU
- [ ] 20GB de espaço em disco

### Software (Windows)
- [ ] Windows 10 Pro/Enterprise OU Windows 11
- [ ] WSL2 instalado: `wsl --version`
- [ ] Docker Desktop instalado
- [ ] Docker rodando (verificar bandeja)
- [ ] PowerShell 5.0+

### Software (Linux/Mac)
- [ ] Docker instalado: `docker --version`
- [ ] Docker Compose instalado: `docker-compose --version`
- [ ] Terminal funcionando
- [ ] Bash shell disponível

### Verificar Instalações

```bash
# Windows PowerShell
docker --version
docker-compose --version

# Linux/Mac Terminal
docker --version
docker-compose --version
```

---

## 🚀 FASE 2: SETUP AUTOMÁTICO (10 min)

### Passo 1: Clonar/Navegar Projeto
- [ ] Projeto em: `C:\laragon\www\sisalmox\`
- [ ] Verificar que arquivos Docker existem
- [ ] Abrir PowerShell (Admin)

### Passo 2: Executar Script Setup

#### Windows (PowerShell Admin)
```powershell
cd C:\laragon\www\sisalmox
powershell -ExecutionPolicy Bypass -File docker-setup.ps1
```
- [ ] Script iniciou sem erros
- [ ] Mensagens em cores apareceram
- [ ] Docker containers foram criados

#### Linux/Mac (Bash)
```bash
cd /caminho/sisalmox
bash docker-setup.sh
```
- [ ] Script iniciou sem erros
- [ ] Mensagens coloridas apareceram
- [ ] Docker containers foram criados

### Passo 3: Aguardar Conclusão
- [ ] Aguardou ~1 minuto
- [ ] Mensagem "✅ Setup Concluído!" apareceu
- [ ] URLs de acesso exibidas

---

## 🔍 FASE 3: VERIFICAÇÃO (5 min)

### Verificar Containers Rodando

```bash
docker-compose ps
```

**Esperado:**
```
NAME                  STATUS
sisalmox_app          Up 2 minutes
sisalmox_mysql        Up 2 minutes
sisalmox_phpmyadmin   Up 2 minutes
sisalmox_redis        Up 2 minutes
sisalmox_mailhog      Up 1 minute
```

- [ ] sisalmox_app: **UP** ✅
- [ ] sisalmox_mysql: **UP** ✅
- [ ] sisalmox_redis: **UP** ✅
- [ ] sisalmox_phpmyadmin: **UP** ✅
- [ ] sisalmox_mailhog: **UP** ✅

### Verificar Arquivo .env

```bash
cat .env
```

- [ ] Arquivo `.env` existe
- [ ] Contém `APP_KEY=base64:...`
- [ ] `DB_HOST=mysql`
- [ ] `DB_DATABASE=sisalmox_db_local`

---

## 🌐 FASE 4: ACESSAR APLICAÇÃO (3 min)

### Abrir Navegador

#### SISALMOX - Aplicação Principal
- **URL**: http://localhost
- [ ] Página carrega
- [ ] Vê logo/interface do SISALMOX
- [ ] Sem erros 404 ou 500

#### PhpMyAdmin - Gerenciar BD
- **URL**: http://localhost:8080
- **Usuário**: sisalmox_user
- **Senha**: sisalmox_password
- [ ] Página carrega
- [ ] Consegue fazer login
- [ ] Vê banco `sisalmox_db_local`

#### Mailhog - Testes Email
- **URL**: http://localhost:8025
- [ ] Interface carrega (opcional)
- [ ] Pode verificar emails de testes

---

## 🗄️ FASE 5: BANCO DE DADOS (5 min)

### PhpMyAdmin
- [ ] Aberto em http://localhost:8080
- [ ] Logado com sisalmox_user
- [ ] Menu esquerdo mostra `sisalmox_db_local`

### Verificar Tabelas
- [ ] Expandir `sisalmox_db_local`
- [ ] Ver tabelas (users, produtos, etc.)
- [ ] Clicar em uma tabela
- [ ] Ver dados na tabela

### Verificar Dados
```bash
# Ou via PhpMyAdmin
docker-compose exec -T mysql mysql -u sisalmox_user -psissalmox_password sisalmox_db_local -e "SELECT * FROM users LIMIT 5;"
```
- [ ] Executa sem erro
- [ ] Retorna dados ou tabela vazia

---

## 🛠️ FASE 6: TERMINAL PHP (5 min)

### Acessar Container
```bash
docker-compose exec app bash
```
- [ ] Prompt muda para `root@[ID]:/app#`
- [ ] Está dentro do container

### Testar Artisan
```bash
php artisan --version
```
- [ ] Mostra versão do Laravel (11.0 ou similar)
- [ ] Sem erros

### Verificar Banco Internamente
```bash
php artisan tinker
```
Dentro do tinker:
```php
DB::connection()->getPdo()
User::count()
exit
```
- [ ] DB connection retorna objeto
- [ ] User::count() retorna número
- [ ] Saiu do tinker com `exit`

### Sair do Container
```bash
exit
```
- [ ] Voltou ao prompt do host

---

## 💾 FASE 7: BACKUP INICIAL (2 min)

### Fazer Backup
```bash
docker-compose exec -T mysql mysqldump -u sisalmox_user -psissalmox_password sisalmox_db_local > backup_inicial.sql
```
- [ ] Comando executou sem erro
- [ ] Arquivo `backup_inicial.sql` foi criado
- [ ] Arquivo tem tamanho > 0

### Testar Backup
```bash
ls -lh backup_inicial.sql
```
- [ ] Arquivo listado
- [ ] Tamanho maior que 1KB

---

## 📚 FASE 8: DOCUMENTAÇÃO (10 min)

### Ler Documentação
- [ ] Abriu `DOCKER_INDICE.md`
- [ ] Entendeu a estrutura de arquivos
- [ ] Sabe onde encontrar cada documento

### Ler Guia Rápido
- [ ] Abriu `DOCKER_README.md`
- [ ] Entendeu comandos essenciais
- [ ] Anotou comandos que vai usar

### Ler Passo a Passo (⭐ Importante)
- [ ] Abriu `DOCKER_PASSO_A_PASSO.md`
- [ ] Leu os 11 passos
- [ ] Entendeu o fluxo de desenvolvimento

---

## 💻 FASE 9: TESTE DE DESENVOLVIMENTO (10 min)

### Testar Hot Reload
```bash
# 1. Fazer mudança em arquivo PHP
# Exemplo: resources/views/welcome.blade.php
# Adicionar um comentário

# 2. Salvar arquivo (Ctrl+S)

# 3. Atualizar navegador (F5)
```
- [ ] Mudança apareció no navegador
- [ ] Não precisa reiniciar container

### Testar Artisan
```bash
docker-compose exec app php artisan route:list
```
- [ ] Lista todas as rotas
- [ ] Sem erros

### Testar npm (se necessário)
```bash
docker-compose exec app npm run dev
```
- [ ] Inicia dev server (ou Vite)
- [ ] Sem erros
- [ ] Pressionar Ctrl+C para parar

---

## 📊 FASE 10: VERIFICAÇÃO FINAL (5 min)

### Performance
```bash
docker stats
```
- [ ] Abriu monitor de recursos
- [ ] CPU < 50%
- [ ] Memória < 70%
- [ ] Pressionar Ctrl+C para sair

### Todos os Serviços UP
```bash
docker-compose ps
```
- [ ] ✅ Todos mostram **Up**
- [ ] ✅ Nenhum **Exited** ou **Dead**

### Aplicação Respondendo
- [ ] ✅ http://localhost carrega
- [ ] ✅ http://localhost:8080 carrega
- [ ] ✅ Sem erros no console

### Banco Acessível
- [ ] ✅ PhpMyAdmin conecta
- [ ] ✅ Dados visíveis
- [ ] ✅ Backup feito

---

## 🎯 PRÓXIMOS PASSOS

### Imediato (Hoje)
- [ ] Explorar a aplicação SISALMOX
- [ ] Entender as funcionalidades
- [ ] Fazer um backup adicional
- [ ] Anotar dúvidas

### Curto Prazo (Esta Semana)
- [ ] [ ] Ler DOCKER_ARQUITETURA.md
- [ ] [ ] Ler DOCKER_SETUP.md completo
- [ ] [ ] Fazer primeira mudança no código
- [ ] [ ] Testar novos comandos Artisan
- [ ] [ ] Criar primeira migração

### Médio Prazo (Este Mês)
- [ ] Configurações avançadas
- [ ] Deploy para staging
- [ ] Testes de carga
- [ ] Setup de CI/CD

### Longo Prazo
- [ ] Deploy em produção
- [ ] Monitoramento 24/7
- [ ] Backups automáticos
- [ ] Documentação de produção

---

## 🚨 PROBLEMAS? CHECKLIST DE DEBUG

### Se Algo Não Funciona

1. **Verificar Docker**
   - [ ] Docker Desktop está aberto?
   - [ ] Está rodando (verificar bandeja)?
   - [ ] WSL2 está habilitado?

2. **Verificar Containers**
   - [ ] `docker-compose ps` mostra UP?
   - [ ] Nenhum **Exited** ou erro?

3. **Verificar Logs**
   - [ ] `docker-compose logs -f app`
   - [ ] Há erros vermelhos?

4. **Tentar Restart**
   - [ ] `docker-compose restart`
   - [ ] Aguardar 30 segundos
   - [ ] Testar novamente

5. **Ler Documentação**
   - [ ] Consultou DOCKER_TROUBLESHOOTING.md?
   - [ ] Procurou o erro específico?

6. **Última Opção**
   ```bash
   docker-compose down -v
   docker-compose up -d
   docker-compose exec app php artisan migrate
   ```
   - [ ] Reset completo
   - [ ] Dados vão ser perdidos!

---

## 📋 Checklist Final - Antes de Começar a Desenvolver

### Sistema
- [ ] Docker funcionando
- [ ] Todos containers UP
- [ ] Nenhum aviso de erro

### Aplicação
- [ ] http://localhost carrega
- [ ] Interface visível
- [ ] Sem erro 500

### Banco de Dados
- [ ] PhpMyAdmin conecta
- [ ] Banco `sisalmox_db_local` existe
- [ ] Tabelas visíveis
- [ ] Dados acessíveis

### Ambiente
- [ ] Arquivo `.env` existe
- [ ] `APP_KEY` preenchida
- [ ] Credenciais corretas

### Desenvolvimento
- [ ] Terminal PHP funciona (`docker-compose exec app bash`)
- [ ] Artisan responde (`php artisan --version`)
- [ ] Backup inicial feito
- [ ] Hot reload testado

### Documentação
- [ ] Leu DOCKER_INDICE.md
- [ ] Leu DOCKER_README.md
- [ ] Leu DOCKER_PASSO_A_PASSO.md
- [ ] Sabe onde encontrar outros docs

---

## 🎊 PARABÉNS!

Se todos os itens acima estão marcados ✅, você está 100% pronto!

### Seu ambiente agora tem:

✅ Docker completo rodando
✅ MySQL com dados persistentes
✅ Redis para cache
✅ PhpMyAdmin para BD
✅ Mailhog para testes
✅ Laravel em produção
✅ Hot reload funcionando
✅ Backup seguro

### Próximo passo:

**Comece a desenvolver!** 🚀

Qualquer dúvida, consulte os documentos de ajuda.

---

**Checklist Criado em**: 2026
**Status**: ✅ Completo
**Última Atualização**: 2026

---

**Boa sorte! 🍀**
