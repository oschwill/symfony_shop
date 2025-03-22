<?php

namespace App\Controller;

use App\Form\Product\ProductFormType;
use App\Service\ProductService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ProductController extends AbstractController
{
    public function __construct(private ProductService $productService)
    {}

    #[Route('/product/insert', name: 'app_product_insert')]
    #[IsGranted('ROLE_ADMIN')]
    public function insertProduct(): Response
    {
         $form = $this->createForm(ProductFormType::class, null, [
            'limit_pictures' => true,  
        ]);

        return $this->render('product/productInsert.html.twig', [
            'controller_name' => 'Produkt erstellen',
            'productForm' => $form->createView(),
        ]);
    }

    #[Route('/product/{id<\d+>}', name: 'app_product_detail')]
    #[Cache(smaxage: 3600, mustRevalidate: true)]
    public function productDetail(int $id): Response
    {
        $product = $this->productService->getSingleProduct($id);
        $form = $this->createForm(ProductFormType::class, $product,  [
            'limit_pictures' => false,  
        ]);

        if (!$product) {
            // Wenn Produkt nicht gefunden wird aus was für Gründen auch immer einfach wieder auf die Shop Products Seite weiterleiten
            return $this->redirectToRoute('app_shop');
        }

        return $this->render('product/productDetails.html.twig', [
            'title_message' => 'Shop',
            'controller_name' => 'Produkt Details',
            'product' => $product,
            'editProductForm' => $form->createView(),
        ]);
    }    
}
