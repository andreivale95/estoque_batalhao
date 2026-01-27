# Deploy no Railway - Guia Completo

## 📋 Pré-requisitos
- Conta no [GitHub](https://github.com)
- Conta no [Railway.app](https://railway.app)
- Git instalado localmente

## 🚀 Passos para Deploy

### 1. Preparar o Repositório GitHub

```bash
# Inicializar git (se não estiver inicializado)
git init

# Adicionar todos os arquivos
git add .

# Commit das configurações do Railway
git commit -m "Configurar deploy no Railway via Nixpacks"

# Subir para o GitHub
git push -u origin main
```

### 2. Configurar o Railway

1. Acesse [Railway.app](https://railway.app)
2. Clique em **+ New Project**
3. Selecione **Deploy from GitHub repo**
4. Autorize o GitHub e selecione seu repositório `sisalmox`
5. Railway detectará automaticamente que é um projeto Laravel

### 3. Criar Banco de Dados PostgreSQL

No painel do Railway:
1. Clique em **+ Add Service**
2. Selecione **PostgreSQL**
3. Railway criará automaticamente o banco de dados

### 4. Configurar Variáveis de Ambiente

No painel do projeto Railway, vá para **Variables** e adicione:

```env
APP_NAME=Sisalmox
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:YOUR_GENERATED_KEY_HERE
APP_URL=https://your-app-name.up.railway.app

DB_CONNECTION=pgsql
DB_HOST=${{Postgres.PGHOST}}
DB_PORT=${{Postgres.PGPORT}}
DB_DATABASE=${{Postgres.PGDATABASE}}
DB_USERNAME=${{Postgres.PGUSER}}
DB_PASSWORD=${{Postgres.PGPASSWORD}}

LOG_CHANNEL=stack
LOG_LEVEL=error

CACHE_DRIVER=file
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
FILESYSTEM_DISK=local

MAIL_MAILER=log
MAIL_FROM_ADDRESS=noreply@sisalmox.com
```

#### Gerar APP_KEY:
```bash
php artisan key:generate --show
```
Copie a saída e configure em `APP_KEY` no Railway.

### 5. Configurar PostgreSQL (se necessário)

Se usar variáveis dinâmicas do Railway:

No arquivo `.env.example`, Railway substitui automaticamente:
- `${{Postgres.PGHOST}}` - Host do banco
- `${{Postgres.PGPORT}}` - Porta (5432)
- `${{Postgres.PGDATABASE}}` - Nome do banco
- `${{Postgres.PGUSER}}` - Usuário
- `${{Postgres.PGPASSWORD}}` - Senha

### 6. Deploy no Railway

O deploy é automático quando você faz push para `main` no GitHub.

O Railway irá:
1. Detectar o arquivo `nixpacks.toml`.
2. Instalar PHP 8.3 e Node.js 20.
3. Configurar **Nginx + PHP-FPM** automaticamente.
4. Executar os scripts de build (`npm run build`).
5. Iniciar a aplicação.

### 7. Rodar Migrações

Após o primeiro deploy:

```bash
# Via Railway CLI
railway run php artisan migrate --force
```

Ou no painel Railway:
1. Vá para **Deploy Logs**
2. Clique em **Command Palette** (Ctrl+K)
3. Execute: `php artisan migrate --force`

### 8. Verificar Deployment

1. Acesse o URL fornecido pelo Railway
2. Teste as principais funcionalidades:
   - Login
   - Listagem de estoque
   - Transferência entre seções
   - Geração de PDF

## 🔧 Troubleshooting

### Erro "No app key"
```bash
# Gerar chave localmente
php artisan key:generate --show

# Copiar saída para APP_KEY no Railway
```

### Erro de Banco de Dados
```bash
# Verificar conexão
railway run php artisan tinker
# Na prompt: DB::connection()->getPdo();
```

### Erro de Permissão de Storage
```bash
railway run php artisan storage:link
```

### Verificar Logs
```bash
# Via Railway CLI
railway logs
```

## 📊 Monitoramento

No painel Railway você pode:
- Ver **Deployment Logs**
- Monitorar **CPU/Memory Usage**
- Configurar **Webhooks** para eventos
- Ver **Build History**

## 🔐 Segurança

Certifique-se de:
- ✅ `APP_DEBUG=false` em produção
- ✅ Todas as senhas configuradas como **secrets** no Railway
- ✅ `.env` **nunca** commitado no Git
- ✅ `MAIL_PASSWORD` e credenciais **nunca** em código
- ✅ Railway handles **HTTPS automatically**

## 📝 Documentação Adicional

- [Railway Docs](https://railway.app/docs)
- [Laravel Deployment](https://laravel.com/docs/deployment)
- [Railway PostgreSQL](https://railway.app/docs/databases/postgresql)

## ❓ Perguntas Frequentes

**P: Como resetar a base de dados?**
R: No Railway, delete o serviço PostgreSQL e crie um novo.

**P: Como fazer backup do banco?**
R: Railway oferece backups automáticos. Veja em Project Settings > Backups.

**P: Posso usar MySQL em vez de PostgreSQL?**
R: Sim, adicione o serviço MySQL do Railway em vez de PostgreSQL.

**P: Como atualizar o código em produção?**
R: Apenas faça `git push` para `main`. Railway fará o deploy automaticamente.
