# Atletas estrangeiros (sem CPF)

## Situação atual

- **Cadastro:** o campo "documento" (CPF) é obrigatório e único; é salvo em `users.documento` e `atletas.cpf`.
- **Login:** aceita **e-mail** ou **documento** (CPF/CNPJ). Se for e-mail, não há validação de CPF. Se for número, o front valida CPF/CNPJ (11/14 dígitos) antes de enviar.
- **Identificação (inscrição/loja):** busca por **e-mail** ou **CPF**. Quem não tem CPF já pode usar e-mail + data de nascimento.

Ou seja: **login e identificação já funcionam por e-mail**. O que falta é permitir **cadastro sem CPF** e tratar “documento estrangeiro” (ex.: passaporte).

---

## Abordagem recomendada

### 1. Cadastro (Register)

- **Opção explícita:** checkbox **“Sou atleta estrangeiro (não resido no Brasil)”**. Quando marcada:
  - Documento (CPF) não é exigido e o campo fica desabilitado.
  - Estado e cidade são opcionais e o bloco “Onde você está?” fica oculto.
  - O backend recebe `estrangeiro=1` e normaliza `documento`, `estado_id` e `cidade_id` para `null`.
- **Documento:** obrigatório só para quem não marca “Sou estrangeiro”.
  - Se preenchido com **11 dígitos:** validar como CPF (algoritmo).
  - Se **outro formato** (ex.: passaporte): aceitar como texto e salvar em `users.documento` e `atletas.cpf`.
- **E-mail:** obrigatório e único → atleta estrangeiro sempre usa e-mail para login e identificação.

### 2. Login

- **Opção explícita:** checkbox **“Sou estrangeiro (entrar com e-mail)”**. Quando marcada:
  - O label do campo passa a ser **“E-mail”** e o placeholder **“seu@email.com”**.
  - Máscara de CPF/CNPJ não é aplicada; a validação no envio exige que o valor contenha `@` (e-mail).
  - Exibida a dica: “Use o mesmo e-mail cadastrado na plataforma.”
- **Backend:** continua aceitando login por **e-mail** ou **documento** (apenas dígitos). Estrangeiros usam **e-mail + senha**.

### 3. Identificação (inscrição / loja)

- Já existe busca por **e-mail** ou por **CPF**.
- Atleta sem CPF usa **e-mail + data de nascimento**.
- Nenhuma alteração de regra necessária.

### 4. Banco de dados

- `users.documento` e `atletas.cpf` já são **nullable** nas migrations.
- Tamanhos atuais: `users.documento` 20 caracteres, `atletas.cpf` 14 caracteres. Documentos estrangeiros (ex.: passaporte) são truncados a 14 no atleta e 20 no user; se precisar de mais caracteres, crie uma migration aumentando o tamanho da coluna.

### 5. Estado e cidade (localização)

- **Cadastro:** Estado e cidade (BR) são **obrigatórios** quando o atleta informa documento (CPF); são **opcionais** quando não informa documento (estrangeiro).
- **Validação:** `estado_id` e `cidade_id` usam `required_if:documento,filled` e `nullable`; valores vazios são normalizados para `null` antes de validar.
- **Banco:** `atletas.estado_id` e `atletas.cidade_id` já são **nullable** (migration `update_location_fields_in_atletas_table`).
- **Exibição:** Onde o sistema exibe cidade/estado (listas, relatórios), tratar `null` como “Exterior” ou “N/I” conforme a tela.
- **Futuro (opcional):** Se quiser exibir país/cidade para estrangeiros, pode-se adicionar `pais_id` e `cidade_estrangeiro` (texto) e preencher quando estado/cidade forem nulos.

### 6. Outros pontos

- **Pagamento (PIX/cartão):** o Mercado Pago pode exigir CPF para alguns pagadores. Para estrangeiros, pode ser necessário usar apenas cartão internacional ou fluxo específico (fora do escopo desta doc).
- **Relatórios/exportação:** onde hoje aparece “CPF”, exibir “CPF/Documento” e, se vazio, “N/I” ou “Estrangeiro”.
- **Check-in / lista:** busca por CPF pode continuar; atleta estrangeiro pode ser localizado por nome ou número de inscrição.

---

## Resumo prático

| Fluxo              | Brasileiro (com CPF)     | Estrangeiro (sem CPF)   |
|--------------------|--------------------------|--------------------------|
| Cadastro           | CPF obrigatório, validado; Estado e Cidade obrigatórios | Documento opcional; **Estado e Cidade opcionais** |
| Login              | CPF ou e-mail            | **Só e-mail**            |
| Identificação      | CPF ou e-mail + nascimento | **E-mail + nascimento** |
| Pagamento          | CPF usado se houver      | Pode não ter CPF (limitação do gateway) |

Implementação mínima: **tornar documento opcional no cadastro** e **validar CPF apenas quando houver 11 dígitos**. O resto (login e identificação por e-mail) já está coberto.
