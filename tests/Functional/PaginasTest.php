<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PaginasTest extends WebTestCase
{
    public function testHomeTemLinksParaAsTelas(): void
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/');

        self::assertResponseIsSuccessful();
        foreach (['/livros', '/autores', '/assuntos', '/relatorios/livros-por-autor'] as $link) {
            self::assertGreaterThan(0, $crawler->filter(sprintf('main a[href="%s"]', $link))->count(), $link);
        }
    }

    public function testPagina404Propria(): void
    {
        $client = self::createClient(['debug' => false]);
        $client->request('GET', '/nao-existe');

        self::assertResponseStatusCodeSame(404);
        self::assertSelectorTextContains('h1', 'Página não encontrada');
    }
}
