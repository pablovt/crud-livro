<?php

namespace App\Tests\Functional;

use App\Entity\Assunto;
use App\Entity\Autor;
use App\Entity\Livro;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AutorCrudTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();
    }

    public function testCriaEListaAutor(): void
    {
        $this->client->request('GET', '/autores/novo');
        $this->client->submitForm('Salvar', ['autor[nome]' => 'Machado de Assis']);

        self::assertResponseRedirects('/autores');
        $this->client->followRedirect();
        self::assertSelectorTextContains('.alert-success', 'Autor salvo');
        self::assertSelectorTextContains('table', 'Machado de Assis');
    }

    public function testNaoAceitaNomeMaiorQueAColuna(): void
    {
        $this->client->request('GET', '/autores/novo');
        $this->client->submitForm('Salvar', ['autor[nome]' => str_repeat('a', 41)]);

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('.invalid-feedback', 'no máximo 40');
    }

    public function testEditaAutor(): void
    {
        $autor = $this->criarAutor('Machado');

        $this->client->request('GET', sprintf('/autores/%d/editar', $autor->getId()));
        $this->client->submitForm('Salvar', ['autor[nome]' => 'Machado de Assis']);

        self::assertResponseRedirects('/autores');
        $this->client->followRedirect();
        self::assertSelectorTextContains('table', 'Machado de Assis');
    }

    public function testExcluiAutorSemVinculo(): void
    {
        $this->criarAutor('Machado');

        $this->client->request('GET', '/autores');
        $this->client->submitForm('Excluir');

        self::assertResponseRedirects('/autores');
        $this->client->followRedirect();
        self::assertSelectorTextContains('.alert-success', 'Autor excluído');
    }

    public function testNaoExcluiAutorEmUso(): void
    {
        $autor = $this->criarAutor('Machado');
        $em = $this->em();
        $assunto = (new Assunto())->setDescricao('Romance');
        $em->persist($assunto);
        $em->persist(
            (new Livro())->setTitulo('Livro')->setEditora('Editora')->setEdicao(1)
                ->setAnoPublicacao('2000')->setValor('10.00')
                ->addAutor($autor)->addAssunto($assunto)
        );
        $em->flush();

        $this->client->request('GET', '/autores');
        $this->client->submitForm('Excluir');

        self::assertResponseRedirects('/autores');
        $this->client->followRedirect();
        self::assertSelectorTextContains('.alert-danger', 'vinculado a livros');
        self::assertSelectorTextContains('table', 'Machado');
    }

    public function testAutorInexistenteRetorna404(): void
    {
        $this->client->request('GET', '/autores/999999/editar');

        self::assertResponseStatusCodeSame(404);
    }

    private function criarAutor(string $nome): Autor
    {
        $autor = (new Autor())->setNome($nome);
        $em = $this->em();
        $em->persist($autor);
        $em->flush();

        return $autor;
    }

    private function em(): EntityManagerInterface
    {
        return self::getContainer()->get(EntityManagerInterface::class);
    }
}
