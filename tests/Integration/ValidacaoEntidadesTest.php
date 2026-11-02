<?php

namespace App\Tests\Integration;

use App\Entity\Assunto;
use App\Entity\Autor;
use App\Entity\Livro;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ValidacaoEntidadesTest extends KernelTestCase
{
    public function testLivroValidoNaoTemViolacoes(): void
    {
        self::assertSame([], $this->camposInvalidos($this->livroValido()));
    }

    #[DataProvider('livrosInvalidos')]
    public function testLivroInvalido(callable $alterar, string $campo): void
    {
        $livro = $this->livroValido();
        $alterar($livro);

        self::assertContains($campo, $this->camposInvalidos($livro));
    }

    public static function livrosInvalidos(): iterable
    {
        yield 'título vazio' => [fn (Livro $l) => $l->setTitulo(''), 'titulo'];
        yield 'título longo' => [fn (Livro $l) => $l->setTitulo(str_repeat('a', 41)), 'titulo'];
        yield 'editora longa' => [fn (Livro $l) => $l->setEditora(str_repeat('a', 41)), 'editora'];
        yield 'edição zero' => [fn (Livro $l) => $l->setEdicao(0), 'edicao'];
        yield 'ano com letras' => [fn (Livro $l) => $l->setAnoPublicacao('20a4'), 'anoPublicacao'];
        yield 'ano curto' => [fn (Livro $l) => $l->setAnoPublicacao('204'), 'anoPublicacao'];
        yield 'valor negativo' => [fn (Livro $l) => $l->setValor('-0.01'), 'valor'];
        yield 'valor acima da coluna' => [fn (Livro $l) => $l->setValor('100000000.00'), 'valor'];
    }

    public function testLivroExigeAutorEAssunto(): void
    {
        $campos = $this->camposInvalidos(new Livro());

        self::assertContains('autores', $campos);
        self::assertContains('assuntos', $campos);
    }

    public function testNomeDoAutorRespeitaTamanhoDaColuna(): void
    {
        self::assertSame([], $this->camposInvalidos((new Autor())->setNome(str_repeat('a', 40))));
        self::assertContains('nome', $this->camposInvalidos((new Autor())->setNome(str_repeat('a', 41))));
    }

    public function testDescricaoDoAssuntoRespeitaTamanhoDaColuna(): void
    {
        self::assertSame([], $this->camposInvalidos((new Assunto())->setDescricao(str_repeat('a', 20))));
        self::assertContains('descricao', $this->camposInvalidos((new Assunto())->setDescricao(str_repeat('a', 21))));
    }

    private function livroValido(): Livro
    {
        return (new Livro())
            ->setTitulo('Dom Casmurro')
            ->setEditora('Garnier')
            ->setEdicao(1)
            ->setAnoPublicacao('1899')
            ->setValor('39.90')
            ->addAutor((new Autor())->setNome('Machado de Assis'))
            ->addAssunto((new Assunto())->setDescricao('Romance'));
    }

    /** @return list<string> */
    private function camposInvalidos(object $entidade): array
    {
        $violacoes = self::getContainer()->get(ValidatorInterface::class)->validate($entidade);

        $campos = [];
        foreach ($violacoes as $violacao) {
            $campos[] = $violacao->getPropertyPath();
        }

        return $campos;
    }
}
