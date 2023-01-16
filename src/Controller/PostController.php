<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\SecurityBundle\Security;
use App\Entity\Post;

#[IsGranted("ROLE_USER")]
class PostController extends AbstractController
{
    public function __construct(private ManagerRegistry $doctrine, private Security $security)
    {
    }

    #[Route('/list', name: 'post_list')]
    public function list(): Response
    {
        $posts = $this->doctrine->getRepository(Post::class)->findAll();
        return $this->render('post/list.html.twig', ['posts' => $posts]);
    }

    #[Route('/post/{id}/delete', name: 'post_delete')]
    public function delete(Post $post): Response
    {
        $entityManager = $this->doctrine->getManager();
        $entityManager->remove($post);
        $entityManager->flush();

        return $this->redirectToRoute('post_list');
    }
}
