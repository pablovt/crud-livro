-- Dados de demonstração. Pode ser reexecutado: INSERT IGNORE mantém o que já existe.

INSERT IGNORE INTO Autor (CodAu, Nome) VALUES
    (1, 'Machado de Assis'),
    (2, 'Clarice Lispector'),
    (3, 'Robert C. Martin'),
    (4, 'Erich Gamma'),
    (5, 'Richard Helm');

INSERT IGNORE INTO Assunto (codAs, Descricao) VALUES
    (1, 'Literatura'),
    (2, 'Romance'),
    (3, 'Tecnologia'),
    (4, 'Eng. de Software');

INSERT IGNORE INTO Livro (Codl, Titulo, Editora, Edicao, AnoPublicacao, Valor) VALUES
    (1, 'Dom Casmurro', 'Editora Garnier', 1, '1899', 39.90),
    (2, 'A Hora da Estrela', 'Rocco', 1, '1977', 44.50),
    (3, 'Clean Code', 'Prentice Hall', 1, '2008', 189.00),
    (4, 'Design Patterns', 'Addison-Wesley', 1, '1994', 1234.56);

INSERT IGNORE INTO Livro_Autor (Livro_Codl, Autor_CodAu) VALUES
    (1, 1), (2, 2), (3, 3), (4, 4), (4, 5);

INSERT IGNORE INTO Livro_Assunto (Livro_Codl, Assunto_codAs) VALUES
    (1, 1), (1, 2), (2, 1), (2, 2), (3, 3), (3, 4), (4, 3), (4, 4);
