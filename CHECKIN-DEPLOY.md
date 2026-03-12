# Check-in: o que conferir no servidor

Para as alterações do check-in e do menu funcionarem em produção:

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
