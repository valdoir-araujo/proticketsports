# Segurança e Robustez – ProTicketSports

Este documento resume a **auditoria de segurança e robustez** do site, com foco em **pagamentos**, **confirmação de inscrições** e **preparação para alto volume**. As validações críticas devem ocorrer **sempre no backend**; o navegador é apenas camada de UX.

---

## 1. Resumo executivo

- **Pagamento e inscrição:** Ajustes aplicados para garantir que valores e dono do pedido/inscrição sejam validados no servidor, que estoque não fique negativo e que rotas sensíveis tenham throttling e (quando aplicável) exclusão de CSRF.
- **Recomendações adicionais:** Form Requests para endpoints de pagamento, idempotência opcional, filas para webhooks/emails e boas práticas de deploy (cache, PHP/MySQL, filas).

---

## 2. Onde as validações ocorrem

### 2.1 Backend (obrigatório para segurança)

| Área | O que é validado no servidor |
|------|------------------------------|
| **Inscrição (criar/editar)** | `StoreInscricaoRequest` / `UpdateInscricaoRequest` – evento, categoria, produtos, quantidades. Autorização de edição: dono da inscrição. |
| **Pagamento inscrição** | `PagamentoController::process`: dono do atleta ou quem paga o grupo (403 se não for). **Valor cobrado:** sempre `$inscricao->valor_pago` (nunca valor vindo do cliente). |
| **Pagamento loja** | `LojaCheckoutController::processarPagamento`: dono do pedido (sessão `checkout_user_id` ou usuário autenticado). Valor: sempre `$pedido->valor_total` do banco. |
| **Sucesso/pendente loja** | Apenas o dono do pedido pode acessar as telas de sucesso e pendente. |
| **Webhook MP** | Assinatura verificada (`verifyWebhookSignature`). Rota excluída de CSRF para o Mercado Pago conseguir enviar POST. |
| **Estoque** | Antes de dar baixa no estoque (pagamento inscrição ou webhook), o código verifica `limite_estoque >= quantidade`. Se não houver estoque, a transação é revertida e a confirmação não é aplicada. |

### 2.2 Navegador (apenas UX)

- Validação de campos obrigatórios e formato (ex.: CPF, email) nos formulários.
- Mensagens de erro e desabilitação de botões para evitar duplo envio.
- **Nunca** confie em valor de pagamento, lista de itens ou “quem é o dono” apenas com base no que o front envia; isso é sempre recalculado/verificado no backend.

---

## 3. Correções aplicadas nesta auditoria

### 3.1 Valor do pagamento (inscrição)

- **Problema:** O valor da transação era lido de `$data['transaction_amount']` (vindo do cliente).
- **Correção:** Em `MercadoPagoService::processPayment()` o valor passou a ser **sempre** `(float) $inscricao->valor_pago`.
- **Arquivo:** `app/Services/MercadoPagoService.php`.

### 3.2 Autorização na loja (pagamento e telas de resultado)

- **Problema:** Qualquer um que soubesse o `pedido_id` poderia chamar `processarPagamento` ou abrir sucesso/pendente.
- **Correção:** Em `processarPagamento`, `sucesso` e `pendente` é verificado se o “usuário de checkout” (sessão ou auth) é o dono do pedido (`pedido->user_id`). Caso contrário: 403 (processar) ou redirecionamento com mensagem (sucesso/pendente).
- **Arquivo:** `app/Http/Controllers/LojaCheckoutController.php`.

### 3.3 Estoque nunca negativo

- **Problema:** A baixa de estoque era feita com `decrement` sem garantir que `limite_estoque >= quantidade`, podendo ficar negativo em picos de concorrência.
- **Correção:** Dentro da mesma transação, após `lockForUpdate()` no produto, é verificado `limite_estoque >= quantidade`. Se não houver estoque, é lançada exceção e a transação é revertida (não confirma inscrição e não decrementa).
- **Arquivos:** `app/Http/Controllers/PagamentoController.php` (bloco de estoque no `process` e no `webhook`).

### 3.4 Throttle e CSRF

- **Pagamento inscrição:** Rota `pagamento.process` com throttle `30,1` (30 requisições por minuto por usuário).
- **Webhook Mercado Pago:** Rota `webhook/mercadopago` excluída da verificação CSRF em `bootstrap/app.php` (`validateCsrfTokens(except: ['webhook/mercadopago'])`), para o servidor do MP conseguir enviar POST.
- **Rotas:** `routes/web.php` e `bootstrap/app.php`.

---

## 4. Throttling já existente

| Rota / grupo | Limite |
|--------------|--------|
| `pagamento.processar` (loja) | 30 req/min |
| `pagamento.process` (inscrição) | 30 req/min |
| `webhook.mercadopago` | 120 req/min |
| Login (RateLimiter no Request) | 5 tentativas |
| Verificação de email | 6 req/min |

---

## 5. Transações e locks (integridade)

- **Inscrição gratuita:** `DB::transaction` + `lockForUpdate()` em produtos e verificação de estoque antes de decrementar.
- **Pagamento inscrição (aprovado):** Baixa de estoque em `DB::transaction` com `lockForUpdate()` e verificação de estoque.
- **Webhook:** Confirmação e baixa de estoque em `DB::transaction` com `lockForUpdate()` e verificação de estoque.
- **Criação de inscrição:** `DB::transaction` e `lockForUpdate()` em produtos ao validar/reservar.
- **Loja:** Valor do pagamento sempre do banco (`$pedido->valor_total`); criação de pedido com transação.

---

## 6. Recomendações para alto volume e estabilidade

### 6.1 Banco de dados

