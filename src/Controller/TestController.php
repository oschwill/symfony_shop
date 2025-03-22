<?php
namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\DBAL\Connection;
use Monolog\Logger as MonologLogger;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class TestController extends AbstractController
{


    private $productRepository;
    private $logger;

    public function __construct(ProductRepository $productRepository, ParameterBagInterface $params)
    {
        $this->productRepository = $productRepository;
        // Initen unseren custom log channel
        $this->logger = new MonologLogger('custom');
        // Wir brauchen den Pfad va/logs, den wir uns aus dem Kernel holen
        // $logPath = $params->get('kernel.logs_dir') . '/custom.log'; 
        // $logFilePath =realpath(__DIR__ . '../../../var/logs') .'/custom.log'; 
        $logFilePath = __DIR__ . '/../../var/logs/custom.log';   

        
        echo $logFilePath;

        $this->logger->pushHandler(new \Monolog\Handler\StreamHandler($logFilePath, \Monolog\Logger::DEBUG));        
    }


    #[Route('/test', name: 'app_test')]
    public function index(Connection $connection): Response
    {
        try {
            // Testen der Datenbankverbindung
            $connection->connect();
            
            // Testabfrage
            $result = $connection->fetchOne('SELECT 1');
            
            return new Response('Database connection is working: ' . $result);
        } catch (\Exception $e) {
            return new Response('Database connection failed: ' . $e->getMessage());
        }
    }

    #[Route('/test-tables', name: 'app_test-tables')]
    public function allTables(Connection $connection): Response
    {
        try {
            // Testen der Datenbankverbindung
            $connection->connect();

            // Abrufen der Tabellenliste
            $schemaManager = $connection->createSchemaManager();
            $tables = $schemaManager->listTables();

            $tableNames = array_map(fn($table) => $table->getName(), $tables);

            return new Response('Tables in the database: <br>' . implode('<br>', $tableNames));
        } catch (\Exception $e) {
            return new Response('Database connection failed: ' . $e->getMessage());
        }
    }

    #[Route('/test-productRepo', name: 'app_test-productRepo')]
    public function testProductRepository(){
       try {
            // Alle Produkte abrufen
            $products = $this->productRepository->findAll();

            // Produkte in einem Array formatieren
            $productData = array_map(function ($product) {
                return [
                    'id' => $product->getId(),
                    'name' => $product->getTitle(),
                    'price' => $product->getPrice(),
                    'description' => $product->getDescription(),
                    // Fügen Sie hier andere Eigenschaften hinzu
                ];
            }, $products);

            // Formatierte Daten als JSON ausgeben
            return new Response(
                'PRODUCTDATA=' . json_encode($productData, JSON_PRETTY_PRINT),
                Response::HTTP_OK,
                ['Content-Type' => 'application/json']
            );
        } catch (\Exception $e) {
            return new Response('Error fetching products: ' . $e->getMessage());
        }
    }

    #[Route('/test-logging', name: 'app_test-logging')]
    public function testLogging(){
        $this->logger->info('<<<***GET ALL PRODUCTS START***>>>');
        
        return new Response('Check the logs!');
    }
}