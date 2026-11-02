<?php

namespace App\Dto;

final class GrupoAutor
{
    /** @var list<array<string, mixed>> */
    public array $livros = [];
    public string $total = '0.00';

    public function __construct(public readonly string $autor)
    {
    }

    /** @param array<string, mixed> $livro */
    public function adicionar(array $livro): void
    {
        $this->livros[] = $livro;
        $this->total = bcadd($this->total, $livro['Valor'], 2);
    }
}
