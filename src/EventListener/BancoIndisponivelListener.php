<?php

namespace App\EventListener;

use Doctrine\DBAL\Exception\ConnectionException;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

#[AsEventListener(KernelEvents::EXCEPTION)]
class BancoIndisponivelListener
{
    public function __construct(
        private readonly Environment $twig,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(ExceptionEvent $event): void
    {
        for ($erro = $event->getThrowable(); null !== $erro; $erro = $erro->getPrevious()) {
            if ($erro instanceof ConnectionException) {
                $this->logger->error('Falha de conexão com o banco de dados.', ['exception' => $erro]);
                $event->setResponse(new Response(
                    $this->twig->render('bundles/TwigBundle/Exception/error503.html.twig'),
                    Response::HTTP_SERVICE_UNAVAILABLE,
                ));

                return;
            }
        }
    }
}
