# Passo a passo: SSL, Webhook e Medir qualidade (Mercado Pago)

Guia prático para fazer as três ações que faltam para aprovar a qualidade da integração no Mercado Pago.

---

## 1. SSL + TLS 1.2+ (site em HTTPS)

**O que é:** Seu site precisa abrir com `https://` e o servidor deve aceitar conexões TLS 1.2 ou superior. O Mercado Pago exige isso para produção.

**Onde fazer:** No painel da sua **hospedagem** (ex.: Hostinger). Não é no código nem no painel do Mercado Pago.

### Se você usa Hostinger

1. **Entrar no painel**
   - Acesse https://www.hostinger.com.br e faça login.
   - Abra o **hPanel** (painel de controle).

2. **Abrir SSL**
   - No menu lateral, procure por **SSL** ou **Segurança** ou **Websites**.
   - Clique em **SSL** (ou algo como “Certificado SSL”).

3. **Ativar SSL gratuito**
   - A Hostinger costuma oferecer SSL gratuito (Let’s Encrypt) por domínio.
   - Selecione o domínio do seu site (ex.: `seudominio.com.br`).
   - Clique em **Instalar** ou **Ativar** SSL / **Free SSL**.
   - Aguarde alguns minutos até o certificado ser emitido.

4. **Forçar HTTPS (opcional mas recomendado)**
   - Ainda em **SSL** ou em **Configurações do domínio**, procure algo como:
     - “Redirecionar HTTP para HTTPS”, ou  
     - “Force HTTPS”, ou  
     - “Redirects”
   - Ative **redirecionar HTTP para HTTPS** para que quem digitar `http://` seja levado para `https://`.

5. **TLS 1.2+**
   - Na Hostinger, os planos atuais já usam TLS 1.2 ou superior por padrão.
   - Se tiver opção de “TLS” ou “Protocolos SSL”, deixe pelo menos **TLS 1.2** habilitado (e 1.3 se existir).

**Como conferir:** Abra no navegador `https://seudominio.com.br`. O endereço deve mostrar o cadeado e não dar aviso de “conexão não segura”.

---

### Se você usa outra hospedagem (cPanel, Plesk, etc.)

- **cPanel:** Procure por **SSL/TLS** ou **Let’s Encrypt**. Instale o certificado para o domínio e ative redirecionamento HTTP → HTTPS se existir a opção.
- **Plesk:** Domínios → seu domínio → **SSL/TLS** → instalar certificado e redirecionar para HTTPS.
- **Servidor próprio (VPS):** Configure SSL no Nginx ou Apache (ex.: certificado Let’s Encrypt com Certbot) e desabilite TLS 1.0/1.1, mantendo TLS 1.2 (e 1.3).

---

## 2. Webhook no painel do Mercado Pago e no .env

**O que é:** O Mercado Pago envia notificações (webhooks) para uma URL do seu site quando o status de um pagamento muda. Você precisa cadastrar essa URL no painel do MP e colocar o “secret” no `.env`.

**Onde fazer:**  
- URL e eventos → no **painel do Mercado Pago** (Suas integrações).  
- Secret → no arquivo **.env** do projeto (no servidor de produção).

### Passo 2.1 – Descobrir a URL do webhook

A URL é sempre:

```text
https://SEU_DOMINIO/webhook/mercadopago
```

Exemplos:
- Se o site é `https://proticketsports.com.br` → `https://proticketsports.com.br/webhook/mercadopago`
- Se é `https://www.seudominio.com.br` → `https://www.seudominio.com.br/webhook/mercadopago`

Use o domínio exatamente como o usuário acessa o site (com ou sem `www`, conforme configurado).

---

### Passo 2.2 – Cadastrar o webhook no painel do Mercado Pago

1. **Entrar no painel de desenvolvedores**
   - Acesse: https://www.mercadopago.com.br/developers/panel/app  
   - Faça login com a conta que usa para receber os pagamentos.

2. **Abrir sua aplicação**
   - Clique na aplicação que está ligada ao ProTicket (a que usa as chaves que estão no seu `.env`).

3. **Ir em Webhooks**
   - No menu da aplicação, procure **Webhooks** (pode estar em “Produção”, “Configurações” ou “Notificações”).

4. **Cadastrar a URL**
   - Onde pedir **URL de notificação** ou **Endpoint**, cole:  
     `https://SEU_DOMINIO/webhook/mercadopago`  
     (troque SEU_DOMINIO pelo seu domínio real).
   - Se pedir **eventos**, marque pelo menos **Pagamentos** (payments).
   - Salve.

