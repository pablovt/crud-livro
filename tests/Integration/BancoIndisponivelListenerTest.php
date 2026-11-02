<?php

namespace App\Tests\Integration;

use App\EventListener\BancoIndisponivelListener;
use Doctrine\DBAL\Driver\PDO\Exception as PdoDriverException;
use Doctrine\DBAL\Exception\ConnectionException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class BancoIndisponivelListenerTest extends KernelTestCase
{
    public function testErroDeConexaoViraPagina503(): void
    {
        $evento = $this->evento($this->erroDeConexao());

        $this->listener()($evento);

        self::assertSame(503, $evento->getResponse()?->getStatusCode());
        self::assertStringContainsString('Serviço indisponível', $evento->getResponse()->getContent());
    }

    public function testErroDeConexaoEncapsuladoTambemViraPagina503(): void
    {
        $evento = $this->evento(new \RuntimeException('falha', previous: $this->erroDeConexao()));

        $this->listener()($evento);

        self::assertSame(503, $evento->getResponse()?->getStatusCode());
    }

    public function testOutrosErrosNaoSaoTratados(): void
    {
        $evento = $this->evento(new \RuntimeException('outro erro'));

        $this->listener()($evento);

        self::assertNull($evento->getResponse());
    }

    private function listener(): BancoIndisponivelListener
    {
        self::getContainer()->get('request_stack')->push(new Request());

        return self::getContainer()->get(BancoIndisponivelListener::class);
    }

    private function erroDeConexao(): ConnectionException
    {
        return new ConnectionException(PdoDriverException::new(new \PDOException('Connection refused')), null);
    }

    private function evento(\Throwable $erro): ExceptionEvent
    {
        return new ExceptionEvent(self::bootKernel(), new Request(), HttpKernelInterface::MAIN_REQUEST, $erro);
    }
}