- Índices já usados em consultas críticas: `status`, `evento_id + status`, `codigo_grupo`, `transacao_id_gateway`, `codigo_grupo_parcial`.
- Em produção, monitorar consultas lentas (slow query log) e adicionar índices conforme necessidade (ex.: filtros por data, evento, usuário).

### 6.2 Filas (queues)

- Envio de e-mails (ex.: confirmação de inscrição) e processamento pesado do webhook podem ser colocados em **filas** (ex.: `database` ou Redis) para não travar a resposta HTTP e absorver picos.
- Exemplo: após confirmar inscrição no webhook, despachar um job `EnviarEmailInscricaoConfirmada` em vez de enviar o e-mail de forma síncrona.

### 6.3 Cache

- Dados que mudam pouco (ex.: lista de eventos públicos, categorias por evento) podem usar `Cache::remember()` com TTL curto para reduzir carga no banco em listagens.
- Não cachear valores de pedido/inscrição no fluxo de pagamento; sempre ler do banco.

### 6.4 Deploy e infraestrutura

- **PHP:** `opcache` habilitado; versão estável (ex.: 8.2).
- **MySQL/MariaDB:** Connection pool e limites de conexão adequados; timeouts e `max_connections` ajustados para pico.
- **Web server:** Manter limite de requisições simultâneas e timeouts alinhados ao tempo médio das ações (pagamento, criação de inscrição).
- **HTTPS:** Obrigatório em produção; cartão (Brick) do Mercado Pago exige HTTPS.

### 6.5 Idempotência (opcional, próximo passo)

- **Loja:** O idempotency key atual é gerado por requisição (`uniqid`), então retentativas podem gerar mais de um pagamento. Para evitar cobrança duplicada em duplo clique ou retry, considerar chave por `pedido_id` + sessão e rejeitar requisições duplicadas.
- **Inscrição:** Similar: aceitar um idempotency key (ex.: header) por inscrição e recusar processamento duplicado com a mesma chave.

---

## 7. Checklist rápido de segurança

- [x] Valor de pagamento (inscrição) sempre do servidor (`$inscricao->valor_pago`).
- [x] Valor de pagamento (loja) sempre do servidor (`$pedido->valor_total`).
- [x] Dono do pedido verificado em processarPagamento, sucesso e pendente (loja).
- [x] Dono da inscrição verificado em show, process, sucesso e falha (inscrição).
- [x] Webhook com verificação de assinatura.
- [x] Webhook excluído de CSRF.
- [x] Estoque com lock e verificação antes de decrementar (evitar negativo).
- [x] Throttle em rotas de pagamento e webhook.
- [ ] Form Requests dedicados para `processarPagamento` e `process` (recomendado).
- [ ] Idempotência em pagamentos (recomendado para evitar duplicidade em retries).

---

## 8. Riscos residuais e mitigações

### 8.1 Criação em massa de inscrições (DoS / enchimento de banco)

- **Risco:** Um atacante com sessão válida (após identificação) poderia enviar muitos POST para `inscricao.store` (vários eventos diferentes) e gerar muitas inscrições, sobrecarregando o banco ou a aplicação.
- **Mitigação aplicada:** Throttle na rota `inscricao.store` (15 requisições por minuto por IP). A regra "já está inscrito" (um atleta por evento) continua no backend.
- **Recomendação:** Monitorar picos de criação de inscrições; em eventos muito grandes, considerar limite por atleta (ex.: máximo de N inscrições pendentes por hora).

### 8.2 WebhookController (código legado)

- **Risco:** Existe um `WebhookController` que confirma inscrição **sem** verificar assinatura do Mercado Pago e **sem** baixa de estoque. Se uma rota apontar para esse controller, qualquer um poderia enviar POST e confirmar inscrições falsas.
- **Situação atual:** Nenhuma rota em `routes/web.php` usa esse controller; o webhook em uso é `PagamentoController::webhook` (com assinatura e estoque).
- **Recomendação:** Não criar rotas para `WebhookController::handle`. Preferir remover a classe ou deixá-la claramente marcada como legado/não utilizado.

### 8.3 Brute-force de cupom

- **Risco:** Tentativas de adivinhar códigos de cupom em `inscricao.cupom.aplicar` ou no fluxo de grupo.
- **Mitigação:** Cupom é validado no backend (existência, evento, ativo, validade, limite de usos). Aplicar cupom exige ser dono da inscrição (403 para outros).
- **Recomendação (opcional):** Throttle na rota de aplicar cupom (ex.: 10 por minuto por usuário) para dificultar tentativa e erro em massa.

### 8.4 Confirmação de inscrição sem pagamento

- **Quem pode marcar como “confirmada”?** Apenas:
  - **Pagamento aprovado:** `PagamentoController::process` (dono da inscrição) + gateway; ou `PagamentoController::webhook` (assinatura MP).
  - **Inscrição gratuita:** `PagamentoController::show` (dono da inscrição, valor_pago ≤ 0).
  - **Cortesia:** `EventoOrganizadorController::confirmarCortesia` (autorização `update` no evento = organizador dono).
- Não existe rota pública ou API que permita a um atacante “confirmar” uma inscrição alheia enviando um POST simples.

---

## 9. Contato do organizador e dados sensíveis

- Telas de inscrição/pagamento exibem apenas o que o usuário tem direito a ver (dono da inscrição ou do pedido).
- Dados de cartão não passam pelo seu servidor (processados pelo Brick do Mercado Pago).
- Tokens e chaves do Mercado Pago devem ficar apenas em variáveis de ambiente (`.env`), nunca no código ou no front.

Este documento deve ser revisado quando houver mudanças em fluxos de pagamento, inscrição ou webhooks.
