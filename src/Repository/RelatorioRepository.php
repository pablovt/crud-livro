<?php

namespace App\Repository;

use Doctrine\DBAL\Connection;

class RelatorioRepository
{
    public function __construct(private readonly Connection $conexao)
    {
    }

    /** @return list<array<string, mixed>> */
    public function livrosPorAutor(): array
    {
        return $this->conexao->fetchAllAssociative(
            'SELECT CodAu, Autor, Codl, Titulo, Editora, Edicao, AnoPublicacao, Valor, Assuntos
             FROM vw_relatorio_livros_por_autor
             ORDER BY Autor, CodAu, Titulo'
        );
    }
}
