-- ============================================================
--  ProdutoHub — Script de criação do banco de dados
--  Execute no PgAdmin conectado ao banco "produtos"
-- ============================================================

-- 1. Tabela de usuários
CREATE TABLE IF NOT EXISTS public.usuario
(
    idusuario integer NOT NULL DEFAULT nextval('usuario_idusuario_seq'::regclass),
    username  character varying(50)  NOT NULL,
    password  character varying(100) NOT NULL,
    status    boolean DEFAULT true,
    CONSTRAINT usuario_pkey PRIMARY KEY (idusuario)
);

-- Sequência caso não exista
CREATE SEQUENCE IF NOT EXISTS usuario_idusuario_seq
    START WITH 1 INCREMENT BY 1;

-- Usuário padrão: admin / 123456
INSERT INTO public.usuario (username, password, status)
VALUES ('admin', '123456', true)
ON CONFLICT DO NOTHING;

-- 2. Tabela de produtos
CREATE SEQUENCE IF NOT EXISTS produto_idproduto_seq
    START WITH 1 INCREMENT BY 1;

CREATE TABLE IF NOT EXISTS public.produto
(
    idproduto    integer NOT NULL DEFAULT nextval('produto_idproduto_seq'::regclass),
    produtonome  character varying(100) NOT NULL,
    produtopreco real NOT NULL DEFAULT 0,
    produtofoto  character varying(150),
    produtostatus boolean DEFAULT false,
    CONSTRAINT produto_pkey PRIMARY KEY (idproduto)
);

-- 3. Produtos de exemplo
INSERT INTO public.produto (produtonome, produtopreco, produtofoto, produtostatus) VALUES
('Notebook Dell Inspiron 15', 3499.90, 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=200', true),
('Mouse Sem Fio Logitech MX3', 349.90, 'https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?w=200', true),
('Teclado Mecânico HyperX', 599.00, 'https://images.unsplash.com/photo-1595225476474-87563907a212?w=200', true),
('Monitor 27" LG UltraWide', 2199.00, 'https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?w=200', false),
('Headset Gamer JBL Quantum', 499.90, '', false);