<?php

namespace App\Controller;

use App\Service\ElasticSearchQueryService;
use App\Service\ProductService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use FOS\ElasticaBundle\Finder\PaginatedFinderInterface;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class ShopController extends AbstractController
{
    private $limit = 6; // Erstmal hardcoden
    private $offset;

    public function __construct(
        private ProductService $productService,
        private ElasticSearchQueryService $elasticSearchQueryService,
        private PaginatedFinderInterface $finder) 
    {}

    #[Route('/shop/{page}', name: 'app_shop', requirements: ['page' => '\d+'], defaults: ['page' => 1])]
    #[Cache(smaxage: 3600, mustRevalidate: true)]
    public function index(int $page = 1, CacheInterface $cache): Response
    {
        // Pagination
        $this->offset = ($page - 1) * $this->limit;

        // Cache Key
        $productsCacheKey = 'shop_products_page_' . $page;
        $totalProductsCacheKey = 'shop_total_products_count';

        // Lets get all Products 
        $getAllProducts = $cache->get($productsCacheKey, function (ItemInterface $item) {
            $item->expiresAfter(3600); 
            return $this->productService->getAllProducts($this->limit, $this->offset);
        });

        // Cache the ProductCount!
        $totalProducts = $cache->get($totalProductsCacheKey, function (ItemInterface $item) {
            $item->expiresAfter(3600); 
            return $this->productService->getTotalProductsCount();
        });

        $totalPages = ceil($totalProducts / $this->limit); // Berechnung der Gesamtseitenanzahl

        return $this->render('shop/index.html.twig', [            
            'title_message' => 'Shop',
            'getAllProducts' => $getAllProducts,
            'totalProducts' => $totalProducts,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'limit' => $this->limit,
            'isSearch' => false
        ]);
    }

    #[Route('/shop/search/{page}', name: 'app_shop_search')]
    public function search(Request $request, int $page = 1): Response
    {
        $searchTerm = $request->get('search', '');

        if (empty($searchTerm)) {
            return $this->render('shop/index.html.twig', [
                'title_message' => 'Suchergebnisse',
                'getAllProducts' => [],
                'totalProducts' => 0,
                'currentPage' => 1,
                'totalPages' => 1,
                'limit' => $this->limit,
                'searchTerm' => $searchTerm,
                'isSearch' => true,
            ]);
        }
        
        // Elasticsearch Abfrage
        $query = $this->elasticSearchQueryService->queryOne($searchTerm);
        
        $this->offset = ($page - 1) * $this->limit;
        
        // Finde die Ergebnisse
        $returnResult = [];
        $results = $this->finder->find($query, null);

        // Normalisiere die Daten
        $this->productService->normalizeData($returnResult,  $results);
        $resultCount = count($returnResult);

        $pagedResults = array_slice($returnResult, $this->offset, $this->limit);
        
        // Pagination
        $totalPages = ceil($resultCount / $this->limit); 

        return $this->render('shop/index.html.twig', [
            'title_message' => 'Suchergebnisse',
            'getAllProducts' => $pagedResults,
            'totalProducts' => $resultCount,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'limit' => $this->limit,
            'searchTerm' => $searchTerm,
            'isSearch' => true
        ]);
    }
}
