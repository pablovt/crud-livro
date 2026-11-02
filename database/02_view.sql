-- Fonte do relatório de livros por autor: uma linha por par autor/livro.
-- Um livro com dois autores aparece duas vezes, uma em cada autor.
-- LEFT JOIN mantém no relatório os autores ainda sem livros.

CREATE OR REPLACE VIEW vw_relatorio_livros_por_autor AS
SELECT
    a.CodAu,
    a.Nome AS Autor,
    l.Codl,
    l.Titulo,
    l.Editora,
    l.Edicao,
    l.AnoPublicacao,
    l.Valor,
    GROUP_CONCAT(DISTINCT s.Descricao ORDER BY s.Descricao SEPARATOR ', ') AS Assuntos
FROM Autor a
LEFT JOIN Livro_Autor la ON la.Autor_CodAu = a.CodAu
LEFT JOIN Livro l ON l.Codl = la.Livro_Codl
LEFT JOIN Livro_Assunto ls ON ls.Livro_Codl = l.Codl
LEFT JOIN Assunto s ON s.codAs = ls.Assunto_codAs
GROUP BY a.CodAu, a.Nome, l.Codl, l.Titulo, l.Editora, l.Edicao, l.AnoPublicacao, l.Valor;
