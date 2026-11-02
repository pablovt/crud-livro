<?php

namespace App\Tests\Functional;

use App\Entity\Assunto;
use App\Entity\Autor;
use App\Entity\Livro;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LivroCrudTest extends WebTestCase
{
    private KernelBrowser $client;
    private Autor $autor;
    private Assunto $assunto;

    protected function setUp(): void
    {
        $this->client = self::createClient();

        $this->autor = (new Autor())->setNome('Machado de Assis');
        $this->assunto = (new Assunto())->setDescricao('Romance');
        $em = $this->em();
        $em->persist($this->autor);
        $em->persist($this->assunto);
        $em->flush();
    }

    public function testListaMostraValorEmReaisEOsRelacionamentos(): void
    {
        $this->criarLivro('1234.56');

        $this->client->request('GET', '/livros');

        self::assertResponseIsSuccessful();
        $linha = $this->client->getCrawler()->filter('table tbody tr')->text();
        self::assertStringContainsString('Dom Casmurro', $linha);
        self::assertStringContainsString('Machado de Assis', $linha);
        self::assertStringContainsString('Romance', $linha);
        self::assertStringContainsString('1.234,56', $linha);
        self::assertStringContainsString('R$', $linha);
    }

    public function testCriaLivroConvertendoOValorFormatado(): void
    {
        $crawler = $this->client->request('GET', '/livros/novo');
        $form = $crawler->selectButton('Salvar')->form([
            'livro[titulo]' => 'Dom Casmurro',
            'livro[editora]' => 'Garnier',
            'livro[edicao]' => '1',
            'livro[anoPublicacao]' => '1899',
            'livro[valor]' => '1.234,56',
        ]);
        $form['livro[autores]'][0]->tick();
        $form['livro[assuntos]'][0]->tick();
        $this->client->submit($form);

        self::assertResponseRedirects('/livros');
        $this->client->followRedirect();
        self::assertSelectorTextContains('.alert-success', 'Livro salvo');

        $livro = $this->em()->getRepository(Livro::class)->findOneBy(['titulo' => 'Dom Casmurro']);
        self::assertSame('1234.56', $livro->getValor());
        self::assertCount(1, $livro->getAutores());
        self::assertCount(1, $livro->getAssuntos());
    }

    public function testNaoCriaLivroSemAutorEAssunto(): void
    {
        $this->client->request('GET', '/livros/novo');
        $this->client->submitForm('Salvar', [
            'livro[titulo]' => 'Dom Casmurro',
            'livro[editora]' => 'Garnier',
            'livro[edicao]' => '1',
            'livro[anoPublicacao]' => '1899',
            'livro[valor]' => '10,00',
        ]);

        self::assertResponseStatusCodeSame(422);
        self::assertAnySelectorTextContains('.invalid-feedback', 'Selecione ao menos um autor');
        self::assertAnySelectorTextContains('.invalid-feedback', 'Selecione ao menos um assunto');
    }

    public function testNaoAceitaValorInvalido(): void
    {
        $this->client->request('GET', '/livros/novo');
        $this->client->submitForm('Salvar', ['livro[valor]' => 'abc']);

        self::assertResponseStatusCodeSame(422);
    }

    public function testEditarExibeEMantemOValor(): void
    {
        $livro = $this->criarLivro('1234.56');

        $this->client->request('GET', sprintf('/livros/%d/editar', $livro->getId()));
        self::assertInputValueSame('livro[valor]', '1.234,56');

        $this->client->submitForm('Salvar', ['livro[titulo]' => 'Memórias Póstumas']);

        self::assertResponseRedirects('/livros');
        $this->em()->clear();
        $editado = $this->em()->find(Livro::class, $livro->getId());
        self::assertSame('Memórias Póstumas', $editado->getTitulo());
        self::assertSame('1234.56', $editado->getValor());
    }

    public function testExcluirLivroRemoveVinculosEMantemAutorEAssunto(): void
    {
        $this->criarLivro('10.00');

        $this->client->request('GET', '/livros');
        $this->client->submitForm('Excluir');

        self::assertResponseRedirects('/livros');
        $this->client->followRedirect();
        self::assertSelectorTextContains('.alert-success', 'Livro excluído');

        $conexao = self::getContainer()->get(Connection::class);
        self::assertSame(0, (int) $conexao->fetchOne('SELECT COUNT(*) FROM Livro_Autor'));
        self::assertSame(0, (int) $conexao->fetchOne('SELECT COUNT(*) FROM Livro_Assunto'));
        self::assertSame(1, (int) $conexao->fetchOne('SELECT COUNT(*) FROM Autor'));
        self::assertSame(1, (int) $conexao->fetchOne('SELECT COUNT(*) FROM Assunto'));
    }

    public function testLivroInexistenteRetorna404(): void
    {
        $this->client->request('GET', '/livros/999999/editar');

        self::assertResponseStatusCodeSame(404);
    }

    private function criarLivro(string $valor): Livro
    {
        $em = $this->em();
        $autor = $em->getReference(Autor::class, $this->autor->getId());
        $assunto = $em->getReference(Assunto::class, $this->assunto->getId());
        $livro = (new Livro())->setTitulo('Dom Casmurro')->setEditora('Garnier')->setEdicao(1)
            ->setAnoPublicacao('1899')->setValor($valor)->addAutor($autor)->addAssunto($assunto);
        $em->persist($livro);
        $em->flush();

        return $livro;
    }

    private function em(): EntityManagerInterface
    {
        return self::getContainer()->get(EntityManagerInterface::class);
    }
}
