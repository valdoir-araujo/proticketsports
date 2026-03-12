# Como fazer o site aparecer no Google e captar mais clientes

Este guia reúne o que **fazer no site** (já implementado ou a configurar) e **ações de marketing** para atrair organizadores e atletas.

---

## Parte 1: O que já foi feito no site (SEO técnico)

- **Meta description** e **Open Graph** (Facebook/WhatsApp/Google) nos layouts `public` e `app`, para todas as páginas.
- **Sitemap dinâmico:** a URL `/sitemap.xml` lista a home, eventos, campeonatos, parceiros, contato e cada evento/campeonato público. O Google usa isso para descobrir e indexar páginas.
- **Robots.txt dinâmico:** a URL `/robots.txt` permite todos os robôs e informa onde está o sitemap: `Sitemap: https://SEU-DOMINIO/sitemap.xml` (usa o `APP_URL` do `.env`).

**O que você precisa fazer:**
- No **.env** (produção), deixar `APP_URL` com o domínio final, com `https://` (ex.: `https://proticketsports.com.br`). Assim o sitemap e os links canônicos ficam corretos.

---

## Parte 2: Fazer o site aparecer no Google (passo a passo)

### 1. Google Search Console (obrigatório)

1. Acesse: https://search.google.com/search-console  
2. Adicione a **propriedade** com a URL do seu site (ex.: `https://seudominio.com.br`).  
3. **Comprove a propriedade** por um dos métodos (arquivo HTML no site, meta tag no `<head>`, DNS ou Google Analytics).  
4. Depois de verificado, em **“Sitemaps”** informe: `https://seudominio.com.br/sitemap.xml` e envie.  
5. Em **“Inspeção de URL”** você pode pedir indexação de páginas importantes (home, /eventos, /campeonatos).

O Google passa a usar o sitemap e, com o tempo, indexa suas páginas. A indexação não é instantânea (pode levar dias ou semanas).

### 2. Conteúdo que o Google valoriza

- **Título e descrição por página**  
  As views já usam `@section('title')`. Para páginas importantes (home, lista de eventos, página de cada evento), preencha também `@section('meta_description')` com 1–2 frases claras (ex.: “Inscrições para a Corrida X em São Paulo. Data, percurso, categorias e valor.”).  
  Na página do evento, use o nome do evento e a cidade no título e na descrição.

- **URLs amigáveis**  
  Eventos já usam `slug` (ex.: `/eventos/corrida-sao-paulo-2025`). Mantenha slugs curtos e com palavras-chave (nome do evento, cidade, ano).

- **HTTPS**  
  Em produção, use sempre HTTPS (certificado SSL). O Google prioriza sites seguros.

- **Velocidade e celular**  
  Site responsivo e rápido ajudam no ranking. Evite muitas imagens pesadas na home; use redimensionamento/otimização de imagens.

### 3. Não bloquear o Google

- Não coloque área pública (home, eventos, campeonatos, contato) em `Disallow` no `robots.txt`. O `robots.txt` atual está correto (permite tudo e só indica o sitemap).  
- Páginas que exigem login (painel do organizador, área do atleta) não precisam ser indexadas; pode bloquear só essas rotas no `robots.txt` se quiser (opcional).

---

## Parte 3: Captar mais clientes (organizadores e atletas)

### Para atrair **organizadores** (quem cria eventos)

1. **Palavras-chave no site**  
   Use no texto da home, na página “Contato” e em qualquer página “Para organizadores” ou “Planos”:  
   - “plataforma de inscrição para corridas”, “inscrições para eventos esportivos”, “gestão de eventos esportivos”, “check-in de corrida”, “resultados de corrida”.

2. **Landing / página “Para organizadores”**  
   Uma página que explique: inscrições online, pagamento (PIX/cartão), check-in, resultados, repasse, suporte. Inclua um CTA “Solicitar contato” ou “Criar meu evento” que leve ao cadastro ou ao contato.

3. **Google Ads (opcional)**  
   Campanhas com palavras como “inscrição corrida online”, “plataforma inscrição eventos esportivos”, “sistema inscrição corrida”. Direcione para a home ou para a página “Para organizadores”.

4. **Redes sociais e parcerias**  
   Instagram/Facebook com dicas para organizadores, casos de uso (“Evento X usou o ProTicketsports”) e parcerias com assessorias, federações ou lojas de corrida.

5. **Indicação e primeiros clientes**  
   Ofereça condições especiais ou suporte próximo para os primeiros 5–10 organizadores; peça depoimento e uso do nome na divulgação.

### Para atrair **atletas** (quem se inscreve)

1. **Cada evento é uma porta de entrada**  
   Quando um organizador publica um evento no seu site, a página do evento pode ranquear no Google (ex.: “Corrida São Paulo 2025”). Garanta que a página do evento tenha título e meta description com nome do evento + cidade + ano.

2. **Compartilhamento pelo organizador**  
   Oriente os organizadores a divulgarem o link do evento (site/redes/WhatsApp). Quanto mais tráfego e compartilhamentos, melhor para SEO e para novos atletas conhecerem a plataforma.

3. **Calendário de eventos**  
   A página `/eventos` (lista/calendário) pode ranquear para buscas como “corridas 2025”, “eventos esportivos [cidade]”. Mantenha títulos e descrições claros e atualizados.

4. **Conteúdo (blog ou notícias)**  
   Se criar uma área de notícias/blog, use textos como “Como se inscrever na corrida X”, “Calendário de corridas em SP”. Isso atrai buscas e mostra que o site é atual.

### Ferramentas úteis (gratuitas)

- **Google Search Console** – ver como o Google vê o site, erros e desempenho.  
- **Google Analytics 4** – ver quantas pessoas visitam, de onde vêm e quais páginas são mais vistas.  
- **Bing Webmaster Tools** – mesmo conceito do Search Console, para o Bing.

---

## Checklist rápido

| Ação | Feito? |
|------|--------|
| `APP_URL` em produção com `https://` e domínio correto | |
| Google Search Console: propriedade adicionada e verificada | |
| Sitemap enviado no Search Console (`/sitemap.xml`) | |
| Meta description nas páginas principais (home, eventos, evento) | |
| HTTPS em produção | |
| Página “Para organizadores” ou landing com CTA | |
| Google Analytics (ou similar) instalado | |
| Divulgação em redes + pedir aos organizadores que divulguem o link do evento | |

---

Resumindo: **para aparecer no Google**, o site já tem sitemap e robots corretos; falta configurar o **Search Console**, enviar o sitemap e melhorar **títulos e descrições** nas páginas principais. **Para captar clientes**, combine SEO (palavras-chave, conteúdo) com divulgação direta (redes, parcerias, primeiros organizadores e indicação).
