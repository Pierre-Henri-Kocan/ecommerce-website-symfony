<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Form\ProductFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/product', name: 'admin_product_')]
class ProductController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('admin/product/index.html.twig');
    }

    #[Route('/create', name: 'create')]
    public function create(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // On créé un nouveau produit
        $product = new Product();

        // On créé le formulaire
        $productForm = $this->createForm(ProductFormType::class, $product);

        // On traite la requête du formulaire
        $productForm->handleRequest($request);
       
        // On vérifie que le formulaire est soumis et valdie
        if ($productForm->isSubmitted() && $productForm->isValid()) {
            // On génère le slug
            $slug = $slugger->slug(strtolower($product->getName()));
            $product->setSlug($slug);

            // On arrondit le prix
            $price = $product->getPrice() * 100;
            $product->setPrice($price);

            // On stocke en BDD
            $em->persist($product);
            $em->flush();

            $this->addFlash('success', 'Produit ajouté avec succès');

            // On redirige
            return $this->redirectToRoute('admin_product_index');
        }

        return $this->render('admin/product/create.html.twig', [
            'productForm' => $productForm->createView(),
        ]);
    }

    #[Route('/update/{id}', name: 'update')]
    public function update(Product $product, Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        // On vérifie si l'utilisateur peut éditer avec le Voter
        $this->denyAccessUnlessGranted('PRODUCT_UPDATE', $product);

        // On divise le prix par 100
        $price = $product->getPrice() / 100;
        $product->setPrice($price);

        // On créé le formulaire
        $productForm = $this->createForm(ProductFormType::class, $product);

        // On traite la requête du formulaire
        $productForm->handleRequest($request);
       
        // On vérifie que le formulaire est soumis et valdie
        if ($productForm->isSubmitted() && $productForm->isValid()) {
            // On génère le slug
            $slug = $slugger->slug(strtolower($product->getName()));
            $product->setSlug($slug);

            // On arrondit le prix
            $price = $product->getPrice() * 100;
            $product->setPrice($price);

            // On stocke en BDD
            $em->persist($product);
            $em->flush();

            $this->addFlash('success', 'Produit modifié avec succès');

            // On redirige
            return $this->redirectToRoute('admin_product_index');
        }

        return $this->render('admin/product/update.html.twig', [
            'productForm' => $productForm->createView(),
        ]);
    }

    #[Route('/delete/{id}', name: 'delete')]
    public function delete(Product $product): Response
    {
        // On vérifie si l'utilisateur peut supprimer avec le Voter
        $this->denyAccessUnlessGranted('PRODUCT_DELETE', $product);
        return $this->render('admin/product/index.html.twig');
    }
}