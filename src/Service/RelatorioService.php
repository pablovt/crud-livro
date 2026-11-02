<?php

namespace App\Service;

use App\Dto\GrupoAutor;
use App\Repository\RelatorioRepository;

class RelatorioService
{
    public function __construct(private readonly RelatorioRepository $repositorio)
    {
    }

    /**
     * Não há total geral: um livro com vários autores aparece em cada grupo
     * e seria contado mais de uma vez.
     *
     * @return list<GrupoAutor>
     */
    public function livrosPorAutor(): array
    {
        $grupos = [];

        foreach ($this->repositorio->livrosPorAutor() as $linha) {
            $grupo = $grupos[$linha['CodAu']] ??= new GrupoAutor($linha['Autor']);

            if (null !== $linha['Codl']) {
                $grupo->adicionar($linha);
            }
        }

        return array_values($grupos);
    }
}
