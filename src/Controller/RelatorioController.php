<?php

namespace App\Controller;

use App\Service\GeradorPdf;
use App\Service\RelatorioService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/relatorios/livros-por-autor')]
class RelatorioController extends AbstractController
{
    #[Route('', name: 'app_relatorio_livros_por_autor', methods: ['GET'])]
    public function index(RelatorioService $relatorio): Response
    {
        return $this->render('relatorio/livros_por_autor.html.twig', [
            'grupos' => $relatorio->livrosPorAutor(),
        ]);
    }

    #[Route('/pdf', name: 'app_relatorio_livros_por_autor_pdf', methods: ['GET'])]
    public function pdf(RelatorioService $relatorio, GeradorPdf $gerador): Response
    {
        $html = $this->renderView('relatorio/livros_por_autor_pdf.html.twig', [
            'grupos' => $relatorio->livrosPorAutor(),
        ]);

        return new Response($gerador->gerar($html), Response::HTTP_OK, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => HeaderUtils::makeDisposition(HeaderUtils::DISPOSITION_ATTACHMENT, 'livros-por-autor.pdf'),
        ]);
    }
}
