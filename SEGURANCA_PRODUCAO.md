# 🔐 GUIA DE SEGURANÇA - PRODUÇÃO RAILWAY

## ⚠️ Configurações Críticas Antes do Deploy

### 1. APP_DEBUG

```env
# ❌ NUNCA EM PRODUÇÃO
APP_DEBUG=true

# ✅ CORRETO PARA PRODUÇÃO
APP_DEBUG=false
```

**Por quê?** Com `APP_DEBUG=true`, você expõe:
- Stack traces com caminho de arquivos
- Variáveis de ambiente
- Queries SQL
- Informações sensíveis do servidor

### 2. APP_ENV

```env
# ❌ NÃO USE
APP_ENV=local
APP_ENV=testing

# ✅ CORRETO PARA PRODUÇÃO
APP_ENV=production
```

### 3. LOG_LEVEL

```env
# ❌ NÃO USE
LOG_LEVEL=debug

# ✅ CORRETO PARA PRODUÇÃO
LOG_LEVEL=error
```

Isso reduz I/O de disco e mantém logs limpos para apenas erros críticos.

### 4. HTTPS / SSL

Railway fornece HTTPS automaticamente. Configure:

```env
APP_URL=https://seu-app-name.up.railway.app
```

Nunca use `http://`.

### 5. CORS (Cross-Origin)

Se usar APIs:

```php
// config/cors.php
'paths' => ['api/*'],
'allowed_origins' => [env('APP_URL')],  // Apenas seu domínio
'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE'],
'allowed_headers' => ['Content-Type', 'Authorization'],
'exposed_headers' => ['X-Total-Count'],
'max_age' => 86400,  // 24 horas
'supports_credentials' => true,
```

## 🔑 Gestão de Secrets

### ❌ O QUE NUNCA FAZER

```php
// ❌ Nunca fazer isso!
'db_password' => 'minha_senha_123',

// ❌ Nunca commitar arquivo .env
.env (actual file)

// ❌ Nunca expor em logs
Log::info('Conectando com senha: ' . env('DB_PASSWORD'));

// ❌ Nunca em comentários de código
// TODO: usar API key sk-1234567890
```

### ✅ O QUE FAZER

```bash
# 1. SEMPRE usar variáveis de ambiente
DB_PASSWORD={env('DB_PASSWORD')}

# 2. SEMPRE usar .env.example (sem valores reais)
.env.example ✅ Commitar
.env ❌ Nunca commitar

# 3. SEMPRE configurar em Railway Variables
# Railway Dashboard → Project → Variables
```

### Secrets no Railway

1. Vá para seu projeto no Railway
2. Clique em **Variables**
3. Adicione cada secret:
   - `DB_PASSWORD` → valor real
   - `APP_KEY` → saída do `php artisan key:generate --show`
   - `MAIL_PASSWORD` → se usar email
   - Etc.

4. Railway não mostra valores já salvos (por segurança) ✅

## 🛡️ Proteção de Rotas

### Authentication (Middleware)

```php
// routes/web.php

// ❌ SEM PROTEÇÃO
Route::get('/admin', 'AdminController@index');

// ✅ COM PROTEÇÃO
Route::middleware('auth')->group(function () {
    Route::get('/admin', 'AdminController@index');
    Route::post('/estoque', 'EstoqueController@store');
});
```

### Seu projeto já tem:
```php
Route::middleware('auth')->group(function () {
    // Inventory routes
    Route::get('/registros/estoque/listar', 'EstoqueController@listarEstoque');
    Route::get('/registros/estoque/detalhes/{id}', 'EstoqueController@detalhes');
    // ... mais rotas
});
```

## 🔒 Validação de Entrada

### Sempre validar dados:

```php
// ✅ SEMPRE FAZER ISSO
$validated = $request->validate([
    'nome' => 'required|string|max:255',
    'quantidade' => 'required|integer|min:1',
    'preco' => 'required|numeric|min:0',
]);

// ❌ NUNCA FAZER ISSO
$data = $request->all(); // Aceita tudo!
Model::create($data);
```

Seu projeto já usa validações em Controllers. ✅

## 🚨 SQL Injection Prevention

### Seu projeto já usa Eloquent ORM:

```php
// ✅ SEGURO - Eloquent
Produto::where('id', $id)->first();

// ✅ SEGURO - Query Builder com bindings
DB::table('produtos')->where('id', $id)->first();

// ❌ PERIGOSO - SQL Puro
DB::raw("SELECT * FROM produtos WHERE id = $id");
```

Mantenha sempre a prática de usar Eloquent. ✅

