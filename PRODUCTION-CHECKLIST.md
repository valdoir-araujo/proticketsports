# Checklist de produção (ProTicketSports)

Use este checklist no servidor (Hostinger ou outro) para não esquecer nada importante.

---

## 1. Arquivo `.env` no servidor

- [ ] **DB_DATABASE**, **DB_USERNAME**, **DB_PASSWORD** preenchidos com os dados reais do MySQL da hospedagem.
- [ ] **APP_ENV=production** e **APP_DEBUG=false** (nunca `true` em produção).
- [ ] **APP_URL** com HTTPS e domínio final (ex.: `https://www.proticketsports.com.br`). Sem barra no final.
- [ ] **ASSET_URL** igual ao **APP_URL** (para assets e CDN).
- [ ] **SESSION_SECURE_COOKIE=true** (obrigatório em HTTPS).
- [ ] **LOG_LEVEL=error** ou **LOG_LEVEL=warning** em produção (evite `debug` para não encher log e expor detalhes).
- [ ] **Strava**: a URL de callback no `.env` (**STRAVA_REDIRECT_URI**) deve ser **exatamente** a mesma configurada no painel do Strava (com ou sem `www` conforme o seu domínio). Se **APP_URL** tem `www`, use `https://www.proticketsports.com.br/strava/callback` no Strava e no .env.

---

## 2. Proxy reverso (Cloudflare, Nginx na frente do PHP)

Se o acesso ao site passa por Cloudflare ou outro proxy:

- [ ] No `.env`: **APP_TRUSTED_PROXIES=\*** (ou o IP do proxy, conforme a documentação Laravel).
- [ ] Em `app/Http/Middleware/TrustProxies.php`: descomentar/ajustar conforme a doc do Laravel para o seu ambiente.

Isso evita que o Laravel pense que todas as requisições vêm de HTTP ou do IP do proxy.

---

## 3. Após cada deploy

No servidor, na pasta do projeto:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Se usar filas:

```bash
php artisan queue:restart
```

Se não tiver link simbólico do storage:

```bash
php artisan storage:link
```

---

## 4. Permissões (Linux)

- [ ] Pastas **storage** e **bootstrap/cache** graváveis pelo usuário do PHP (ex.: `chmod -R 775 storage bootstrap/cache` ou o usuário correto do servidor).

---

## 5. Segurança

- [ ] **.env** nunca commitado no Git (deve estar no `.gitignore`).
- [ ] Se você já compartilhou o `.env` (por exemplo em chat), **troque** senhas e tokens: e-mail (MAIL_PASSWORD), Z-API, Mercado Pago (se necessário), e gere nova **APP_KEY** se for o caso (`php artisan key:generate`).
- [ ] No painel do **Mercado Pago**, a URL do webhook deve ser HTTPS e acessível (ex.: `https://www.proticketsports.com.br/webhook/mercadopago` ou a rota que você usa). **MERCADOPAGO_WEBHOOK_SECRET** deve ser o mesmo configurado no painel.

---

## 6. Resumo do que costuma faltar

| Item | Descrição |
|------|-----------|
| **LOG_LEVEL** | Em produção use `error` ou `warning`. `debug` gera muito log e pode expor detalhes. |
| **Strava** | APP_URL e STRAVA_REDIRECT_URI com mesmo domínio (www vs sem www). |
| **DB_*** | Preencher no servidor com credenciais do banco real. |
| **Cache** | Rodar `config:cache` e `route:cache` após alterar .env ou rotas. |
| **Storage** | `php artisan storage:link` se as imagens/PDFs não aparecerem. |

---

*Última atualização: março 2025.*
