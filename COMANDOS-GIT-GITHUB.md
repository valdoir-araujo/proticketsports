# Comandos para enviar o Proticketsports ao GitHub

**Usuário:** valdoir-araujo  
**Repositório:** proticketsports  
**URL:** https://github.com/valdoir-araujo/proticketsports

---

## Se ainda estiver com merge em andamento (conflito)

Aborte o merge para voltar ao estado anterior:

```powershell
cd c:\wamp64\www\proticketsports
git merge --abort
```

---

## Conectar ao repositório (só na primeira vez)

Se ainda não adicionou o remote:

```powershell
cd c:\wamp64\www\proticketsports
git remote add origin https://github.com/valdoir-araujo/proticketsports.git
```

Se já adicionou e quiser conferir:

```powershell
git remote -v
```

---

## Enviar todo o projeto (substitui o README do GitHub)

Adicione as alterações, faça o commit e envie. O `--force` faz o conteúdo do GitHub ser substituído pelo seu projeto local (o README criado pelo GitHub some).

```powershell
cd c:\wamp64\www\proticketsports
git add .
git status
git commit -m "Projeto Proticketsports - envio inicial"
git push -u origin master --force
```

Se o GitHub usar a branch **main** em vez de **master**, use um destes:

```powershell
git push -u origin master:main --force
```

Ou renomeie sua branch para main e depois dê push:

```powershell
git branch -M main
git push -u origin main --force
```

---

## Resumo (copiar e colar no PowerShell)

```powershell
cd c:\wamp64\www\proticketsports
git merge --abort
git remote add origin https://github.com/valdoir-araujo/proticketsports.git
git add .
git commit -m "Projeto Proticketsports - envio inicial"
git push -u origin master:main --force
```

*(Se der erro dizendo que o remote já existe, pule a linha do `git remote add`. Se sua branch no GitHub for `master`, use `git push -u origin master --force` em vez da última linha.)*
