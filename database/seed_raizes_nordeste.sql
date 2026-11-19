-- Dump e Seed Inicial - Rede Raízes do Nordeste
-- Autora: Giselle Nunes (RU: 4753682)

CREATE TABLE IF NOT EXISTS usuario (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha_hash VARCHAR(255) NOT NULL,
    cpf VARCHAR(14),
    perfil VARCHAR(30) NOT NULL DEFAULT 'CLIENTE',
    aceita_termos_lgpd BOOLEAN NOT NULL DEFAULT TRUE,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS unidade (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    cidade VARCHAR(100) NOT NULL,
    estado CHAR(2) NOT NULL,
    endereco VARCHAR(255) NOT NULL,
    ativo BOOLEAN NOT NULL DEFAULT TRUE
);

CREATE TABLE IF NOT EXISTS produto (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    descricao TEXT,
    preco NUMERIC(10, 2) NOT NULL,
    categoria VARCHAR(60) NOT NULL,
    ativo BOOLEAN NOT NULL DEFAULT TRUE
);

CREATE TABLE IF NOT EXISTS estoque_unidade (
    id SERIAL PRIMARY KEY,
    unidade_id INTEGER NOT NULL REFERENCES unidade(id),
    produto_id INTEGER NOT NULL REFERENCES produto(id),
    quantidade_disponivel INTEGER NOT NULL DEFAULT 0,
    UNIQUE (unidade_id, produto_id)
);

CREATE TABLE IF NOT EXISTS pedido (
    id SERIAL PRIMARY KEY,
    usuario_id INTEGER REFERENCES usuario(id),
    unidade_id INTEGER NOT NULL REFERENCES unidade(id),
    canal_pedido VARCHAR(20) NOT NULL CHECK (canal_pedido IN ('APP', 'TOTEM', 'BALCAO', 'PICKUP', 'WEB')),
    status VARCHAR(30) NOT NULL DEFAULT 'AGUARDANDO_PAGAMENTO',
    valor_total NUMERIC(10, 2) NOT NULL DEFAULT 0,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS pedido_item (
    id SERIAL PRIMARY KEY,
    pedido_id INTEGER NOT NULL REFERENCES pedido(id) ON DELETE CASCADE,
    produto_id INTEGER NOT NULL REFERENCES produto(id),
    quantidade INTEGER NOT NULL,
    preco_unitario NUMERIC(10, 2) NOT NULL
);

CREATE TABLE IF NOT EXISTS pagamento_mock (
    id SERIAL PRIMARY KEY,
    pedido_id INTEGER NOT NULL REFERENCES pedido(id),
    valor NUMERIC(10, 2) NOT NULL,
    metodo VARCHAR(30) NOT NULL DEFAULT 'PIX',
    status VARCHAR(30) NOT NULL,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS fidelidade (
    id SERIAL PRIMARY KEY,
    usuario_id INTEGER NOT NULL UNIQUE REFERENCES usuario(id),
    pontos INTEGER NOT NULL DEFAULT 0,
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS auditoria_log (
    id SERIAL PRIMARY KEY,
    usuario_id INTEGER,
    acao VARCHAR(100) NOT NULL,
    entidade VARCHAR(100) NOT NULL,
    entidade_id INTEGER,
    detalhes TEXT,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Usuários de teste (senhas com hash BCrypt)
-- Admin@123 | Atendente@123 | Cozinha@123 | Cliente@123
INSERT INTO usuario (nome, email, senha_hash, cpf, perfil, aceita_termos_lgpd) VALUES
('Administrador Raízes', 'admin@raizesdonordeste.com.br', '$2y$13$EixZaYVK1fsbw1ZfbX3OXePaWxn96p36WQoeG6Lruj3vjPGga31lW', '111.111.111-11', 'ADMIN', TRUE),
('Atendente Recife', 'atendente@raizesdonordeste.com.br', '$2y$13$EixZaYVK1fsbw1ZfbX3OXePaWxn96p36WQoeG6Lruj3vjPGga31lW', '222.222.222-22', 'ATENDENTE', TRUE),
('Cozinha Central', 'cozinha@raizesdonordeste.com.br', '$2y$13$EixZaYVK1fsbw1ZfbX3OXePaWxn96p36WQoeG6Lruj3vjPGga31lW', '333.333.333-33', 'COZINHA', TRUE),
('Cliente Nordeste', 'cliente@raizesdonordeste.com.br', '$2y$13$EixZaYVK1fsbw1ZfbX3OXePaWxn96p36WQoeG6Lruj3vjPGga31lW', '444.444.444-44', 'CLIENTE', TRUE)
ON CONFLICT (email) DO NOTHING;

-- Unidades
INSERT INTO unidade (nome, cidade, estado, endereco) VALUES
('Unidade Recife Antigo', 'Recife', 'PE', 'Rua do Bom Jesus, 120'),
('Unidade Fortaleza Beira-Mar', 'Fortaleza', 'CE', 'Av. Beira Mar, 1500'),
('Unidade Salvador Pelourinho', 'Salvador', 'BA', 'Largo do Pelourinho, 45');

-- Produtos Nordestinos
INSERT INTO produto (nome, descricao, preco, categoria) VALUES
('Baião de Dois Tradicional', 'Arroz, feijão verde, queijo coalho e carne de sol desfiada.', 34.90, 'Pratos Principais'),
('Tapioca de Carne de Sol com Nata', 'Tapioca crocante recheada com carne de sol e nata fresca.', 22.50, 'Tapiocas'),
('Acarajé Completo', 'Bolinho de feijão-fradinho frito no dendê com vatapá e camarão.', 26.00, 'Petiscos'),
('Cuscuz Nordestino Recheado', 'Cuscuz de milho amarelo com queijo coalho e ovo caipira.', 18.90, 'Café Regional'),
('Suco de Caju Natural 400ml', 'Polpa fresca de caju nordestino.', 9.50, 'Bebidas');

-- Estoque Inicial
INSERT INTO estoque_unidade (unidade_id, produto_id, quantidade_disponivel) VALUES
(1, 1, 50), (1, 2, 40), (1, 3, 30), (1, 4, 60), (1, 5, 80),
(2, 1, 35), (2, 2, 45), (2, 3, 25), (2, 4, 50), (2, 5, 70),
(3, 1, 40), (3, 2, 30), (3, 3, 55), (3, 4, 40), (3, 5, 65)
ON CONFLICT (unidade_id, produto_id) DO NOTHING;

