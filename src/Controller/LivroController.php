<?php

namespace App\Controller;

use App\Entity\Livro;
use App\Exception\RegistroEmUsoException;
use App\Form\LivroType;
use App\Repository\LivroRepository;
use App\Service\ExclusaoService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/livros')]
class LivroController extends AbstractController
{
    #[Route('', name: 'app_livro_index', methods: ['GET'])]
    public function index(LivroRepository $repository): Response
    {
        return $this->render('livro/index.html.twig', [
            'livros' => $repository->listarComRelacionamentos(),
        ]);
    }

    #[Route('/novo', name: 'app_livro_novo', methods: ['GET', 'POST'])]
    public function novo(Request $request, EntityManagerInterface $em): Response
    {
        return $this->formulario($request, $em, new Livro());
    }

    #[Route('/{id}/editar', name: 'app_livro_editar', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function editar(Request $request, EntityManagerInterface $em, Livro $livro): Response
    {
        return $this->formulario($request, $em, $livro);
    }

    #[Route('/{id}/excluir', name: 'app_livro_excluir', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function excluir(Request $request, ExclusaoService $exclusao, Livro $livro): Response
    {
        if (!$this->isCsrfTokenValid('excluir_livro'.$livro->getId(), $request->getPayload()->getString('_token'))) {
            throw new AccessDeniedHttpException('Token CSRF inválido.');
        }

        try {
            $exclusao->excluir($livro);
            $this->addFlash('success', 'Livro excluído com sucesso.');
        } catch (RegistroEmUsoException) {
            $this->addFlash('danger', 'Não foi possível excluir o livro.');
        }

        return $this->redirectToRoute('app_livro_index');
    }

    private function formulario(Request $request, EntityManagerInterface $em, Livro $livro): Response
    {
        $form = $this->createForm(LivroType::class, $livro);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($livro);
            $em->flush();
            $this->addFlash('success', 'Livro salvo com sucesso.');

            return $this->redirectToRoute('app_livro_index');
        }

        return $this->render('livro/form.html.twig', ['form' => $form, 'livro' => $livro]);
    }
}
