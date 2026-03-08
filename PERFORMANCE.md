# Performance – ProTicketSports

Este documento resume **boas práticas já adotadas** e **melhorias recomendadas** para o site aguentar pico de acessos e inscrições sem ficar lento ou cair.

---

## 1. O que já está bem

| Prática | Onde |
|--------|------|
| **Paginação** | Lista de eventos públicos (12 por página), dashboard atleta (5 inscrições), lista de eventos do organizador (10), inscrições no show do evento (10), lançamentos e repasses. |
| **Eager loading** | Uso consistente de `->with()` em listas de inscrições (atleta.user, categoria, equipe, resultado), eventos (cidade.estado, percursos.categorias), evitando N+1 na maioria dos fluxos. |
| **Índices no banco** | Colunas como `status`, `evento_id`, `data_evento`, `codigo_grupo`, índice composto `(evento_id, status)` em inscrições, etc. |
| **E-mail em fila** | `InscricaoRecebida` implementa `ShouldQueue`; com worker rodando, o envio não trava a resposta. |
| **Throttle** | Rotas de pagamento e webhook com limite de requisições por minuto. |
| **Vite** | Assets compilados e versionados; um único bundle (pode evoluir para code-splitting em telas pesadas). |

---

## 2. Melhorias já aplicadas nesta revisão

| Melhoria | Onde |
|----------|------|
| **Cache de estados e cidades** | `LocationController::getEstados()` e `getCidades()` usam `Cache::remember` (1 hora). Reduz consultas em formulários e filtros que usam a API. |
| **Cache da lista de estados (filtro)** | Na listagem pública de eventos, a lista de estados do filtro usa cache de 30 min (`public_eventos_estados`). |
| **Limite em listas grandes** | Página pública de **inscritos** e de **resultados** do evento passam a usar `limit(2000)` e `limit(3000)` para evitar carregar dezenas de milhares de linhas de uma vez. Para eventos com mais inscritos, a recomendação é implementar paginação na view (ver abaixo). |

---

## 3. Recomendações para produção e alto volume

### 3.1 Cache

- **Driver:** Em produção, use `CACHE_STORE=redis` (ou `memcached`) em vez de `database`. Redis reduz carga no MySQL e é mais rápido para leitura.
- **Configuração:** No `.env`: `CACHE_STORE=redis` e configurar conexão Redis em `config/cache.php` e `config/database.php`.
- **O que cachear (opcional):**
  - Lista pública de eventos: TTL curto (1–2 min) se a atualização em tempo real não for crítica.
  - Página de detalhe do evento (show público): TTL 1–5 min para eventos publicados.
- **O que não cachear:** Páginas de pagamento, checkout, painel do organizador (dados em tempo real).

### 3.2 Sessão

- **Driver:** `SESSION_DRIVER=database` adiciona uma leitura/escrita de sessão a cada request. Em alto volume, use `SESSION_DRIVER=redis` (ou `file` se não tiver Redis) para reduzir carga no banco.
- **Lifetime:** Ajuste `SESSION_LIFETIME` conforme necessidade (ex.: 120 minutos).

### 3.3 Filas (queue)

- **Produção:** Mantenha `QUEUE_CONNECTION=database` (ou `redis`) e rode um ou mais workers:  
  `php artisan queue:work --tries=3`
- **Supervisor:** Use Supervisor (ou equivalente) para manter o worker ativo e reiniciar em caso de falha.
- **E-mails:** O Mailable `InscricaoRecebida` já é enfileirado quando `ShouldQueue` está implementado; garanta que o worker está rodando para não enviar em modo síncrono.

### 3.4 Banco de dados

- **Conexões:** Ajuste `DB_CONNECTION` e pool de conexões conforme o provedor (ex.: Hostinger). Evite esgotar conexões em pico.
- **Exportação de inscritos:** Em eventos com muitos inscritos, `exportarInscritos` carrega tudo com `->get()`. Para 10k+ linhas, considere `->chunkById(500, function ($inscricoes) { ... })` e escrever no stream em blocos para reduzir pico de memória.
- **Check-in / numeração:** Telas que listam todas as inscrições do evento com `->get()` podem ficar pesadas. Avalie paginação ou carregamento sob demanda (ex.: infinite scroll ou “carregar mais”).

### 3.5 Listas públicas muito grandes (inscritos / resultados)

- **Situação atual:** Foram colocados limites (2000 inscritos, 3000 resultados) para evitar pico de memória e tempo de resposta.
- **Próximo passo:** Para eventos com mais que isso, implementar **paginação** (ou “carregar mais”) na view e na query, mantendo filtros por categoria/percurso se existirem.

### 3.6 Front-end

- **Imagens:** Usar imagens otimizadas (WebP quando possível) e `loading="lazy"` em listagens (ex.: cards de eventos).
- **Vite:** Para telas com muito JS (ex.: formulário de inscrição com muitas etapas), considerar code-splitting (lazy load de componentes) no futuro.
- **CSS/JS:** Manter um único entry point está ok; verificar se não há bibliotecas pesadas carregadas em toda a aplicação.

### 3.7 Servidor e PHP

- **OPcache:** Manter habilitado em produção (`opcache.enable=1`) com tamanho e revalidate adequados.
- **PHP:** Versão estável (ex.: 8.2) e limite de memória (`memory_limit`) compatível com as operações mais pesadas (exportação, relatórios).
- **Timeout:** Timeout de request (PHP e servidor web) alinhado ao tempo máximo aceitável para as ações mais lentas (ex.: exportação grande).

---

## 4. Checklist rápido

- [x] Paginação nas listagens principais
- [x] Eager loading nas listas de inscrições/eventos
- [x] Cache para estados/cidades e lista de estados (filtro)
- [x] Limite em listas públicas de inscritos/resultados
- [x] E-mail de inscrição enfileirado (ShouldQueue)
- [ ] Em produção: `CACHE_STORE=redis` (ou memcached)
- [ ] Em produção: `SESSION_DRIVER=redis` (ou file) se sessão em DB for gargalo
- [ ] Worker de fila rodando (Supervisor)
- [ ] OPcache habilitado
- [ ] Para eventos com > 2k inscritos: paginação na lista pública de inscritos/resultados
- [ ] Exportação de inscritos com chunk para eventos muito grandes

---

## 5. Monitoramento

- **Slow query log:** Habilitar no MySQL/MariaDB e revisar consultas lentas; adicionar índices quando fizer sentido.
- **Log de erros:** Revisar `storage/logs` e erros 500; tempo de resposta em rotas de pagamento e inscrição.
- **Fila:** Monitorar tamanho da fila e jobs falhos (`php artisan queue:failed`); usar retry e dead-letter conforme necessidade.

Com essas práticas e evoluções, o site fica bem preparado para pico de acessos e inscrições, mantendo resposta rápida e estabilidade.
