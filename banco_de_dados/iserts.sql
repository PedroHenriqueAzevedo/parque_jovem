CREATE TABLE escola_sabatina (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    arquivo VARCHAR(255) NOT NULL,
    data_upload TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE projetos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    conteudo TEXT NOT NULL,
    foto VARCHAR(255),
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE projetos_fotos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    projeto_id INT NOT NULL,
    caminho VARCHAR(255) NOT NULL,
    FOREIGN KEY (projeto_id) REFERENCES projetos(id) ON DELETE CASCADE
);

CREATE TABLE cadastros_jovens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    telefone VARCHAR(20) NOT NULL,
    tipo_cadastro VARCHAR(100) NOT NULL,
    adventista ENUM('Sim', 'Não') NOT NULL,
    igreja VARCHAR(255),
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE inscricoes_acampamento (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL UNIQUE,
    cpf VARCHAR(14) NOT NULL UNIQUE,
    data_nascimento DATE NOT NULL,
    telefone VARCHAR(15) NOT NULL UNIQUE,
    igreja VARCHAR(100) NOT NULL,
    cep VARCHAR(9) NOT NULL,
    rua VARCHAR(150) NOT NULL,
    numero VARCHAR(10) NOT NULL,
    bairro VARCHAR(100) NOT NULL,
    cidade VARCHAR(100) NOT NULL,
    estado VARCHAR(2) NOT NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
);





ALTER TABLE inscricoes_acampamento
DROP COLUMN cep,
DROP COLUMN rua,
DROP COLUMN numero,
DROP COLUMN bairro,
DROP COLUMN cidade,
DROP COLUMN estado;

ALTER TABLE inscricoes_acampamento
ADD COLUMN acomodacao VARCHAR(100) NOT NULL AFTER igreja;

ALTER TABLE inscricoes_acampamento
ADD COLUMN responsavel_id INT NULL AFTER id;

ALTER TABLE inscricoes_acampamento
ADD forma_pagamento VARCHAR(20) NOT NULL AFTER acomodacao;
TRUNCATE TABLE inscricoes_acampamento;








ALTER TABLE inscricoes_acampamento 
ADD COLUMN status_pagamento VARCHAR(30) NOT NULL DEFAULT 'Pendente';


CREATE TABLE acomodacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    limite INT NOT NULL,
    usado INT NOT NULL DEFAULT 0
);

INSERT INTO acomodacoes (nome, limite, usado) VALUES
('Suíte 4 leitos', 16, 0),
('Suíte 3 leitos', 3, 0),
('Alojamento Coletivo', 73, 0),
('Barraca', 9999, 0); -- ilimitado

UPDATE acomodacoes SET usado = (
    SELECT COUNT(*) 
    FROM inscricoes_acampamento 
    WHERE 
        CASE acomodacoes.nome
            WHEN 'Suíte 4 leitos' THEN acomodacao LIKE 'Suíte 4 leitos%'
            WHEN 'Suíte 3 leitos' THEN acomodacao LIKE 'Suíte 3 leitos%'
            WHEN 'Alojamento Coletivo' THEN acomodacao LIKE 'Alojamento coletivo%'
            WHEN 'Barraca' THEN acomodacao LIKE 'Barraca%'
        END
);
INSERT INTO acomodacoes (nome, limite, usado) 
VALUES ('Day Use', 9999, 0);
DELETE FROM acomodacoes WHERE nome = 'Alojamento Coletivo';

INSERT INTO acomodacoes (nome, limite, usado) VALUES
('Alojamento Coletivo Masculino', 36, 0),
('Alojamento Coletivo Feminino', 37, 0);
