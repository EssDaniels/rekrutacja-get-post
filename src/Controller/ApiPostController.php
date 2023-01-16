<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Post;

class ApiPostController extends AbstractController
{
    public function __construct(private ManagerRegistry $doctrine)
    {
    }

    #[Route('/api/posts', name: 'app_api_posts', methods: 'GET')]
    public function list(): JsonResponse
    {
        $posts = $this->doctrine->getRepository(Post::class)->findAll();
        $arrayCollection = [];

        foreach ($posts as $item) {
            $arrayCollection[] = [
                'id'     => $item->getID(),
                'title'  => $item->getTitle(),
                'body'   => $item->getBody(),
                'author' => $item->getAuthorName()
            ];
        }

        return new JsonResponse($arrayCollection);
    }
}
