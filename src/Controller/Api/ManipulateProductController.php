<?php
namespace App\Controller\Api;

use App\Service\AuthService;
use App\Service\ProductService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ManipulateProductController extends AbstractController
{

    public function __construct(private AuthService $authService, private ProductService $productService)
    {}

    #[Route('/api/v1/create-product', name: 'api_create_product', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function createProduct(Request $request): JsonResponse
    {  
        try {
            // Unsere Daten als Json abfangen
            $data = json_decode($request->getContent(), true);
     
            if (!$data) {
                return new JsonResponse(['error' => 'Fehler beim erstellen des Produktes', 'success' => false], 400);
            }

            // Hier kommt das Speichern der Daten
            $response = $this->productService->createNewProduct($data);

            if ($response['status']) {
                return new JsonResponse(['message' => 'Produkt erfolgreich erstellt', 'id' => $response['id']], 201);
            } else {
                return new JsonResponse(['error' => 'Fehler beim Erstellen des Produktes', 'success' => false], 500);
            }
          
           
        } catch (\Exception $e) {  
            $statusCode = $e->getCode() ?: 500;       
            return new JsonResponse(['error' => $e->getMessage()],  $statusCode);
        }
    }

    #[Route('/api/v1/update-product/{id}', name: 'api_update_product', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function editProduct(Request $request, int $id): JsonResponse
    {  
        try {
        $data = json_decode($request->getContent(), true);

        // Überprüfen ob Daten vorhanden sind
        if (!$data) {
            return new JsonResponse(['error' => 'Fehler beim Aktualisieren des Produkts: keine Daten übermittelt', 'success' => false], 400);
        }

        // Produkt holen
        $product = $this->productService->getProductById($id);

        if (!$product) {
            return new JsonResponse(['error' => 'Produkt nicht gefunden', 'success' => false], 404);
        }

        // Produkt-Daten aktualisieren
        $response = $this->productService->updateProduct($product, $data);

        // Erfolgreiche Aktualisierung
        if ($response['status']) {
            return new JsonResponse(['message' => 'Produkt erfolgreich aktualisiert', 'id' => $response['id']], 200);
        } else {
            // Fehler beim Speichern der aktualisierten Daten
            return new JsonResponse(['error' => 'Fehler beim Aktualisieren des Produkts', 'success' => false], 500);
        }
    } catch (\Exception $e) {
        // Fehlerbehandlung
        $statusCode = $e->getCode() ?: 500;
        return new JsonResponse(['error' => $e->getMessage()], $statusCode);
    }
    }

    #[Route('/api/v1/delete-product', name: 'api_delete_product', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function deleteProduct(Request $request): JsonResponse
    {  
        try {
            $data = json_decode($request->getContent(), true);

            if (!$data) {
                return new JsonResponse(['status' => false], 400);
            }

             $response = $this->productService->deleteDaProduct(intval($data ['data']));

            if ($response) {
                return new JsonResponse(['status' => true], 200);
            } else {
                return new JsonResponse(['status' => false], 400);
            }
        } catch (null) {    
            return new JsonResponse(['status' => false]);
        }
    }
}