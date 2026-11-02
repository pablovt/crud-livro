<?php

namespace App\Controller;

use App\Entity\Autor;
use App\Exception\RegistroEmUsoException;
use App\Form\AutorType;
use App\Repository\AutorRepository;
use App\Service\ExclusaoService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/autores')]
class AutorController extends AbstractController
{
    #[Route('', name: 'app_autor_index', methods: ['GET'])]
    public function index(AutorRepository $repository): Response
    {
        return $this->render('autor/index.html.twig', [
            'autores' => $repository->findBy([], ['nome' => 'ASC']),
        ]);
    }

    #[Route('/novo', name: 'app_autor_novo', methods: ['GET', 'POST'])]
    public function novo(Request $request, EntityManagerInterface $em): Response
    {
        return $this->formulario($request, $em, new Autor());
    }

    #[Route('/{id}/editar', name: 'app_autor_editar', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function editar(Request $request, EntityManagerInterface $em, Autor $autor): Response
    {
        return $this->formulario($request, $em, $autor);
    }

    #[Route('/{id}/excluir', name: 'app_autor_excluir', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function excluir(Request $request, ExclusaoService $exclusao, Autor $autor): Response
    {
        if (!$this->isCsrfTokenValid('excluir_autor'.$autor->getId(), $request->getPayload()->getString('_token'))) {
            throw new AccessDeniedHttpException('Token CSRF inválido.');
        }

        try {
            $exclusao->excluir($autor);
            $this->addFlash('success', 'Autor excluído com sucesso.');
        } catch (RegistroEmUsoException) {
            $this->addFlash('danger', 'Este autor está vinculado a livros e não pode ser excluído.');
        }

        return $this->redirectToRoute('app_autor_index');
    }

    private function formulario(Request $request, EntityManagerInterface $em, Autor $autor): Response
    {
        $form = $this->createForm(AutorType::class, $autor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($autor);
            $em->flush();
            $this->addFlash('success', 'Autor salvo com sucesso.');

            return $this->redirectToRoute('app_autor_index');
        }

        return $this->render('autor/form.html.twig', ['form' => $form, 'autor' => $autor]);
    }
}
