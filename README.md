# Raízes do Nordeste — API RESTful (Back-End)

Projeto Multidisciplinar UNINTER — Trilha Back-End: **Rede "Raízes do Nordeste"**.

- **Autora:** Giselle Nunes
- **RU:** 4753682
- **Repositório:** https://github.com/Gisellebn/gisellenunes-api-raizes-nordeste

---

## Visão Geral

API RESTful desenvolvida em **PHP / Yii2** com banco de dados **PostgreSQL**, projetada para gerenciar pedidos multicanal (`APP`, `TOTEM`, `BALCAO`, `PICKUP`, `WEB`), controle de estoque por unidade, simulação de pagamentos (`pagamento_mock`), programa de fidelidade e auditoria de ações sensíveis em conformidade com a LGPD.

## Arquitetura em Camadas

O projeto segue uma organização modular inspirada em arquitetura em camadas:

- **API (`controllers/`)**: Recepção de requisições HTTP, autenticação JWT, filtros CORS e delegação para os serviços.
- **Application (`services/`)**: Regras de fluxo de negócio (`PedidoService`, `PagamentoService`, `AuditoriaService`).
- **Domain (`models/`)**: Entidades de domínio (`Pedido`, `PedidoItem`, `Produto`, `Unidade`, `EstoqueUnidade`, `PagamentoMock`, `Fidelidade`, `Usuario`, `AuditoriaLog`).
- **Infrastructure (`migrations/`, `components/`, `config/`)**: Migrations de banco de dados, `ApiErrorHandler` global em JSON e configurações do Yii2.

---

## Requisitos

- PHP >= 7.4 (recomendado 8.1+) com extensões `pdo_pgsql`, `mbstring`, `openssl`
- Composer
- PostgreSQL 13+

---

## Configuração do Ambiente

1. **Instale as dependências:**
   ```bash
   composer install
   ```

2. **Configure o arquivo `.env`:**
   ```bash
   cp .env.example .env
   ```
   Edite o `.env` com suas credenciais do PostgreSQL e uma chave secreta para o JWT:
   ```env
   DB_DSN=pgsql:host=localhost;port=5432;dbname=raizes_nordeste
   DB_USER=postgres
   DB_PASSWORD=sua_senha_aqui
   JWT_SECRET=substitua_por_uma_chave_secreta_forte
   ```

3. **Crie o banco de dados e execute as migrations / dump inicial:**
   ```bash
   php yii migrate --interactive=0
   psql -U postgres -d raizes_nordeste -f database/seed_raizes_nordeste.sql
   ```

4. **Inicie o servidor local:**
   ```bash
   php yii serve --port=8080
   ```

---

## Documentação Interativa (Swagger / OpenAPI)

Com o servidor rodando, acesse no navegador:

- **Swagger UI:** `http://localhost:8080/docs` (ou `http://localhost:8080/swagger`)
- **Especificação OpenAPI YAML:** `http://localhost:8080/docs/openapi.yaml`

---

## Coleção de Testes Postman

A coleção pronta com os cenários obrigatórios **T01 a T10** está em:
`docs/Raizes_do_Nordeste.postman_collection.json`

1. Abra o Postman e importe o arquivo `docs/Raizes_do_Nordeste.postman_collection.json`.
2. A variável `{{baseUrl}}` já vem configurada como `http://localhost:8080`.
3. Execute primeiro a requisição **T01 - Login válido (200)** na pasta `Auth`; o script de teste preencherá automaticamente a variável `{{token}}` para as demais requisições.

---

## Credenciais de Teste

| Perfil | E-mail | Senha |
| :--- | :--- | :--- |
| `ADMIN` | `admin@raizesdonordeste.com.br` | `Admin@123` |
| `ATENDENTE` | `atendente@raizesdonordeste.com.br` | `Atendente@123` |
| `COZINHA` | `cozinha@raizesdonordeste.com.br` | `Cozinha@123` |
| `CLIENTE` | `cliente@raizesdonordeste.com.br` | `Cliente@123` |
