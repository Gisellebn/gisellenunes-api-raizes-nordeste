# Evidências de Execução da API - Raízes do Nordeste

**Autora:** Giselle Nunes (RU: 4753682)
**Data:** 2026-11-18 16:25:51

## 1. Login Válido (POST /auth/login)

**Request:** `POST http://127.0.0.1:8080/auth/login`

```json
{"email":"cliente@raizesdonordeste.com.br","senha":"Cliente@123"}
```

**Response Status:** `200`

```json
{"status":"sucesso","token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJyYWl6ZXMtbm9yZGVzdGUtYXBpIiwiaWF0IjoxNzk1MDI5ODkxLjg4NDgwOSwiZXhwIjoxNzk1MDM3MDkxLjg4NDgwOSwidWlkIjo0fQ.2dNXC11TYkBzGhcpgnHEgGXMHqG1_uL-xdzVn3ztFOc"}
```

## 2. Criação de Pedido Multicanal Válido (POST /pedidos - TOTEM)

**Request:** `POST http://127.0.0.1:8080/pedidos`

```json
{"canalPedido":"TOTEM","unidadeId":1,"itens":[{"produtoId":1,"quantidade":2}]}
```

**Response Status:** `201`

```json
{"usuario_id":4,"unidade_id":1,"canal_pedido":"TOTEM","status":"AGUARDANDO_PAGAMENTO","valor_total":69.8,"id":1,"cliente":{"id":4,"nome":"Cliente Nordeste","email":"cliente@raizesdonordeste.com.br","perfil":"CLIENTE","aceita_termos_lgpd":true,"criado_em":"2026-11-18 19:25:12"},"loja":{"id":1,"nome":"Unidade Recife Antigo","cidade":"Recife","estado":"PE","endereco":"Rua do Bom Jesus, 120","ativo":true},"itens":[{"id":1,"pedido_id":1,"produto_id":1,"quantidade":2,"preco_unitario":"34.9","produto":"Baião de Dois Tradicional"}],"pagamento":[]}
```

## 3. Bloqueio por Estoque Insuficiente (POST /pedidos -> 409)

**Request:** `POST http://127.0.0.1:8080/pedidos`

```json
{"canalPedido":"TOTEM","unidadeId":1,"itens":[{"produtoId":1,"quantidade":99999}]}
```

**Response Status:** `409`

```json
{"error":"CONFLITO_DE_ESTOQUE_OU_ESTADO","message":"Estoque insuficiente para o produto 'Baião de Dois Tradicional' na unidade selecionada.","details":[],"timestamp":"2026-11-18T19:25:52Z","path":"/pedidos"}
```

## 4a. Pagamento Mock Aprovado (POST /pagamentos/mock)

**Request:** `POST http://127.0.0.1:8080/pagamentos/mock`

```json
{"pedidoId":1,"metodo":"PIX","simularRecusa":false}
```

**Response Status:** `200`

```json
{"pagamentoId":1,"pedidoId":1,"statusPagamento":"APROVADO","statusPedido":"PAGO","valor":69.8,"pontosCreditados":69}
```

## 4b. Pagamento Mock Recusado (POST /pagamentos/mock)

**Request:** `POST http://127.0.0.1:8080/pagamentos/mock`

```json
{"pedidoId":1,"metodo":"CARTAO","simularRecusa":true}
```

**Response Status:** `200`

```json
{"pagamentoId":2,"pedidoId":1,"statusPagamento":"RECUSADO","statusPedido":"PAGAMENTO_RECUSADO","valor":69.8,"pontosCreditados":0}
```

## 5. Consulta Filtrada por Canal (GET /pedidos?canalPedido=TOTEM)

**Request:** `GET http://127.0.0.1:8080/pedidos?canalPedido=TOTEM`

**Response Status:** `200`

```json
[{"id":1,"usuario_id":4,"unidade_id":1,"canal_pedido":"TOTEM","status":"PAGAMENTO_RECUSADO","valor_total":"69.8","criado_em":"2026-11-18 19:25:52","cliente":{"id":4,"nome":"Cliente Nordeste","email":"cliente@raizesdonordeste.com.br","perfil":"CLIENTE","aceita_termos_lgpd":true,"criado_em":"2026-11-18 19:25:12"},"loja":{"id":1,"nome":"Unidade Recife Antigo","cidade":"Recife","estado":"PE","endereco":"Rua do Bom Jesus, 120","ativo":true},"itens":[{"id":1,"pedido_id":1,"produto_id":1,"quantidade":2,"preco_unitario":"34.9","produto":"Baião de Dois Tradicional"}],"pagamento":[{"id":1,"pedido_id":1,"valor":"69.8","metodo":"PIX","status":"APROVADO","criado_em":"2026-11-18 19:25:52"},{"id":2,"pedido_id":1,"valor":"69.8","metodo":"CARTAO","status":"RECUSADO","criado_em":"2026-11-18 19:25:52"}]}]
```

