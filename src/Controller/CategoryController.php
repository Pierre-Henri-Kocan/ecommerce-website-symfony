<?php

namespace App\Controller;

use App\Entity\Category;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/category', name: 'category_')]
class CategoryController extends AbstractController
{
    #[Route('/{slug}', name: 'list')]
    public function list(Category $category): Response
    {
        // On récupère la liste des produits associées à la catégorie
        $products = $category->getProducts();

        return $this->render('category/list.html.twig', [
            'category' => $category,
            'products' => $products
        ]);
    }
}

