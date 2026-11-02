<?php

namespace App\Tests\Integration;

use App\Entity\Assunto;
use App\Entity\Autor;
use App\Entity\Livro;
use App\Repository\RelatorioRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ViewRelatorioTest extends KernelTestCase
{
    public function testLivroComDoisAutoresApareceEmCadaAutorSemDuplicarAssuntos(): void
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $ana = (new Autor())->setNome('Ana');
        $bruno = (new Autor())->setNome('Bruno');
        $semLivros = (new Autor())->setNome('Carla');
        $romance = (new Assunto())->setDescricao('Romance');
        $drama = (new Assunto())->setDescricao('Drama');
        $livro = (new Livro())->setTitulo('Livro')->setEditora('Editora')->setEdicao(2)
            ->setAnoPublicacao('2001')->setValor('15.50')
            ->addAutor($ana)->addAutor($bruno)->addAssunto($romance)->addAssunto($drama);

        foreach ([$ana, $bruno, $semLivros, $romance, $drama, $livro] as $entidade) {
            $em->persist($entidade);
        }
        $em->flush();

        $linhas = self::getContainer()->get(RelatorioRepository::class)->livrosPorAutor();

        self::assertSame(['Ana', 'Bruno', 'Carla'], array_column($linhas, 'Autor'));
        self::assertSame('Livro', $linhas[0]['Titulo']);
        self::assertSame('Livro', $linhas[1]['Titulo']);
        self::assertSame('Drama, Romance', $linhas[0]['Assuntos']);
        self::assertSame('15.50', $linhas[0]['Valor']);
        self::assertNull($linhas[2]['Codl']);
    }
}
