<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/category', name: 'category_')]
class CategoryController extends AbstractController
{
    #[Route('/{slug}', name: 'list')]
    public function list(Category $category, ProductRepository $productRepository, Request $request): Response
    {
        // On récupère le numéro de page dans l'URL
        $page = $request->query->getInt('page', 1);

        // On récupère la liste des produits associées à la catégorie
        $products = $productRepository->findProductsPaginated($page, $category->getSlug(), 2);

        return $this->render('category/list.html.twig', [
            'category' => $category,
            'products' => $products
        ]);
    }
}

