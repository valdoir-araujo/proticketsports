# Mercado Pago: Cartão e Webhook — O que ajustar

## 1. Webhook "assinatura inválida" (não afeta o cartão)

O webhook é usado pelo Mercado Pago para **avisar quando um pagamento foi aprovado** (ex.: PIX pago). Quando a assinatura está inválida, o MP chama sua URL mas nós recusamos a notificação — ou seja, a **inscrição/pedido não é confirmada automaticamente** após o pagamento. O **cartão não funcionar** é outro problema (formulário que não carrega ou não envia).

### Como corrigir a assinatura do webhook

1. Acesse **Suas integrações**: https://www.mercadopago.com.br/developers/panel/app  
2. Selecione a **aplicação** que o ProTicket usa.  
3. Vá em **Webhooks** (menu lateral).  
4. Confirme a **URL de produção** (ex.: `https://seudominio.com.br/webhook/mercadopago`).  
5. Veja a **Assinatura secreta** (Secret) na mesma tela e clique em **Copiar** (ou “Ver secret”).  
6. No servidor, abra o **.env** e ajuste **uma** das linhas (sem aspas, sem espaço no início/fim):
   ```env
   MERCADOPAGO_WEBHOOK_SECRET=valor_que_voce_colou
   ```
   ou
   ```env
   MERCADO_PAGO_WEBHOOK_SECRET=valor_que_voce_colou
   ```
7. Salve o .env. Não é necessário reiniciar o servidor.

Se a assinatura continuar inválida, copie o secret de novo no painel (ele pode ter sido regenerado) e cole de novo no .env, garantindo que não há espaço ou quebra de linha no valor.

---

## 2. Cartão não funciona — O que checar no Mercado Pago e no site

O formulário de cartão (Brick) pode falhar por configuração ou ambiente. Siga na ordem:

### No painel do Mercado Pago (Suas integrações)

1. **Aplicação correta**  
   O ProTicket usa **uma** aplicação. Todas as chaves (Public Key, Access Token, Webhook Secret) devem ser **dessa mesma aplicação**.

2. **Credenciais de produção**  
   - No painel: **Credenciais de produção** (não “Credenciais de teste”).  
   - No **.env** do servidor:
     - `MERCADOPAGO_PUBLIC_KEY` = **Chave pública** de produção.
     - `MERCADOPAGO_ACCESS_TOKEN` = **Access Token** de produção.  
   Se o site estiver em produção e as chaves forem de teste (ou o contrário), o Brick pode dar erro.

3. **Produtos / modo de pagamento**  
   Na aplicação, confirme que está habilitado o que você usa (ex.: **Checkout API / Pagamentos** ou **Checkout Bricks**). Não é necessário mudar nada se o PIX já funciona na mesma aplicação.

4. **URL do webhook (opcional para o cartão)**  
   Só impacta notificações. A URL deve ser a mesma que está no .env e no painel (ex.: `https://seudominio.com.br/webhook/mercadopago`).

### No site (servidor e navegador)

5. **HTTPS**  
   O Brick de cartão costuma **só funcionar em HTTPS**. Em produção o site deve abrir em `https://`. Em `http://` o formulário de cartão pode não carregar; a tela já orienta a usar PIX nesse caso.

6. **Chave pública no front**  
   A mesma **Public Key de produção** usada no .env é a que o front usa para carregar o Brick. O controller já envia essa chave para a view; não precisa alterar código se o .env estiver certo.

7. **Bloqueadores e rede**  
   Extensões (bloqueador de anúncios, privacidade) ou rede podem bloquear scripts do Mercado Pago. Peça para testar em **aba anônima** ou outro navegador.

### Resumo rápido

| Onde | O que conferir |
|------|-----------------|
| Painel MP | Mesma aplicação para todas as chaves; credenciais de **produção**; Webhook com URL correta e secret copiado. |
| .env | `MERCADOPAGO_PUBLIC_KEY` e `MERCADOPAGO_ACCESS_TOKEN` de produção; `MERCADOPAGO_WEBHOOK_SECRET` igual ao do painel (sem espaços). |
| Site | Acesso em **HTTPS** em produção; testar sem bloqueadores. |

Se depois disso o cartão ainda falhar, envie:
- A **URL exata** em que você está (ex.: `https://proticketsports.com.br/...`).
- Se aparece alguma **mensagem de erro** na tela (ex.: “Formulário de cartão não está disponível” ou “Use PIX”).
- Se no navegador (F12 > Aba “Console”) aparece algum **erro em vermelho** ao clicar em “Cartão de Crédito”.

Com isso dá para apontar se o problema é chave, HTTPS, Brick ou rede.
