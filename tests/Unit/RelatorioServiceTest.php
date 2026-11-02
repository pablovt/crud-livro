<?php

namespace App\Tests\Unit;

use App\Repository\RelatorioRepository;
use App\Service\RelatorioService;
use PHPUnit\Framework\TestCase;

class RelatorioServiceTest extends TestCase
{
    public function testAgrupaLinhasPorAutor(): void
    {
        $grupos = $this->agrupar([
            $this->linha(1, 'Ana', 10, '10.00'),
            $this->linha(1, 'Ana', 11, '20.00'),
            $this->linha(2, 'Bruno', 10, '10.00'),
        ]);

        self::assertCount(2, $grupos);
        self::assertSame('Ana', $grupos[0]->autor);
        self::assertCount(2, $grupos[0]->livros);
        self::assertSame('Bruno', $grupos[1]->autor);
        self::assertCount(1, $grupos[1]->livros);
    }

    public function testSeparaHomonimosPeloCodigo(): void
    {
        $grupos = $this->agrupar([
            $this->linha(1, 'Ana', 10, '10.00'),
            $this->linha(2, 'Ana', 11, '20.00'),
        ]);

        self::assertCount(2, $grupos);
    }

    public function testTotalNaoSofreErroDePontoFlutuante(): void
    {
        $grupos = $this->agrupar([
            $this->linha(1, 'Ana', 10, '0.10'),
            $this->linha(1, 'Ana', 11, '0.20'),
        ]);

        self::assertSame('0.30', $grupos[0]->total);
    }

    public function testAutorSemLivrosFicaComGrupoVazio(): void
    {
        $grupos = $this->agrupar([$this->linha(1, 'Ana', null, null)]);

        self::assertSame([], $grupos[0]->livros);
        self::assertSame('0.00', $grupos[0]->total);
    }

    public function testSemDadosNaoTemGrupos(): void
    {
        self::assertSame([], $this->agrupar([]));
    }

    /** @param list<array<string, mixed>> $linhas */
    private function agrupar(array $linhas): array
    {
        $repositorio = $this->createStub(RelatorioRepository::class);
        $repositorio->method('livrosPorAutor')->willReturn($linhas);

        return (new RelatorioService($repositorio))->livrosPorAutor();
    }

    /** @return array<string, mixed> */
    private function linha(int $codAu, string $autor, ?int $codl, ?string $valor): array
    {
        return [
            'CodAu' => $codAu,
            'Autor' => $autor,
            'Codl' => $codl,
            'Titulo' => null === $codl ? null : 'Livro '.$codl,
            'Editora' => null === $codl ? null : 'Editora',
            'Edicao' => null === $codl ? null : 1,
            'AnoPublicacao' => null === $codl ? null : '2000',
            'Valor' => $valor,
            'Assuntos' => null === $codl ? null : 'Romance',
        ];
    }
}
