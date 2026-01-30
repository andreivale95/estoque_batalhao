# 🐳 SISALMOX Docker - Início Rápido

## ⚡ Começar em 5 Minutos

### Windows (PowerShell)
```powershell
powershell -ExecutionPolicy Bypass -File docker-setup.ps1
```

### macOS/Linux (Bash)
```bash
bash docker-setup.sh
```

## 🌐 Acessar Aplicação

Após o setup, abra no navegador:

| Serviço | URL | Usuário | Senha |
|---------|-----|---------|-------|
| **Aplicação** | http://localhost | - | - |
| **PhpMyAdmin** | http://localhost:8080 | sisalmox_user | sisalmox_password |
| **Mailhog** (dev) | http://localhost:8025 | - | - |

---

## 📦 O que é Instalado

- ✅ **Laravel 11** com PHP 8.3 + Apache
- ✅ **MySQL 8.0** com persistência de dados
- ✅ **Redis 7** para cache
- ✅ **PhpMyAdmin** para gerenciar BD
- ✅ **Mailhog** para testes de email

---

## 🛠️ Comandos Essenciais

```bash
# Iniciar
docker-compose up -d

# Parar
docker-compose down

# Ver logs
docker-compose logs -f app

# Terminal PHP
docker-compose exec app bash

# Comando Artisan
docker-compose exec app php artisan migrate
```

---

## ⚙️ Personalizar Portas

Editar `.env`:
```env
APP_PORT=8000              # http://localhost:8000
MYSQL_PORT=3307            # localhost:3307
PHPMYADMIN_PORT=8081       # http://localhost:8081
```

Depois:
```bash
docker-compose down
docker-compose up -d
```

---

## 🔧 Troubleshooting

### MySQL não conecta?
```bash
docker-compose restart mysql
docker-compose exec app php artisan migrate
```

### Porta em uso?
```bash
# Mudar no .env e reiniciar
docker-compose down
docker-compose up -d
```

### Cache/Permissões?
```bash
docker-compose exec app php artisan cache:clear
docker-compose exec app chown -R www-data:www-data /app/storage
```

---

## 📚 Documentação Completa

Veja [DOCKER_SETUP.md](DOCKER_SETUP.md) para:
- Setup detalhado
- Configurações avançadas
- Deploy para produção
- Otimizações de performance
- Soluções de problemas

---

## 🚀 Próximos Passos

1. ✅ Executar `docker-compose up -d`
2. ✅ Abrir http://localhost
3. ✅ Acessar PhpMyAdmin em http://localhost:8080
4. ✅ Verificar dados com `docker-compose logs -f`

---

**Dúvidas?** Veja DOCKER_SETUP.md ou os scripts docker-setup.sh/ps1
