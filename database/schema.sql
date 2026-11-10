create database HomeServices;

use HomeServices;

create table  if not exists usuario(
	id_usuario bigint primary key auto_increment,
    nome_usuario varchar(150),
    email varchar(100),
    senha varchar(250),
    telefone varchar(20),
    tipo_usuario varchar(20),
    status_ativo boolean,
    data_cadastro timestamp
    );
    
    create table  if not exists categoria(
	id_categoria bigint primary key auto_increment,
    nome_categoria varchar(100),
    desc_categoria varchar(255) null,
    status_ativo boolean
    );
    
    create table  if not exists servico(
    id_servico bigint not null primary key,
    id_prestador bigint not null,
    id_categoria bigint not null,
    nome_servico varchar(150) not null,
    desc_servico text,
    valor_base decimal(10,2),
    status_ativo boolean not null,
    foreign key (id_prestador) references usuario(id_usuario),
    foreign key (id_categoria) references categoria(id_categoria)
);

    create table  if not exists solicitacao(
    id_solicitacao bigint not null primary key,
    id_cliente bigint not null,
    id_categoria bigint not null,
    desc_titulo varchar(150) not null,
    desc_solicitacao text,
	desc_endereco_atendimento varchar(255),
    status_solicitacao varchar(20),
    data_solicitacao date,
    foreign key (id_cliente) references usuario(id_usuario),
    foreign key (id_categoria) references categoria(id_categoria)
);



    create table  if not exists proposta(
    id_proposta bigint not null primary key,
    id_solicitacao bigint not null,
    id_prestador bigint not null,
    valor_proposta decimal(10,2) not null,
    desc_proposta text,
    status_proposta varchar(20),
    data_proposta date,
    foreign key (id_solicitacao) references solicitacao(id_solicitacao),
    foreign key (id_prestador) references usuario(id_usuario)
);

create table  if not exists agendamento(
    id_agendamento bigint not null primary key,
    id_solicitacao bigint not null,
    id_proposta bigint not null,
    id_cliente bigint not null,
    id_prestador bigint not null,
    data_agendamento timestamp,
    status_agendamento varchar(20),
    data_criacao date,
    foreign key (id_solicitacao) references solicitacao(id_solicitacao),
    foreign key (id_proposta) references proposta(id_proposta),
    foreign key (id_cliente) references usuario(id_usuario),
    foreign key (id_prestador) references usuario(id_usuario)
);

create table  if not exists avaliacao(
    id_avaliacao bigint not null primary key,
    id_agendamento bigint not null,
    id_avaliador bigint not null,
    id_avaliado bigint not null,
    numero_nota integer not null,
    desc_comentario text,
    data_avaliacao date,
    foreign key (id_agendamento) references agendamento(id_agendamento),
    foreign key (id_avaliador ) references usuario(id_usuario),
    foreign key (id_avaliado) references usuario(id_usuario)
);
