DROP DATABASE IF EXISTS HomeServices;
CREATE DATABASE HomeServices;

USE HomeServices;

CREATE TABLE IF NOT EXISTS usuario (
    id_usuario BIGINT PRIMARY KEY AUTO_INCREMENT,
    nome_usuario VARCHAR(150),
    email VARCHAR(100),
    senha VARCHAR(250),
    telefone VARCHAR(20),
    tipo_usuario VARCHAR(20),
    status_ativo BOOLEAN,
    data_cadastro TIMESTAMP
);

CREATE TABLE IF NOT EXISTS categoria (
    id_categoria BIGINT PRIMARY KEY AUTO_INCREMENT,
    nome_categoria VARCHAR(100),
    desc_categoria VARCHAR(255) NULL,
    status_ativo BOOLEAN
);

CREATE TABLE IF NOT EXISTS servico (
    id_servico BIGINT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    id_prestador BIGINT NOT NULL,
    id_categoria BIGINT NOT NULL,
    nome_servico VARCHAR(150) NOT NULL,
    desc_servico TEXT,
    valor_base DECIMAL(10,2),
    status_ativo BOOLEAN NOT NULL,
    FOREIGN KEY (id_prestador) REFERENCES usuario(id_usuario),
    FOREIGN KEY (id_categoria) REFERENCES categoria(id_categoria)
);

CREATE TABLE IF NOT EXISTS solicitacao (
    id_solicitacao BIGINT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    id_cliente BIGINT NOT NULL,
    id_prestador BIGINT NULL,
    id_categoria BIGINT NOT NULL,
    desc_titulo VARCHAR(150) NOT NULL,
    desc_solicitacao TEXT,
    desc_endereco_atendimento VARCHAR(255),
    status_solicitacao VARCHAR(20),
    data_solicitacao DATE,
    FOREIGN KEY (id_cliente) REFERENCES usuario(id_usuario),
    FOREIGN KEY (id_prestador) REFERENCES usuario(id_usuario),
    FOREIGN KEY (id_categoria) REFERENCES categoria(id_categoria)
);

CREATE TABLE IF NOT EXISTS proposta (
    id_proposta BIGINT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    id_solicitacao BIGINT NOT NULL,
    id_prestador BIGINT NOT NULL,
    valor_proposta DECIMAL(10,2) NOT NULL,
    desc_proposta TEXT,
    status_proposta VARCHAR(20),
    data_proposta DATE,
    FOREIGN KEY (id_solicitacao) REFERENCES solicitacao(id_solicitacao),
    FOREIGN KEY (id_prestador) REFERENCES usuario(id_usuario)
);

CREATE TABLE IF NOT EXISTS agendamento (
    id_agendamento BIGINT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    id_solicitacao BIGINT NOT NULL,
    id_proposta BIGINT NOT NULL,
    id_cliente BIGINT NOT NULL,
    id_prestador BIGINT NOT NULL,
    data_agendamento DATE,
    hora_agendamento VARCHAR(5),
    observacoes TEXT,
    status_agendamento VARCHAR(20),
    data_criacao DATE,
    FOREIGN KEY (id_solicitacao) REFERENCES solicitacao(id_solicitacao),
    FOREIGN KEY (id_proposta) REFERENCES proposta(id_proposta),
    FOREIGN KEY (id_cliente) REFERENCES usuario(id_usuario),
    FOREIGN KEY (id_prestador) REFERENCES usuario(id_usuario)
);

CREATE TABLE IF NOT EXISTS avaliacao (
    id_avaliacao BIGINT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    id_agendamento BIGINT NOT NULL,
    id_avaliador BIGINT NOT NULL,
    id_avaliado BIGINT NOT NULL,
    numero_nota INTEGER NOT NULL,
    desc_comentario TEXT,
    data_avaliacao DATE,
    FOREIGN KEY (id_agendamento) REFERENCES agendamento(id_agendamento),
    FOREIGN KEY (id_avaliador) REFERENCES usuario(id_usuario),
    FOREIGN KEY (id_avaliado) REFERENCES usuario(id_usuario)
);
