-- Cadastro de Livros: tabelas, chaves e índices.
-- Pode ser reexecutado: não apaga tabelas nem dados.
-- Não há CREATE DATABASE/USE: selecione o banco antes de executar.

CREATE TABLE IF NOT EXISTS Livro (
    Codl          INT            NOT NULL AUTO_INCREMENT,
    Titulo        VARCHAR(40)    NOT NULL,
    Editora       VARCHAR(40)    NOT NULL,
    Edicao        INT            NOT NULL,
    AnoPublicacao VARCHAR(4)     NOT NULL,
    Valor         DECIMAL(10, 2) NOT NULL,
    PRIMARY KEY (Codl),
    CONSTRAINT chk_livro_valor CHECK (Valor >= 0)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS Autor (
    CodAu INT         NOT NULL AUTO_INCREMENT,
    Nome  VARCHAR(40) NOT NULL,
    PRIMARY KEY (CodAu),
    INDEX idx_autor_nome (Nome) -- ajuste de performance: ordenação do relatório
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS Assunto (
    codAs     INT         NOT NULL AUTO_INCREMENT,
    Descricao VARCHAR(20) NOT NULL,
    PRIMARY KEY (codAs)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Excluir um livro remove seus vínculos; autor/assunto em uso não pode ser excluído.
CREATE TABLE IF NOT EXISTS Livro_Autor (
    Livro_Codl  INT NOT NULL,
    Autor_CodAu INT NOT NULL,
    PRIMARY KEY (Livro_Codl, Autor_CodAu),
    INDEX Livro_Autor_FKIndex1 (Livro_Codl),
    INDEX Livro_Autor_FKIndex2 (Autor_CodAu),
    CONSTRAINT fk_livro_autor_livro FOREIGN KEY (Livro_Codl)
        REFERENCES Livro (Codl) ON DELETE CASCADE,
    CONSTRAINT fk_livro_autor_autor FOREIGN KEY (Autor_CodAu)
        REFERENCES Autor (CodAu) ON DELETE RESTRICT
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS Livro_Assunto (
    Livro_Codl    INT NOT NULL,
    Assunto_codAs INT NOT NULL,
    PRIMARY KEY (Livro_Codl, Assunto_codAs),
    INDEX Livro_Assunto_FKIndex1 (Livro_Codl),
    INDEX Livro_Assunto_FKIndex2 (Assunto_codAs),
    CONSTRAINT fk_livro_assunto_livro FOREIGN KEY (Livro_Codl)
        REFERENCES Livro (Codl) ON DELETE CASCADE,
    CONSTRAINT fk_livro_assunto_assunto FOREIGN KEY (Assunto_codAs)
        REFERENCES Assunto (codAs) ON DELETE RESTRICT
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
