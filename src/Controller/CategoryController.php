<?php declare(strict_types=1);

namespace App\Controller;

use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/categories', name: 'api_categories_')]
class CategoryController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(
        Request $request,
        CategoryRepository $repo
    ): Response {
        $onlyWithTodo = $request->query->getBoolean('onlyWithTodo', false);

        if ($onlyWithTodo) {
            $categories = $repo->findCategoriesWithOpenTickets();
        } else {
            $categories = $repo->findAll();
        }

        return $this->json($categories, Response::HTTP_OK, [], ['groups' => 'category:read']);
    }
}
