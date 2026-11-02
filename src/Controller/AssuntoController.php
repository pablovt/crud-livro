<?php

namespace App\Controller;

use App\Entity\Assunto;
use App\Exception\RegistroEmUsoException;
use App\Form\AssuntoType;
use App\Repository\AssuntoRepository;
use App\Service\ExclusaoService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/assuntos')]
class AssuntoController extends AbstractController
{
    #[Route('', name: 'app_assunto_index', methods: ['GET'])]
    public function index(AssuntoRepository $repository): Response
    {
        return $this->render('assunto/index.html.twig', [
            'assuntos' => $repository->findBy([], ['descricao' => 'ASC']),
        ]);
    }

    #[Route('/novo', name: 'app_assunto_novo', methods: ['GET', 'POST'])]
    public function novo(Request $request, EntityManagerInterface $em): Response
    {
        return $this->formulario($request, $em, new Assunto());
    }

    #[Route('/{id}/editar', name: 'app_assunto_editar', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function editar(Request $request, EntityManagerInterface $em, Assunto $assunto): Response
    {
        return $this->formulario($request, $em, $assunto);
    }

    #[Route('/{id}/excluir', name: 'app_assunto_excluir', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function excluir(Request $request, ExclusaoService $exclusao, Assunto $assunto): Response
    {
        if (!$this->isCsrfTokenValid('excluir_assunto'.$assunto->getId(), $request->getPayload()->getString('_token'))) {
            throw new AccessDeniedHttpException('Token CSRF inválido.');
        }

        try {
            $exclusao->excluir($assunto);
            $this->addFlash('success', 'Assunto excluído com sucesso.');
        } catch (RegistroEmUsoException) {
            $this->addFlash('danger', 'Este assunto está vinculado a livros e não pode ser excluído.');
        }

        return $this->redirectToRoute('app_assunto_index');
    }

    private function formulario(Request $request, EntityManagerInterface $em, Assunto $assunto): Response
    {
        $form = $this->createForm(AssuntoType::class, $assunto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($assunto);
            $em->flush();
            $this->addFlash('success', 'Assunto salvo com sucesso.');

            return $this->redirectToRoute('app_assunto_index');
        }

        return $this->render('assunto/form.html.twig', ['form' => $form, 'assunto' => $assunto]);
    }
}
