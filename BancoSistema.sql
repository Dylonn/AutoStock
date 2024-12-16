use sistema;

create table usuarios(
id_usuario int primary key auto_increment,
email varchar(200) unique,
senha varchar(50) ,
nome_usuario varchar(100),
cpfEcnpj varchar(20) unique
);

create table cliente(
id_cliente int primary key auto_increment,
nome varchar(200),
senha varchar(50),
email varchar (200) unique,
foto_perfil VARCHAR(255)
);

select * from produto;


CREATE TABLE perfilEmpresa (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    endereco VARCHAR(255) NOT NULL,
    cidade VARCHAR(100) NOT NULL,
    estado VARCHAR(100) NOT NULL,
    cep VARCHAR(10),
    foto_perfil VARCHAR(255),
    telefone varchar(40),
     id_usuario INT,
     horario_funcionamento varchar(100),
     dias_abertos varchar (100),
	FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
);



select * from cliente;

select * from perfilEmpresa;

select * from usuarios;

create table produto(
id_produto int primary key auto_increment,
nomeProduto varchar(250) not null,
valorProduto decimal (10,2) not null,
codigo_tributacao VARCHAR(50),
aliquota_imposto DECIMAL(5, 2),
CodigoNcm varchar(50),
data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP,
data_atualizacao DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
descricao text,
id_usuario int,
foto varchar(250),
FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
);



create table produtoPerfil(
id_produto int primary key auto_increment,
nomeProduto varchar(250) not null,
valorProduto decimal (10,2) not null,
id_empresa INT,
id_usuario INT);

create table servicoPerfil(
    id_servico INT PRIMARY KEY AUTO_INCREMENT,
    nomeServico VARCHAR(255) NOT NULL,
    preco DECIMAL(10, 2) NOT NULL,
    id_empresa INT);
    
    select  * from produtoPerfil;




create table venda(
id_venda int primary key auto_increment,
dia datetime default current_timestamp,
forma_pagamento VARCHAR(50) NOT NULL,
id_usuario int,
id_caixa INT,
FOREIGN KEY (id_caixa) REFERENCES caixa(id_caixa),
FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
);


CREATE TABLE caixa (
    id_caixa INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    data_abertura DATETIME NOT NULL,
    data_fechamento DATETIME,
    status ENUM('aberto', 'fechado') NOT NULL DEFAULT 'aberto',
    total_vendas DECIMAL(10,2) DEFAULT 0,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
);



select * from venda;

CREATE TABLE servicos (
    id_servico INT PRIMARY KEY AUTO_INCREMENT,
    nomeServico VARCHAR(255) NOT NULL,
    preco DECIMAL(10, 2) NOT NULL,
    descricao text,
	foto varchar(250),
    id_usuario int,
    foreign key (id_usuario) references usuarios(id_usuario)
);

CREATE TABLE orcamento (
    id_orcamento INT PRIMARY KEY AUTO_INCREMENT,
    id_cliente INT,
    id_usuario INT,
    veiculo_marca VARCHAR(100),
    veiculo_modelo VARCHAR(100),
    veiculo_ano INT,
    placa VARCHAR(10),
    quilometragem INT,
    data_orcamento DATETIME DEFAULT CURRENT_TIMESTAMP,
    valor_total DECIMAL(10,2),
    observacoes TEXT,
    FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
);

select * from orcamento;

CREATE TABLE servicos_orcamento (
    id_servico_orcamento INT PRIMARY KEY AUTO_INCREMENT,
    id_orcamento INT,
    descricao_servico VARCHAR(255),
    valor_mao_obra DECIMAL(10,2),
    tempo_estimado VARCHAR(50),
    FOREIGN KEY (id_orcamento) REFERENCES orcamento(id_orcamento)
);

CREATE TABLE pecas_orcamento (
    id_peca_orcamento INT PRIMARY KEY AUTO_INCREMENT,
    id_orcamento INT,
    nome_peca VARCHAR(255),
    quantidade INT,
    preco_unitario DECIMAL(10,2),
    FOREIGN KEY (id_orcamento) REFERENCES orcamento(id_orcamento)
);



CREATE TABLE agendamentos (
    id_agendamento INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT,
    produto_id INT,
    servico_id INT,
    id_usuario INT,
    data_agendamento DATETIME,
    FOREIGN KEY (cliente_id) REFERENCES cliente(id_cliente),
    FOREIGN KEY (produto_id) REFERENCES produto(id_produto),
    FOREIGN KEY (servico_id) REFERENCES servicos(id_servico),
    foreign key (id_usuario) references usuarios(id_usuario)
);
select * from agendamentos;

alter table agendamentos 
add column Stats varchar(3000);

select * from caixa;

CREATE TABLE itens_venda (
    id_item_venda INT PRIMARY KEY AUTO_INCREMENT,
    id_venda INT NOT NULL,
    tipo ENUM('produto', 'servico') NOT NULL,
    id_item INT NOT NULL,
    preco DECIMAL(10, 2) NOT NULL,
    desconto DECIMAL(5, 2) NOT NULL,
    FOREIGN KEY (id_venda) REFERENCES venda(id_venda)
);

ALTER TABLE agendamentos ADD COLUMN justificativa_cancelamento TEXT;
ALTER TABLE venda ADD COLUMN justificativa_cancelamento TEXT;


ALTER TABLE produto ADD COLUMN id_empresa INT;
ALTER TABLE servicos ADD COLUMN id_empresa INT;


