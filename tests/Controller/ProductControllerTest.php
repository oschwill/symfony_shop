<?php

namespace App\Tests\Controller;

use App\DataFixtures\ProductFixtures;
use App\DataFixtures\ProductPicturesFixtures;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use App\DataFixtures\UserFixtures;
use App\Entity\Product;
use App\Entity\User;
use App\Enum\UserRole;
use Doctrine\Bundle\FixturesBundle\Loader\SymfonyFixturesLoader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ProductControllerTest extends WebTestCase
{
    private ?EntityManagerInterface $entityManager;    
    private UserPasswordHasherInterface $passwordHasher;
    private $originalExceptionHandler;
    private $client;

    protected function setUp(): void
    {
         error_reporting(E_ALL);

        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();
        $this->passwordHasher = $this->client->getContainer()->get(UserPasswordHasherInterface::class);

        // Erstellen Sie das Datenbankschema
        $schemaTool = new SchemaTool($this->entityManager);
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($metadata);// Löschen
        $schemaTool->createSchema($metadata);

        $loader = new SymfonyFixturesLoader(self::$kernel->getContainer());
        $loader = new SymfonyFixturesLoader($this->client->getContainer());

        // Alle Fixtures hier hinzufügen
        $loader->addFixture(new UserFixtures($this->passwordHasher));
        $loader->addFixture(new ProductFixtures());
        $loader->addFixture(new ProductPicturesFixtures());

        $purger = new ORMPurger();
        // $executor = new ORMExecutor($this->entityManager);
        $executor = new ORMExecutor($this->entityManager, $purger);
        // $executor->purge();
        $executor->execute($loader->getFixtures());

        $this->entityManager->clear();

         $this->originalExceptionHandler = set_exception_handler(function($e) {
            // Leerer Handler
        });
        
        // Deaktiviere Fehlerseiten
        static::ensureKernelShutdown();
    }

    public function testSimple()
    {
        $product = $this->entityManager->getRepository(Product::class)->findOneBy([]);
        $this->assertNotNull($product, 'Es sollte mindestens ein Produkt in der Datenbank sein');
   
    }

    public function testProductDetailPageRedirectsWhenProductNotFound()
    {
        $this->client = static::createClient();

        // Die Produkt ID existiert nicht
        $this->client->request('GET', '/product/99999');

        // Weiterleitung erfolgreich auf '/shop'
        $this->assertResponseRedirects('/shop');
        $this->assertResponseStatusCodeSame(302); 
    }

    public function testCreateProduct()
    {
        $this->client = static::createClient();

        /* EINLOGGEN */
        $user = new User();
        $user->setFirstName('Test');
        $user->setLastName('User');
        $user->generateUserName();
        $user->setEmail('admin@example.com');
        $user->setPassword('password'); 
        $user->setCreatedAt(new \DateTime(date("Y-m-d h:i:sa")));
        $user->setRole(UserRole::ADMIN); 
        $user->setActive(true);

        // Speichere den Benutzer in der Datenbank
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // Melde den Benutzer an
        $this->client->loginUser($user);

        // Produktdaten
         $fakeProductData = [
            'data' => [
                'title' => 'Test Produkt',
                'description' => 'Dies ist eine Beschreibung für ein Testprodukt.',
                'price' => 99.99,
                'pictures' => [
                    'https://st2.depositphotos.com/1006542/6566/i/450/depositphotos_65669135-stock-photo-woman-sitting-on-an-old.jpg',
                ],
                'category' => 'Test Kategorie',
            ],
        ];

        // HEADERS
        $this->client->request('POST', '/api/v1/create-product', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',            
        ], json_encode($fakeProductData));

        // Überprüfe die Antwort
        $this->assertResponseStatusCodeSame(201);

        // Überprüfe die JSON-Antwort
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $responseContent);
        $this->assertSame('Produkt erfolgreich erstellt', $responseContent['message']);
        $this->assertArrayHasKey('id', $responseContent);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        if ($this->entityManager) {
            $this->entityManager->close();
            $this->entityManager = null;
        }
    }
}