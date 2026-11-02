<?php

namespace App\Tests\Functional;

use App\Entity\Assunto;
use App\Entity\Autor;
use App\Entity\Livro;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RelatorioTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();
    }

    public function testTelaAgrupaPorAutorComTotais(): void
    {
        $this->criarLivroComDoisAutores();

        $crawler = $this->client->request('GET', '/relatorios/livros-por-autor');

        self::assertResponseIsSuccessful();
        $grupos = $crawler->filter('.app-grupo-autor');
        self::assertCount(2, $grupos);
        self::assertStringContainsString('Ana', $grupos->eq(0)->text());
        self::assertStringContainsString('Bruno', $grupos->eq(1)->text());
        foreach ([0, 1] as $i) {
            self::assertStringContainsString('Dom Casmurro', $grupos->eq($i)->text());
            self::assertStringContainsString('Romance', $grupos->eq($i)->text());
            self::assertStringContainsString('1.234,56', $grupos->eq($i)->text());
        }
    }

    public function testTelaSemDadosInformaQueEstaVazio(): void
    {
        $this->client->request('GET', '/relatorios/livros-por-autor');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('main', 'Nenhum autor cadastrado');
    }

    public function testBaixaPdf(): void
    {
        $this->criarLivroComDoisAutores();

        $this->client->request('GET', '/relatorios/livros-por-autor/pdf');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('Content-Type', 'application/pdf');
        self::assertStringContainsString('livros-por-autor.pdf', $this->client->getResponse()->headers->get('Content-Disposition'));
        self::assertStringStartsWith('%PDF-', $this->client->getResponse()->getContent());
    }

    private function criarLivroComDoisAutores(): void
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $ana = (new Autor())->setNome('Ana');
        $bruno = (new Autor())->setNome('Bruno');
        $assunto = (new Assunto())->setDescricao('Romance');
        $livro = (new Livro())->setTitulo('Dom Casmurro')->setEditora('Garnier')->setEdicao(1)
            ->setAnoPublicacao('1899')->setValor('1234.56')
            ->addAutor($ana)->addAutor($bruno)->addAssunto($assunto);

        foreach ([$ana, $bruno, $assunto, $livro] as $entidade) {
            $em->persist($entidade);
        }
        $em->flush();
    }
}
