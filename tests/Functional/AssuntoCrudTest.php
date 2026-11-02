<?php

namespace App\Tests\Functional;

use App\Entity\Assunto;
use App\Entity\Autor;
use App\Entity\Livro;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AssuntoCrudTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();
    }

    public function testListaOsAssuntos(): void
    {
        $this->criarAssunto('Romance');

        $this->client->request('GET', '/assuntos');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('table', 'Romance');
    }

    public function testCriaAssunto(): void
    {
        $this->client->request('GET', '/assuntos/novo');
        $this->client->submitForm('Salvar', ['assunto[descricao]' => 'Tecnologia']);

        self::assertResponseRedirects('/assuntos');
        $this->client->followRedirect();
        self::assertSelectorTextContains('.alert-success', 'Assunto salvo');
        self::assertSelectorTextContains('table', 'Tecnologia');
    }

    public function testNaoCriaAssuntoSemDescricao(): void
    {
        $this->client->request('GET', '/assuntos/novo');
        $this->client->submitForm('Salvar', ['assunto[descricao]' => '']);

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('.invalid-feedback', 'Informe a descrição');
    }

    public function testEditaAssunto(): void
    {
        $assunto = $this->criarAssunto('Romance');

        $this->client->request('GET', sprintf('/assuntos/%d/editar', $assunto->getId()));
        $this->client->submitForm('Salvar', ['assunto[descricao]' => 'Ficção']);

        self::assertResponseRedirects('/assuntos');
        $this->client->followRedirect();
        self::assertSelectorTextContains('table', 'Ficção');
    }

    public function testExcluiAssuntoSemVinculo(): void
    {
        $this->criarAssunto('Romance');

        $this->client->request('GET', '/assuntos');
        $this->client->submitForm('Excluir');

        self::assertResponseRedirects('/assuntos');
        $this->client->followRedirect();
        self::assertSelectorTextContains('.alert-success', 'Assunto excluído');
        self::assertSelectorNotExists('table tbody tr td form');
    }

    public function testNaoExcluiAssuntoEmUso(): void
    {
        $assunto = $this->criarAssunto('Romance');
        $em = $this->em();
        $autor = (new Autor())->setNome('Autor');
        $em->persist($autor);
        $em->persist(
            (new Livro())->setTitulo('Livro')->setEditora('Editora')->setEdicao(1)
                ->setAnoPublicacao('2000')->setValor('10.00')
                ->addAutor($autor)->addAssunto($em->getReference(Assunto::class, $assunto->getId()))
        );
        $em->flush();

        $this->client->request('GET', '/assuntos');
        $this->client->submitForm('Excluir');

        self::assertResponseRedirects('/assuntos');
        $this->client->followRedirect();
        self::assertSelectorTextContains('.alert-danger', 'vinculado a livros');
        self::assertSelectorTextContains('table', 'Romance');
    }

    public function testExclusaoComTokenCsrfInvalidoENegada(): void
    {
        $assunto = $this->criarAssunto('Romance');

        $this->client->request('POST', sprintf('/assuntos/%d/excluir', $assunto->getId()), ['_token' => 'invalido']);

        self::assertResponseStatusCodeSame(403);
    }

    public function testExclusaoNaoAceitaGet(): void
    {
        $assunto = $this->criarAssunto('Romance');

        $this->client->request('GET', sprintf('/assuntos/%d/excluir', $assunto->getId()));

        self::assertResponseStatusCodeSame(405);
    }

    public function testAssuntoInexistenteRetorna404(): void
    {
        $this->client->request('GET', '/assuntos/999999/editar');

        self::assertResponseStatusCodeSame(404);
    }

    private function criarAssunto(string $descricao): Assunto
    {
        $assunto = (new Assunto())->setDescricao($descricao);
        $em = $this->em();
        $em->persist($assunto);
        $em->flush();

        return $assunto;
    }

    private function em(): EntityManagerInterface
    {
        return self::getContainer()->get(EntityManagerInterface::class);
    }
}
