# Deploy em produção (Check-in, Dashboard, Menus)

## Por que a página do Dashboard (ou outras) fica “quebrada”?

O layout usa **Vite** para carregar Tailwind e Alpine (`public/build/`). Se no servidor **não existir** a pasta `public/build/` (porque `npm run build` não foi rodado ou a pasta não foi enviada), o CSS e o JS não carregam e a página aparece sem estilo (layout quebrado).

**Solução recomendada:** rodar `npm run build` no servidor (ou no seu PC e depois subir a pasta `public/build/`).

**Fallback:** Foi adicionado um fallback no layout: se `public/build/manifest.json` não existir, o site carrega Tailwind e Alpine por CDN. A página deixa de “quebrar”, mas o visual pode ser um pouco diferente do build local. O ideal ainda é ter o build no servidor.

### Se o problema for só no site hospedado e só no desktop

Isso costuma ser **cache do navegador no desktop**: o PC está guardando uma versão antiga da página (que pedia os arquivos do Vite e recebia 404). No celular o cache é outro, então pode parecer que “só no desktop quebra”.

**O que fazer no desktop:**

1. **Atualização forçada:** `Ctrl+Shift+R` (Windows/Linux) ou `Cmd+Shift+R` (Mac) na página do dashboard.
2. **Testar sem cache:** abrir o site em uma **aba anônima/privada** e acessar de novo o dashboard.
3. **Conferir no servidor:** garantir que o layout com fallback está em produção (o Blade que verifica `public/build/manifest.json` e usa CDN quando não existir).
4. **Console (F12):** na aba **Rede**, recarregar a página e ver se aparecem 404 em `manifest.json` ou em arquivos em `/build/assets/`. Se aparecer, o HTML que está vindo ainda é o antigo (cache). Hard refresh ou aba anônima devem resolver.
5. **Extensões:** no desktop, desativar temporariamente bloqueadores de anúncio/script; às vezes eles bloqueiam o Alpine ou o Tailwind por CDN.

Solução definitiva em produção: rodar `npm run build` e enviar a pasta `public/build/` para o servidor.

---

## Perfil: Strava e foto no celular (produção)

**Strava não conecta / erro "redirect_uri invalid"**

- O Strava exige que a **URL de callback** enviada na autorização seja **exatamente** o domínio configurado no painel.
- No **servidor** (.env), defina uma das opções:
  - `APP_URL=https://www.proticketsports.com.br` (sem barra no final), **ou**
  - `STRAVA_REDIRECT_URI=https://www.proticketsports.com.br/strava/callback` (recomendado em produção).
- No [painel do Strava](https://www.strava.com/settings/api) → **My API Application** → **Authorization Callback Domain** coloque **só o domínio**, sem `https://` e sem caminho:
  - `www.proticketsports.com.br`
- A URL completa de callback deve ser exatamente: `https://www.proticketsports.com.br/strava/callback` (sem barra no final). Se o site usar outro domínio (ex.: sem www), use esse domínio no Strava e no .env.

**Foto de perfil não sobe no smartphone**

- O upload passou a usar um `<label for="foto">` no avatar: o toque na foto abre o seletor nativo (câmera/galeria) sem depender de clique por JavaScript.
- O backend aceita até 2 MB e formato webp. Se ainda falhar, verifique no servidor: `upload_max_filesize` e `post_max_size` no PHP (recomendado ≥ 4M).

---

## 1. Deploy do código
- Faça pull do repositório (ou envie os arquivos atualizados).
- As views ficam em `resources/views/organizador/eventos/checkin.blade.php`.

## 2. Build do frontend (Alpine.js + Tailwind)
O menu hambúrguer e a interação da página dependem do JavaScript compilado.

No servidor, na pasta do projeto:
```bash
npm ci
npm run build
```

Ou localmente e depois suba a pasta `public/build/` (se não estiver no .gitignore).

## 3. Limpar cache do Laravel
```bash
php artisan view:clear
php artisan cache:clear
```

## 4. Conferir se a nova versão está no ar
- Abra a página de check-in do evento.
- Clique com o botão direito na página → **Inspecionar** (ou Ver código-fonte).
- No HTML, procure por `data-checkin-version="persist-v2"` no início do conteúdo.
- Se aparecer, a view nova está sendo servida.

## 5. Testar persistência da busca
- Na página de check-in, digite algo no campo de busca (ex.: um nome).
- Recarregue a página (F5).
- O texto da busca deve voltar sozinho.

Se nada disso funcionar, verifique:
- Se o navegador não está em cache (teste com Ctrl+Shift+R ou em aba anônima).
- No console do navegador (F12 → Console) se há erros de JavaScript.
- Se a URL do site é **HTTPS** (necessário para a câmera do QR no celular).
