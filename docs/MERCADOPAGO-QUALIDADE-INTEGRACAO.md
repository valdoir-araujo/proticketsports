# Qualidade da integração Mercado Pago

Este documento descreve o que foi implementado para melhorar a pontuação em **Suas integrações → Qualidade** e o que ainda depende de configuração no servidor.

## O que foi implementado no código

### 1. Identificador do dispositivo (Device ID) — **obrigatório**
- **Frontend:** Script oficial `https://www.mercadopago.com/v2/security.js` (view="checkout") e **SDK MercadoPago.JS V2** carregados no **&lt;head&gt;** do layout público (`layouts/public.blade.php`), para que todas as páginas de pagamento tenham SDK e Device ID disponíveis desde o carregamento (exigência da medição de qualidade).
- O script expõe a variável global `MP_DEVICE_SESSION_ID`.
- O valor é enviado no payload como `device_id` (PIX e cartão) e no backend é repassado no header **X-Meli-Session-Id** na criação do pagamento (inscrição e loja).
- **Arquivos:** `resources/views/layouts/public.blade.php`, `resources/views/pagamento/show.blade.php`, `resources/views/loja/pagamento.blade.php`, `app/Services/MercadoPagoService.php`, `app/Http/Controllers/LojaCheckoutController.php`.

### 2. Notificações Webhook — **obrigatório**
- O campo **notification_url** é enviado em **todos** os pagamentos (PIX e cartão, inscrição e loja).
- A URL aponta para a rota `webhook.mercadopago` (POST `/webhook/mercadopago`).
- Em produção a URL é forçada para HTTPS quando `APP_URL` for HTTP.
- **Arquivos:** `app/Services/MercadoPagoService.php` (método `getWebhookUrl()`), `app/Http/Controllers/LojaCheckoutController.php`.

### 3. Itens do pagamento (items) — **recomendado**
- **additional_info.items** é enviado com: `id`, `title`, `description`, `category_id`, `quantity`, `unit_price`.
- **Inscrição:** um item com categoria `events` representando a inscrição.
- **Loja:** um item por produto do pedido (ou um item resumo se o pedido não tiver itens), categoria `others`.
- **Arquivos:** `app/Services/MercadoPagoService.php` (`buildItemsInscricao()`), `app/Http/Controllers/LojaCheckoutController.php`.

### 4. Descrição na fatura do cartão (statement_descriptor) — **recomendado**
- O campo **statement_descriptor** é enviado como `PROTICKET` em todos os pagamentos (PIX e cartão, inscrição e loja).
- **Arquivos:** `app/Services/MercadoPagoService.php`, `app/Http/Controllers/LojaCheckoutController.php`.

### 5. SDK MercadoPago.JS V2 e Secure Fields (PCI)
- O **SDK MercadoPago.JS V2** é carregado no **&lt;head&gt;** do layout público (antes do conteúdo), para que a ferramenta de qualidade do MP detecte “SDK instalado”.
- O checkout utiliza o **Payment Brick** (`bricksBuilder.create('payment', ...)`), que captura os dados do cartão via **Secure Fields** (iframes do MP), sem que número de cartão ou CVV passem pelo nosso servidor — atendendo PCI Compliance.
- **Importante:** Após publicar essas alterações, faça um **novo pagamento de teste em produção** (cartão ou PIX) e use o **Payment ID** desse pagamento ao clicar em **Medir novamente**. A medição é baseada no último pagamento produtivo; se o ID for antigo, as melhorias podem não ser contabilizadas.

---

## O que você precisa fazer no servidor / painel

### 1. Certificados SSL e TLS 1.2 — **obrigatório**
- O site em produção deve ser servido via **HTTPS** (certificado SSL válido).
- O servidor deve suportar **TLS 1.2 ou superior**.
- **Onde configurar:** no seu provedor de hospedagem (ex.: Hostinger, cPanel, Nginx/Apache). Não é alteração de código.

### 2. Webhook no painel do Mercado Pago
- Em **Suas integrações** → sua aplicação → **Webhooks**, cadastre a URL de produção, por exemplo:  
  `https://seudominio.com.br/webhook/mercadopago`
- Configure o **Webhook secret** e preencha no `.env`:  
  `MERCADOPAGO_WEBHOOK_SECRET=...`  
  (ou a variável que seu `config/services.php` usa para o Mercado Pago.)

### 3. Medir novamente
- Após publicar as alterações e garantir HTTPS + webhook configurado, em **Suas integrações** → **Qualidade** clique em **Medir novamente**.
- A meta é **pelo menos 73 pontos** para aprovar.

---

## Resumo das ações do relatório de qualidade

| Ação                         | Tipo        | Status                                                                 |
|-----------------------------|------------|-------------------------------------------------------------------------|
| Identificador do dispositivo| Obrigatório| Implementado (security.js + device_id + X-Meli-Session-Id)             |
| notification_url            | Obrigatório| Implementado em todos os pagamentos                                   |
| SDK JS V2                   | Obrigatório| Já em uso (sdk.mercadopago.com/js/v2 + Payment Brick)                  |
| Secure Fields / PCI         | Obrigatório| Atendido pelo Payment Brick                                            |
| items (id, title, description, etc.) | Recomendado | Implementado (inscrição e loja)                               |
| statement_descriptor        | Recomendado| Implementado (PROTICKET)                                                |
| Certificados SSL            | Obrigatório| Configurar no servidor (HTTPS)                                         |
| Certificados TLS 1.2+       | Obrigatório| Configurar no servidor                                                 |
