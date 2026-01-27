# ✅ Checklist de Deploy no Railway

## Antes de Fazer Push para GitHub

- [ ] Verificar que `.env` **NÃO** está commitado
- [ ] Confirmar que `.env.example` está atualizado
- [ ] Rodar testes locais: `php artisan test`
- [ ] Limpar caches: `php artisan cache:clear`
- [ ] Fazer backup do banco local (MySQL)
- [ ] Revisar commits: `git log --oneline -5`

## Configuração GitHub

- [ ] Criar repositório no GitHub
- [ ] Fazer `git push` para `main` branch
- [ ] Verificar que `.gitignore` está funcionando (nenhum `.env` commitado)
- [ ] Proteger a branch `main` com rules (Settings > Branches)

## Configuração Railway

- [ ] Criar conta em railway.app
- [ ] Conectar repositório GitHub
- [ ] Selecionar repositório `sisalmox`
- [ ] Confirmar que Procfile foi detectado

## Banco de Dados Railway

- [ ] Adicionar serviço PostgreSQL
- [ ] Anotar credenciais do PostgreSQL
- [ ] Configurar variáveis de ambiente automaticamente

## Variáveis de Ambiente

- [ ] `APP_KEY` - Gerar com `php artisan key:generate --show`
- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] `DB_CONNECTION=pgsql`
- [ ] `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- [ ] `MAIL_MAILER` (configurar se usar email)
- [ ] `LOG_LEVEL=error`

## Primeiro Deploy

- [ ] Fazer primeiro `git push` para triggerar deploy
- [ ] Acompanhar logs em Railway Dashboard
- [ ] Confirmar que build completou com sucesso
- [ ] Migrações rodaram automaticamente

## Testes Pós-Deploy

- [ ] Acessar URL do app no navegador
- [ ] Login com usuário existente
- [ ] Testar listagem de estoque
- [ ] Testar transferência entre seções
- [ ] Gerar PDF de cautela
- [ ] Verificar logs em Railway Dashboard

## Monitoramento Contínuo

- [ ] Configurar alertas no Railway
- [ ] Revisar logs periodicamente
- [ ] Monitorar uso de CPU/Memória
- [ ] Fazer backup regularmente

## Troubleshooting Rápido

| Erro | Solução |
|------|---------|
| "No app key" | Gerar com `php artisan key:generate --show` e configurar em Railway |
| "PDO Exception" | Verificar DB_HOST, DB_USERNAME, DB_PASSWORD em Railway Variables |
| "404 Not Found" | Confirmar que `public/` está em APP_URL; revisar routes |
| "Permission denied storage" | Rodar `php artisan storage:link` |
| "Timeout em migrações" | Aumentar timeout: `--timeout=600` |

## Comandos Úteis Railway

```bash
# Ver logs
railway logs

# Rodar comando único
railway run php artisan tinker

# Deploy manual
railway up

# Status do projeto
railway status
```

## Próximos Passos

1. ✅ **Fazer primeiro push** para GitHub
2. ✅ **Railway inicia deploy automaticamente**
3. ✅ **Migrações rodam automaticamente** via Procfile.sh
4. ✅ **App disponível em production**
5. ✅ **Configurar CI/CD** se desejar (GitHub Actions)

---

**Dúvidas?** Consulte [RAILWAY_DEPLOYMENT.md](RAILWAY_DEPLOYMENT.md)
