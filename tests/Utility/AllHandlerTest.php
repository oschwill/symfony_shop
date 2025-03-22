<?php

namespace App\Tests\Utility;

use App\Entity\User;
use App\Utility\UploadHandler;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Ramsey\Uuid\Uuid;

class AllHandlerTest extends WebTestCase
{
  private $tempUploadDir;

    protected function setUp(): void
    {
        // Temporären Upload-Pfad festlegen
         $this->tempUploadDir = __DIR__ . '/../test_uploads/uploads';
        if (!is_dir($this->tempUploadDir)) {
            if (!mkdir($this->tempUploadDir, 0777, true) && !is_dir($this->tempUploadDir)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $this->tempUploadDir));
            }
        }
        echo "Temporary upload directory: " . $this->tempUploadDir . "\n";
    }

    public function testHandleProfilePictureUpload(): void
    {    
         $tempFilePath = tempnam($this->tempUploadDir, 'testFile');
        file_put_contents($tempFilePath, 'dummy content');
        echo "Temporary file created: " . $tempFilePath . "\n";

        $uploadedFile = new UploadedFile(
            $tempFilePath,
            'test_image.jpg',
            'image/jpeg',
            null,
            true
        );

        $service = new UploadHandler($this->tempUploadDir);
        $user = new User();
        $changes = [];

        $service->handleProfilePictureUpload($user, $uploadedFile, $changes);
        
        echo "Changes array after upload: " . print_r($changes, true) . "\n";

        // Überprüfen Sie den tatsächlichen Pfad basierend auf $changes['picturePath']
        $this->assertArrayHasKey('picturePath', $changes, "picturePath was not set in changes array");
        $picturePath = ltrim($changes['picturePath'], 'uploads/'); // uploads entfernen, sonst krieg ich ne Krise
        $actualPath = $this->tempUploadDir . '/' .  $picturePath;
        echo "Actual path: " . $actualPath . "\n";

        $this->assertFileExists($actualPath, "The uploaded file does not exist at the expected location");

        $this->assertStringContainsString('uploads/Users/', $changes['picturePath']);
        $this->assertStringEndsWith('.txt', $changes['picturePath']);

        // Überprüfen Sie die Verzeichnisstruktur
        $usersDir = $this->tempUploadDir . '/Users';
        $this->assertTrue(is_dir($usersDir), "Users directory was not created");
        
        $jpgFiles = glob($usersDir . '/*/*.txt');
        echo "TXT files found: " . print_r($jpgFiles, true) . "\n";

        $this->assertNotEmpty($jpgFiles, "No TXT file was created in the Users directory");

        // Aufräumen
        unlink($tempFilePath);
    }

    // protected function tearDown(): void
    // {
    //     if (is_dir($this->tempUploadDir)) {
    //         $files = new \RecursiveIteratorIterator(
    //             new \RecursiveDirectoryIterator($this->tempUploadDir, \RecursiveDirectoryIterator::SKIP_DOTS),
    //             \RecursiveIteratorIterator::CHILD_FIRST
    //         );
    //         foreach ($files as $fileinfo) {
    //             $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
    //             $todo($fileinfo->getRealPath());
    //         }
    //         rmdir($this->tempUploadDir);
    //     }
    // }
}