<?php

namespace App\Controller;

use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/product', name: 'product_')]
class ProductController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('product/index.html.twig');
    }

    #[Route('/{slug}', name: 'detail')]
    public function read(Product $product): Response
    {
        //dd($product->getDescription());
        return $this->render('product/read.html.twig', [
            'product' => $product
        ]);
    }
}

