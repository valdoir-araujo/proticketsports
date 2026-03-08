# Como versionar e subir o site para a Hostinger

**Como usar este arquivo:**  
Este é um guia de texto. Abra este arquivo no Cursor (ele está na raiz do projeto, junto de `app`, `resources`, etc.) e vá fazendo **um passo de cada vez**: leia o passo, abra o terminal na pasta do projeto e rode o comando que está escrito. Não precisa “executar” o arquivo — só seguir as instruções.

---

## Parte 1 – Versionamento (Git) – você faz no PC

### 1. Instalar o Git (se ainda não tiver)
- Baixe: https://git-scm.com/download/win  
- Instale e reinicie o terminal (ou o Cursor).

### 2. Configurar nome e e-mail (uma vez no PC)
O Git exige isso para cada commit. No terminal, rode (troque pelo seu nome e e-mail):

```bash
git config --global user.name "Seu Nome"
git config --global user.email "seu@email.com"
```

### 3. Inicializar o repositório (uma vez)
Abra o terminal na pasta do projeto e rode:

```bash
cd c:\wamp64\www\proticketsports
git init
git add .
git commit -m "Estado inicial do projeto"
```

**Se o terminal disser que "git" não é reconhecido**, use o caminho completo no PowerShell (copie e cole uma linha por vez):

```powershell
& "C:\Program Files\Git\bin\git.exe" config --global user.name "Seu Nome"
& "C:\Program Files\Git\bin\git.exe" config --global user.email "seu@email.com"
cd c:\wamp64\www\proticketsports
& "C:\Program Files\Git\bin\git.exe" init
& "C:\Program Files\Git\bin\git.exe" add .
& "C:\Program Files\Git\bin\git.exe" commit -m "Estado inicial do projeto"
```

### 4. A cada melhoria no site
```bash
git add .
git commit -m "Descrição do que mudou (ex: equipe na inscrição em grupo)"
```

### 5. (Opcional) Usar GitHub para backup e deploy
1. Crie uma conta em https://github.com (se não tiver).
2. Crie um repositório novo (ex.: `proticketsports`), **sem** README.
3. No terminal, na pasta do projeto:

```bash
git remote add origin https://github.com/SEU_USUARIO/proticketsports.git
git branch -M main
git push -u origin main
```

Troque `SEU_USUARIO` pelo seu usuário do GitHub.

---

## Parte 2 – Subir para a Hostinger – você faz no painel/servidor

### Opção A – Enviar arquivos por FTP (mais comum)
1. No painel da Hostinger: **Arquivos** (File Manager) ou use **FileZilla**.
2. Conecte com o usuário e senha FTP da sua hospedagem.
3. Vá até a pasta do site (geralmente `public_html` ou a que a Hostinger indicar).
4. Envie **apenas os arquivos/pastas que mudaram** (por exemplo):
   - `app/`
   - `resources/views/`
   - `routes/`
   - `config/` (se alterou algo)
   - `public/` (se alterou CSS/JS/imagens)

**Importante:** Na Hostinger, a raiz do site costuma ser a pasta `public` do Laravel. Confirme no painel qual pasta está configurada como “raiz do domínio”.

### Opção B – Git no servidor (se tiver SSH)
1. Ative **SSH** na sua conta Hostinger.
2. Conecte por SSH e vá na pasta do site.
3. Rode:
```bash
git pull origin main
php artisan migrate --force
php artisan config:cache
```

---

## Parte 3 – Depois de subir os arquivos (obrigatório)

Sempre que você enviar arquivos novos (por FTP ou Git), **entre no servidor por SSH** e rode estes comandos na **pasta raiz do projeto Laravel** (onde está o `artisan`):

### 1. Rodar as migrations (novas tabelas e colunas do banco)
Cria/atualiza tabelas como: endereço do atleta (CEP, logradouro, etc.), parceiros, contatos, evento_contatos, regulamento em eventos, cupom em pedidos, Strava no atleta, e outras.

```bash
cd /home/u769958563/domains/proticketsports.com.br/public_html
php artisan migrate --force
```

O `--force` é necessário em produção (Hostinger). Se aparecer "Nothing to migrate", está tudo certo.

### 2. Link da pasta storage (para fotos de perfil, uploads)
Se ainda não rodou ou se deu erro de "storage link":

```bash
php artisan storage:link
```

### 3. Limpar cache (recomendado após mudar .env ou config)
```bash
php artisan config:clear
php artisan cache:clear
```

### Resumo dos comandos no servidor (copiar e colar)
```bash
cd /home/u769958563/domains/proticketsports.com.br/public_html
php artisan migrate --force
php artisan storage:link
php artisan config:clear
php artisan cache:clear
```

**Conexão SSH** (senha quando pedir):
```bash
ssh -p 65002 u769958563@93.127.189.99
```

---

## Resumo: o que é automático x o que você faz

| Ação | Quem faz |
|------|----------|
| Instalar Git no Windows | Você |
| Rodar `git init`, `git add`, `git commit` | Você (no terminal) |
| Criar repositório no GitHub e dar `git push` | Você |
| Entrar no painel da Hostinger / FTP / SSH | Você |
| Enviar arquivos ou dar `git pull` no servidor | Você |
| **Rodar `php artisan migrate --force` no servidor** | **Você (obrigatório após subir código novo)** |

Nada disso o Cursor pode fazer por você porque depende da sua conta (GitHub, Hostinger) e do Git instalado no seu PC.

Depois que o Git estiver instalado, você pode pedir para eu te guiar comando a comando (por exemplo: “me diz o próximo comando”) e eu indico exatamente o que rodar.