## 🔐 Senhas de Usuário

### Seu projeto já usa Hashing:

```php
// ✅ SEMPRE FAZER ISSO
$user = User::create([
    'name' => 'João',
    'email' => 'joao@email.com',
    'password' => Hash::make($password), // Hashed!
]);

// ❌ NUNCA FAZER ISSO
'password' => $password, // Texto plano!
```

Verificar em seu `register` controller que está usando `Hash::make()`. ✅

## 📋 Rate Limiting

Proteja contra brute force:

```php
// config/sanctum.php ou routes/web.php
Route::middleware('throttle:60,1')->group(function () {
    Route::post('/login', 'AuthController@login');
});

// 60 requisições por 1 minuto por IP
```

## 🔄 CSRF Protection

Rails.csrf_token Laravel já está configurado:

```php
// Middleware automático
Route::middleware('web')->group(function () {
    // Todas as rotas aqui têm proteção CSRF
    Route::post('/estoque/transferencia-secoes', ...);
});
```

Nas views Blade, sempre adicione:

```blade
<form method="POST" action="{{ route('estoque.transferir.secoes') }}">
    @csrf  <!-- Token CSRF -->
    <!-- inputs -->
</form>
```

## 🎯 Checklist de Segurança Pré-Deploy

```
✅ Verificar antes de fazer git push:

Configuração:
  [ ] APP_DEBUG=false
  [ ] APP_ENV=production
  [ ] LOG_LEVEL=error
  [ ] APP_URL=https://...

Segurança:
  [ ] Nenhum .env real commitado
  [ ] Nenhuma senha no código
  [ ] Nenhuma API key em comentários
  [ ] All routes com `auth` middleware
  [ ] All forms com @csrf
  [ ] All inputs com validação

Deployment:
  [ ] APP_KEY gerado e salvado em Railway
  [ ] DB_PASSWORD configurado em Railway
  [ ] Todos os secrets em Railway Variables
  [ ] Nenhum valor sensível em .env.example
  [ ] .gitignore inclui: .env, .env.backup
  [ ] composer.lock está no .gitignore ou commitado (escolha 1)

Database:
  [ ] Migrações testadas localmente
  [ ] Backups configurados
  [ ] Usuário PostgreSQL com permissões mínimas
  [ ] Nenhuma conexão com usuário `postgres` root

Aplicação:
  [ ] Testes passando localmente
  [ ] Sem erros em logs
  [ ] Funcionalidades críticas testadas
  [ ] PDF generation testando
  [ ] Transferências testadas
```

## 🆘 Se Algo der Errado em Produção

### Verificar Logs

```bash
# Via Railway CLI
railway logs

# Via Railway Dashboard
# Clique em Deployments → Clique em um deployment → Ver logs
```

### Revert Rápido

Se fizer um deploy com erro:

```bash
# Railway Dashboard
# Clique em Deployments
# Selecione o deploy anterior OK
# Clique em "Redeploy"

# Ou via Git (revert e push)
git revert HEAD
git push origin main
```

### Diagnosticar no Production

```bash
# Acessar shell do Railway
railway run bash

# Dentro do shell:
php artisan tinker
>>> DB::connection()->getPdo();  # Testa conexão DB
>>> config('app.debug');  # Verifica se debug está OFF
>>> env('APP_KEY');  # Verifica se APP_KEY existe
```

## 📊 Monitoramento Contínuo

### No Railway Dashboard:

1. **Deployments** - Veja histórico
2. **Logs** - Acompanhe erros em real-time
3. **Metrics** - CPU, Memory, Network
4. **Health Checks** - Status do app

### Configurar Alertas:

Railway pode enviar notificações por email/Slack quando:
- Deployment falha
- Aplicação fica offline
- Uso de CPU/Memory ultrapassa limite

## 🔗 Recursos de Segurança

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [Laravel Security](https://laravel.com/docs/security)
- [Railway Docs - Security](https://railway.app/docs/security)
- [CWE/SANS Top 25](https://cwe.mitre.org/top25/)

## 📝 Checklist Semanal

```
☐ Revisar logs em busca de erros anormais
☐ Verificar uso de recursos (CPU, Memory, Disk)
☐ Testar funcionalidades críticas
☐ Verificar integridade do banco de dados
☐ Confirmar que backups estão rodando
☐ Revisar qualquer novo código commitado
```

---

**Última atualização:** 27 de Janeiro de 2026
**Versão:** 1.0
**Status:** ✅ PRONTO PARA PRODUÇÃO

Lembre-se: Segurança em produção é responsabilidade compartilhada!
