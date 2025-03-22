<?php

namespace App\Service;

use App\Entity\Product;
use App\Enum\LogAction;
use App\Logger\CustomLogger;
use App\Repository\ProductRepository;
use App\Repository\ProductPicturesRepository;
use App\Utility\ProductHandler as UtilityProductHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class ProductService {

  public function __construct(
    private ProductRepository $productRepository,
    private ProductPicturesRepository $productPicturesRepository,
    private EntityManagerInterface $entityManager,
    private CustomLogger $customLogger,
    private Security $security,
    private UtilityProductHandler $productHandler){}

  public function getAllProducts(int $limit, int $offset) : array{
    // Lets get all Products
    $products = $this->productRepository->findAllProductsByLimit($limit, $offset);    
    $allProducts = []; 
    
    // convert to Array Map
    $productData = array_map(function ($product) {
          return $product->toArray();
      }, $products);

    // Daten aufbereiten
    $this->normalizeData($allProducts,$products);    

    // Let us log some Data aoha
    $this->customLogger->logEvent(LogAction::PRODUCT_GETALL, $productData);
  
    return $allProducts;
  }

  public function getTotalProductsCount(){
    return $this->productRepository->countAllProducts(); 
  }

  public function getSingleProduct(int $id): ?Product{
    /* Meine Funktion tap erlaubt eine Callback Funktion eingebaut unter CustomFunctions.php in src/Helper, dann in den Autoloader in composer einbinden und neu dumpen
      Danach noch in den services.yaml den Helper Ordner exclude - "../src/Helper/", da der Symfony AutoLoader standardmäßig nach Klassen sucht
    */
    return tap($this->productRepository->find($id), function($product) use ($id) { // Mit use übergeben wir die Variable der Closure
      if ($product) {
        $this->customLogger->logEvent(LogAction::PRODUCT_GETONE, json_encode($product->toArray(), JSON_PRETTY_PRINT));
      }else{
        $this->customLogger->logEvent(LogAction::CRITICAL_ERROR, sprintf('Produkt nicht vorhanden mit der ID: %s', $id));
      }
    });
  }

  public function createNewProduct($data): array {
    try {
        // Transaction beginnen
        $this->entityManager->getConnection()->beginTransaction();
        
        $product = new Product();
        $this->productHandler->setProductData($product, $data, true);
        
        $currentUser = $this->security->getUser();
        if ($currentUser) {
          $product->setCreatedFrom($currentUser);
        }else {
            throw new \Exception("Kein gültiger Benutzer gefunden");
        }
 
        $this->entityManager->persist($product);
        $this->entityManager->flush();

        if (isset($data['data']['pictures']) && is_array($data['data']['pictures'])) {
                $this->productHandler->processProductPictures($product, $data['data']['pictures']);
        }

        $this->entityManager->flush();
        $this->entityManager->getConnection()->commit();

        // LOGGEN
        $this->customLogger->logEvent(LogAction::PRODUCT_INSERTONE, $product->toArray());
        // returnen den status und die id
        return ['status' => true, 'id' => $product->getId()];
      } catch (\Exception $e) {
        $this->entityManager->rollback(); 
        // LOGGEN
        $this->customLogger->logEvent(LogAction::CRITICAL_ERROR, sprintf('Fehler beim erstellen eines neuen Produktes => %s'. $e->getMessage()));
        return ['status' => false, 'error' => $e->getMessage()];
      }
  }

  // Update Product
  public function updateProduct(Product $product, $data): array {
    try {
        // Transaction beginnen
        $this->entityManager->getConnection()->beginTransaction();
        
        // Produktdaten aktualisieren
        $this->productHandler->setProductData($product, $data, false);

        // Vorhandene Bilder des Produkts entfernen
        $this->productHandler->removeExistingPictures($product);

        if (isset($data['data']['pictures']) && is_array($data['data']['pictures'])) {
                $this->productHandler->processProductPictures($product, $data['data']['pictures']);
        }

        $this->entityManager->flush();
        $this->entityManager->getConnection()->commit();

        // LOGGEN
        $this->customLogger->logEvent(LogAction::PRODUCT_UPDATEONE, $product->toArray());

        // Rückgabe des Status und der ID
        return ['status' => true, 'id' => $product->getId()];
    } catch (\Exception $e) {
        $this->entityManager->rollback();
        
        // LOGGEN
        $this->customLogger->logEvent(LogAction::CRITICAL_ERROR, sprintf('Fehler beim Aktualisieren des Produkts => %s', $e->getMessage()));

        return ['status' => false, 'error' => $e->getMessage()];
    }
  } 

  public function deleteDaProduct ($id): bool {
    //
    try {
      $product = $this->entityManager->getRepository(Product::class)->find($id);

      if (!$product) {
          throw new \Exception('Produkt nicht gefunden.');
      }
      
      // Löschen der zugehörigen Bilder
      foreach ($product->getProductPictures() as $picture) {
          // Entferne das Bild von der Festplatte (falls nötig)
          $picturePath = $picture->getPicturePath();
          if ($picturePath && file_exists($picturePath)) {
              unlink($picturePath); // Löschen der Bilddatei
          }

          // Lösche das Bild aus der Datenbank
          $this->entityManager->remove($picture);
      }

      // Lösche das Produkt aus der Datenbank
      $this->entityManager->remove($product);
      $this->entityManager->flush();

      $this->customLogger->logEvent(LogAction::PRODUCT_DELETEONE, sprintf('Das Produkt mit der ID: %s wurde erfolgreich gelöscht', $id));

      return true;
    } catch (\Exception $e) {
      $this->customLogger->logEvent(LogAction::CRITICAL_ERROR, sprintf('Fehler beim Löschen des Produktes => %s', $e->getMessage()));
      return false;      
    }
  }

  public function normalizeData(&$allProducts, $products): void {   
    foreach ($products as $product) {
        // Convert product to array (assuming toArray() method exists)
        $productData = $product->toArray();

        
        // Fetch associated picture (if any)
        $pictureRepo = $this->productPicturesRepository->findOneBy(['product' => $productData['id']]);
        $picture = $pictureRepo ? $pictureRepo->toArray() : null;

        // Create array for products
        $allProducts[] = [
            'product' => $productData,
            'picture' => $picture
        ];

      }
  }

  public function getProductById(int $id)
  {      
      return $this->productRepository->find($id);
  }
}


