# Deploy no Render - Guia Completo

## 📋 Pré-requisitos
- Conta no [GitHub](https://github.com)
- Conta no [Render.com](https://render.com)
- Git instalado

## 🚀 Passos para Deploy

### 1. Preparar o Repositório GitHub

```bash
# Inicializar git (se não estiver inicializado)
git init

# Adicionar todos os arquivos
git add .

# Primeiro commit
git commit -m "Initial commit for Render deployment"

# Criar repositório no GitHub e fazer push
git remote add origin https://github.com/seu-usuario/sisalmox.git
git branch -M main
git push -u origin main
```

### 2. Conectar GitHub ao Render

1. Acesse [Render.com](https://render.com)
2. Clique em **New +** no canto superior direito
3. Selecione **Web Service**
4. Selecione **Deploy an existing Git repository** (ou **GitHub**)
5. Autorize o GitHub se necessário
6. Selecione o repositório `sisalmox`
7. Clique **Continue**

### 3. Configurar o Web Service

Na página de configuração:

**Configurações básicas:**
- **Name:** sisalmox
- **Region:** Ohio (ou sua preferência)
- **Branch:** main
- **Runtime:** Docker

**Build e Deploy:**
- **Build Command:** (deixar vazio - usa Dockerfile)
- **Start Command:** (deixar vazio - usa Dockerfile CMD)

### 4. Configurar Variáveis de Ambiente

Na seção **Environment Variables**, adicione:

```env
APP_KEY=base64:SEU_APP_KEY_AQUI
APP_URL=https://seu-app-name.onrender.com
```

Para gerar `APP_KEY`, execute localmente:
```bash
php artisan key:generate --show
```

Outras variáveis serão preenchidas automaticamente pelo `render.yaml`.

### 5. Criar Banco de Dados PostgreSQL

No Render Dashboard:

1. Clique em **New +** → **PostgreSQL**
2. Configure:
   - **Name:** sisalmox-db
   - **Database:** sisalmox
   - **User:** sisalmox_user
   - **Region:** Ohio (mesma do web service)
   - **Plan:** Free (ou plan desejado)

3. Clique **Create Database**

4. Copie as credenciais (Render preenche automaticamente via `render.yaml`)

### 6. Deploy!

Na página do web service:

1. Clique em **Manual Deploy** ou simplesmente faça `git push`
2. Render detectará mudanças automaticamente
3. Aguarde o build (3-5 minutos)
4. Quando ficar verde ✅, está live!

Seu app estará em: `https://seu-app-name.onrender.com`

## ⚙️ Configuração Manual no Render Dashboard

Se preferir configurar manualmente (sem `render.yaml`):

1. **Variáveis de Ambiente Obrigatórias:**
   ```
   APP_KEY=base64:...
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://seu-app-name.onrender.com
   DB_CONNECTION=pgsql
   DB_HOST=seu-db.onrender.com
   DB_PORT=5432
   DB_DATABASE=sisalmox
   DB_USERNAME=sisalmox_user
   DB_PASSWORD=senha_segura
   LOG_LEVEL=error
   ```

2. **Database Settings:**
   - Criar PostgreSQL service separado
   - Copiar credenciais para o web service

## 🔧 Troubleshooting

### Erro: "Build failed"
- Verifique os logs em **Logs**
- Confirme que Dockerfile existe e é válido
- Verifique se todas as dependências estão em composer.json

### Erro: "Cannot connect to database"
- Confirme que PostgreSQL foi criado
- Verifique credenciais de DB_*
- Aguarde ~2 minutos para conexão ser estabelecida

### App está lento
- Render free tier tem recursos limitados
- Upgrade para plan pago se necessário
- Use `redis` para cache (plan pago)

### Migrações não rodaram
- O `release` command em `render.yaml` deve rodar
- Se não rodar, execute manualmente:
  - Clique no web service
  - **Shell** → Execute: `php artisan migrate --force`

## 📊 Monitoramento

No dashboard Render você pode:
- Ver **Logs** em tempo real
- Monitorar **CPU, Memory, Disk**
- Ver **Deployment History**
- Configurar **Alerts**
- **Restart** a aplicação se necessário

## 🔐 Segurança

✅ Checklist:
- [ ] `APP_DEBUG=false`
- [ ] `APP_ENV=production`
- [ ] `LOG_LEVEL=error`
- [ ] `APP_KEY` configurado
- [ ] Banco de dados com senha segura
- [ ] `.env` real nunca commitado
- [ ] HTTPS automático (Render fornece)

## 📝 Depois do Deploy

1. **Teste o app:**
   - Acesse a URL fornecida
   - Teste login
   - Teste funcionalidades principais

2. **Backup do banco:**
   - Render oferece backups automáticos (plan pago)
   - Para free tier, faça backups manuais periódicos

3. **Logs:**
   - Monitore regularmente em **Logs**
   - Procure por erros ou warnings

## 🔄 Updates Futuros

Para fazer updates:

```bash
# Fazer mudanças localmente
git add .
git commit -m "Feature: descrição da mudança"
git push origin main
```

Render detectará automaticamente e fará novo deploy!

## 📞 Suporte

- [Render Documentation](https://render.com/docs)
- [Render Support](https://support.render.com)
- [Laravel Deployment](https://laravel.com/docs/deployment)

## ⚠️ Limitações do Free Tier Render

- **CPU:** Limitado
- **RAM:** 512 MB
- **Disk:** 5 GB para PostgreSQL
- **Deployment:** Spin down após 15 min de inatividade
- **Backups:** Não disponíveis
- **SSL:** Fornecido automaticamente

Para produção, considere upgrade para **Starter** ou **Standard** plan.

---

**Pronto! Seu Sisalmox está hospedado no Render!** 🚀
