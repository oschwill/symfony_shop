<?php
namespace App\Controller\Api;

use App\Entity\Product;
use App\Service\ElasticSearchQueryService;
use App\Service\ProductService;
use FOS\ElasticaBundle\Finder\PaginatedFinderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ElasticSearchQueryController extends AbstractController
{
  public function __construct(private PaginatedFinderInterface $finder, private ProductService $productService, private ElasticSearchQueryService $elasticSearchQueryService)
  {}

  #[Route('/api/v1/elastic-search', name: 'api_elastic_search', methods: ['POST'])]
  public function elasticSearchProduct(Request $request) {
    // search query holn
    $requestData = json_decode($request->getContent(), true);    
    // Den Suchbegriff holn
    $searchTerm = $requestData['data'] ?? '';

    // Popelabfrage
    $query = $this->elasticSearchQueryService->queryOne($searchTerm);

    $returnResult = [];
    $results = $this->finder->find($query, 10);
    
    $this->productService->normalizeData($returnResult,$results);
 
    return new JsonResponse($returnResult);
  }
}