5. **Pegar o “Secret” (chave de verificação)**
   - Após salvar, o painel deve mostrar um **Secret** ou **Chave de verificação** do webhook.
   - **Copie e guarde** esse valor; você vai colar no `.env` no próximo passo.
   - Se não aparecer, procure por “Ver secret” ou “Webhook secret” na mesma tela de Webhooks.

---

### Passo 2.3 – Colocar o secret no .env (no servidor)

1. **No servidor (Hostinger ou outro)**
   - Abra o arquivo **.env** da sua aplicação.  
   - Na Hostinger: File Manager → pasta do projeto (onde está o Laravel) → arquivo `.env`.  
   - Ou edite por FTP/SSH no mesmo caminho.

2. **Adicionar ou ajustar a linha do webhook**
   - Adicione uma destas linhas (use o nome que você já usa para o Mercado Pago no projeto):

   ```env
   MERCADOPAGO_WEBHOOK_SECRET=o_valor_que_voce_copiou_do_painel
   ```

   Ou, se no seu `.env` você usa o nome com underline:

   ```env
   MERCADO_PAGO_WEBHOOK_SECRET=o_valor_que_voce_copiou_do_painel
   ```

   O projeto aceita os dois nomes; o importante é que o **valor** seja exatamente o Secret que o painel do MP mostrou.

3. **Salvar o .env**
   - Salve o arquivo. Em hospedagem compartilhada não é necessário reiniciar nada; o Laravel lê o `.env` a cada requisição.

**Resumo:**  
- **Onde cadastrar a URL:** Painel MP → Suas integrações → sua app → Webhooks.  
- **Onde colocar o secret:** Arquivo `.env` no servidor (variável `MERCADOPAGO_WEBHOOK_SECRET` ou `MERCADO_PAGO_WEBHOOK_SECRET`).

---

## 3. Medir novamente (Qualidade da integração)

**O que é:** Depois de ter HTTPS ativo e o webhook configurado, você pede ao Mercado Pago para medir de novo a qualidade da integração. A meta é atingir pelo menos 73 pontos.

**Onde fazer:** No **painel do Mercado Pago**, em **Suas integrações** → **Qualidade**.

### Passo a passo

1. **Garantir que já fez:**
   - Site em **HTTPS** (passo 1).
   - **Webhook** cadastrado e **secret** no `.env` (passo 2).
   - **Deploy** das últimas alterações do projeto (código com notification_url, device_id, items, etc.) já publicado em produção.

2. **Fazer um pagamento de teste em produção (recomendado)**
   - Acesse seu site em **https://**.
   - Faça um fluxo completo até gerar um pagamento (PIX ou cartão) em modo **produção** (credenciais de produção no `.env`).
   - Isso ajuda o MP a “enxergar” sua integração com os novos dados (device_id, notification_url, items).

3. **Abrir a página de Qualidade**
   - Acesse: https://www.mercadopago.com.br/developers/panel/app  
   - Clique na sua **aplicação**.
   - No menu da aplicação, clique em **Qualidade** (ou “Medição de qualidade da integração”).

4. **Medir novamente**
   - Na tela de Qualidade deve aparecer algo como **“Medir novamente”** ou **“Nova medição”**.
   - Clique nesse botão.
   - O Mercado Pago vai analisar a integração de novo (pode usar o último pagamento produtivo, por exemplo).

5. **Conferir o resultado**
   - A nova pontuação aparece na mesma tela (ex.: “28 de 100” → após correções pode subir).
   - **Meta:** pelo menos **73 pontos** para aprovar.
   - Se ainda faltar ponto, a própria tela lista as “ações pendentes”; compare com o que já implementamos no código e com este guia (SSL, webhook, etc.).

---

## Resumo rápido

| O que fazer              | Onde fazer                          |
|--------------------------|-------------------------------------|
| **SSL + HTTPS**          | Painel da **hospedagem** (ex.: Hostinger → SSL → ativar e forçar HTTPS). |
| **URL do webhook**       | **Painel MP** → Suas integrações → sua app → Webhooks → cadastrar URL. |
| **Secret do webhook**    | Arquivo **.env** no **servidor** (`MERCADOPAGO_WEBHOOK_SECRET=...`). |
| **Medir novamente**      | **Painel MP** → Suas integrações → sua app → **Qualidade** → botão “Medir novamente”. |

Se quiser, na próxima vez que subir alterações para produção, podemos revisar juntos o checklist (HTTPS, .env, webhook e medição).
